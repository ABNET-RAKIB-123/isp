<?php
session_start();
require_once '../includes/db.php';
require_once '../api/mikrotik_api.php';

$router_id = (int)($_POST['router_id']);
$profile_name = trim($_POST['profile_name']);
$local_address = trim($_POST['local_address']);
$remote_address = trim($_POST['remote_address']);
$dns_servers = trim($_POST['dns_servers']);
$rate_limit = trim($_POST['rate_limit']);
$comment = trim($_POST['comment']);

// Insert into database
// $stmt = $conn->prepare("INSERT INTO profiles (router_id, profile_name, local_address, remote_address, rate_limit, comment) VALUES (?, ?, ?, ?, ?, ?)");
// $stmt->bind_param("isssss", $router_id, $profile_name, $local_address, $remote_address, $rate_limit, $comment);
// $stmt->execute();


$stmt = $conn->prepare("INSERT INTO profiles (router_id, profile_name, local_address, remote_address, rate_limit, comment, dns_servers) 
VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssss", $router_id, $profile_name, $local_address, $remote_address, $rate_limit, $comment, $dns_servers);
$stmt->execute();


// Create on Router
$router = $conn->query("SELECT * FROM routers WHERE id = $router_id")->fetch_assoc();
if ($router) {
    $API = new RouterosAPI();
    $API->port = $router['router_port'];

    if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
        $API->comm("/ppp/profile/add", [
            "name" => $profile_name,
            "local-address" => $local_address,
            "remote-address" => $remote_address,
            "rate-limit" => $rate_limit,
            "comment" => $comment
        ]);
        $API->disconnect();
    }
}

header('Location: list_profiles.php?success=Profile Added Successfully');
exit;
?>
