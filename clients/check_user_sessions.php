<?php
// file_put_contents(__DIR__ . '/../cron/log.txt', date('Y-m-d H:i:s') . " - Cron started\n", FILE_APPEND);
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../api/mikrotik_api.php');

$now = date("Y-m-d H:i:s");

// Get all active sessions with no logout
$sessions = $conn->query("SELECT * FROM user_sessions WHERE logout_time IS NULL");

while ($row = $sessions->fetch_assoc()) {
    $username = $row['username'];
    $router_id = $row['router_id'];
    $session_id = $row['id'];
    $login_time = new DateTime($row['login_time']);

    // Get router credentials
    $stmt = $conn->prepare("SELECT * FROM routers WHERE id = ?");
    $stmt->bind_param("i", $router_id);
    $stmt->execute();
    $router = $stmt->get_result()->fetch_assoc();

    if (!$router) continue;

    $API = new RouterosAPI();
    $API->port = $router['router_port'];

    $is_online = false;
    $tx_byte = 0;
    $rx_byte = 0;
    $ip_address = '';

    if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
        $active_users = $API->comm("/ppp/active/print", [".proplist" => "name,address,rx-byte,tx-byte"]);
        foreach ($active_users as $user) {
            if ($user['name'] == $username) {
                $is_online = true;
                $tx_byte = $user['tx-byte'] ?? 0;
                $rx_byte = $user['rx-byte'] ?? 0;
                $ip_address = $user['address'] ?? '';
                break;
            }
        }
        $API->disconnect();
    }

    // If offline, update logout info
    if (!$is_online) {
        $logout_time = new DateTime();
        $duration = $login_time->diff($logout_time);
        $minutes = ($duration->h * 60) + $duration->i;

        $update = $conn->prepare("UPDATE user_sessions SET logout_time = ?, session_duration = ?, tx_bytes = ?, rx_bytes = ? WHERE id = ?");
        $update->bind_param("siiii", $now, $minutes, $tx_byte, $rx_byte, $session_id);
        $update->execute();
    }
}
?>
