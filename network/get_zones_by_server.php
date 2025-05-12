<?php
require_once '../includes/db.php';
if (isset($_POST['server_id'])) {
    $server_id = (int)$_POST['server_id'];
    $zones = $conn->query("SELECT id, zone_name FROM zones WHERE server_id = $server_id");
    echo '<option value="">-- Select Zone --</option>';
    while ($z = $zones->fetch_assoc()) {
        echo '<option value="' . $z['id'] . '">' . htmlspecialchars($z['zone_name']) . '</option>';
    }
}
