<?php
session_start();

// 🔒 Check login
if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit;
}

// 🧑‍💼 Logged in User Info
$employee_name = $_SESSION['employee_name'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - ISP Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h2>Welcome, <?= htmlspecialchars($employee_name) ?>!</h2>
    <p>Your Role: <strong><?= htmlspecialchars(ucfirst($role)) ?></strong></p>

    <!-- ✅ Role-Based Content Example -->
    <?php if ($role === 'admin'): ?>
        <div class="alert alert-success">🔐 You have full Admin Access!</div>
    <?php elseif ($role === 'editor'): ?>
        <div class="alert alert-info">✏️ You can edit and manage clients.</div>
    <?php elseif ($role === 'support'): ?>
        <div class="alert alert-warning">👁️ You have view-only permissions.</div>
    <?php else: ?>
        <div class="alert alert-danger">❌ Unknown Role!</div>
    <?php endif; ?>

    <!-- ✅ Optional Role-Based Button -->
    <?php if ($role === 'admin' || $role === 'editor'): ?>
        <a href="/clients/add_client.php" class="btn btn-primary">Add New Client</a>
    <?php endif; ?>

    <a href="/emp/logout.php" class="btn btn-outline-danger">Logout</a>
</div>

</body>
</html>
