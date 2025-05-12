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
require_once '../api/mikrotik_api.php'; // MikroTik API Class

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = intval($_POST['client_id']);

    // --- Personal Information ---
    $customer_name = $_POST['customer_name'];
    $occupation = $_POST['occupation'];
    $father_name = $_POST['father_name'];
    $mother_name = $_POST['mother_name'];
// Validate and sanitize date_of_birth
// Add this near the top of your file or in an included file
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

$date_of_birth = $_POST['date_of_birth'];

if (empty($date_of_birth) || !validateDate($date_of_birth)) {
    $date_of_birth = NULL; // or '0000-00-00' if your DB allows
}


$gender = $_POST['gender'];

    // Get the username (to save image with username as filename)
    $username = $_POST['username'];

    // Handle profile_picture upload (optional)
    $profile_picture = uploadFile('profile_picture', $username);
    if ($profile_picture) {
        $conn->query("UPDATE clients SET profile_picture = '$profile_picture' WHERE id = $client_id");
    }

    $nid_picture = uploadFile('nid_picture', $username);
    if ($nid_picture) {
        $conn->query("UPDATE clients SET nid_picture = '$nid_picture' WHERE id = $client_id");
    }

    // --- Update Personal Information ---
    $stmt = $conn->prepare("UPDATE clients SET customer_name=?, occupation=?, father_name=?, mother_name=?, date_of_birth=?, gender=? WHERE id=?");
    $stmt->bind_param("ssssssi", $customer_name, $occupation, $father_name, $mother_name, $date_of_birth, $gender, $client_id);
    $stmt->execute();

    // --- Contact Information ---
    $mobile_number = $_POST['mobile_number'];
    $phone_number = $_POST['phone_number'];
    $email_address = $_POST['email_address'];
    $district = $_POST['district'];
    $upazila = $_POST['upazila'];

    $stmt = $conn->prepare("UPDATE contact_information SET mobile_number=?, phone_number=?, email_address=?, district=?, upazila=? WHERE client_id=?");
    $stmt->bind_param("sssssi", $mobile_number, $phone_number, $email_address, $district, $upazila, $client_id);
    $stmt->execute();

    // --- Service Information ---
    $package_prices = $_POST['package_price'];
    $new_username = $_POST['username'];
    $profile_ids = $_POST['profile_id'];
    $new_password = $_POST['password'];
    $billing_start_month = $_POST['billing_start_month'];
    $expire_date = $_POST['expire_date'];

    $stmt = $conn->prepare("UPDATE service_information SET username=?, password=?, profile_id=?, billing_start_month=?, expire_date=?, money_bill=? WHERE client_id=?");
    $stmt->bind_param("ssissdi", $new_username, $new_password, $profile_ids, $billing_start_month, $expire_date, $package_prices, $client_id);
    $stmt->execute();

    // --- Update Network Product Information ---
    $package_id = $_POST['package_ids'];
    $zone_id_U = $_POST['zone_id'];
    $subzone_id_U = $_POST['subzone_id'];
    $stmt = $conn->prepare("UPDATE network_product_information SET  package_id=?, zone_id=?, subzone_id=? WHERE client_id=?");
    $stmt->bind_param("iiii", $package_id, $zone_id_U, $subzone_id_U, $client_id);
    $stmt->execute();

    // --- Update PPPoE user on Mikrotik ---
    updatePPPoEUser($client_id, $new_username, $new_password);

    // --- After Update ---
    header("Location: list_clients.php?success=Client updated successfully");
    exit();
}

// --- Upload New File Function ---
function uploadFile($field, $username) {
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
        // Create a filename using the username and current timestamp to ensure uniqueness
        $filename = $username . '_' . time() . '_' . basename($_FILES[$field]['name']);
        
        // Define the upload path
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

// --- Update PPPoE Secret Function ---
function updatePPPoEUser($client_id, $new_username, $new_password) {
    global $conn;

    // Step 1: Get old username and server_id
    $stmt = $conn->prepare("
        SELECT si.username AS old_username, npi.server_id, si.profile_id
        FROM service_information si
        JOIN network_product_information npi ON si.client_id = npi.client_id
        WHERE si.client_id = ?
    ");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $client = $stmt->get_result()->fetch_assoc();

    if ($client) {
        $old_username = $client['old_username'];
        $server_id = $client['server_id'];
        $profile_id = $client['profile_id'];

        // Step 2: Get router info
        $stmt = $conn->prepare("SELECT router_id FROM servers WHERE id = ?");
        $stmt->bind_param("i", $server_id);
        $stmt->execute();
        $server = $stmt->get_result()->fetch_assoc();

        if ($server && $server['router_id']) {
            $router_id = $server['router_id'];

            $stmt = $conn->prepare("SELECT * FROM routers WHERE id = ?");
            $stmt->bind_param("i", $router_id);
            $stmt->execute();
            $router = $stmt->get_result()->fetch_assoc();

            // Step 3: Get profile name
            $stmt = $conn->prepare("SELECT profile_name FROM profiles WHERE id = ?");
            $stmt->bind_param("i", $profile_id);
            $stmt->execute();
            $profile = $stmt->get_result()->fetch_assoc();       
            if ($router && $profile) {
                $API = new RouterosAPI();
                $API->port = $router['router_port'];
                if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {

                    // Step 4: Find the old secret
                    $secrets = $API->comm("/ppp/secret/print", ["?name" => $_POST['old_username']]);

                    if (!empty($secrets)) {
                        $secret_id = $secrets[0]['.id'];

                        // Step 5: Update the secret
                        $API->comm("/ppp/secret/set", [
                            ".id" => $secret_id,
                            "name" => $new_username,
                            "password" => $new_password,
                            "profile" => $profile['profile_name']
                        ]);
                    }

                    $API->disconnect();
                }
            }
        }
    }
}
?>
