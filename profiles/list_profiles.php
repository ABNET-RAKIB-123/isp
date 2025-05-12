<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container p-4">
    <h2>Profiles List</h2>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Profile Name</th>
                <th>Local Address</th>
                <th>Remote Address</th>
                <th>Rate Limit</th>
                <th>Comment</th>
                <th>Router ID</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $profiles = $conn->query("SELECT * FROM profiles ORDER BY id DESC");
            while ($p = $profiles->fetch_assoc()):
            ?>
                <tr>
                    <td><?= htmlspecialchars($p['profile_name']) ?></td>
                    <td><?= htmlspecialchars($p['local_address']) ?></td>
                    <td><?= htmlspecialchars($p['remote_address']) ?></td>
                    <td><?= htmlspecialchars($p['rate_limit']) ?></td>
                    <td><?= htmlspecialchars($p['comment']) ?></td>
                    <td><?= $p['router_id'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
