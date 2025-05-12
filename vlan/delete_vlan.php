<?php
session_start();
require_once '../includes/db.php';
require_once '../api/mikrotik_api.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header("Location: list_vlans.php?error=VLAN ID missing");
    exit;
}

// Get VLAN + Router Info
$vlan = $conn->query("
    SELECT v.*, b.bridge_name, r.router_ip, r.router_username, r.router_password, r.router_port 
    FROM vlans v 
    JOIN bridges b ON v.bridge_id = b.id 
    JOIN routers r ON b.router_id = r.id 
    WHERE v.id = $id
")->fetch_assoc();

if (!$vlan) {
    header("Location: list_vlans.php?error=VLAN not found");
    exit;
}

// Connect and Remove VLAN Interface
$API = new RouterosAPI();
$API->port = $vlan['router_port'];

if ($API->connect($vlan['router_ip'], $vlan['router_username'], $vlan['router_password'])) {
    $vlan_interface_name = 'vlan' . $vlan['vlan_id'];

    $interfaces = $API->comm("/interface/vlan/print", ["?name" => $vlan_interface_name]);

    if (!empty($interfaces)) {
        $API->comm("/interface/vlan/remove", [
            ".id" => $interfaces[0]['.id']
        ]);
    }

    $API->disconnect();
}

// Delete from Database
$conn->query("DELETE FROM vlans WHERE id = $id");

header("Location: list_vlans.php?success=VLAN deleted from Database and Router");
exit;
?>
