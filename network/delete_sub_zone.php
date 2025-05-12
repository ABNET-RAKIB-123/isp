<?php
require_once '../includes/db.php';

$id = isset($_POST['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM subzones WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: list_subzones.php?deleted=1");
exit;
