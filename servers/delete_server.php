<?php
session_start();
require_once '../includes/db.php';

if (isset($_GET['id'])) {
    $server_id = intval($_GET['id']);
    $conn->query("DELETE FROM servers WHERE id = $server_id");

    header('Location: list_servers.php?success=Server Deleted Successfully');
    exit();
}
?>
