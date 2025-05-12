<?php
require_once '../includes/db.php';

if (isset($_POST['load_profile'])) {
    $router_id = intval($_POST['load_profile']);
    $profiles = $conn->query("SELECT * FROM profiles WHERE router_id = $router_id");

    echo '<option value="">Select Profile</option>';
    while ($profile = $profiles->fetch_assoc()) {
        echo '<option value="'.$profile['id'].'">'.$profile['profile_name'].'</option>';
    }
}
?>
