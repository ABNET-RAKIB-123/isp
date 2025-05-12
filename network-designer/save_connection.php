<?php
$conn = new mysqli("localhost", "designer", "(w9XIEhzvHz7D9(b", "network_designer");

$from = $_POST['from'];
$to = $_POST['to'];
$cable_type = $_POST['cable_type'];
$cable_length = $_POST['cable_length'];

$stmt = $conn->prepare("INSERT INTO connections (from_device_id, to_device_id, cable_type, cable_length) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iisi", $from, $to, $cable_type, $cable_length);
$stmt->execute();
?>
