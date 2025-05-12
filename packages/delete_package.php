<?php
session_start();
require_once '../includes/db.php';

if (isset($_GET['id'])) {
    $package_id = intval($_GET['id']);
    $conn->query("DELETE FROM packages WHERE id = $package_id");

    header('Location: list_packages.php?success=Package Deleted Successfully');
    exit();
}
?>
