<?php
session_start();
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

<div class="container-fluid p-4">
    <h2 class="mb-4">Router List</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <a href="add_router.php" class="btn btn-success mb-3">Add New Router</a>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Router Name</th>
                        <th>IP Address</th>
                        <th>Username</th>
                        <!-- <th>Owner</th> -->
                        <th>Port</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // $routers = $conn->query("SELECT * FROM routers ORDER BY id DESC");
                    if ($routers->num_rows > 0):
                        $i = 1;
                        while ($router = $routers->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($router['router_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($router['router_ip'] ?? '') ?></td>
                        <td><?= htmlspecialchars($router['router_username'] ?? '') ?></td>
                        <!-- <td><?= htmlspecialchars($router['owner_name'] ?? 'Unknown') ?></td> -->
                        <td><?= htmlspecialchars($router['router_port'] ?? '') ?></td>
                            <td class="d-flex gap-2">
                            <?php if ($role !== 'support'): ?>
                                <a href="edit_router.php?id=<?= $router['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <a href="delete_router.php?id=<?= $router['id'] ?>" 
                                    onclick="return confirm('Are you sure to delete this router?');"
                                    class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                            <?php else: ?>
                                <button type="button" class="btn btn-success me-2" disabled> Edit Delite (No Permission)</button>
                                <!-- <a href="list_clients.php" class="btn btn-secondary">Cancel</a> -->
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No routers found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
