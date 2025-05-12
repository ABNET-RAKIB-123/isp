<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../api/mikrotik_api.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header("Location: list_bridges.php?error=Bridge ID missing");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM bridges WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$bridge = $stmt->get_result()->fetch_assoc();

if (!$bridge) {
    header("Location: list_bridges.php?error=Bridge not found");
    exit;
}

$routers = $conn->query("SELECT * FROM routers ORDER BY router_name ASC");

// Fetch current bridge ports from router
$bridge_ports = [];
$interfaces = [];

if ($bridge && $bridge['router_id']) {
    $router_info = $conn->query("SELECT * FROM routers WHERE id = {$bridge['router_id']}")->fetch_assoc();
    if ($router_info) {
        $API = new RouterosAPI();
        $API->port = $router_info['router_port'];
        if ($API->connect($router_info['router_ip'], $router_info['router_username'], $router_info['router_password'])) {
            // Get all interfaces
            $interfaces = $API->comm("/interface/print");

            // Get bridge ports
            $bridge_ports = $API->comm("/interface/bridge/port/print", ["?bridge" => $bridge['bridge_name']]);

            $API->disconnect();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bridge_name = $_POST['bridge_name'];
    $router_id = $_POST['router_id'];
    $vlan_id = $_POST['vlan_id'] ?? null;
    $comment = $_POST['comment'] ?? '';
    $selected_interfaces = $_POST['interfaces'] ?? [];

    $stmt = $conn->prepare("UPDATE bridges SET bridge_name=?, router_id=?, vlan_id=?, comment=? WHERE id=?");
    $stmt->bind_param("sissi", $bridge_name, $router_id, $vlan_id, $comment, $id);
    $stmt->execute();

    $router = $conn->query("SELECT * FROM routers WHERE id = $router_id")->fetch_assoc();
    if ($router) {
        $API = new RouterosAPI();
        $API->port = $router['router_port'];

        if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
            $bridges = $API->comm("/interface/bridge/print", ["?name" => $bridge['bridge_name']]);
            if (!empty($bridges)) {
                $bridge_id_mikrotik = $bridges[0]['.id'];

                // Update existing bridge
                $API->comm("/interface/bridge/set", [
                    ".id" => $bridge_id_mikrotik,
                    "comment" => $comment,
                    "name" => $bridge_name
                ]);

                // Add each selected interface
                foreach ($selected_interfaces as $intf) {
                    $API->comm("/interface/bridge/port/add", [
                        "interface" => $intf,
                        "bridge" => $bridge_name
                    ]);
                }
            }

            $API->disconnect();
        }
    }

    header("Location: list_bridges.php?success=Bridge updated");
    exit;
}
?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h4>Edit Bridge</h4>
        </div>
        <div class="card-body">
            <form method="POST" id="bridgeForm">
                <div class="mb-3">
                    <label for="bridge_name" class="form-label">Bridge Name</label>
                    <input type="text" class="form-control" name="bridge_name" required value="<?= htmlspecialchars($bridge['bridge_name']) ?>">
                </div>

                <div class="mb-3">
                    <label for="router_id" class="form-label">Router</label>
                    <select class="form-select" name="router_id" id="router_id" required>
                        <option value="">-- Select Router --</option>
                        <?php while ($r = $routers->fetch_assoc()): ?>
                            <option value="<?= $r['id'] ?>" <?= $r['id'] == $bridge['router_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['router_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="vlan_id" class="form-label">VLAN ID</label>
                    <input type="number" class="form-control" name="vlan_id" value="<?= htmlspecialchars($bridge['vlan_id']) ?>">
                </div>

                <div class="mb-3">
                    <label for="comment" class="form-label">Comment</label>
                    <input type="text" class="form-control" name="comment" value="<?= htmlspecialchars($bridge['comment']) ?>">
                </div>

                <div class="mb-3">
                    <label for="interfaces" class="form-label">Add Interfaces</label>
                    <select multiple class="form-select" name="interfaces[]" id="interfaces">
                        <?php foreach ($interfaces as $intf): ?>
                            <option value="<?= $intf['name'] ?>"><?= $intf['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Hold CTRL (Windows) or CMD (Mac) to select multiple.</div>
                </div>

                <button type="submit" class="btn btn-primary">Update Bridge</button>
                <a href="list_bridges.php" class="btn btn-secondary">Cancel</a>
            </form>

            <hr>
            <h5 class="mt-4">🔗 Existing Bridge Ports</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Interface</th>
                        <th>Bridge</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bridge_ports)): ?>
                        <?php foreach ($bridge_ports as $i => $port): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= $port['interface'] ?></td>
                                <td><?= $port['bridge'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No ports assigned.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('router_id').addEventListener('change', function () {
    const routerId = this.value;
    const interfaceSelect = document.getElementById('interfaces');
    interfaceSelect.innerHTML = '<option>Loading...</option>';

    fetch('get_interfaces.php?router_id=' + routerId)
        .then(res => res.json())
        .then(data => {
            interfaceSelect.innerHTML = '';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.name;
                option.textContent = item.name;
                interfaceSelect.appendChild(option);
            });
        })
        .catch(err => {
            interfaceSelect.innerHTML = '<option>Error loading interfaces</option>';
        });
});
</script>
