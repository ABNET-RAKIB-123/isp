<?php
require_once '../includes/db.php';

if (isset($_POST['package_id'])) {
    $package_id = intval($_POST['package_id']);
    $package = $conn->query("SELECT price FROM packages WHERE id = $package_id")->fetch_assoc();
    if ($package) {
        echo $package['price'];
    } else {
        echo 0;
    }
}
?>