<?php
session_start();

// 🔒 Check login
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
// 🧑‍💼 Logged in User Info
$employee_name = $_SESSION['name'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';
$_SESSION['id']          = $user['id'];
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $zone_name = trim($_POST['zone_name']);
    $server_id = (int)$_POST['server_id'];
    $ids = (int)$_POST['ids'];

    $stmt = $conn->prepare("UPDATE zones SET zone_name = ?, server_id = ? WHERE id = ?");
    $stmt->bind_param("sii", $zone_name, $server_id, $ids);
    $stmt->execute();
    header("Location: list_zones.php?success=Zone updated!");
}