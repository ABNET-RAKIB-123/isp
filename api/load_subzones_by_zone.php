<?php
require_once '../includes/db.php';
$zone_id = intval($_POST['zone_id']);
$result = $conn->query("SELECT * FROM subzones WHERE zone_id = $zone_id ORDER BY subzone_name ASC");
echo '<option value="">Select Subzone</option>';
while ($row = $result->fetch_assoc()) {
    echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['subzone_name']).'</option>';
}
?>
