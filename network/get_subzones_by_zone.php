<?php
require_once '../includes/db.php';
if (isset($_POST['zone_id'])) {
    $zone_id = (int)$_POST['zone_id'];
    $subzones = $conn->query("SELECT id, subzone_name FROM subzones WHERE zone_id = $zone_id");
    echo '<option value="">-- Select Sub Zone --</option>';
    while ($s = $subzones->fetch_assoc()) {
        echo '<option value="' . $s['id'] . '">' . htmlspecialchars($s['subzone_name']) . '</option>';
    }
}
