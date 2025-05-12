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
$client_id = intval($_POST['id']);
$profile_id = intval($_POST['id']);
$sub_zone_id = intval($_POST['id']);

// Fetch client data
$client = $conn->query("SELECT * FROM clients WHERE id = $client_id")->fetch_assoc();
$contact = $conn->query("SELECT * FROM contact_information WHERE client_id = $client_id")->fetch_assoc();
$network = $conn->query("SELECT * FROM network_product_information WHERE client_id = $client_id")->fetch_assoc();
$service = $conn->query("SELECT * FROM service_information WHERE client_id = $client_id")->fetch_assoc();
// $service = $conn->query("SELECT * FROM subzones WHERE client_id = $client_id")->fetch_assoc();
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Edit Client - <?= htmlspecialchars($client['customer_name'] ?? '') ?></h2>

    <form action="update_client_copy.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="client_id" value="<?= $client_id ?>">
        <input type="hidden" id="selected_server" value="<?= htmlspecialchars($network['server_id'] ?? '') ?>">
        <input type="hidden" id="selected_zone" value="<?= htmlspecialchars($network['zone_id'] ?? '') ?>">
        <input type="hidden" id="selected_subzone" value="<?= htmlspecialchars($network['subzone_id'] ?? '') ?>">
        <input type="hidden" id="selected_profile" value="<?= htmlspecialchars($service['profile_id'] ?? '') ?>">
        <input type="hidden" id="selected_package" value="<?= htmlspecialchars($service['package_id'] ?? '') ?>">


        <div class="accordion" id="editClientAccordion">
            <!-- 1. Personal Information -->
            <div class="accordion-item mb-3">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#personalInfo" aria-expanded="true">
                        Personal Information
                    </button>
                </h2>
                <div id="personalInfo" class="accordion-collapse collapse show" data-bs-parent="#editClientAccordion">
                    <div class="accordion-body">
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label>Customer Name *</label>
                                <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($client['customer_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Occupation</label>
                                <input type="text" name="occupation" class="form-control" value="<?= htmlspecialchars($client['occupation'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Father's Name</label>
                                <input type="text" name="father_name" class="form-control" value="<?= htmlspecialchars($client['father_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Mother's Name</label>
                                <input type="text" name="mother_name" class="form-control" value="<?= htmlspecialchars($client['mother_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($client['date_of_birth'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="Male" <?= ($client['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= ($client['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                                    <option value="Other" <?= ($client['gender'] == 'Other') ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Profile Picture</label><br>
                                <?php if (!empty($client['profile_picture'])): ?>
                                    <img src="../<?= $client['profile_picture'] ?>" alt="Profile" width="100" height="100" class="rounded mb-2"><br>
                                <?php endif; ?>
                                <input type="file" name="profile_picture" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>NID Picture</label><br>
                                <?php if (!empty($client['nid_picture'])): ?>
                                    <img src="../<?= $client['nid_picture'] ?>" alt="NID" width="100" height="100" class="rounded mb-2"><br>
                                <?php endif; ?>
                                <input type="file" name="nid_picture" class="form-control">
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Contact Information -->
            <div class="accordion-item mb-3">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#contactInfo">
                        Contact Information
                    </button>
                </h2>
                <div id="contactInfo" class="accordion-collapse collapse" data-bs-parent="#editClientAccordion">
                    <div class="accordion-body">
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label>Mobile Number</label>
                                <input type="text" name="mobile_number" class="form-control" value="<?= htmlspecialchars($contact['mobile_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" value="<?= htmlspecialchars($contact['phone_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email Address</label>
                                <input type="email" name="email_address" class="form-control" value="<?= htmlspecialchars($contact['email_address'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>District</label>
                                <input type="text" name="district" class="form-control" value="<?= htmlspecialchars($contact['district'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Upazila</label>
                                <input type="text" name="upazila" class="form-control" value="<?= htmlspecialchars($contact['upazila'] ?? '') ?>">
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            
            <!-- Network & Product Information -->
            <div class="accordion-item mb-3">
                <h2 class="accordion-header" id="networkHeading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#networkInfo">
                        3. Network & Product Information
                    </button>
                </h2>
                <div id="networkInfo" class="accordion-collapse collapse" data-bs-parent="#clientAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <!-- Dynamic Dropdown Server, Zone, Subzone -->
                            <div class="col-md-6 mb-3">
                                <label>Server</label>
                                <select name="server_id" id="server_id" class="form-select" required>
                                    <option value="">Select Server</option>
                                    <?php
                                    $servers = $conn->query("SELECT * FROM servers");
                                    while ($server = $servers->fetch_assoc()) {
                                        echo '<option value="'.$server['id'].'" '.($server['id'] == $network['server_id'] ? 'selected' : '').'>'.$server['server_name'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Zone</label>
                                <select name="zone_id" id="zone_id" class="form-select" required>
                                    <option value="">Select Zone</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Subzone</label>
                                <select name="subzone_id" id="subzone_id" class="form-select" required>
                                    <option value="">Select Subzone</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Connection Type</label>
                                <select name="connection_type" class="form-select">
                                    <option>Wired</option>
                                    <option>Wireless</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Protocol Type</label>
                                <select name="protocol_type" class="form-select">
                                    <option>PPPoE</option>
                                    <option>Static IP</option>
                                    <option>DHCP</option>
                                </select>
                            </div>
                                <div class="col-md-6 mb-3">
                                    <label>Profile (MikroTik PPPoE)</label>
                                    <select name="profile_id" id="profile_id" class="form-select" required>
                                        <option value="">Select Profile</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                <label>Package</label>
                                <select name="package_ids" id="package_id" class="form-select" required>
                                    <option value="">Select Package</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Package Price (BDT)</label>
                                <input type="text" name="package_price" id="package_price" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Service Information -->
            <div class="accordion-item mb-3">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#serviceInfo">
                        Service Information
                    </button>
                </h2>
                <div id="serviceInfo" class="accordion-collapse collapse" data-bs-parent="#editClientAccordion">
                    <div class="accordion-body">
                        <div class="row">

                            <!-- Hidden input for old username -->
                            <input type="hidden" name="old_username" value="<?= htmlspecialchars($service['username'] ?? '') ?>">

                            <!-- Visible input for new username -->
                            <div class="col-md-6 mb-3">
                                <label>Username (PPPoE ?? '')</label>
                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($service['username'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Password</label>
                                <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($service['password'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                            <label>Billing Start Month</label>
                            <input type="date" name="billing_start_month" id="billing_start_month" class="form-control" value="<?= htmlspecialchars($service['billing_start_month'] ?? '') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Expire Date</label>
                            <input type="date" name="expire_date" id="expire_date" class="form-control" value="<?= htmlspecialchars($service['expire_date'] ?? '') ?>">
                        </div>


                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-4 d-flex justify-content-end">
            <button type="submit" class="btn btn-success me-2">Update Client</button>
            <a href="list_clients.php" class="btn btn-secondary">Cancel</a>
        </div>

    </form>
</div>
<script>
$(document).ready(function(){

    var selected_server = $('#selected_server').val();
    var selected_zone = $('#selected_zone').val();
    var selected_subzone = $('#selected_subzone').val();
    var selected_profile = $('#selected_profile').val();
    var selected_package = $('#selected_package').val();

    // On Page Load: Load Zones, Subzones, Profiles, Packages
    if(selected_server) {
        loadZones(selected_server, selected_zone);
        if(selected_zone) {
            loadSubzones(selected_zone, selected_subzone);
        }
        loadProfiles(selected_server, selected_profile);
        loadPackages(selected_server, selected_package);
    }

    // When Server Changes
    $('#server_id').change(function(){
        var server_id = $(this).val();
        loadZones(server_id, null);
        loadProfiles(server_id, null);
        loadPackages(server_id, null);
        $('#subzone_id').html('<option value=\"\">Select Subzone</option>'); // Reset subzone
    });

    // When Zone Changes
    $('#zone_id').change(function(){
        var zone_id = $(this).val();
        loadSubzones(zone_id, null);
    });

    // Load Zones by Server
    function loadZones(server_id, selected) {
        $.ajax({
            url: '../api/load_zones.php',
            method: 'POST',
            data: {server_id: server_id},
            success: function(data){
                $('#zone_id').html(data);
                if (selected) $('#zone_id').val(selected).change();
            }
        });
    }

    // Load Subzones by Zone
    function loadSubzones(zone_id, selected) {
        $.ajax({
            url: '../api/load_subzones_by_zone.php',
            method: 'POST',
            data: {zone_id: zone_id},
            success: function(data){
                $('#subzone_id').html(data);
                if (selected) $('#subzone_id').val(selected);
            }
        });
    }

    // Load Profiles by Server
    function loadProfiles(server_id, selected) {
        $.ajax({
            url: '../api/load_profiles_by_server.php',
            method: 'POST',
            data: {server_id: server_id},
            success: function(data){
                $('#profile_id').html(data);
                if (selected) $('#profile_id').val(selected);
            }
        });
    }

    // Load Packages by Server
    function loadPackages(server_id, selected) {
        $.ajax({
            url: '../api/load_packages.php',
            method: 'POST',
            data: {server_id: server_id},
            success: function(data){
                $('#package_id').html(data);
                if (selected) $('#package_id').val(selected);
            }
        });
    }

    $('#package_id').change(function(){
        var package_id = $(this).val();
        if (package_id) {
            $.ajax({
                url: '../api/load_package_price_copy.php',
                method: 'POST',
                data: {package_id: package_id},
                success: function(price) {
                    $('#package_price').val(price);
                }
            });
        } else {
            $('#package_price').val('');
        }
    });


    // Billing Date Auto Calculate Expire Date
    $('#billing_start_month').change(function(){
        var start_date = $(this).val();
        if (start_date) {
            var date = new Date(start_date);
            date.setMonth(date.getMonth() + 1);

            var year = date.getFullYear();
            var month = ('0' + (date.getMonth() + 1)).slice(-2);
            var day = ('0' + date.getDate()).slice(-2);

            var expire_date = year + '-' + month + '-' + day;
            $('#expire_date').val(expire_date);
        }
    });

});
</script>
<?php require_once '../includes/footer.php'; ?>
