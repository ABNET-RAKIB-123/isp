<?php
require_once '../api/mikrotik_api.php'; // Your RouterOSAPI class

function getOnlinePPPoEUsers($router_ip, $router_username, $router_password, $router_port) {
    $API = new RouterosAPI();
    $API->port = $router_port;
    $online_users = [];

    if ($API->connect($router_ip, $router_username, $router_password)) {
        $active_users = $API->comm("/ppp/active/print");

        foreach ($active_users as $user) {
            $online_users[] = $user['name'];
        }
        $API->disconnect();
    }
    return $online_users;
}
?>
