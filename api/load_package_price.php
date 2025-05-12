


<?php
require_once '../includes/db.php';

if (isset($_POST['package_id'])) {
    $server_id = intval($_POST['package_id']);

    // Step 1: Find router_id linked to this server
    $stmt = $conn->prepare("SELECT * FROM servers WHERE id = ?");
    $stmt->bind_param("i", $server_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $server = $result->fetch_assoc();
    $router_id = $server['id'] ?? 0;

    // Step 2: Load profiles by router_id
    if ($router_id) {
        $profiles = $conn->query("SELECT * FROM packages WHERE server_id = $router_id");

        echo '<option value="">Select Profile</option>';
        while ($profile = $profiles->fetch_assoc()) {
            echo '<option value="'.$profile['id'].'">'.$profile['package_name'].'</option>';
        }
    } else {
        echo '<option value="">No Profiles Found</option>';
    }
}
?>

