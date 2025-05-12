<?php
require_once '../includes/db.php';

if (isset($_POST['server_id'])) {
    $server_id = intval($_POST['server_id']);

    // Step 1: Find router_id linked to this server
    $stmt = $conn->prepare("SELECT router_id FROM servers WHERE id = ?");
    $stmt->bind_param("i", $server_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $server = $result->fetch_assoc();
    $router_id = $server['router_id'] ?? 0;

    // Step 2: Load profiles by router_id
    if ($router_id) {
        $profiles = $conn->query("SELECT id, profile_name FROM profiles WHERE router_id = $router_id");

        echo '<option value="">Select Profile</option>';
        while ($profile = $profiles->fetch_assoc()) {
            echo '<option value="'.$profile['id'].'">'.$profile['profile_name'].'</option>';
        }
    } else {
        echo '<option value="">No Profiles Found</option>';
    }
}
?>
