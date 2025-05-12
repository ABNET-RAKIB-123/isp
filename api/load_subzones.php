<?php
require_once '../includes/db.php';

if (isset($_POST['server_id'])) {
    $zone_id = intval($_POST['server_id']);
    $subzones = $conn->query("SELECT * FROM subzones WHERE zone_id = $zone_id");

    echo '<option value="">Select Sub_Zone</option>';
    while ($subzone = $subzones->fetch_assoc()) {
        echo '<option value="'.$subzone['id'].'">'.$subzone['subzone_name'].'</option>';
    }
}
?>
