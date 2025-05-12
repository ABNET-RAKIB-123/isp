<?php
session_start();
require_once '../includes/db.php';

if (isset($_GET['id'])) {
    // $router_id = intval($_GET['id']);
    // $conn->query("DELETE FROM routers WHERE id = $router_id");

    $router_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $role = $_SESSION['role'] ?? '';
    $employee_id = $_SESSION['employee_id'] ?? 0;

    // 🚫 Support users cannot delete anything
    if ($role === 'support') {
        header("Location: list_routers.php?success=Access denied: Support role cannot delete routers.");
        // header("Location: list_routers.php?error=access");
            exit;
    }

    // Check access before deleting
    $query = "SELECT * FROM routers WHERE id = ?";
    $params = [$router_id];
    $types = "i";

    if ($role !== 'admin') {
        $query .= " AND owner_id = ?";
        $params[] = $employee_id;
        $types .= "i";
    }

    // If valid, delete
    $delete = $conn->prepare("DELETE FROM routers WHERE id = ?");
    $delete->bind_param("i", $router_id);
    $delete->execute();

    // Redirect with success message
    header("Location: list_routers.php?success=Router deleted successfully");
    exit;

    if (!$router) {
        die("Access denied or router not found.");
    }

    
}
