<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../api/mikrotik_api.php';
?>

<div class="container p-4">
    <h2>Add New PPPoE Profile</h2>

    <form method="POST" action="save_profile.php">
        <div class="mb-3">
            <label>Router:</label>
            <select id="routerSelect" name="router_id" class="form-select" required>
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
            <label>Profile Name:</label>
            <input type="text" name="profile_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Local Address:</label>
            <input type="text" name="local_address" class="form-control">
        </div>

        <div class="mb-3">
            <label>Remote Address (Select Pool):</label>
            <select id="remoteAddressSelect" name="remote_address" class="form-select" required>
                <option value="">Select Pool</option>
            </select>
        </div>

        <div class="mb-3">
            <label>DNS Servers (Comma separated):</label>
            <input type="text" name="dns_servers" class="form-control" placeholder="8.8.8.8, 1.1.1.1">
        </div>

        <div class="mb-3">
            <label>Rate Limit:</label>
            <input type="text" name="rate_limit" class="form-control" placeholder="eg: 5M/5M">
        </div>

        <div class="mb-3">
            <label>Comment:</label>
            <input type="text" name="comment" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">Save Profile</button>
        <a href="list_profiles.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
$('#routerSelect').change(function() {
    var router_id = $(this).val();
    if (router_id) {
        $('#remoteAddressSelect').html('<option>Loading...</option>');
        $.ajax({
            url: 'load_pools.php',
            type: 'GET',
            data: { router_id: router_id },
            success: function(data) {
                $('#remoteAddressSelect').html(data);
            },
            error: function() {
                $('#remoteAddressSelect').html('<option>Failed to load pools</option>');
            }
        });
    } else {
        $('#remoteAddressSelect').html('<option>Select Router First</option>');
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
