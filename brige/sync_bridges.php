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
            $router_bridges = $API->comm("/interface/bridge/print");
            $API->disconnect();
        }

        // Fetch DB Bridges
        $db_bridges = [];
        $query = $conn->query("SELECT * FROM bridges WHERE router_id = $router_id");
        while ($row = $query->fetch_assoc()) {
            $db_bridges[$row['bridge_name']] = $row;
        }

        $router_bridge_names = [];
        foreach ($router_bridges as $rb) {
            $router_bridge_names[] = $rb['name'];

            // If bridge NOT in Database ➔ Insert it
            if (!isset($db_bridges[$rb['name']])) {
                $stmt = $conn->prepare("INSERT INTO bridges (bridge_name, router_id, comment) VALUES (?, ?, ?)");
                $stmt->bind_param("sis", $rb['name'], $router_id, $rb['comment']);
                $stmt->execute();
            }
        }

        // Now check Database bridges that no longer exist in Router ➔ Delete them
        foreach ($db_bridges as $bridge_name => $dbb) {
            if (!in_array($bridge_name, $router_bridge_names)) {
                $conn->query("DELETE FROM bridges WHERE id = {$dbb['id']}");
            }
        }

        $sync_message = "Bridges Sync Completed!";
    }
}
?>

<div class="container-fluid p-4">
    <h2>Sync Bridges Between Database and Router</h2>

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

    <?php if (isset($sync_message)): ?>
        <div class="alert alert-success"><?= $sync_message ?></div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
