<?php
require_once '../includes/db.php';
require_once '../api/mikrotik_api.php';

$router_id = (int)($_GET['router_id'] ?? 0);
if (!$router_id) {
    echo "<option value=''>Select Router First</option>";
    exit;
}

$router = $conn->query("SELECT * FROM routers WHERE id = $router_id")->fetch_assoc();
if (!$router) {
    echo "<option value=''>Router Not Found</option>";
    exit;
}

$API = new RouterosAPI();
$API->port = $router['router_port'];

if (!$API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
    echo "<option value=''>Failed to connect</option>";
    exit;
}

$pools = $API->comm("/ip/pool/print");

$API->disconnect();

if (empty($pools)) {
    echo "<option value=''>No Pools Found</option>";
    exit;
}

foreach ($pools as $pool) {
    echo "<option value='".htmlspecialchars($pool['name'])."'>".htmlspecialchars($pool['name'])."</option>";
}
?>
