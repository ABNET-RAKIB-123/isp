<?php
include('../config/db.php');

$from = $_POST['from_port_id'];
$to = $_POST['to_port_id'];
$cable = $_POST['cable_type'];
$module = $_POST['module_type'];
$splitter = $_POST['splitter_ratio'];

$stmt = $conn->prepare("INSERT INTO connections (from_port_id, to_port_id, cable_type, module_type, splitter_ratio) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $from, $to, $cable, $module, $splitter);

if ($stmt->execute()) {
    echo "Connection added successfully.";
} else {
    echo "Error: " . $conn->error;
}
