<?php
require_once '../includes/db.php';
require_once '../api/mikrotik_api.php';

$router_id = (int)($_POST['router_id'] ?? 0);

if (!$router_id) {
    echo json_encode([]);
    exit;
}

// Get Router Info
$router = $conn->query("SELECT * FROM routers WHERE id = $router_id")->fetch_assoc();

if (!$router) {
    echo json_encode([]);
    exit;
}

// Connect MikroTik
$API = new RouterosAPI();
$API->port = $router['router_port'];

$interfaces = [];

if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {

    // Get Bridges
    $bridges = $API->comm("/interface/bridge/print");
    foreach ($bridges as $bridge) {
        if (isset($bridge['name'])) {
            $interfaces[] = [
                "name" => $bridge['name'],
                "type" => "Bridge"
            ];
        }
    }

    // Get Ethernet Ports
    $ethernets = $API->comm("/interface/ethernet/print");
    foreach ($ethernets as $ether) {
        if (isset($ether['name'])) {
            $interfaces[] = [
                "name" => $ether['name'],
                "type" => "Ethernet"
            ];
        }
    }

    // Get Wireless Ports (optional if any)
    $wireless = $API->comm("/interface/wireless/print");
    foreach ($wireless as $wifi) {
        if (isset($wifi['name'])) {
            $interfaces[] = [
                "name" => $wifi['name'],
                "type" => "Wireless"
            ];
        }
    }

    $API->disconnect();
}

echo json_encode($interfaces);
exit;
?>
