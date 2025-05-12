<?php
session_start();
require_once '../includes/db.php';
require_once '../api/mikrotik_api.php'; // MikroTik API class

if (isset($_POST['id'])) {
    $client_id = intval($_POST['id']);

    // Step 1: Find username, server_id, profile_id
    $stmt = $conn->prepare("
        SELECT si.username, npi.server_id
        FROM service_information si
        JOIN network_product_information npi ON si.client_id = npi.client_id
        WHERE si.client_id = ?
    ");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $client = $result->fetch_assoc();

    if ($client) {
        $username = $client['username'];
        $server_id = $client['server_id'];

        // Step 2: Find Router connected to Server
        $stmt = $conn->prepare("SELECT router_id FROM servers WHERE id = ?");
        $stmt->bind_param("i", $server_id);
        $stmt->execute();
        $server = $stmt->get_result()->fetch_assoc();

        if ($server && $server['router_id']) {
            $router_id = $server['router_id'];

            $stmt = $conn->prepare("SELECT * FROM routers WHERE id = ?");
            $stmt->bind_param("i", $router_id);
            $stmt->execute();
            $router = $stmt->get_result()->fetch_assoc();

            if ($router) {
                $API = new RouterosAPI();
                $API->port = $router['router_port'];
                if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
                    
                    // Step 3: Find the PPPoE secret ID first
                    $secrets = $API->comm("/ppp/secret/print", [
                        "?name" => $username
                    ]);

                    if (!empty($secrets)) {
                        $secret_id = $secrets[0]['.id'];
                        $API->comm("/ppp/secret/remove", [
                            ".id" => $secret_id
                        ]);
                    }
                    
                    $API->disconnect();
                }
            }
        }
    }

    // Step 4: Remove user from database
    $conn->query("DELETE FROM service_information WHERE client_id = $client_id");
    $conn->query("DELETE FROM network_product_information WHERE client_id = $client_id");
    $conn->query("DELETE FROM contact_information WHERE client_id = $client_id");
    $conn->query("DELETE FROM clients WHERE id = $client_id");

    header('Location: list_clients.php?success=Client deleted successfully.');
    exit();
}
?>
