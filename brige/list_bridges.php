<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$bridges = $conn->query("
    SELECT b.*, r.router_name 
    FROM bridges b
    JOIN routers r ON b.router_id = r.id
    ORDER BY b.id DESC
");

?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Bridge List</h2>
        <a href="add_bridge.php" class="btn btn-success">Add New Bridge</a>
    </div>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Bridge Name</th>
                <th>Router</th>
                <th>VLAN ID</th>
                <th>Comment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $i=1; while($bridge = $bridges->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($bridge['bridge_name']) ?></td>
                    <td><?= htmlspecialchars($bridge['router_name']) ?></td>
                    <td><?= htmlspecialchars($bridge['vlan_id']) ?></td>
                    <td><?= htmlspecialchars($bridge['comment']) ?></td>
                    <td>
    <a href="view_bridge.php?id=<?= $bridge['id'] ?>" class="btn btn-primary btn-sm">View</a>
    <a href="edit_bridge.php?id=<?= $bridge['id'] ?>" class="btn btn-info btn-sm">Edit</a>
    <a href="delete_bridge.php?id=<?= $bridge['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
</td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
