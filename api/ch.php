<?php
require('mikrotik_api.php'); // Adjust path

$API = new RouterosAPI();
$API->port = $api_port = 8028;
// MikroTik Login from database (example)
$router_ip = '103.14.151.25';
$api_user = 'NOC';
$api_pass = 'NOC@#321';

if ($API->connect($router_ip, $api_user, $api_pass)) {

    // Get all users
    $pppoe_users = $API->comm('/ppp/secret/print');
    
    // Get active users (online now)
    $active_users = $API->comm('/ppp/active/print');
    $active_map = [];
    foreach ($active_users as $active) {
        $active_map[$active['name']] = $active;
    }

    echo "<table border='1'>";
    echo "<tr><th>Username</th><th>Status</th><th>Uptime</th><th>Speed</th><th>Last Logged In</th></tr>";

    foreach ($pppoe_users as $user) {
        $username = $user['name'];
        $status = isset($active_map[$username]) ? 'Online' : 'Offline';
        $uptime = $status === 'Online' ? $active_map[$username]['uptime'] : '-';

        // Get speed only if online
        $speed = '-';
        if ($status === 'Online') {
            $interface = $active_map[$username]['interface'];

            $interface_info = $API->comm('/interface/monitor-traffic', [
                "interface" => $interface,
                "once" => ""
            ]);

            $tx = $interface_info[0]['tx-bits-per-second'] ?? 0;
            $rx = $interface_info[0]['rx-bits-per-second'] ?? 0;

            $speed = "TX: " . round($tx / 1024, 2) . " kbps | RX: " . round($rx / 1024, 2) . " kbps";
        }

        $last_logged_in = $user['last-logged-out'] ?? 'N/A';

        echo "<tr>
                <td>$username</td>
                <td>$status</td>
                <td>$uptime</td>
                <td>$speed</td>
                <td>$last_logged_in</td>
              </tr>";
    }

    echo "</table>";

    $API->disconnect();

} else {
    echo "❌ Failed to connect to MikroTik.";
}

?>
