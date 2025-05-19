<?php
$conn = new mysqli("localhost", "designer", "(w9XIEhzvHz7D9(b", "network_designer");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $devices = json_encode($_POST['devices']);
    $cables = json_encode($_POST['cables']);

    $sql = "REPLACE INTO fiber_network_data (id, devices, cables) VALUES (1, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $devices, $cables);
    $stmt->execute();
    echo "Saved";
}
?>
