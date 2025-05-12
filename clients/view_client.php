<?php
session_start();

// 🔒 Check login
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
// 🧑‍💼 Logged in User Info
$employee_name = $_SESSION['name'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';
$_SESSION['id']          = $user['id'];
require_once '../includes/db.php';


$client_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$client_id) {
    echo "Invalid Client ID";
    exit;
}

$role = $_SESSION['role'] ?? '';

$client = $conn->query("SELECT * FROM clients WHERE id = $client_id")->fetch_assoc();
$contact = $conn->query("SELECT * FROM contact_information WHERE client_id = $client_id")->fetch_assoc();
$service = $conn->query("SELECT si.*, p.package_name, p.price, pr.profile_name FROM service_information si
    LEFT JOIN packages p ON si.package_id = p.id
    LEFT JOIN profiles pr ON si.profile_id = pr.id
    WHERE client_id = $client_id")->fetch_assoc();
$network = $conn->query("SELECT npi.*, s.server_name, z.zone_name, sz.subzone_name FROM network_product_information npi
    LEFT JOIN servers s ON npi.server_id = s.id
    LEFT JOIN zones z ON npi.zone_id = z.id
    LEFT JOIN subzones sz ON npi.subzone_id = sz.id
    WHERE client_id = $client_id")->fetch_assoc();
$billing = $conn->query("SELECT * FROM billing_collection WHERE client_id = $client_id ORDER BY id DESC LIMIT 1")->fetch_assoc();
$billing_history = $conn->query("SELECT b.*, ep.name FROM billing_collection b
    LEFT JOIN employees ep ON b.collected_by = ep.id
    WHERE client_id = $client_id ORDER BY client_id DESC");

// Calculate Expiry Status
$today = date('Y-m-d');
$expire_date = $service['expire_date'] ?? '';
$status_label = '';
if ($expire_date) {
    if ($expire_date >= $today) {
        $status_label = '<span class="badge bg-success">Active</span>';
    } else {
        $status_label = '<span class="badge bg-danger">Expired</span>';
    }
}
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container-fluid p-4">
    <h3>Client Profile</h3>
    <hr>

    <h5>👤 Personal Information</h5>
    <ul>
        <li><strong>Name:</strong> <?= htmlspecialchars($client['customer_name'] ?? '') ?></li>
        <li><strong>Occupation:</strong> <?= htmlspecialchars($client['occupation'] ?? '') ?></li>
        <li><strong>Date of Birth:</strong> <?= htmlspecialchars($client['date_of_birth'] ?? '') ?></li>
        <li><strong>Gender:</strong> <?= htmlspecialchars($client['gender'] ?? '') ?></li>
    </ul>

    <h5>📞 Contact Information</h5>
    <ul>
        <li><strong>Mobile:</strong> <?= htmlspecialchars($contact['mobile_number'] ?? '') ?></li>
        <li><strong>Email:</strong> <?= htmlspecialchars($contact['email_address'] ?? '') ?></li>
        <li><strong>District:</strong> <?= htmlspecialchars($contact['district'] ?? '') ?></li>
        <li><strong>Upazila:</strong> <?= htmlspecialchars($contact['upazila'] ?? '') ?></li>
    </ul>

    <h5>🌐 Network & Product Information</h5>
    <ul>
        <li><strong>Server:</strong> <?= htmlspecialchars($network['server_name'] ?? '') ?></li>
        <li><strong>Zone:</strong> <?= htmlspecialchars($network['zone_name'] ?? '') ?></li>
        <li><strong>Sub Zone:</strong> <?= htmlspecialchars($network['subzone_name'] ?? '') ?></li>
    </ul>

    <h5>🔧 Service Information</h5>
    <ul>
        <li><strong>Username:</strong> <?= htmlspecialchars($service['username'] ?? '') ?></li>
        <li><strong>Password:</strong> <?= htmlspecialchars($service['password'] ?? '') ?></li>
        <li><strong>Package:</strong> <?= htmlspecialchars($service['package_name'] ?? '') ?></li>
        <li><strong>Profile:</strong> <?= htmlspecialchars($service['profile_name'] ?? '') ?></li>
        <li><strong>Status:</strong> <?= htmlspecialchars($service['status'] ?? '') ?> <?= $status_label ?></li>
        <li><strong>Expire Date:</strong> <?= htmlspecialchars($service['expire_date'] ?? '') ?></li>
        
    </ul>

    <h5>💰 Billing Summary</h5>
    <ul>
        <li><strong>Monthly Bill:</strong> <?= $service ? $service['price'] : 'N/A' ?></li>
        <li><strong>Last Paid Amount:</strong> <?= $billing ? $billing['received_amount'] . ' ৳' : 'N/A' ?></li>
        <li><strong>Due Amount:</strong> <?= $service ? $service['money_bill'] : 'N/A' ?></li>
        <li><strong>Method:</strong> <?= $billing ? $billing['payment_method'] : 'N/A' ?></li>
        <li><strong>Last Payment Date:</strong> <?= htmlspecialchars($service['last_payment_date'] ?? '') ?></li>
        <li><strong>Next Due Date:</strong> <?= htmlspecialchars($service['next_due_date'] ?? '') ?></li>
    </ul>

    <h5>📜 Payment History</h5>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Amount</th>
                <th>Billing Month</th>
                <th>Payment Method</th>
                <th>Paid At</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($billing_history->num_rows > 0): $i = 1; while ($row = $billing_history->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['received_amount']) ?> ৳</td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['payment_method']) ?></td>
                <td><?= htmlspecialchars($row['received_date']) ?></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="5">No payment history available.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <?php if ($role !== 'support'): ?>
    <a href="#" class="btn btn-info" onclick="postEditClient(<?= $client_id ?>)">Edit Client</a>
<?php endif; ?>
<a href="list_clients.php" class="btn btn-info">Back Client</a>
</div>

<script>
function postEditClient(clientId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'edit_client.php';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'id';
    input.value = clientId;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
</script>
<?php require_once '../includes/footer.php'; ?>

