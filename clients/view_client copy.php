<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/db.php';

if (!isset($_GET['id'])) {
    header('Location: list_clients.php');
    exit();
}

$client_id = intval($_GET['id']);

// Fetch client info
$client = $conn->query("SELECT * FROM clients WHERE id = $client_id")->fetch_assoc();
$contact = $conn->query("SELECT * FROM contact_information WHERE client_id = $client_id")->fetch_assoc();
$network = $conn->query("SELECT * FROM network_product_information WHERE client_id = $client_id")->fetch_assoc();
$service = $conn->query("SELECT * FROM service_information WHERE client_id = $client_id")->fetch_assoc();
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Client Profile: <?= htmlspecialchars($client['customer_name']?? '') ?></h2>

    <ul class="nav nav-tabs mb-3" id="profileTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">Personal Information</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">Contact Information</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="network-tab" data-bs-toggle="tab" data-bs-target="#network" type="button" role="tab">Network & Product</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="service-tab" data-bs-toggle="tab" data-bs-target="#service" type="button" role="tab">Service Information</button>
        </li>
    </ul>

    <div class="tab-content" id="profileTabContent">
        
        <!-- Personal -->
        <div class="tab-pane fade show active" id="personal" role="tabpanel">
            <div class="card mb-4">
                <div class="card-body">
                    <p><strong>Customer Name:</strong> <?= htmlspecialchars($client['customer_name'] ?? '') ?></p>
                    <p><strong>Occupation:</strong> <?= htmlspecialchars($client['occupation'] ?? '') ?></p>
                    <p><strong>Father's Name:</strong> <?= htmlspecialchars($client['father_name'] ?? '') ?></p>
                    <p><strong>Mother's Name:</strong> <?= htmlspecialchars($client['mother_name'] ?? '') ?></p>
                    <p><strong>Date of Birth:</strong> <?= htmlspecialchars($client['date_of_birth'] ?? '') ?></p>
                    <p><strong>Gender:</strong> <?= htmlspecialchars($client['gender'] ?? '') ?></p>
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="tab-pane fade" id="contact" role="tabpanel">
            <div class="card mb-4">
                <div class="card-body">
                    <p><strong>Mobile Number:</strong> <?= htmlspecialchars($contact['mobile_number'] ?? '') ?></p>
                    <p><strong>Phone Number:</strong> <?= htmlspecialchars($contact['phone_number'] ?? '') ?></p>
                    <p><strong>Email Address:</strong> <?= htmlspecialchars($contact['email_address'] ?? '') ?></p>
                    <p><strong>District:</strong> <?= htmlspecialchars($contact['district'] ?? '') ?></p>
                    <p><strong>Upazila:</strong> <?= htmlspecialchars($contact['upazila'] ?? '') ?></p>
                    <p><strong>Road Number:</strong> <?= htmlspecialchars($contact['road_number'] ?? '') ?></p>
                    <p><strong>House Number:</strong> <?= htmlspecialchars($contact['house_number'] ?? '') ?></p>
                </div>
            </div>
        </div>

        <!-- Network -->
        <div class="tab-pane fade" id="network" role="tabpanel">
            <div class="card mb-4">
                <div class="card-body">
                    <p><strong>Server ID:</strong> <?= htmlspecialchars($network['server_id'] ?? '') ?></p>
                    <p><strong>Zone ID:</strong> <?= htmlspecialchars($network['zone_id'] ?? '') ?></p>
                    <p><strong>Subzone ID:</strong> <?= htmlspecialchars($network['subzone_id'] ?? '') ?></p>
                    <p><strong>Connection Type:</strong> <?= htmlspecialchars($network['connection_type'] ?? '') ?></p>
                    <p><strong>Protocol Type:</strong> <?= htmlspecialchars($network['protocol_type'] ?? '') ?></p>
                    <p><strong>Cable Requirement:</strong> <?= htmlspecialchars($network['cable_requirement_meter'] ?? '') ?> meters</p>
                </div>
            </div>
        </div>

        <!-- Service -->
        <div class="tab-pane fade" id="service" role="tabpanel">
            <div class="card mb-4">
                <div class="card-body">
                    <p><strong>Username:</strong> <?= htmlspecialchars($service['username'] ?? '') ?></p>
                    <p><strong>Package ID:</strong> <?= htmlspecialchars($service['package_id'] ?? '') ?></p>
                    <p><strong>Profile ID:</strong> <?= htmlspecialchars($service['profile_id'] ?? '') ?></p>
                    <p><strong>Billing Start Month:</strong> <?= htmlspecialchars($service['billing_start_month'] ?? '') ?></p>
                    <p><strong>Expire Date:</strong> <?= htmlspecialchars($service['expire_date'] ?? '') ?></p>
                    <p><strong>Status:</strong> 
                        <span class="badge <?= $service['billing_status'] == 'Active' ? 'bg-success' : 'bg-danger' ?>">
                            <?= htmlspecialchars($service['billing_status'] ?? '') ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-4">
        <a href="list_clients.php" class="btn btn-secondary">Back to List</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
