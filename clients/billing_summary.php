<?php
session_start();

// 🔒 Check login
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
// 🧑‍💼 Logged in User Info
$employee_name = $_SESSION['employee_name'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container-fluid p-4">
    <h3>💳 Client Payment Report with Due (AJAX Version)</h3>

    <form id="filterForm" method="POST" class="row g-3 mb-4">
        <div class="col-md-3">
            <label>Server</label>
            <select name="server_id" class="form-select">
                <option value="">All Servers</option>
                <?php 
                $servers = $conn->query("SELECT * FROM servers");
                while($s = $servers->fetch_assoc()): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['server_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label>Employee</label>
            <select name="employee_id" class="form-select">
                <option value="">All Employees</option>
                <?php 
                $employees = $conn->query("SELECT * FROM employees");
                while($e = $employees->fetch_assoc()): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>

        <div class="col-md-2">
            <label>End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>

        <div class="col-md-2">
            <label>Search (Name/Mobile)</label>
            <input type="text" name="search" class="form-control" placeholder="Search...">
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-success mt-4">🔍 Filter</button>
        </div>
    </form>

    <div id="billingData">
        <!-- Billing Data Table will load here via AJAX -->
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<!-- jQuery Required -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

<script>
function loadBillingData(page = 1) {
    let formData = $("#filterForm").serialize() + '&page=' + page;
    $.ajax({
        url: '../api/billing_ajax.php',
        method: 'POST',
        data: formData,
        success: function(response) {
            $("#billingData").html(response);
        }
    });
}

// On page load
$(document).ready(function() {
    loadBillingData();

    // Filter form submit
    $("#filterForm").submit(function(e) {
        e.preventDefault();
        loadBillingData();
    });

    // Pagination click
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        loadBillingData(page);
    });
});
</script>
