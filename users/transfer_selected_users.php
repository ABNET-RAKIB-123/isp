<?php
session_start();
require_once '../includes/db.php';
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../api/mikrotik_api.php';

$source_router_id = (int)($_POST['source_router_id'] ?? 0);
$destination_router_id = (int)($_POST['destination_router_id'] ?? 0);
$selected_users = $_POST['selected_users'] ?? [];
$delete_after_transfer = isset($_POST['delete_after_transfer']) ? true : false;

if (!$source_router_id || !$destination_router_id || empty($selected_users)) {
    die("Please select source router, destination router and users!");
}

// Connect to Source Router
$source_router = $conn->query("SELECT * FROM routers WHERE id = $source_router_id")->fetch_assoc();
$destination_router = $conn->query("SELECT * FROM routers WHERE id = $destination_router_id")->fetch_assoc();

if (!$source_router || !$destination_router) {
    die("Router information missing!");
}

$sourceAPI = new RouterosAPI();
$sourceAPI->port = $source_router['router_port'];

if (!$sourceAPI->connect($source_router['router_ip'], $source_router['router_username'], $source_router['router_password'])) {
    die("Failed to connect to Source Router!");
}

$secrets = $sourceAPI->comm("/ppp/secret/print");

// Destination Router Connect
$destinationAPI = new RouterosAPI();
$destinationAPI->port = $destination_router['router_port'];

if (!$destinationAPI->connect($destination_router['router_ip'], $destination_router['router_username'], $destination_router['router_password'])) {
    $sourceAPI->disconnect();
    die("Failed to connect to Destination Router!");
}

// Get Existing Destination Users
$existing_users = $destinationAPI->comm("/ppp/secret/print");
$existing_usernames = array_column($existing_users, 'name');

$existing_profiles = $destinationAPI->comm("/ppp/profile/print");
$profile_names = array_column($existing_profiles, 'name');

// Transfer Counter
$transfer_count = 0;
$skipped_count = 0;
$error_count = 0;

foreach ($secrets as $secret) {
    if (!in_array($secret['name'], $selected_users)) {
        continue;
    }

    $service = $secret['service'] ?? 'pppoe';
    $profile = $secret['profile'] ?? 'default';

    if (in_array($secret['name'], $existing_usernames)) {
        $skipped_count++;
        continue;
    }

    // ✨ Step 1: Check Profile Exists or Not
    if (!in_array($profile, $profile_names)) {
        // ✨ Step 2: Profile Missing ➔ Create it!
        $destinationAPI->comm("/ppp/profile/add", [
            "name" => $profile,
            "local-address" => "0.0.0.0",
            "remote-address" => "0.0.0.0"
        ]);

        // ✨ Step 3: Update local profile list
        $profile_names[] = $profile;
    }

    // Now Add User
    $destinationAPI->comm("/ppp/secret/add", [
        "name" => $secret['name'],
        "password" => $secret['password'],
        "service" => $service,
        "profile" => $profile,
        // "comment" => $secret['comment'] ?? '',
        // "remote-address" => $secret['remote-address'] ?? '',
        // "local-address" => $secret['local-address'] ?? '',
        // "disabled" => $secret['disabled'] ?? 'no'
    ]);

    $transfer_count++;

    if ($delete_after_transfer) {
        $found_users = $sourceAPI->comm("/ppp/secret/print", [
            "?name" => $secret['name']
        ]);

        if (!empty($found_users) && isset($found_users[0]['.id'])) {
            $sourceAPI->comm("/ppp/secret/remove", [
                ".id" => $user_id
            ]);
        }
    }
}


$sourceAPI->disconnect();
$destinationAPI->disconnect();

echo "<h3>✅ Selected Users Transfer Completed!</h3>";
echo "<p>Users Transferred: <b>{$transfer_count}</b></p>";
echo "<p>Users Skipped: <b>{$skipped_count}</b></p>";
echo "<p>Errors: <b>{$error_count}</b></p>";

?>
