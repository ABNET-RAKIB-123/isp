<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../api/mikrotik_api.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bridge_name = $_POST['bridge_name'];
    $router_id = $_POST['router_id'];
    $vlan_id = $_POST['vlan_id'] ?? null;
    $comment = $_POST['comment'] ?? '';
    $interfaces = $_POST['interfaces'] ?? [];

    $stmt = $conn->prepare("INSERT INTO bridges (bridge_name, bridge_ports, router_id, vlan_id, comment) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siiss", $bridge_name, $interfaces, $router_id, $vlan_id, $comment);
    $stmt->execute();
    $bridge_id = $conn->insert_id;

    $router = $conn->query("SELECT * FROM routers WHERE id = $router_id")->fetch_assoc();
    if ($router) {
        $API = new RouterosAPI();
        $API->port = $router['router_port'];

        if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
            $API->comm("/interface/bridge/add", ["name" => $bridge_name, "comment" => $comment]);

            foreach ($interfaces as $interface) {
                $API->comm("/interface/bridge/port/add", [
                    "bridge" => $bridge_name,
                    "interface" => $interface,
                ]);

                $stmt = $conn->prepare("INSERT INTO bridge_ports (bridge_id, interface_name) VALUES (?, ?)");
                $stmt->bind_param("is", $bridge_id, $interface);
                $stmt->execute();
            }

            $API->disconnect();
        }
    }

    header("Location: list_bridges.php?success=Bridge added");
    exit;
}

$routers = $conn->query("SELECT * FROM routers ORDER BY router_name ASC");
?>

<div class="container mt-4">
    <h2>Add Bridge</h2>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Bridge Name</label>
            <input type="text" name="bridge_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Router</label>
            <select name="router_id" class="form-select" id="router-select" required>
                <option value="">Select Router</option>
                <?php while ($r = $routers->fetch_assoc()) : ?>
                    <option value="<?= $r['id'] ?>"><?= $r['router_name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">VLAN ID (optional)</label>
            <input type="number" name="vlan_id" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Comment</label>
            <input type="text" name="comment" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Interfaces</label>
            <select multiple name="interfaces[]" class="form-select" id="interface-list" required></select>
        </div>
        <button type="submit" class="btn btn-primary">Create Bridge</button>
    </form>
</div>

<script>
document.getElementById('router-select').addEventListener('change', function () {
    const routerId = this.value;
    const interfaceList = document.getElementById('interface-list');
    interfaceList.innerHTML = '<option>Loading...</option>';

    fetch('get_interfaces.php?router_id=' + routerId)
        .then(res => res.json())
        .then(data => {
            interfaceList.innerHTML = '';
            data.forEach(iface => {
                const option = document.createElement('option');
                option.value = iface.name;
                option.textContent = iface.name;
                interfaceList.appendChild(option);
            });
        });
});
</script>
