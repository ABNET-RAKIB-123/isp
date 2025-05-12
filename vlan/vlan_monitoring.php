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
            $vlans = $API->comm("/interface/vlan/print");
            $pppoe_users = $API->comm("/ppp/active/print");
            $API->disconnect();
        }
    }
}
?>

<div class="container-fluid p-4">
    <h2>VLAN Monitoring</h2>

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

    <?php if (!empty($vlans)): ?>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>VLAN Name</th>
                <th>VLAN ID</th>
                <th>Assigned Interface</th>
                <th>Comment</th>
                <th>Online Clients</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($vlans as $vlan): ?>
    <?php
        $online_count = 0;
        if (!empty($pppoe_users)) {
            foreach ($pppoe_users as $user) {
                if (!empty($user['interface'])) {
                    // Check if VLAN name is inside user interface (case insensitive)
                    if (stripos($user['interface'], $vlan['name']) !== false) {
                        $online_count++;
                    }
                }
            }
        }
    ?>
    <tr>
        <td><?= htmlspecialchars($vlan['name']) ?></td>
        <td><?= htmlspecialchars($vlan['vlan-id'] ?? '-') ?></td>
        <td><?= htmlspecialchars($vlan['interface'] ?? '-') ?></td>
        <td><?= htmlspecialchars($vlan['comment'] ?? '-') ?></td>
        <td><span class="badge bg-success"><?= $online_count ?></span></td>
    </tr>
<?php endforeach; ?>

        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
