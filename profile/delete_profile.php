<?php
session_start();
require_once '../includes/db.php';
require_once '../api/mikrotik_api.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    die("Invalid Profile ID!");
}

// Find profile
$profile = $conn->query("SELECT * FROM profiles WHERE id = $id")->fetch_assoc();
if (!$profile) {
    die("Profile not found!");
}

$router = $conn->query("SELECT * FROM routers WHERE id = {$profile['router_id']}")->fetch_assoc();

// Delete from router
if ($router) {
    $API = new RouterosAPI();
    $API->port = $router['router_port'];

    if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
        // Find Profile ID on Router
        $profiles = $API->comm("/ppp/profile/print", ["?name" => $profile['profile_name']]);
        if (!empty($profiles) && isset($profiles[0]['.id'])) {
            $API->comm("/ppp/profile/remove", [".id" => $profiles[0]['.id']]);
        }
        $API->disconnect();
    }
}

// Delete from Database
$conn->query("DELETE FROM profiles WHERE id = $id");

header('Location: list_profiles.php?success=Profile Deleted Successfully');
exit;
?>
