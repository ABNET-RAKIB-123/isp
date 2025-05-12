<?php
require_once '../includes/db.php';
require_once '../api/mikrotik_api.php';

$router_id = $_GET['router_id'] ?? 0;
$interfaces = [];

if ($router_id) {
    $router = $conn->query("SELECT * FROM routers WHERE id = $router_id")->fetch_assoc();

    if ($router) {
        $API = new RouterosAPI();
        $API->port = $router['router_port'];
        if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
            $interfaces = $API->comm("/interface/print");
            $API->disconnect();
        }
    }
}

header('Content-Type: application/json');
echo json_encode($interfaces);
