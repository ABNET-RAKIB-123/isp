<?php
session_start();
require_once '../includes/db.php';
require_once '../api/mikrotik_api.php'; // MikroTik API

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header("Location: list_bridges.php?error=Bridge ID missing");
    exit;
}

// Get bridge info before delete
$stmt = $conn->prepare("
    SELECT b.*, r.router_ip, r.router_username, r.router_password, r.router_port 
    FROM bridges b 
    JOIN routers r ON b.router_id = r.id 
    WHERE b.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$bridge = $stmt->get_result()->fetch_assoc();

if (!$bridge) {
    header("Location: list_bridges.php?error=Bridge not found");
    exit;
}

// Connect to Router
$API = new RouterosAPI();
$API->port = $bridge['router_port'];

if ($API->connect($bridge['router_ip'], $bridge['router_username'], $bridge['router_password'])) {
    // Find Bridge
    $bridges = $API->comm("/interface/bridge/print", ["?name" => $bridge['bridge_name']]);

    if (!empty($bridges)) {
        $bridge_id_mikrotik = $bridges[0]['.id'];

        // Find Ports on the Bridge
        $ports = $API->comm("/interface/bridge/port/print", ["?bridge" => $bridge_id_mikrotik]);

        // Remove Ports from Bridge
        if (!empty($ports)) {
            foreach ($ports as $port) {
                $API->comm("/interface/bridge/port/remove", [
                    ".id" => $port['.id']
                ]);
            }
        }

        // Remove Bridge
        $API->comm("/interface/bridge/remove", [
            ".id" => $bridge_id_mikrotik
        ]);
    }

    $API->disconnect();
}


// Delete from Database
$stmt = $conn->prepare("DELETE FROM bridges WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt = $conn->prepare("DELETE FROM bridge_ports WHERE bridge_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: list_bridges.php?success=Bridge deleted from DB and Router");
exit;
?>
