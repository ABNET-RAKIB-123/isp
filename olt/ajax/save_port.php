<?php
include('../config/db.php');

$device_id = $_POST['device_id'];
$port_name = $_POST['port_name'];
$port_type = $_POST['port_type'];

$stmt = $conn->prepare("INSERT INTO ports (device_id, port_name, port_type) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $device_id, $port_name, $port_type);

if ($stmt->execute()) {
    echo "Port added successfully.";
} else {
    echo "Error: " . $conn->error;
}
