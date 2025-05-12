<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../admin/login.php");
    exit;
}
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;

require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container p-4">
    <h3>Clients</h3>

    <!-- Filter Section -->
    <div class="row mb-3">
        <div class="col-md-3">
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
        <div class="col-md-3">
            <label>Zone</label>
            <select id="zoneFilter" class="form-select"><option value="">All Zones</option></select>
        </div>
        <div class="col-md-3">
            <label>Subzone</label>
            <select id="subzoneFilter" class="form-select"><option value="">All Subzones</option></select>
        </div>
        <div class="col-md-3">
            <label>Status</label>
            <select id="statusFilter" class="form-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="disabled">Disabled</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <table id="clientTable" class="table table-bordered">
        <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>ID</th>
            <th>Name</th>
            <th>Mobile</th>
            <th>Username</th>
            <th>Status</th>
            <th>Account</th>
            <th>Billing</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Scripts -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"> -->

<script>
$(document).ready(function () {
    const table = $('#clientTable').DataTable({
        ajax: {
            url: 'fetch_all_clients.php',
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
                render: row =>
                    row.status === 'active'
                        ? `<a href="toggle_user_status.php?id=${row.client_id}&action=disable" class="btn btn-warning btn-sm" onclick="return confirm('Disable user?')">Disable</a>`
                        : `<a href="toggle_user_status.php?id=${row.client_id}&action=enable" class="btn btn-success btn-sm" onclick="return confirm('Enable user?')">Enable</a>`
            },
            {
                data: 'billing_status',
                render: status =>
                    status === 'paid'
                        ? `<span class="badge bg-success">Paid</span>`
                        : `<a href="bill_receive.php?id=${row.client_id}" class="btn btn-sm btn-primary">Pay</a>`
            },
            {
                data: null,
                render: row => `
                    <a href="view_client.php?id=${row.client_id}" class="btn btn-primary btn-sm">View</a>
                    <a href="edit_client.php?id=${row.client_id}" class="btn btn-info btn-sm">Edit</a>
                    <a href="delete_client.php?id=${row.client_id}" class="btn btn-danger btn-sm" onclick="return confirm('Delete user?')">Delete</a>
                `
            }
        ]
    });

    // Reload table on filter change
    $('#serverFilter, #zoneFilter, #subzoneFilter, #statusFilter').on('change', function () {
        table.ajax.reload();
    });

    // Dependent dropdowns
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
</script>

<?php require_once '../includes/footer.php'; ?>
