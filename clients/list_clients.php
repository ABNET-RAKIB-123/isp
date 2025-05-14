<?php
session_start();

// 🔒 Check login
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
// 🧑‍💼 Logged in User Info
$employee_id= $_SESSION['employee_id'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $server_id   = isset($_POST['server_id']) ? (int)$_POST['server_id'] : 0;
    $zone_id     = isset($_POST['zone_id']) ? (int)$_POST['zone_id'] : 0;
    $subzone_id  = isset($_POST['subzone_id']) ? (int)$_POST['subzone_id'] : 0;
    $status      = isset($_POST['status']) ? $_POST['status'] : '';

    $where = [];
    if ($server_id > 0) $where[] = "npi.server_id = $server_id";
    if ($zone_id > 0) $where[] = "npi.zone_id = $zone_id";
    if ($subzone_id > 0) $where[] = "npi.subzone_id = $subzone_id";
    if (!empty($status)) $where[] = "si.status = '" . $conn->real_escape_string($status) . "'";

    if ($role === 'editor' || $role === 'support') {
        $where[] = "npi.router_id IN (SELECT id FROM routers WHERE owner_id = $employee_id)";
    }

    $where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $sql = "
        SELECT
            c.id AS client_id,
            c.customer_name,
            ci.mobile_number,
            si.username,
            si.status,
            si.billing_status,
            r.owner_id as router_owner_id
        FROM clients c
        JOIN contact_information ci ON c.id = ci.client_id
        JOIN service_information si ON c.id = si.client_id
        JOIN network_product_information npi ON c.id = npi.client_id
        JOIN routers r ON npi.router_id = r.id
        $where_sql
        ORDER BY c.id DESC
    ";
    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $row['can_edit'] = $role === 'admin' || ($role === 'editor' && $row['router_owner_id'] == $employee_id);
        $row['can_payment'] = $role === 'admin' || $role === 'support' || $row['billing_status'] !== 'paid';
        $data[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>
<style>
    body.sidebar-open {
        overflow: hidden;
    }
    div.dataTables_wrapper {
        width: 100%;
        overflow-x: auto;
    }
</style>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('mobileSidebar');
        const isVisible = sidebar.style.display === 'block';
        sidebar.style.display = isVisible ? 'none' : 'block';
        document.body.classList.toggle('sidebar-open', !isVisible);
    }
</script>

<div class="container-fluid p-4">
    <h3>Clients</h3>
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3 mb-2">
            <label>Server</label>
            <select id="serverFilter" class="form-select">
                <option value="">All Servers</option>
                <?php
                $server_sql = $role === 'admin'
                    ? "SELECT * FROM servers"
                    : "SELECT * FROM servers WHERE router_id IN (SELECT id FROM routers WHERE owner_id = $employee_id)";
                $servers = $conn->query($server_sql);
                while ($s = $servers->fetch_assoc()):
                    echo "<option value='{$s['id']}'>{$s['server_name']}</option>";
                endwhile;
                ?>
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-3 mb-2">
            <label>Zone</label>
            <select id="zoneFilter" class="form-select"><option value="">All Zones</option></select>
        </div>
        <div class="col-12 col-sm-6 col-md-3 mb-2">
            <label>Subzone</label>
            <select id="subzoneFilter" class="form-select"><option value="">All Subzones</option></select>
        </div>
        <div class="col-12 col-sm-6 col-md-3 mb-2">
            <label>Status</label>
            <select id="statusFilter" class="form-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Disabled</option>
            </select>
        </div>
    </div>

    <div class="rounded-lg shadow" style="overflow-x: auto; width: 100%;">
    <table id="clientTable" class="table table-hover nowrap text-center" style="width: 100%;">

            <thead class="table-dark">
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">ID</th>
                <th class="text-center">Name</th>
                <th class="text-center">Mobile</th>
                <th class="text-center">PPPoE USERS</th>
                <th class="text-center">Status</th>
                <th class="text-center">M.S</th>
                <th class="text-center">Billing</th>
                <th class="text-center">Actions</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
<script>
$(document).ready(function () {
    const table = $('#clientTable').DataTable({
        scrollX: true, // 👈 ADD THIS
        pageLength: 25,
        language: {
            paginate: {
                previous: "‹",
                next: "›"
            }
        },
        ajax: {
            url: '',
            type: 'POST',
            data: function (d) {
                d.server_id = $('#serverFilter').val();
                d.zone_id = $('#zoneFilter').val();
                d.subzone_id = $('#subzoneFilter').val();
                d.status = $('#statusFilter').val();
            },
            dataSrc: ''
        },
        destroy: true,
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'client_id' },
            { data: 'customer_name' },
            { data: 'mobile_number' },
            { data: 'username' },
            {
                data: 'status',
                render: status => `<span class="badge bg-${status === 'active' ? 'success' : 'danger'}">${status}</span>`
            },
            {
                data: null,
                render: function(row, type, data, meta) {
                    <?php if ($role !== 'support'): ?>
                    let checked = row.status === 'active' ? 'checked' : '';
                    return `
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="statusSwitch${row.client_id}" ${checked}
                                onchange="if(confirm('Are you sure you want to ${row.status === 'active' ? 'disable' : 'enable'} this user?')) submitPost('toggle_user_status.php', { id: '${row.client_id}', action: '${row.status === 'active' ? 'disable' : 'enable'}' }); else this.checked = ${row.status === 'active'};">
                        </div>`;
                    <?php else: ?>
                    return `<div class="form-check form-switch"><input class="form-check-input" type="checkbox" disabled></div>`;
                    <?php endif; ?>
                }
            },
            {
                data: null,
                render: row => row.billing_status === 'paid'
                    ? `<a href="#" class="btn btn-sm btn-primary" onclick="submitPost('bill_receive.php', { id: '${row.client_id}' })">Pay</a><span class="badge bg-success">Paid</span>`
                    : row.can_payment
                    ? `<a href="#" class="btn btn-sm btn-primary" onclick="submitPost('bill_receive.php', { id: '${row.client_id}' })">Pay</a><span class="badge bg-success">Pay</span>`
                    : ''
            },
            {
                data: null,
                render: row => {
                    let html = `
                        <div class="d-none d-md-inline">
                            <a href="#" class="btn btn-primary btn-sm" onclick="submitPost('view_client.php', { id: '${row.client_id}' })">View</a>
                    `;
                    <?php if ($role !== 'support'): ?>
                        html += `
                            <a href="#" class="btn btn-info btn-sm" onclick="submitPost('edit_client.php', { id: '${row.client_id}' })">Edit</a>
                            <a href="#" class="btn btn-danger btn-sm" onclick="if(confirm('Delete user?')) submitPost('delete_client.php', { id: '${row.client_id}' })">Delete</a>
                        `;
                    <?php else: ?>
                        html += `
                            <a href="#" class="btn btn-info btn-sm disabled">Edit</a>
                            <a href="#" class="btn btn-danger btn-sm disabled">Delete</a>
                        `;
                    <?php endif; ?>
                    html += `</div><div class="dropdown d-md-none">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">⋮</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="submitPost('view_client.php', { id: '${row.client_id}' })">View</a></li>
                    `;
                    <?php if ($role !== 'support'): ?>
                        html += `
                            <li><a class="dropdown-item" href="#" onclick="submitPost('edit_client.php', { id: '${row.client_id}' })">Edit</a></li>
                            <li><a class="dropdown-item" href="#" onclick="if(confirm('Delete user?')) submitPost('delete_client.php', { id: '${row.client_id}' })">Delete</a></li>
                        `;
                    <?php else: ?>
                        html += `
                            <li><a class="dropdown-item disabled" href="#">Edit</a></li>
                            <li><a class="dropdown-item disabled" href="#">Delete</a></li>
                        `;
                    <?php endif; ?>
                    html += `</ul></div>`;
                    return html;
                }
            }
        ]
    });

    $('#serverFilter, #zoneFilter, #subzoneFilter, #statusFilter').on('change', function () {
        table.ajax.reload();
    });

    $('#serverFilter').on('change', function () {
        const serverId = $(this).val();
        $('#zoneFilter').html('<option value="">Loading...</option>');
        $('#subzoneFilter').html('<option value="">All Subzones</option>');
        $.post('../network/get_zones_by_server.php', { server_id: serverId }, function (data) {
            $('#zoneFilter').html(data);
        });
    });

    $('#zoneFilter').on('change', function () {
        const zoneId = $(this).val();
        $('#subzoneFilter').html('<option value="">Loading...</option>');
        $.post('../network/get_subzones_by_zone.php', { zone_id: zoneId }, function (data) {
            $('#subzoneFilter').html(data);
        });
    });
});

function submitPost(url, data) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.style.display = 'none';

    for (const key in data) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = data[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}
</script>

<?php require_once '../includes/footer.php'; ?>
