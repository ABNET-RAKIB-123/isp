<?php
include('../config/db.php');
$json = file_get_contents("php://input");
$conn->query("DELETE FROM layout_data");
$stmt = $conn->prepare("INSERT INTO layout_data (json_data) VALUES (?)");
$stmt->bind_param("s", $json);
echo $stmt->execute() ? "Layout saved!" : "Error";
