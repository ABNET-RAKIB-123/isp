<?php
require_once '../includes/db.php';
require_once 'mikrotik_api.php';

$router_id = $_GET['router_id'] ?? null;
$interface = $_GET['interface'] ?? null;

header('Content-Type: application/json');

if (!$router_id || !$interface) {
    echo json_encode(['tx' => 0, 'rx' => 0]);
    exit;
}

// Get router info
$stmt = $conn->prepare("SELECT * FROM routers WHERE id = ?");
$stmt->bind_param("i", $router_id);
$stmt->execute();
$router = $stmt->get_result()->fetch_assoc();

if (!$router) {
    echo json_encode(['tx' => 0, 'rx' => 0]);
    exit;
}

$API = new RouterosAPI();
$API->port = $router['router_port'];

$tx = 0;
$rx = 0;

if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
    $API->write('/interface/monitor-traffic', false);
    $API->write('=interface=' . $interface, false);
    $API->write('=once=');
    $traffic = $API->read();
    if (isset($traffic[0])) {
        $rx = isset($traffic[0]['rx-bits-per-second']) ? round($traffic[0]['rx-bits-per-second'] / 1024 / 1024, 2) : 0;
        $tx = isset($traffic[0]['tx-bits-per-second']) ? round($traffic[0]['tx-bits-per-second'] / 1024 / 1024, 2) : 0;
    }
    $API->disconnect();
}

echo json_encode(['tx' => $tx, 'rx' => $rx]);
