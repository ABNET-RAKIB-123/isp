<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;

require_once '../includes/db.php';
require_once '../api/mikrotik_api.php';

$router_id = (int)($_POST['router_id'] ?? 0);

if (!$router_id) {
    die("Router not selected!");
}

// Get Router Info
$router = $conn->query("SELECT * FROM routers WHERE id = $router_id")->fetch_assoc();
if (!$router) {
    die("Router not found!");
}

// Connect to Router
$API = new RouterosAPI();
$API->port = $router['router_port'];

if (!$API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
    die("Failed to connect to Router!");
}

// Get Profiles
$profiles = $API->comm("/ppp/profile/print");

$API->disconnect();

if (empty($profiles)) {
    die("No profiles found!");
}

// Insert or Update Profiles into Database
foreach ($profiles as $profile) {
    $profile_name = $profile['name'] ?? '';
    $local_address = $profile['local-address'] ?? '';
    $remote_address = $profile['remote-address'] ?? '';
    $rate_limit = $profile['rate-limit'] ?? '';
    $comment = $profile['comment'] ?? '';
    
    if (empty($profile_name)) continue; // Skip invalid entries

    // Check if Profile already exists
    $check = $conn->prepare("SELECT id FROM profiles WHERE router_id=? AND profile_name=?");
    $check->bind_param("is", $router_id, $profile_name);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Update existing
        $update = $conn->prepare("UPDATE profiles SET local_address=?, remote_address=?, rate_limit=?, comment=? WHERE router_id=? AND profile_name=?");
        $update->bind_param("ssssis", $local_address, $remote_address, $rate_limit, $comment, $router_id, $profile_name);
        $update->execute();
    } else {
        // Insert new
        $insert = $conn->prepare("INSERT INTO profiles (router_id, profile_name, local_address, remote_address, rate_limit, comment) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("isssss", $router_id, $profile_name, $local_address, $remote_address, $rate_limit, $comment);
        $insert->execute();
    }
    header("Location: list_profiles.php");
}

// echo "<h3>✅ Profiles Imported/Updated Successfully!</h3>";
// echo "<a href='list_profiles.php' class='btn btn-primary'>View Profiles</a>";
?>
