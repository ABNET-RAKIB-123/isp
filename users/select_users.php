<?php
session_start();
require_once '../includes/db.php';
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;
require_once '../includes/header.php';
require_once '../api/mikrotik_api.php';
?>

<div class="container p-4">
    <h2>Select Users to Transfer</h2>

    <form id="userSelectForm" method="POST" action="transfer_selected_users.php">

        <div class="mb-3">
            <label for="source_router" class="form-label">Source Router:</label>
            <select id="source_router" name="source_router_id" class="form-select" required>
                <option value="">Select Router</option>
                <?php
                $routers = $conn->query("SELECT * FROM routers");
                while ($r = $routers->fetch_assoc()):
                ?>
                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['router_name']) ?> (<?= htmlspecialchars($r['router_ip']) ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="destination_router" class="form-label">Destination Router:</label>
            <select id="destination_router" name="destination_router_id" class="form-select" required>
                <option value="">Select Destination Router</option>
                <?php
                $routers = $conn->query("SELECT * FROM routers");
                while ($r = $routers->fetch_assoc()):
                ?>
                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['router_name']) ?> (<?= htmlspecialchars($r['router_ip']) ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>

        <div id="userListArea">
            <!-- Ajax will load users here -->
        </div>

        <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" name="delete_after_transfer" id="deleteAfter">
            <label class="form-check-label" for="deleteAfter">
                Delete Users from Source after Transfer
            </label>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Transfer Selected Users</button> & <a href="/users/transfer_users.php" class="btn btn-info mt-3" role="button">Transfer Users</a>

    </form>
</div>

<script>
$(document).ready(function() {
    $('#source_router').change(function() {
        var router_id = $(this).val();
        if (router_id) {
            $('#userListArea').html('<div class="text-center">Loading users...</div>');
            $.ajax({
                url: 'load_users.php',
                type: 'GET',
                data: { source_router_id: router_id },
                success: function(data) {
                    $('#userListArea').html(data);
                },
                error: function() {
                    $('#userListArea').html('<div class="text-danger">Failed to load users!</div>');
                }
            });
        } else {
            $('#userListArea').html('');
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
