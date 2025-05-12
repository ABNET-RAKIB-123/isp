<?php
session_start();
require_once '../includes/db.php';
require_once '../api/mikrotik_api.php';

$id = (int)($_POST['id']);
$profile_name = trim($_POST['profile_name']);
$local_address = trim($_POST['local_address']);
$remote_address = trim($_POST['remote_address']);
$rate_limit = trim($_POST['rate_limit']);
$comment = trim($_POST['comment']);

if (!$id) {
    die("Invalid Profile ID!");
}

// Load old profile
$profile = $conn->query("SELECT * FROM profiles WHERE id = $id")->fetch_assoc();
if (!$profile) {
    die("Profile not found!");
}

$router_id = $profile['router_id'];

// Update in Database
$stmt = $conn->prepare("UPDATE profiles SET profile_name=?, local_address=?, remote_address=?, rate_limit=?, comment=? WHERE id=?");
$stmt->bind_param("sssssi", $profile_name, $local_address, $remote_address, $rate_limit, $comment, $id);
$stmt->execute();

// Update in Router
$router = $conn->query("SELECT * FROM routers WHERE id = $router_id")->fetch_assoc();
if ($router) {
    $API = new RouterosAPI();
    $API->port = $router['router_port'];

    if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
        // Find the Profile on Router
        $router_profiles = $API->comm("/ppp/profile/print", ["?name" => $profile['profile_name']]);
        if (!empty($router_profiles) && isset($router_profiles[0]['.id'])) {
            $API->comm("/ppp/profile/set", [
                ".id" => $router_profiles[0]['.id'],
                "name" => $profile_name,
                "local-address" => $local_address,
                "remote-address" => $remote_address,
                "rate-limit" => $rate_limit,
                "comment" => $comment
            ]);
        }
        $API->disconnect();
    }
}

header('Location: list_profiles.php?success=Profile Updated Successfully');
exit;
?>
