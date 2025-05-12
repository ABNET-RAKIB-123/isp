<?php
session_start();
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
    die("No profiles found on router!");
}

// Clear old profiles for this router
$conn->query("DELETE FROM profiles WHERE router_id = $router_id");

// Insert profiles
$stmt = $conn->prepare("INSERT INTO profiles (router_id, profile_name, local_address, remote_address, rate_limit, comment) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($profiles as $profile) {
    $router_id_val = $router_id;
    $profile_name = $profile['name'] ?? '';
    $local_address = $profile['local-address'] ?? '';
    $remote_address = $profile['remote-address'] ?? '';
    $rate_limit = $profile['rate-limit'] ?? '';
    $comment = $profile['comment'] ?? '';

    $stmt->bind_param("isssss", $router_id_val, $profile_name, $local_address, $remote_address, $rate_limit, $comment);
    $stmt->execute();
}

echo "<h3>✅ Profiles Imported Successfully!</h3>";
echo "<a href='list_profiles.php' class='btn btn-primary'>View Profiles</a>";
?>
