<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../api/mikrotik_api.php';

$routers = $conn->query("SELECT * FROM routers");

if (isset($_POST['router_id'])) {
    $router_id = (int)$_POST['router_id'];
    $router = $conn->query("SELECT * FROM routers WHERE id = $router_id")->fetch_assoc();

    if ($router) {
        $API = new RouterosAPI();
        $API->port = $router['router_port'];

        if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
            $bridges = $API->comm("/interface/bridge/print");
            $API->disconnect();
        }
    }
}
?>

<div class="container-fluid p-4">
    <h2>Live Router Bridges</h2>

    <form method="POST" class="mb-4">
        <select name="router_id" class="form-control" required onchange="this.form.submit()">
            <option value="">Select Router</option>
            <?php while($r = $routers->fetch_assoc()): ?>
                <option value="<?= $r['id'] ?>" <?= (isset($router_id) && $router_id == $r['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['router_name']) ?> (<?= htmlspecialchars($r['router_ip']) ?>)
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if (!empty($bridges)): ?>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Comment</th>
                <th>MTU</th>
                <th>Priority</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bridges as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b['name']) ?></td>
                    <td><?= htmlspecialchars($b['comment'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($b['mtu'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($b['priority'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
