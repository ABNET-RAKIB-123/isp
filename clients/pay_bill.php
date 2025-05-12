<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: list_clients.php');
    exit();
}

require_once '../includes/db.php';
$client_id = intval($_GET['id']);
$client = $conn->query("
    SELECT si.*, npi.server_id
    FROM service_information si
    JOIN network_product_information npi ON si.client_id = npi.client_id
    WHERE si.client_id = $client_id
")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_date = date('Y-m-d');
    $new_expiry_date = date('Y-m-d', strtotime('+1 month', strtotime($client['expire_date'] ?? '')));

    $stmt = $conn->prepare("
        UPDATE service_information 
        SET billing_status='paid', last_payment_date=?, expire_date=?, next_due_date=?
        WHERE client_id=?
    ");
    $stmt->bind_param("sssi", $payment_date, $new_expiry_date, $new_expiry_date, $client_id);
    $stmt->execute();

    // Enable user in MikroTik
    $server_id = $client['server_id'];
    $server = $conn->query("SELECT router_id FROM servers WHERE id = $server_id")->fetch_assoc();
    if ($server && $server['router_id']) {
        $router = $conn->query("SELECT * FROM routers WHERE id = ".$server['router_id'])->fetch_assoc();
        if ($router) {
            $API = new RouterosAPI();
            $API->port =$router['router_port'];
            if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
                $secrets = $API->comm("/ppp/secret/print", ["?name" => $client['username']]);
                if (!empty($secrets)) {
                    $API->comm("/ppp/secret/set", [
                        ".id" => $secrets[0]['.id'],
                        "disabled" => "no"
                    ]);
                }
                $API->disconnect();
            }
        }
    }

    header('Location: list_clients.php?success=Payment Successful and User Enabled');
    exit();
}
require_once '../includes/header.php'; 
require_once '../api/mikrotik_api.php'; // your mikrotik api class
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Pay Bill - <?= htmlspecialchars($client['username'] ?? '') ?></h2>

    <form action="" method="POST">
        <div class="alert alert-info">
            Last Expiry: <?= htmlspecialchars($client['expire_date'] ?? '') ?><br>
            New Expiry (after payment): <?= date('Y-m-d', strtotime('+1 month', strtotime($client['expire_date'] ?? ''))) ?>
        </div>

        <button type="submit" class="btn btn-success">Confirm Payment</button>
        <a href="list_clients.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
