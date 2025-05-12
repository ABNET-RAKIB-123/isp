<?php
include('../config/db.php');

$name = $_POST['name'];
$type = $_POST['type'];
$location = $_POST['location'];
$notes = $_POST['notes'];

$stmt = $conn->prepare("INSERT INTO devices (name, type, location, notes) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $type, $location, $notes);

if ($stmt->execute()) {
    echo "Device added successfully.";
} else {
    echo "Error: " . $conn->error;
}
