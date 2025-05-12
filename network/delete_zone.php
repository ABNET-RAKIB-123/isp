<?php
require_once '../includes/db.php';
$id = (int)$_POST['id'];
$conn->query("DELETE FROM zones WHERE id = $id");
header("Location: list_zones.php?deleted=1");
