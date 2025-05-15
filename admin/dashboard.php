<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;

require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../api/mikrotik_api.php';

// Load Routers List
$routers = $conn->query("SELECT * FROM routers");
$routers_list = [];
while ($row = $routers->fetch_assoc()) {
    $routers_list[] = $row;
}

// Handle router selection
$router_id = isset($_POST['router_id']) ? (int)$_POST['router_id'] : (isset($_GET['router_id']) ? (int)$_GET['router_id'] : 0);

// If no router selected, default first router
if ($router_id == 0 && !empty($routers_list)) {
    $router_id = $routers_list[0]['id'];
}

// Get selected router info
$selected_router = null;
foreach ($routers_list as $router) {
    if ($router['id'] == $router_id) {
        $selected_router = $router;
        break;
    }
}

if (!$selected_router) {
    die("Router not found!");
}

// Database Counts for selected router
$whereRouter = "WHERE npi.router_id = $router_id";

// Total Clients
$total_clients = $conn->query("
    SELECT COUNT(*) AS total 
    FROM network_product_information npi
    $whereRouter
")->fetch_assoc()['total'];

// Total Paid Clients and Due Clients Count
$sql_client_status = "
SELECT
    COUNT(CASE WHEN si.billing_status = 'paid' THEN 1 END) AS total_paid_clients,
    COUNT(CASE WHEN si.billing_status = 'due' THEN 1 END) AS total_due_clients
FROM service_information si
JOIN network_product_information npi ON si.client_id = npi.client_id
WHERE npi.router_id = $router_id
";
$result_client_status = $conn->query($sql_client_status);
$row_client_status = $result_client_status->fetch_assoc();

$total_paid_clients = $row_client_status['total_paid_clients'] ?? 0;
$total_due_clients = $row_client_status['total_due_clients'] ?? 0;

// Total Paid and Due Amounts
$sql_amount = "
SELECT 
    SUM(CASE WHEN si.billing_status = 'paid' THEN p.price ELSE 0 END) AS total_paid_amount,
    SUM(CASE WHEN si.billing_status = 'due' THEN p.price ELSE 0 END) AS total_due_amount
FROM service_information si
JOIN packages p ON si.package_id = p.id
JOIN network_product_information npi ON si.client_id = npi.client_id
WHERE npi.router_id = $router_id
";
$result_amount = $conn->query($sql_amount);
$row_amount = $result_amount->fetch_assoc();

$total_paid_amount = $row_amount['total_paid_amount'] ?? 0;
$total_due_amount = $row_amount['total_due_amount'] ?? 0;

// MikroTik API Connection
$router_ip = $selected_router['router_ip'];
$router_username = $selected_router['router_username'];
$router_password = $selected_router['router_password'];
$router_port = $selected_router['router_port'];

$total_online = 0;

$API = new RouterosAPI();
$API->port = $router_port;
if ($API->connect($router_ip, $router_username, $router_password)) {
    $active_users = $API->comm("/ppp/active/print");
    $total_online = count($active_users);
    $API->disconnect();
}

$total_offline = $total_clients - $total_online;
?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Dashboard</h2>
        <!-- <div class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" id="autoRefreshToggle">
            <label class="form-check-label" for="autoRefreshToggle">🛠️ Auto Suspend System</label>
        </div> -->



        <?php
// Connect to DB

// Get current status
$status = $conn->query("SELECT value FROM settings WHERE `key` = 'auto_suspend_on'")->fetch_assoc()['value'];
?>

<form method="post">
    <input type="hidden" name="toggle_suspend" value="1">
    <button type="submit" class="btn btn-<?php echo ($status == '1') ? 'danger' : 'success'; ?>">
        <?php echo ($status == '1') ? 'Turn OFF Auto Suspend' : 'Turn ON Auto Suspend'; ?>
    </button>
</form>

<?php
// Update on POST
if (isset($_POST['toggle_suspend'])) {
    $newStatus = ($status == '1') ? '0' : '1';
    $conn->query("UPDATE settings SET value = '$newStatus' WHERE `key` = 'auto_suspend_on'");
    header("Refresh:1");
}
?>




        <select id="routerSelect" class="form-control w-25">
            <?php foreach ($routers_list as $router): ?>
                <option value="<?= $router['id'] ?>" <?= ($router['id'] == $router_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($router['router_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

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
                    <h5 class="card-title">Paid Clients</h5>
                    <p class="card-text display-6"><?= $total_paid_clients ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Due Clients</h5>
                    <p class="card-text display-6"><?= $total_due_clients ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Paid Amount</h5>
                    <p class="card-text fs-3">৳ <?= number_format($total_paid_amount, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Due Amount</h5>
                    <p class="card-text fs-3">৳ <?= number_format($total_due_amount, 2) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
<!-- এখানে নিচে Paste করুন -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('autoRefreshToggle');

    // প্রথম লোডে LocalStorage চেক করবো
    if (localStorage.getItem('auto_refresh_enabled') === 'true') {
        toggle.checked = true;
        startAutoRefresh();
    }

    toggle.addEventListener('change', function () {
        if (this.checked) {
            localStorage.setItem('auto_refresh_enabled', 'true');
            startAutoRefresh();
        } else {
            localStorage.setItem('auto_refresh_enabled', 'false');
            clearInterval(window.refreshInterval);
        }
    });

    function startAutoRefresh() {
        window.refreshInterval = setInterval(function () {
            const now = new Date();
            const hours = now.getHours();
            const minutes = now.getMinutes();

            if (hours === 18 && minutes === 58) {
                console.log('Running auto_suspend_expired.php ...');
                fetch('auto_suspend_expired.php')
                    .then(response => response.text())
                    .then(data => console.log('Auto Suspend Done!', data))
                    .catch(error => console.error('Error:', error));
            }
        }, 60000); // প্রতি ১ মিনিটে চেক
    }
});

$(document).ready(function(){
    $('#routerSelect').on('change', function(){
        var router_id = $(this).val();
        var form = $('<form>', {
            action: 'dashboard.php',
            method: 'POST'
        }).append($('<input>', {
            type: 'hidden',
            name: 'router_id',
            value: router_id
        }));
        $('body').append(form);
        form.submit();
    });
});
</script>