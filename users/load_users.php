<?php
require_once '../includes/db.php';
require_once '../api/mikrotik_api.php';

$source_router_id = (int)($_GET['source_router_id'] ?? 0);

if (!$source_router_id) {
    echo "<p class='text-danger'>Please select a router first.</p>";
    exit;
}

$router = $conn->query("SELECT * FROM routers WHERE id = $source_router_id")->fetch_assoc();

if (!$router) {
    echo "<p class='text-danger'>Router not found!</p>";
    exit;
}

$API = new RouterosAPI();
$API->port = $router['router_port'];

if (!$API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
    echo "<p class='text-danger'>Failed to connect to router!</p>";
    exit;
}

$secrets = $API->comm("/ppp/secret/print");

$API->disconnect();

if (empty($secrets)) {
    echo "<p class='text-warning'>No users found on this router.</p>";
    exit;
}
?>

<table class="table table-bordered table-hover mt-3">
    <thead class="table-dark">
        <tr>
            <th><input type="checkbox" id="selectAll"></th>
            <th>Username</th>
            <th>Service</th>
            <th>Profile</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($secrets as $secret): ?>
            <tr>
                <td><input type="checkbox" name="selected_users[]" value="<?= htmlspecialchars($secret['name']) ?>"></td>
                <td><?= htmlspecialchars($secret['name']) ?></td>
                <td><?= htmlspecialchars($secret['service'] ?? 'pppoe') ?></td>
                <td><?= htmlspecialchars($secret['profile'] ?? 'default') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
// Select All / Deselect All Functionality
$('#selectAll').click(function() {
    $('input[name="selected_users[]"]').prop('checked', this.checked);
});
</script>
