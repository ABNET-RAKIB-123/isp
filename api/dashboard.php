
<?php
session_start();

// 🔒 Check login
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
// 🧑‍💼 Logged in User Info
$employee_name = $_SESSION['employee_name'] ?? 'User';
$name = $_SESSION['name'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';

require_once '../includes/db.php';


// Database Counts
$total_clients = $conn->query("SELECT COUNT(*) AS total FROM clients")->fetch_assoc()['total'];
// Get total collection amount
$total_collection = $conn->query("SELECT SUM(received_amount) AS total FROM billing_collection")->fetch_assoc()['total'] ?? 0;
$total_packages = $conn->query("SELECT COUNT(*) AS total FROM packages")->fetch_assoc()['total'];
$total_routers = $conn->query("SELECT COUNT(*) AS total FROM routers")->fetch_assoc()['total'];
$total_paid = $conn->query("SELECT COUNT(*) AS total FROM service_information WHERE billing_status = 'paid'")->fetch_assoc()['total'];
$total_due = $conn->query("SELECT COUNT(*) AS total FROM service_information WHERE billing_status = 'due'")->fetch_assoc()['total'];

// MikroTik API Connection
$router_ip = '103.14.151.24';    // your MikroTik router IP
$router_username = 'NOC';     // your username
$router_password = 'NOC@#321';          // your password
$router_port = 18710;            // API port

$total_online = 0;

$API = new RouterosAPI();
$API->port = $router_port;
if ($API->connect($router_ip, $router_username, $router_password)) {
    $active_users = $API->comm("/ppp/active/print");
    $total_online = count($active_users);
    $API->disconnect();
}

$total_offline = $total_clients - $total_online;
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
include '../includes/mikrotik_functions.php'; // <-- Include it!
?>



<div class="container mt-4">
    <h2>Dashboard | Welcome <?= htmlspecialchars(ucfirst($name)) ?>!</h2>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Clients</h5>
                    <p class="card-text display-6"><?= $total_clients ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Online Users</h5>
                    <p class="card-text display-6"><?= $total_online ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Offline Users</h5>
                    <p class="card-text display-6"><?= $total_offline ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Packages</h5>
                    <p class="card-text display-6"><?= $total_packages ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Routers Connected</h5>
                    <p class="card-text display-6"><?= $total_routers ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Paid Clients</h5>
                    <p class="card-text display-6"><?= $total_paid ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Due Clients</h5>
                    <p class="card-text display-6"><?= $total_due ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Bill Collection</h5>
                    <p class="card-text display-6"><?= $total_collection ?></p>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>
