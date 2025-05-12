<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;
require_once '../includes/db.php';


// Default Filters
$start_date = $_POST['start_date'] ?? date('Y-m-01');
$end_date = $_POST['end_date'] ?? date('Y-m-d');
$server_id = $_POST['server_id'] ?? '';
$employee_filter = $_POST['employee_id'] ?? '';
$billing_status = $_POST['billing_status'] ?? '';

// Dynamic WHERE
$where = ["DATE(bc.received_date) BETWEEN '$start_date' AND '$end_date'"];
if (!empty($server_id)) {
    $where[] = "npi.server_id = " . intval($server_id);
}
if (!empty($employee_filter)) {
    $where[] = "bc.collected_by = " . intval($employee_filter);
}
if (!empty($billing_status)) {
    $where[] = "si.billing_status = '" . $conn->real_escape_string($billing_status) . "'";
}
$where_sql = 'WHERE ' . implode(' AND ', $where);

// 🔹 Employee Wise Total Collection
$collectionSQL = "
    SELECT e.name AS employee_name, SUM(bc.received_amount) as total_collected
    FROM billing_collection bc
    JOIN employees e ON bc.collected_by = e.id
    JOIN clients c ON bc.client_id = c.id
    JOIN network_product_information npi ON c.id = npi.client_id
    JOIN service_information si ON c.id = si.client_id
    $where_sql
    GROUP BY bc.collected_by
    ORDER BY total_collected DESC
";
$collections = $conn->query($collectionSQL);

// 🔹 Client Wise Collection Details
$clientCollectionSQL = "
    SELECT 
        c.customer_name, 
        bc.received_amount, 
        bc.received_date, 
        e.name AS collected_by
    FROM billing_collection bc
    JOIN clients c ON bc.client_id = c.id
    JOIN employees e ON bc.collected_by = e.id
    JOIN network_product_information npi ON c.id = npi.client_id
    JOIN service_information si ON c.id = si.client_id
    $where_sql
    ORDER BY bc.received_date DESC LIMIT 5
";
$clientCollections = $conn->query($clientCollectionSQL);

// 🔹 Total Due Query
$totalDueResult = $conn->query("
    SELECT COUNT(*) as total_due_clients
    FROM service_information 
    WHERE billing_status = 'due'
");
$total_due_clients = $totalDueResult->fetch_assoc()['total_due_clients'] ?? 0;

// Servers & Employees load
$servers = $conn->query("SELECT * FROM servers");
$employees = $conn->query("SELECT * FROM employees");
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container-fluid p-4">
    <h3>📑 Billing Summary</h3>

    <form method="POST">
    <!-- <div class="col-md-3"> -->
        <div class="col-md-4 mb-3">
            <label>Server</label>
            <select name="server_id" class="form-select">
                <option value="">All Servers</option>
                <?php while($s = $servers->fetch_assoc()): ?>
                    <option value="<?= $s['id'] ?>" <?= ($server_id == $s['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['server_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label>Employee</label>
            <select name="employee_id" class="form-select">
                <option value="">All Employees</option>
                <?php while($e = $employees->fetch_assoc()): ?>
                    <option value="<?= $e['id'] ?>" <?= ($employee_filter == $e['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-4 mb-3">
            <label>Billing Status</label>
            <select name="billing_status" class="form-select">
                <option value="">All</option>
                <option value="paid" <?= ($billing_status == 'paid') ? 'selected' : '' ?>>Paid</option>
                <option value="due" <?= ($billing_status == 'due') ? 'selected' : '' ?>>Due</option>
            </select>
        </div>

         <div class="col-md-4 mb-3">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
        </div>

         <div class="col-md-4 mb-3">
            <label>End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
        </div>

         <div class="col-md-4 mb-3">
            <button type="submit" class="btn btn-success mt-4">🔍 Filter</button>
        </div>
    <!-- </div>     -->
    </form>

    <div class="mb-4">
        <h5>🔵 Total Due Clients: <span class="badge bg-danger"><?= $total_due_clients ?></span></h5>
    </div>

    <div class="row mb-5">
        <div class="col-md-6">
            <h5 class="text-center">Employee Wise Total Collection</h5>
            <table class="table table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>Employee</th>
                    <th>Total Collected (৳)</th>
                </tr>
                </thead>
                <tbody>
                <?php while($row = $collections->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['employee_name']) ?></td>
                        <td><?= number_format($row['total_collected'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="col-md-6">
            <h5 class="text-center">Client Wise Payment Collection</h5>
            <table class="table table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Collected By</th>
                    <th>Amount (৳)</th>
                </tr>
                </thead>
                <tbody>
                <?php while($row = $clientCollections->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['received_date']) ?></td>
                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td><?= htmlspecialchars($row['collected_by']) ?></td>
                        <td><?= number_format($row['received_amount'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
