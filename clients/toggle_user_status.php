<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}

$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;
$Employee_id_databases = $_SESSION['id'] ?? 0;

require_once '../includes/db.php';
require_once '../api/mikrotik_api.php';

if (isset($_POST['id']) && isset($_POST['action'])) {
    $client_id = intval($_POST['id']);
    $action = $_POST['action']; // "enable" or "disable"

    // Fetch username and server_id
    $stmt = $conn->prepare("
        SELECT si.username, npi.server_id
        FROM service_information si
        JOIN network_product_information npi ON si.client_id = npi.client_id
        WHERE si.client_id = ?
    ");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $client = $stmt->get_result()->fetch_assoc();

    if ($client) {
        $username = $client['username'];
        $server_id = $client['server_id'];

        // Find router
        $server = $conn->query("SELECT router_id FROM servers WHERE id = $server_id")->fetch_assoc();
        if ($server && $server['router_id']) {
            $router_id = $server['router_id'];

            $router = $conn->query("SELECT * FROM routers WHERE id = $router_id")->fetch_assoc();
            if ($router) {
                $API = new RouterosAPI();
                $API->port = $router['router_port'];

                if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {

                    // Find PPPoE user secret
                    $secrets = $API->comm("/ppp/secret/print", [
                        "?name" => $username
                    ]);

                    if (!empty($secrets)) {
                        $secret_id = $secrets[0]['.id'];

                        if ($action == "disable") {
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

                            // Update database
                            $conn->query("UPDATE service_information SET status = 'inactive' WHERE client_id = $client_id");
                        } elseif ($action == "enable") {
                            // Enable the user
                            $API->comm("/ppp/secret/set", [
                                ".id" => $secret_id,
                                "disabled" => "no"
                            ]);

                            // Update database
                            $conn->query("UPDATE service_information SET status = 'active' WHERE client_id = $client_id");
                        }
                    }

                    $API->disconnect();
                }
            }
        }
    }
        header("Location: list_clients.php");
    // header("Location: list_clients.php?success=User " . ($action == 'disable' ? 'Disabled' : 'Enabled') . " Successfully");
    exit();
}
?>
