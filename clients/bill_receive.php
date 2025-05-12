<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;
    require_once '../includes/db.php';

if (isset($_POST['id'])) {
    $client_id = intval($_POST['id']);

    $stmt = $conn->prepare("
    SELECT 
        c.id as client_id,
        c.customer_name,
        ci.mobile_number,
        si.username,
        si.money_bill AS Due_Bill,
        si.status,
        p.package_name,
        p.price as monthly
    FROM clients c
    JOIN contact_information ci ON c.id = ci.client_id
    JOIN service_information si ON c.id = si.client_id
    JOIN network_product_information npi ON c.id = npi.client_id
    JOIN packages p ON npi.package_id = p.id
    WHERE c.id = ?    
");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $client = $result->fetch_assoc();
}
require_once '../includes/header.php';
?>

<div class="container mt-4">
    <h2>Bill Receive</h2>
    <?php if (!empty($client)): ?>
        <form action="save_bill.php" method="POST">
            <input type="hidden" name="client_id" value="<?= $client['client_id'] ?>">

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Client Code</label>
                    <input type="text" class="form-control" value="<?= $client['client_id'] ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label>User Name</label>
                    <input type="text" class="form-control" value="<?= $client['username'] ?>" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Mobile No.</label>
                    <input type="text" class="form-control" value="<?= $client['mobile_number'] ?? '' ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label>Package</label>
                    <input type="text" class="form-control" value="<?= isset($client['package_name']) ? $client['package_name'] : 'No data found' ?>" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Monthly Bill</label>
                    <input type="text" class="form-control" value="<?= $client['monthly'] ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label>Due Amount</label>
                    <input type="text" class="form-control" name="due_amount" value="<?= $client['Due_Bill'] ?>" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label>Receive Amount</label>
                <input type="number" name="received_amount" class="form-control" placeholder="Enter Amount" required>
            </div>

            <div class="mb-3">
                <label>Payment Method</label>
                <select name="payment_method" class="form-control" required>
                    <option value="Cash">Cash</option>
                    <option value="Bkash">Bkash</option>
                    <option value="Bank">Bank</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Submit</button>
            <a href="list_clients.php" class="btn btn-secondary">Cancel</a>
        </form>
    <?php else: ?>
        <div class="alert alert-danger">Client not found.</div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>