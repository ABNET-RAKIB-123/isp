<?php
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../api/mikrotik_api.php');

// ✅ Auto Suspend ON/OFF Check
$statusQuery = $conn->query("SELECT value FROM settings WHERE `key` = 'auto_suspend_on'");
$autoSuspend = $statusQuery->fetch_assoc()['value'];

if ($autoSuspend != '1') {
    exit("Auto suspend is OFF.\n");
}

// Today's date
$today = date('Y-m-d');

// Find all clients where expire_date < today
$stmt = $conn->query("
    SELECT si.username, si.client_id, si.profile_id, npi.server_id
    FROM service_information si
    JOIN network_product_information npi ON si.client_id = npi.client_id
    WHERE si.expire_date < '$today'
");

// Loop expired clients
while ($client = $stmt->fetch_assoc()) {
    $username = $client['username'];
    $client_id = $client['client_id'];
    $server_id = $client['server_id'];

    // Find router linked to server
    $server = $conn->query("SELECT router_id FROM servers WHERE id = $server_id")->fetch_assoc();
    if ($server && $server['router_id']) {
        $router_id = $server['router_id'];

        $router = $conn->query("SELECT * FROM routers WHERE id = $router_id")->fetch_assoc();
        if ($router) {
            $API = new RouterosAPI();
            $API->port = $router['router_port'];
            if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {

                // Find the PPPoE user
                $secrets = $API->comm("/ppp/secret/print", [
                    "?name" => $username
                ]);

                if (!empty($secrets)) {
                    $secret_id = $secrets[0]['.id'];

                    // Disable the user
                    $API->comm("/ppp/secret/set", [
                        ".id" => $secret_id,
                        "disabled" => "yes"
                    ]);
                    // Remove active PPPoE sessions
                    $active_sessions = $API->comm("/ppp/active/print", [
                        "?name" => $username
                    ]);

                    if (!empty($active_sessions)) {
                        foreach ($active_sessions as $session) {
                            $API->comm("/ppp/active/remove", [
                                ".id" => $session[".id"]
                            ]);
                        }
                    }

                    // Update service_information status to 'inactive'
                    $conn->query("UPDATE service_information SET status = 'inactive' WHERE client_id = $client_id");

                    echo "Suspended and updated status for: $username<br>";
                } else {
                    echo "PPPoE user not found: $username<br>";
                }

                $API->disconnect();
            } else {
                echo "Cannot connect to router for $username<br>";
            }
        }
    }
}

echo "Finished checking expired clients.";
?>