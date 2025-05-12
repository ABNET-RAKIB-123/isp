<?php
require_once '../includes/db.php';
$server_id = intval($_POST['server_id']);
$result = $conn->query("SELECT * FROM packages WHERE server_id = $server_id");
echo '<option value="">Select Package</option>';
while ($row = $result->fetch_assoc()) {
    echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['package_name']).'</option>';
}
?>
