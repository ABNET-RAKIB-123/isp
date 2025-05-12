<?php
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header("Location: list_bridges.php?error=Bridge ID missing");
    exit;
}

// Fetch bridge + router info
$stmt = $conn->prepare("
    SELECT b.*, r.router_name, r.router_ip 
    FROM bridges b 
    JOIN routers r ON b.router_id = r.id 
    WHERE b.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$bridge = $stmt->get_result()->fetch_assoc();

if (!$bridge) {
    header("Location: list_bridges.php?error=Bridge not found");
    exit;
}

// Fetch bridge ports
$port_stmt = $conn->prepare("SELECT * FROM bridge_ports WHERE bridge_id = ?");
$port_stmt->bind_param("i", $id);
$port_stmt->execute();
$ports = $port_stmt->get_result();
?>

<div class="container mt-4">
    <h2>Bridge Details: <?= htmlspecialchars($bridge['bridge_name']) ?></h2>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Router:</strong> <?= htmlspecialchars($bridge['router_name']) ?> (<?= $bridge['router_ip'] ?>)</p>
            <p><strong>VLAN ID:</strong> <?= $bridge['vlan_id'] ?: 'None' ?></p>
            <p><strong>Comment:</strong> <?= htmlspecialchars($bridge['comment']) ?></p>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary text-white">
            <strong>Assigned Interfaces (Ports)</strong>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0 table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Interface Name</th>
                        <th>Added At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($ports->num_rows > 0): $i = 1; ?>
                        <?php while ($port = $ports->fetch_assoc()): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($port['interface_name']) ?></td>
                                <td><?= $port['added_at'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">No interfaces assigned.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
