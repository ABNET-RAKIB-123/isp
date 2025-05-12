<?php
require_once '../includes/db.php';
$server_id = intval($_POST['server_id']);
$result = $conn->query("SELECT * FROM zones WHERE server_id = $server_id ORDER BY zone_name ASC");
echo '<option value="">Select Zone</option>';
while ($row = $result->fetch_assoc()) {
    echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['zone_name']).'</option>';
}
?>
