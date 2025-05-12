<?php
require_once '../includes/db.php';

if (isset($_POST['server_id'])) {
    $server_id = intval($_POST['server_id']);
    $packages = $conn->query("SELECT * FROM packages WHERE server_id = $server_id");

    echo '<option value="">Select Package</option>';
    while ($package = $packages->fetch_assoc()) {
        echo '<option value="'.$package['id'].'">'.$package['package_name'].' - '.$package['price'].'৳</option>';
    }
}
?>
