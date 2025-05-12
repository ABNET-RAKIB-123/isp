<?php
require_once '../includes/db.php';

// Pagination Setup
$limit = 20;
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$start_from = ($page - 1) * $limit;

// Filters
$server_id = $_POST['server_id'] ?? '';
$employee_id = $_POST['employee_id'] ?? '';
$start_date = $_POST['start_date'] ?? date('Y-m-01');
$end_date = $_POST['end_date'] ?? date('Y-m-d');
$search = $_POST['search'] ?? '';

// WHERE Conditions
$where = ["DATE(bc.received_date) BETWEEN '$start_date' AND '$end_date'"];

if (!empty($server_id)) {
    $where[] = "npi.server_id = " . intval($server_id);
}
if (!empty($employee_id)) {
    $where[] = "bc.collected_by = " . intval($employee_id);
}
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $where[] = "(c.customer_name LIKE '%$search%' OR ci.mobile_number LIKE '%$search%')";
}
$where_sql = "WHERE " . implode(' AND ', $where);

// Total Clients Count
$countResult = $conn->query("
    SELECT COUNT(*) as total
    FROM billing_collection bc
    JOIN clients c ON bc.client_id = c.id
    JOIN contact_information ci ON c.id = ci.client_id
    LEFT JOIN network_product_information npi ON c.id = npi.client_id
    JOIN service_information si ON c.id = si.client_id
    $where_sql
");
$total_clients = $countResult->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total_clients / $limit);

// Client Query
$billingSQL = "
    SELECT 
        c.customer_name,
        ci.mobile_number,
        bc.received_amount,
        bc.received_date,
        e.name as collected_by,
        si.money_bill
    FROM billing_collection bc
    JOIN clients c ON bc.client_id = c.id
    JOIN contact_information ci ON c.id = ci.client_id
    JOIN employees e ON bc.collected_by = e.id
    LEFT JOIN network_product_information npi ON c.id = npi.client_id
    JOIN service_information si ON c.id = si.client_id
    $where_sql
    ORDER BY bc.received_date DESC
    LIMIT $start_from, $limit
";
$billings = $conn->query($billingSQL);

// Employee Collection Total Query
$employeeCollectionSQL = "
    SELECT e.name AS employee_name, SUM(bc.received_amount) AS total_collected
    FROM billing_collection bc
    JOIN employees e ON bc.collected_by = e.id
    LEFT JOIN clients c ON bc.client_id = c.id
    LEFT JOIN network_product_information npi ON c.id = npi.client_id
    $where_sql
    GROUP BY bc.collected_by
    ORDER BY total_collected DESC
";
$employeeCollections = $conn->query($employeeCollectionSQL);

// Monthly Total Collection Query
$monthlyCollectionSQL = "
    SELECT DATE_FORMAT(bc.received_date, '%Y-%m') AS month, SUM(bc.received_amount) AS total_monthly
    FROM billing_collection bc
    JOIN clients c ON bc.client_id = c.id
    LEFT JOIN network_product_information npi ON c.id = npi.client_id
    $where_sql
    GROUP BY month
    ORDER BY month DESC
";
$monthlyCollections = $conn->query($monthlyCollectionSQL);
?>

<table class="table table-bordered">
    <thead class="table-dark">
    <tr>
        <th>#</th>
        <th>Client Name</th>
        <th>Mobile</th>
        <th>Paid (৳)</th>
        <th>Due (৳)</th>
        <th>Collected By</th>
        <th>Received Date</th>
    </tr>
    </thead>
    <tbody>
    <?php $i = $start_from + 1; while($row = $billings->fetch_assoc()): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?></td>
            <td><?= htmlspecialchars($row['mobile_number']) ?></td>
            <td><?= number_format($row['received_amount'], 2) ?></td>
            <td><?= number_format($row['money_bill'], 2) ?></td>
            <td><?= htmlspecialchars($row['collected_by']) ?></td>
            <td><?= htmlspecialchars($row['received_date']) ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<!-- Employee-wise Collection Summary -->
<h4>👨‍💼 Employee-wise Total Collection Summary</h4>
<table class="table table-bordered">
    <thead class="table-dark">
    <tr>
        <th>Employee</th>
        <th>Total Collected (৳)</th>
    </tr>
    </thead>
    <tbody>
    <?php while ($row = $employeeCollections->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['employee_name']) ?></td>
            <td><?= number_format($row['total_collected'], 2) ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<!-- Monthly Collection Summary -->
<h4>📅 Monthly Total Collection Summary</h4>
<table class="table table-bordered">
    <thead class="table-dark">
    <tr>
        <th>Month</th>
        <th>Total Collected (৳)</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($monthlyCollections->num_rows > 0): ?>
        <?php while ($row = $monthlyCollections->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['month']) ?></td>
                <td><?= number_format($row['total_monthly'], 2) ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="2" class="text-center text-danger">No monthly data found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<!-- Pagination -->
<nav>
    <ul class="pagination justify-content-center">
        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
            <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                <a href="#" class="page-link" data-page="<?= $p ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
