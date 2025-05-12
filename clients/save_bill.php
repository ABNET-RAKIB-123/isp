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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = intval($_POST['client_id']);
    $due_amount = $_POST['due_amount'];
    $received_amount = floatval($_POST['received_amount']);

    $payment_method = $_POST['payment_method'];
    $stmt = $conn->prepare("
        INSERT INTO billing_collection (client_id, collected_by, received_amount, payment_method, received_date)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("iids", $client_id, $Employee_id_databases, $received_amount, $payment_method);
    $stmt->execute();
    // Optional: Update client's billing status to Paid
    // $conn->query("UPDATE service_information SET billing_status = 'paid' WHERE client_id = $client_id");
    // header("Location: list_clients.php?success=1");
    // exit;

    $Due = $due_amount - $received_amount;


// =================================================
// $client_id = intval($_GET['id']);
$client = $conn->query("
    SELECT si.*, npi.server_id
    FROM service_information si
    JOIN network_product_information npi ON si.client_id = npi.client_id
    WHERE si.client_id = $client_id
")->fetch_assoc();
    $payment_date = date('Y-m-d');
    $new_expiry_date = date('Y-m-d', strtotime('+1 month', strtotime($client['expire_date'])));
    $stmt = $conn->prepare("UPDATE service_information SET billing_status='paid', last_payment_date=?, expire_date=?, next_due_date=?, status='active', money_bill=? WHERE client_id=?");
$stmt->bind_param("sssdi", $payment_date, $new_expiry_date, $new_expiry_date, $Due, $client_id);
$stmt->execute();
    // Enable user in MikroTik
    require_once '../api/mikrotik_api.php'; // your mikrotik api class
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
    header('Location: list_clients.php?success=Payment Added Successfully');
    exit();
}
require_once '../includes/footer.php'; ?>