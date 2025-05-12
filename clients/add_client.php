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

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/db.php';
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Add New Client</h2>
    <form action="save_client.php" method="POST" enctype="multipart/form-data">
        <div class="accordion" id="clientAccordion">
            
            <!-- Personal Information -->
            <div class="accordion-item mb-3">
                <h2 class="accordion-header" id="personalHeading">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#personalInfo" aria-expanded="true">
                        1. Personal Information
                    </button>
                </h2>
                <div id="personalInfo" class="accordion-collapse collapse show" data-bs-parent="#clientAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Customer Name *</label>
                                <input type="text" name="customer_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Occupation</label>
                                <input type="text" name="occupation" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Father's Name</label>
                                <input type="text" name="father_name" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Mother's Name</label>
                                <input type="text" name="mother_name" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>NID Certificate No</label>
                                <input type="text" name="nid_certificate_no" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Registration Form No</label>
                                <input type="text" name="registration_form_no" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Profile Picture</label>
                                <input type="file" name="profile_picture" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>NID/ID Card Picture</label>
                                <input type="file" name="nid_picture" class="form-control">
                            </div>
                            <div class="col-12 mb-3">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="accordion-item mb-3">
                <h2 class="accordion-header" id="contactHeading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#contactInfo">
                        2. Contact Information
                    </button>
                </h2>
                <div id="contactInfo" class="accordion-collapse collapse" data-bs-parent="#clientAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Mobile Number</label>
                                <input type="text" name="mobile_number" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Phone Number</label>
                                <input type="text" name="phone_number" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email Address</label>
                                <input type="email" name="email_address" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>District</label>
                                <input type="text" name="district" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Upazila</label>
                                <input type="text" name="upazila" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Road No.</label>
                                <input type="text" name="road_number" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>House No.</label>
                                <input type="text" name="house_number" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Permanent Address</label>
                                <input type="text" name="permanent_address" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>LinkedIn URL</label>
                                <input type="url" name="linkedin_url" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Twitter URL</label>
                                <input type="url" name="twitter_url" class="form-control">
                            </div>
                            <div class="col-md-12 mb-3">
                                <input type="checkbox" name="same_as_present_address" value="1">
                                Same As Present Address
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
                        <div class="col-md-6 mb-3">
                            <label>Server</label>
                            <select name="server_id" id="server_id" class="form-select" required>
                                <option value="">Select Server</option>
                                <?php
                                $servers = $conn->query("SELECT * FROM servers");
                                while ($server = $servers->fetch_assoc()):
                                ?>
                                    <option value="<?= $server['id'] ?>"><?= htmlspecialchars($server['server_name']) ?></option>
                                <?php endwhile; ?>
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
                                <option value="">Select Sub_Zone</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Router</label>
                            <select name="router_id" id="router_id" class="form-select" required>
                                <option value="">Select Router</option>
                                <?php
                                $routers = $conn->query("SELECT * FROM routers");
                                while ($router = $routers->fetch_assoc()):
                                ?>
                                    <option value="<?= $router['id'] ?>"><?= htmlspecialchars($router['router_name']) ?> (<?= $router['router_ip'] ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                                <div class="col-md-6 mb-3">
                                    <label>Package</label>
                                    <select name="package_id" id="package_id" class="form-select" required>
                                    <option value="">Select Profile</option>
                                </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                <label>Profile (MikroTik PPPoE)</label>
                                <select name="profile_id" id="profile_id" class="form-select" required>
                                    <option value="">Select Profile</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Service Information -->
            <div class="accordion-item mb-3">
                <h2 class="accordion-header" id="serviceHeading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#serviceInfo">
                        4. Service Information
                    </button>
                </h2>
                <div id="serviceInfo" class="accordion-collapse collapse" data-bs-parent="#clientAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Password</label>
                                <input type="text" name="password" class="form-control">
                            </div>


                            <div class="col-md-6 mb-3">
                                <label>Package Price (BDT)</label>
                                <input type="text" name="package_price" id="package_price" class="form-control" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Joining Date</label>
                                <input type="date" name="joining_date" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Billing Start Month</label>
                                <input type="date" name="billing_start_month" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Expire Date</label>
                                <input type="date" name="expire_date" class="form-control">
                            </div>
                            <div class="col-md-12 mb-3">
                                <input type="checkbox" name="vip_client" value="1"> VIP Client
                            </div>
                            <div class="col-md-12 mb-3">
                                <input type="checkbox" name="send_greeting_sms" value="1"> Send Greeting SMS
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-4 d-flex justify-content-end">
            <?php if ($role !== 'support'): ?>
                <button type="submit" class="btn btn-success me-2">➕ Add Client</button>
                <a href="list_clients.php" class="btn btn-secondary">Cancel</a>
            <?php else: ?>
                <button type="button" class="btn btn-success me-2" disabled>➕ Add Client (No Permission)</button>
                <a href="list_clients.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>



    </form>
</div>


<script>
$(document).ready(function(){

    $('#server_id').change(function(){
        var server_id = $(this).val();
        if (server_id) {
            $.ajax({
                url: '../api/load_zones.php',
                method: 'POST',
                data: {server_id: server_id},
                success: function(data) {
                    $('#zone_id').html(data);
                    $('#subzone_id').html('<option value="">Sub_Zone</option>'); // reset subzone
                }
            });
        } else {
            $('#zone_id').html('<option value="">Select Zone</option>');
            $('#subzone_id').html('<option value="">Sub_Zone</option>');
        }
    });

    $('#zone_id').change(function(){
        var zone_id = $(this).val();
        if (zone_id) {
            $.ajax({
                url: '../api/load_subzones.php',
                method: 'POST',
                data: {server_id: zone_id},
                success: function(data) {
                    $('#subzone_id').html(data);
                }
            });
        } else {
            $('#subzone_id').html('<option value="">Select Subzone</option>');
        }
    });

});


$('#server_id').change(function(){
        var package_id = $(this).val();
            $.ajax({
                url: '../api/load_package_price.php',
                method: 'POST',
                data: {package_id: package_id},
                success: function(data) {
                    $('#package_id').html(data);
                }
            });
    });

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

        // Load Profiles from Router linked to Server
    $('#server_id').change(function(){
        var server_id = $(this).val();
        $.ajax({
            url: '../api/load_profiles_by_server.php',
            method: 'POST',
            data: {server_id: server_id},
            success: function(data){
                $('#profile_id').html(data);
            }
        });

    });
    
    
</script>





<?php require_once '../includes/footer.php'; ?>
