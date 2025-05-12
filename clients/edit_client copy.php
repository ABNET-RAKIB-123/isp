<?php
session_start();
require_once '../includes/db.php';

// 🔒 Login Check
if (!isset($_SESSION['employee_id'])) {
    header('Location: ../admin/login.php');
    exit;
}

// Check ID
if (!isset($_GET['id'])) {
    header('Location: list_clients.php');
    exit;
}

$client_id = intval($_GET['id']);
$client = $conn->query("SELECT * FROM clients WHERE id = $client_id")->fetch_assoc();
$contact = $conn->query("SELECT * FROM contact_information WHERE client_id = $client_id")->fetch_assoc();
$network = $conn->query("SELECT * FROM network_product_information WHERE client_id = $client_id")->fetch_assoc();
$service = $conn->query("SELECT * FROM service_information WHERE client_id = $client_id")->fetch_assoc();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Edit Client - <?= htmlspecialchars($client['customer_name']) ?></h2>

    <form action="update_client.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="client_id" value="<?= $client_id ?>">

        <div class="row">

            <!-- Personal Information -->
            <div class="col-md-12 mb-4">
                <h5>Personal Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <label>Customer Name *</label>
                        <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($client['customer_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>Occupation</label>
                        <input type="text" name="occupation" class="form-control" value="<?= htmlspecialchars($client['occupation']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Father's Name</label>
                        <input type="text" name="father_name" class="form-control" value="<?= htmlspecialchars($client['father_name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Mother's Name</label>
                        <input type="text" name="mother_name" class="form-control" value="<?= htmlspecialchars($client['mother_name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($client['date_of_birth']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Gender</label>
                        <select name="gender" class="form-select">
                            <option value="Male" <?= ($client['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($client['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= ($client['gender'] == 'Other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="col-md-12 mb-4">
                <h5>Contact Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <label>Mobile Number</label>
                        <input type="text" name="mobile_number" class="form-control" value="<?= htmlspecialchars($contact['mobile_number']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" value="<?= htmlspecialchars($contact['phone_number']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Email Address</label>
                        <input type="email" name="email_address" class="form-control" value="<?= htmlspecialchars($contact['email_address']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label>District</label>
                        <input type="text" name="district" class="form-control" value="<?= htmlspecialchars($contact['district']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Upazila</label>
                        <input type="text" name="upazila" class="form-control" value="<?= htmlspecialchars($contact['upazila']) ?>">
                    </div>
                </div>
            </div>

            <!-- Network & Product Information -->
            <div class="col-md-12 mb-4">
                <h5>Network & Product Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <label>Server</label>
                        <select name="server_id" id="server_id" class="form-select" required>
                            <option value="">Select Server</option>
                            <?php
                            $servers = $conn->query("SELECT * FROM servers");
                            while ($srv = $servers->fetch_assoc()):
                            ?>
                            <option value="<?= $srv['id'] ?>" <?= ($srv['id'] == $network['server_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($srv['server_name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Zone</label>
                        <select name="zone_id" id="zone_id" class="form-select" required>
                            <!-- Loaded by AJAX -->
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Subzone</label>
                        <select name="subzone_id" id="subzone_id" class="form-select" required>
                            <!-- Loaded by AJAX -->
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Connection Type</label>
                        <select name="connection_type" class="form-select">
                            <option value="Wired" <?= ($network['connection_type'] == 'Wired') ? 'selected' : '' ?>>Wired</option>
                            <option value="Wireless" <?= ($network['connection_type'] == 'Wireless') ? 'selected' : '' ?>>Wireless</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Protocol Type</label>
                        <select name="protocol_type" class="form-select">
                            <option value="PPPoE" <?= ($network['protocol_type'] == 'PPPoE') ? 'selected' : '' ?>>PPPoE</option>
                            <option value="Static IP" <?= ($network['protocol_type'] == 'Static IP') ? 'selected' : '' ?>>Static IP</option>
                            <option value="DHCP" <?= ($network['protocol_type'] == 'DHCP') ? 'selected' : '' ?>>DHCP</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Service Information -->
            <div class="col-md-12 mb-4">
                <h5>Service Information</h5>
                <div class="row">
                    <input type="hidden" name="old_username" value="<?= htmlspecialchars($service['username']) ?>">

                    <div class="col-md-6">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($service['username']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>Password</label>
                        <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($service['password']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>Package</label>
                        <select name="package_ids" id="package_id" class="form-select" required>
                            <!-- Loaded by AJAX -->
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Profile</label>
                        <select name="profile_id" id="profile_id" class="form-select" required>
                            <!-- Loaded by AJAX -->
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Billing Start</label>
                        <input type="date" name="billing_start_month" class="form-control" value="<?= htmlspecialchars($service['billing_start_month']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Expire Date</label>
                        <input type="date" name="expire_date" class="form-control" value="<?= htmlspecialchars($service['expire_date']) ?>">
                    </div>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-success">Update Client</button>
                <a href="list_clients.php" class="btn btn-secondary">Cancel</a>
            </div>

        </div>
    </form>
</div>

<script>
// Auto Load Zones, Subzones, Profiles, Packages
$(document).ready(function(){
    var server_id = <?= intval($network['server_id']) ?>;
    var zone_id = <?= intval($network['zone_id']) ?>;
    var subzone_id = <?= intval($network['subzone_id']) ?>;
    var profile_id = <?= intval($service['profile_id']) ?>;
    var package_id = <?= intval($service['package_id']) ?>;

    if (server_id) {
        loadZones(server_id, zone_id);
        loadProfiles(server_id, profile_id);
        loadPackages(server_id, package_id);
    }

    $('#server_id').change(function(){
        var serverId = $(this).val();
        loadZones(serverId, null);
        loadProfiles(serverId, null);
        loadPackages(serverId, null);
        $('#subzone_id').html('<option value="">Select Subzone</option>');
    });

    $('#zone_id').change(function(){
        var zoneId = $(this).val();
        loadSubzones(zoneId, null);
    });

    function loadZones(server_id, selected) {
        $.post('../api/load_zones.php', {server_id: server_id}, function(data){
            $('#zone_id').html(data);
            if (selected) $('#zone_id').val(selected).change();
        });
    }

    function loadSubzones(zone_id, selected) {
        $.post('../api/load_subzones_by_zone.php', {zone_id: zone_id}, function(data){
            $('#subzone_id').html(data);
            if (selected) $('#subzone_id').val(selected);
        });
    }

    function loadProfiles(server_id, selected) {
        $.post('../api/load_profiles_by_server.php', {server_id: server_id}, function(data){
            $('#profile_id').html(data);
            if (selected) $('#profile_id').val(selected);
        });
    }

    function loadPackages(server_id, selected) {
        $.post('../api/load_packages.php', {server_id: server_id}, function(data){
            $('#package_id').html(data);
            if (selected) $('#package_id').val(selected);
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
