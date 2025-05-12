<?php
session_start();
require_once '../includes/db.php';

$package_name = trim($_POST['package_name']);
$rate_limit = trim($_POST['rate_limit']);
$price = (float)$_POST['price'];
$server_id = (int)$_POST['server_id'];

if ($package_name && $rate_limit && $price && $server_id) {
    $stmt = $conn->prepare("INSERT INTO packages (package_name, speed, price, server_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $package_name, $rate_limit, $price, $server_id);
    $stmt->execute();
    header("Location: list_packages.php?success=Package added successfully");
} else {
    echo "All fields are required!";
}
?>
