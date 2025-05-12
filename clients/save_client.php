<?php
// Show errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $server_id = $_POST['server_id'];
    // 1. Personal Information
    $customer_name = $_POST['customer_name'];
    $occupation = $_POST['occupation'];
    $father_name = $_POST['father_name'];
    $mother_name = $_POST['mother_name'];
    
    // Validate and sanitize date_of_birth
    $date_of_birth = $_POST['date_of_birth'];
    
    // Check if the date is empty or invalid
    if (empty($date_of_birth) || !validateDate($date_of_birth)) {
        // If empty or invalid, set it to NULL or a default date
        $date_of_birth = NULL; // Or use '0000-00-00' if your DB allows that
    }
    $gender = $_POST['gender'];
    $nid_certificate_no = $_POST['nid_certificate_no'];
    $registration_form_no = $_POST['registration_form_no'];
    $remarks = $_POST['remarks'];

    // Get username from POST data
    $username = $_POST['username'];

    // Handle file uploads (Profile and NID Pictures) with username included in the filename
    $profile_picture = uploadFile('profile_picture', $username);
    $nid_picture = uploadFile('nid_picture', $username);

    // Insert into clients table
    $stmt = $conn->prepare("INSERT INTO clients 
        (profile_picture, customer_name, occupation, father_name, mother_name, date_of_birth, gender, nid_certificate_no, registration_form_no, nid_picture, remarks)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssss", 
        $profile_picture, $customer_name, $occupation, $father_name, $mother_name, 
        $date_of_birth, $gender, $nid_certificate_no, $registration_form_no, $nid_picture, $remarks
    );
    $stmt->execute();
    $client_id = $stmt->insert_id;

    // 2. Contact Information
    $mobile_number = $_POST['mobile_number'];
    $phone_number = $_POST['phone_number'];
    $email_address = $_POST['email_address'];
    $district = $_POST['district'];
    $upazila = $_POST['upazila'];
    $road_number = $_POST['road_number'];
    $house_number = $_POST['house_number'];
    $permanent_address = $_POST['permanent_address'];
    $linkedin_url = $_POST['linkedin_url'];
    $twitter_url = $_POST['twitter_url'];
    $same_as_present_address = isset($_POST['same_as_present_address']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO contact_information 
        (client_id, mobile_number, phone_number, email_address, district, upazila, road_number, house_number, permanent_address, linkedin_url, twitter_url, same_as_present_address)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssssssi", 
        $client_id, $mobile_number, $phone_number, $email_address, $district, $upazila,
        $road_number, $house_number, $permanent_address, $linkedin_url, $twitter_url, $same_as_present_address
    );
    $stmt->execute();

    // 3. Network & Product Information
    $zone_id = $_POST['zone_id'];
    $subzone_id = $_POST['subzone_id'];
    $connection_type = $_POST['router_id'];
    $cable_requirement_meter = $_POST['package_id'];

    $stmt = $conn->prepare("INSERT INTO network_product_information 
    (client_id, server_id, zone_id, subzone_id, router_id, package_id) 
    VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiiii",
        $client_id, $server_id, $zone_id, $subzone_id, $connection_type, $cable_requirement_meter
    );
    $stmt->execute();

    // 4. Service Information
    $package_id = $_POST['package_id'];
    $profile_id = $_POST['profile_id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $billing_start_month = $_POST['billing_start_month'];
    $expire_date = $_POST['expire_date'];
    $vip_client = isset($_POST['vip_client']) ? 1 : 0;
    $send_greeting_sms = isset($_POST['send_greeting_sms']) ? 1 : 0;
    $joining_date = $_POST['joining_date'];
    $money_bill = $_POST['package_price'];

    $stmt = $conn->prepare("INSERT INTO service_information 
        (client_id, router_id, package_id, profile_id, username, password, billing_start_month, expire_date, vip_client, send_greeting_sms, joining_date, money_bill)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiissssissi", 
        $client_id, $server_id, $package_id, $profile_id, $username, $password,
        $billing_start_month, $expire_date, $vip_client, $send_greeting_sms, $joining_date, $money_bill
    );
    $stmt->execute();

    // 5. Create PPPoE User on MikroTik
    createPPPoEUser($server_id, $username, $password, $profile_id);

    // Redirect after success
    header("Location: list_clients.php?success=Client Added Successfully");
    exit();
}

// --- Helper functions ---

function uploadFile($field, $username) {
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
        // Create a filename using the username and current timestamp to ensure uniqueness
        $filename = $username . '_' . time() . '_' . basename($_FILES[$field]['name']);
        $uploadPath = "../uploads/" . $filename;

        // Move the uploaded file to the destination folder
        if (move_uploaded_file($_FILES[$field]['tmp_name'], $uploadPath)) {
            // Return the file path to save in the database
            return "uploads/" . $filename;
        } else {
            // If the file couldn't be moved, return false
            return false;
        }
    }
    return null; // Return null if no file is uploaded or there's an error
}

function createPPPoEUser($server_id, $username, $password, $profile_id) {
    global $conn;
    require_once '../api/mikrotik_api.php'; // Your MikroTik API PHP class

    // Find router ID based on server
    $stmt = $conn->prepare("SELECT router_id FROM servers WHERE id = ?");
    $stmt->bind_param("i", $server_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $server = $result->fetch_assoc();

    if ($server && $server['router_id']) {
        $router_id = $server['router_id'];

        // Get router details
        $stmt = $conn->prepare("SELECT * FROM routers WHERE id = ?");
        $stmt->bind_param("i", $router_id);
        $stmt->execute();
        $router = $stmt->get_result()->fetch_assoc();

        // Get profile name
        $stmt = $conn->prepare("SELECT profile_name FROM profiles WHERE id = ?");
        $stmt->bind_param("i", $profile_id);
        $stmt->execute();
        $profile = $stmt->get_result()->fetch_assoc();

        if ($router && $profile) {
            $API = new RouterosAPI();
            $API->port = $router['router_port'];
            if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
                $API->comm("/ppp/secret/add", [
                    "name" => $username,
                    "password" => $password,
                    "profile" => $profile['profile_name'],
                    "service" => "pppoe"
                ]);
                $API->disconnect();
            }
        }
    }
}
?>
