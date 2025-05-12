<?php
session_start();
require_once '../includes/db.php';

if (isset($_['id'])) {
    $employee_id = intval($_GET['id']);
    $conn->query("DELETE FROM employees WHERE id = $employee_id");

    header('Location: list_employees.php?success=Employee Deleted Successfully');
    exit();
}
?>
