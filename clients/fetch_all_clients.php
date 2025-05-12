<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;
require_once '../includes/db.php';

require_once '../includes/db.php';

$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;

// Filters from AJAX
$server_id   = isset($_POST['server_id']) ? (int)$_POST['server_id'] : 0;
$zone_id     = isset($_POST['zone_id']) ? (int)$_POST['zone_id'] : 0;
$subzone_id  = isset($_POST['subzone_id']) ? (int)$_POST['subzone_id'] : 0;
$status      = isset($_POST['status']) ? $_POST['status'] : '';

// Build WHERE clause
$where = [];
if ($server_id > 0) {
    $where[] = "npi.server_id = $server_id";
}
if ($zone_id > 0) {
    $where[] = "npi.zone_id = $zone_id";
}
if ($subzone_id > 0) {
    $where[] = "npi.subzone_id = $subzone_id";
}
if (!empty($status)) {
    $where[] = "si.status = '" . $conn->real_escape_string($status) . "'";
}
if ($role === 'editor' || $role === 'support') {
    $where[] = "npi.router_id IN (SELECT id FROM routers WHERE owner_id = $employee_id)";
}

$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Main query
$sql = "
    SELECT
        c.id AS client_id,
        c.customer_name,
        ci.mobile_number,
        si.username,
        si.status,
        si.billing_status,
        r.owner_id as router_owner_id
    FROM clients c
    JOIN contact_information ci ON c.id = ci.client_id
    JOIN service_information si ON c.id = si.client_id
    JOIN network_product_information npi ON c.id = npi.client_id
    JOIN routers r ON npi.router_id = r.id
    $where_sql
    ORDER BY c.id DESC
";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Output JSON
header('Content-Type: application/json');
echo json_encode($data);
exit;
