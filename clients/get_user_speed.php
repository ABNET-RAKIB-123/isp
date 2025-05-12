<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;
require_once '../api/mikrotik_api.php'; // Include your RouterOS API connection class

// Read POST username
$username = $_POST['username'] ?? '';

if (empty($username)) {
    echo json_encode(['status' => 'offline', 'tx_speed' => 0, 'rx_speed' => 0]);
    exit;
}

// Router Connection
$router_ip = '103.14.151.24'; // YOUR Router IP
$router_username = 'NOC'; // Router Username
$router_password = 'NOC@#321'; // Router Password
$router_port = 18710; // Router API port

$response = [
    'status' => 'offline',
    'tx_speed' => 0,
    'rx_speed' => 0
];

$API = new RouterosAPI();
$API->port = $router_port;

if ($API->connect($router_ip, $router_username, $router_password)) {
    
    // Fetch active PPP user by username
    $active_users = $API->comm("/ppp/active/print", [
        "?name" => $username
    ]);

    if (!empty($active_users)) {
        $response['status'] = 'online';
        
        // tx-byte and rx-byte are in bytes
        $tx = $active_users[0]['tx-byte'] ?? 0;
        $rx = $active_users[0]['rx-byte'] ?? 0;

        // Convert bytes to Megabits per second (Mbps)
        // Rough estimation because real speed needs timing difference!
        $response['tx_speed'] = round(($tx / 1024 / 1024) * 8, 2); // Mbps
        $response['rx_speed'] = round(($rx / 1024 / 1024) * 8, 2); // Mbps
    }

    $API->disconnect();
}

echo json_encode($response);
?>
