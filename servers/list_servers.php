<?php
session_start();
$role = $_SESSION['role'];
$employee_id = $_SESSION['employee_id'];
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Server List</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <a href="add_server.php" class="btn btn-success mb-3">Add New Server</a>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Server Name</th>
                        <th>Router IP</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $servers = $conn->query("
                        SELECT servers.*, routers.router_ip 
                        FROM servers 
                        JOIN routers ON servers.router_id = routers.id 
                        ORDER BY servers.id DESC
                    ");
                    if ($servers->num_rows > 0):
                        $i = 1;
                        while ($server = $servers->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($server['server_name']) ?></td>
                        <td><?= htmlspecialchars($server['router_ip']) ?></td>
                        <td class="d-flex gap-2">
                        <?php if ($role !== 'support'): ?>
                            <a href="edit_server.php?id=<?= $server['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                            <a href="delete_server.php?id=<?= $server['id'] ?>" 
                               onclick="return confirm('Are you sure to delete this server?');"
                               class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                            <?php else: ?>
                                <button type="button" class="btn btn-success me-2" disabled>(No Permission)</button>
                                <!-- <a href="list_clients.php" class="btn btn-secondary">Cancel</a> -->
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No servers found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
