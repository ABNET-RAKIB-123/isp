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
    $subzone_name = trim($_POST['subzone_name']);
    $zone_id = (int)$_POST['zone_id'];
    $ids = $_POST['ids'];

    if (!empty($subzone_name) && $zone_id > 0) {
        $stmt = $conn->prepare("UPDATE subzones SET subzone_name = ?, zone_id = ? WHERE id = ?");
        $stmt->bind_param("sii", $subzone_name, $zone_id, $ids);
        $stmt->execute();
        header("Location: list_subzones.php?updated=1");
        exit;
    } else {
        $error = "❌ Please fill in all fields.";
    }
}