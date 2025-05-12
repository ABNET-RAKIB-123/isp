<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;
require_once '../includes/db.php';

$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;

// Filters (POST Method)
$server_id = isset($_POST['server_id']) ? (int)$_POST['server_id'] : 0;
$zone_id = isset($_POST['zone_id']) ? (int)$_POST['zone_id'] : 0;
$subzone_id = isset($_POST['subzone_id']) ? (int)$_POST['subzone_id'] : 0;

$where = [];
if ($server_id > 0) {
    $where[] = "npi.server_id = $server_id";
}
if ($zone_id > 0) {
    $where[] = "npi.zone_id = $zone_id";
}
if ($subzone_id > 0) {
    $where[] = "npi.subzone_id = $subzone_id";
}

// Role-based client filtering
if ($role === 'editor' || $role === 'support') {
    $where[] = "npi.router_id IN (
        SELECT id FROM routers WHERE owner_id = $employee_id
    )";
}

$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
    SELECT c.id as client_id, c.customer_name, ci.mobile_number, si.username, si.status, si.billing_status
    FROM clients c
    JOIN contact_information ci ON c.id = ci.client_id
    JOIN service_information si ON c.id = si.client_id
    JOIN network_product_information npi ON c.id = npi.client_id
    $where_sql
    ORDER BY c.id DESC
";

$result = $conn->query($sql);

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container p-4">
    <h3>Clients</h3>
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-4">
            <label>Server</label>
            <select name="server_id" id="serverSelect" class="form-select">
                <?php
                if ($role === 'admin') {
                    echo '<option value="">-- All Servers --</option>';
                    $servers = $conn->query("SELECT * FROM servers");
                } else {
                    $stmt = $conn->prepare("SELECT * FROM servers WHERE router_id IN (SELECT id FROM routers WHERE owner_id = ?)");
                    $stmt->bind_param("i", $employee_id);
                    $stmt->execute();
                    $servers = $stmt->get_result();
                }
                while ($srv = $servers->fetch_assoc()): ?>
                    <option value="<?= $srv['id'] ?>" <?= ($srv['id'] == $server_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($srv['server_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label>Zone</label>
            <select name="zone_id" id="zoneSelect" class="form-select">
                <option value="">-- All Zones --</option>
            </select>
        </div>

        <div class="col-md-4">
            <label>Sub Zone</label>
            <select name="subzone_id" id="subzoneSelect" class="form-select">
                <option value="">-- All Sub Zones --</option>
            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-success mt-4">🔍 Filter</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>ID</th>
            <th>Name</th>
            <th>Mobile</th>
            <th>Username</th>
            <th>Status</th>
            <th>Billing</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php 
        $i = 1;
        while ($client = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $i++; ?></td>
                <td><?= $client['client_id'] ?></td>
                <td><?= htmlspecialchars($client['customer_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($client['mobile_number'] ?? '') ?></td>
                <td><?= htmlspecialchars($client['username'] ?? '') ?></td>
                <td>
                    <span class="badge bg-<?= $client['status'] === 'active' ? 'success' : 'danger' ?>">
                        <?= ucfirst($client['status']) ?>
                    </span>
                </td>
                <td>
                    <span class="badge bg-<?= $client['billing_status'] === 'paid' ? 'success' : 'danger' ?>">
                        <?= ucfirst($client['billing_status']) ?>
                    </span>
                </td>
                <td>
                    <a href="view_client.php?id=<?= $client['client_id'] ?>" class="btn btn-primary btn-sm">View</a>
                    <a href="edit_client.php?id=<?= $client['client_id'] ?>" class="btn btn-info btn-sm">Edit</a>
                    <a href="delete_client.php?id=<?= $client['client_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script>
$('#serverSelect').on('change', function () {
    const serverId = $(this).val();
    $('#zoneSelect').html('<option value="">Loading...</option>');
    $('#subzoneSelect').html('<option value="">-- All Sub Zones --</option>');

    $.post('../network/get_zones_by_server.php', { server_id: serverId }, function (data) {
        $('#zoneSelect').html(data);
    });
});

$('#zoneSelect').on('change', function () {
    const zoneId = $(this).val();
    $('#subzoneSelect').html('<option value="">Loading...</option>');

    $.post('../network/get_subzones_by_zone.php', { zone_id: zoneId }, function (data) {
        $('#subzoneSelect').html(data);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
