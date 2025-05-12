<?php
session_start();
require_once '../includes/db.php';
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
// Load routers for dropdown
$routers = $conn->query("SELECT * FROM routers ORDER BY router_name ASC");
?>

<div class="container-fluid p-4">
    <h2>PPPoE Users Transfer</h2>

    <form method="POST" action="transfer_users_action.php" class="mt-4">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Source Router</label>
                <select name="source_router_id" class="form-control" required>
                    <option value="">Select Source Router</option>
                    <?php while($r = $routers->fetch_assoc()): ?>
                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['router_name']) ?> (<?= $r['router_ip'] ?>)</option>
                    <?php endwhile; ?>
                <?php $routers->data_seek(0); ?>
            </select>
            </div>

            <div class="col-md-4 mb-3">
                <label>Destination Router</label>
                <select name="destination_router_id" class="form-control" required>
                    <option value="">Select Destination Router</option>
                    <?php while($r = $routers->fetch_assoc()): ?>
                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['router_name']) ?> (<?= $r['router_ip'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="col-md-4 mb-3">
                <label>Service Profile (Optional)</label>
                <input type="text" name="service_profile" class="form-control" placeholder="Enter profile name or leave empty">
            </div>
        </div>
        <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" value="1" name="delete_after_transfer" id="deleteAfter">
                <label class="form-check-label" for="deleteAfter">
                    Delete Users from Source Router after Transfer
                </label>
            </div>

        <button type="submit" class="btn btn-primary mt-3">Transfer Users</button> & <a href="/users/select_users.php" class="btn btn-info mt-3" role="button">Single Transfer</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
