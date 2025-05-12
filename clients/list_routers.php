<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$role = $_SESSION['role'];
$employee_id = $_SESSION['employee_id'];

// Load routers based on role
if ($role === 'admin') {
    $routers = $conn->query("SELECT r.*, e.name as owner_name FROM routers r LEFT JOIN employees e ON r.owner_id = e.id ORDER BY r.id DESC");
} else {
    $stmt = $conn->prepare("SELECT r.*, e.name as owner_name FROM routers r LEFT JOIN employees e ON r.owner_id = e.id WHERE r.owner_id = ? ORDER BY r.id DESC");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $routers = $stmt->get_result();
}
?>

<div class="container p-4">
    <h3>Routers List</h3>
    <a href="/routers/add_router.php" class="btn btn-primary mb-3">➕ Add Router</a>

    <table class="table table-bordered">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>IP</th>
            <th>Port</th>
            <th>Owner</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($router = $routers->fetch_assoc()): ?>
            <tr>
                <td><?= $router['id'] ?></td>
                <td><?= htmlspecialchars($router['router_name']) ?></td>
                <td><?= $router['router_ip'] ?></td>
                <td><?= $router['router_port'] ?></td>
                <td><?= htmlspecialchars($router['owner_name'] ?? 'Unknown') ?></td>
                <td>
                    <a href="view_router.php?id=<?= $router['id'] ?>" class="btn btn-sm btn-primary">View</a>
                    <?php if ($role === 'admin' || $router['owner_id'] == $employee_id): ?>
                        <a href="edit_router.php?id=<?= $router['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                        <a href="delete_router.php?id=<?= $router['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to delete this router?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
