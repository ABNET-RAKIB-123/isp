<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// Join VLANs with Bridges
$vlans = $conn->query("
    SELECT v.*, b.bridge_name 
    FROM vlans v
    JOIN bridges b ON v.bridge_id = b.id
    ORDER BY v.id DESC
");
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>VLAN List</h2>
        <a href="add_vlan.php" class="btn btn-success">Add New VLAN</a>
    </div>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>VLAN ID</th>
                <th>VLAN Name</th>
                <th>IP Address</th>
                <th>Bridge</th>
                <th>Comment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php $i=1; while($vlan = $vlans->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($vlan['vlan_id']) ?></td>
                <td><?= htmlspecialchars($vlan['vlan_name']) ?></td>
                <td><?= htmlspecialchars($vlan['ip_address']) ?></td>
                <td><?= htmlspecialchars($vlan['bridge_name']) ?></td>
                <td><?= htmlspecialchars($vlan['comment']) ?></td>
                <td>
                    <a href="edit_vlan.php?id=<?= $vlan['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="delete_vlan.php?id=<?= $vlan['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete VLAN?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
