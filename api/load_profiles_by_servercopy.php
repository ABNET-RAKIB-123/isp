<?php
require_once '../includes/db.php';
$server_id = intval($_POST['server_id']);
$result = $conn->query("SELECT * FROM profiles WHERE router_id IN (SELECT router_id FROM servers WHERE id = $server_id)");
echo '<option value="">Select Profile</option>';
while ($row = $result->fetch_assoc()) {
    echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['profile_name']).'</option>';
}
?>
