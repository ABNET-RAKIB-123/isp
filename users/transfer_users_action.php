<?php
// Show errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../admin/login.php");
    exit;
}
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['id'] ?? 0;

require_once '../includes/db.php';
require_once '../api/mikrotik_api.php';

$source_router_id = (int)($_POST['source_router_id'] ?? 0);
$destination_router_id = (int)($_POST['destination_router_id'] ?? 0);
$service_profile = trim($_POST['service_profile'] ?? '');
$delete_after_transfer = isset($_POST['delete_after_transfer']) ? true : false;

if (!$source_router_id || !$destination_router_id) {
    die("Source and Destination router must be selected!");
}

$source_router = $conn->query("SELECT * FROM routers WHERE id = $source_router_id")->fetch_assoc();
$destination_router = $conn->query("SELECT * FROM routers WHERE id = $destination_router_id")->fetch_assoc();

if (!$source_router || !$destination_router) {
    die("Router information missing!");
}

// Connect to Source Router
$sourceAPI = new RouterosAPI();
$sourceAPI->port = $source_router['router_port'];

if (!$sourceAPI->connect($source_router['router_ip'], $source_router['router_username'], $source_router['router_password'])) {
    die("Failed to connect Source Router!");
}

$secrets = $sourceAPI->comm("/ppp/secret/print");

// Connect to Destination Router
$destinationAPI = new RouterosAPI();
$destinationAPI->port = $destination_router['router_port'];

if (!$destinationAPI->connect($destination_router['router_ip'], $destination_router['router_username'], $destination_router['router_password'])) {
    $sourceAPI->disconnect();
    die("Failed to connect Destination Router!");
}

// Get Existing Destination Users and Profiles
$existing_users = $destinationAPI->comm("/ppp/secret/print");
$existing_usernames = array_column($existing_users, 'name');

$existing_profiles = $destinationAPI->comm("/ppp/profile/print");
$profile_names = array_column($existing_profiles, 'name');

$transfer_count = 0;
$skipped_count = 0;
$error_count = 0;

foreach ($secrets as $secret) {
    if (!empty($service_profile)) {
        if (($secret['profile'] ?? '') != $service_profile) {
            continue; 
        }
    }

    $service = !empty($secret['service']) ? $secret['service'] : 'pppoe'; 

    if (in_array($secret['name'], $existing_usernames)) {
        $skipped_count++;
        continue;
    }

    // 💥 Step: If Profile Missing ➔ Auto Create
    $user_profile = $secret['profile'] ?? 'default';
    if (!in_array($user_profile, $profile_names)) {
        // Profile not exists ➔ Create
        $destinationAPI->comm("/ppp/profile/add", [
            "name" => $user_profile,
            "local-address" => "0.0.0.0",
            "remote-address" => "0.0.0.0"
        ]);
        // Update local list
        $profile_names[] = $user_profile;
    }

    // Then Add to Destination Router
    $destinationAPI->comm("/ppp/secret/add", [
        "name" => $secret['name'],
        "password" => $secret['password'],
        "service" => $service,
        "profile" => $user_profile,
        // "comment" => $secret['comment'] ?? '',
        // "remote-address" => $secret['remote-address'] ?? '',
        // "local-address" => $secret['local-address'] ?? '',
        // "disabled" => $secret['disabled'] ?? 'no'
    ]);

    $transfer_count++;

    // After add, delete from source if needed
    if ($delete_after_transfer) {
        $found_users = $sourceAPI->comm("/ppp/secret/print", [
            "?name" => $secret['name']
        ]);

        if (!empty($found_users) && isset($found_users[0]['.id'])) {
            $user_id = $found_users[0]['.id'];

            $sourceAPI->comm("/ppp/secret/remove", [
                ".id" => $user_id
            ]);
        }
    }
}

$sourceAPI->disconnect();
$destinationAPI->disconnect();

// Final Report
echo "<h3>✅ Transfer Completed!</h3>";
echo "<p>Users Transferred: <b>{$transfer_count}</b></p>";
echo "<p>Users Skipped (Already Exist): <b>{$skipped_count}</b></p>";
echo "<p>Errors: <b>{$error_count}</b></p>";

?>
