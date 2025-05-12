<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;
$Employee_id_databases = $_SESSION['id'] ?? 0;
require_once '../includes/db.php';
require_once '../api/mikrotik_api.php';

// Show errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = intval($_POST['client_id']);

    // --- Personal Information ---
    $customer_name = $_POST['customer_name'] ?? '';
    $occupation = $_POST['occupation'] ?? '';
    $father_name = $_POST['father_name'] ?? '';
    $mother_name = $_POST['mother_name'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';

    $profile_picture = uploadFile('profile_picture');
    $nid_picture = uploadFile('nid_picture');

    if ($profile_picture) {
        $conn->query("UPDATE clients SET profile_picture = '$profile_picture' WHERE id = $client_id");
    }

    if ($nid_picture) {
        $conn->query("UPDATE clients SET nid_picture = '$nid_picture' WHERE id = $client_id");
    }

    $stmt = $conn->prepare("UPDATE clients SET customer_name=?, occupation=?, father_name=?, mother_name=?, date_of_birth=?, gender=? WHERE id=?");
    $stmt->bind_param("ssssssi", $customer_name, $occupation, $father_name, $mother_name, $date_of_birth, $gender, $client_id);
    $stmt->execute();

    // --- Contact Information ---
    $mobile_number = $_POST['mobile_number'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $email_address = $_POST['email_address'] ?? '';
    $district = $_POST['district'] ?? '';
    $upazila = $_POST['upazila'] ?? '';

    $stmt = $conn->prepare("UPDATE contact_information SET mobile_number=?, phone_number=?, email_address=?, district=?, upazila=? WHERE client_id=?");
    $stmt->bind_param("sssssi", $mobile_number, $phone_number, $email_address, $district, $upazila, $client_id);
    $stmt->execute();

    // --- Service Information ---
    $new_username = $_POST['username'] ?? '';
    $new_password = $_POST['password'] ?? '';
    $new_package_id = intval($_POST['package_ids']);
    $new_profile_id = intval($_POST['profile_id']);
    $billing_start_month = $_POST['billing_start_month'] ?? '';
    $expire_date = $_POST['expire_date'] ?? '';

    $stmt = $conn->prepare("UPDATE service_information SET username=?, password=?, package_id=?, profile_id=?, billing_start_month=?, expire_date=? WHERE client_id=?");
    $stmt->bind_param("ssiissi", $new_username, $new_password, $new_package_id, $new_profile_id, $billing_start_month, $expire_date, $client_id);
    $stmt->execute();

    // --- Network Info ---
    $new_server_id = intval($_POST['server_id']);
    $new_router_id = intval($_POST['router_id']);
    $zone_id = intval($_POST['zone_id']);
    $subzone_id = intval($_POST['subzone_id']);

    $stmt = $conn->prepare("SELECT server_id FROM network_product_information WHERE client_id=?");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $old_server_id = $stmt->get_result()->fetch_assoc()['server_id'] ?? 0;

    $stmt = $conn->prepare("UPDATE network_product_information SET server_id=?, router_id=?, zone_id=?, subzone_id=? WHERE client_id=?");
    $stmt->bind_param("iiiii", $new_server_id, $new_router_id, $zone_id, $subzone_id, $client_id);
    $stmt->execute();

    // --- PPPoE User Handling ---
    updatePPPoEUser($conn, $client_id, $new_username, $new_password, $new_profile_id, $old_server_id, $new_server_id);

    header("Location: list_clients.php?success=Client updated successfully");
    exit();
}

// --- Upload file ---
function uploadFile($field) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;

    $filename = time() . '_' . basename($_FILES[$field]['name']);
    $destination = "../uploads/" . $filename;

    if (move_uploaded_file($_FILES[$field]['tmp_name'], $destination)) {
        return "uploads/" . $filename;
    }
    return null;
}

// --- Update PPPoE ---
function updatePPPoEUser($conn, $client_id, $new_username, $new_password, $new_profile_id, $old_server_id, $new_server_id) {
    // Get router info
    $stmt = $conn->prepare("SELECT r.router_ip, r.router_username, r.router_password, r.router_port
                            FROM routers r JOIN servers s ON r.id = s.router_id WHERE s.id = ?");
    $stmt->bind_param("i", $old_server_id);
    $stmt->execute();
    $old_router = $stmt->get_result()->fetch_assoc();

    $stmt->bind_param("i", $new_server_id);
    $stmt->execute();
    $new_router = $stmt->get_result()->fetch_assoc();

    // Get profile name
    $stmt = $conn->prepare("SELECT profile_name FROM profiles WHERE id = ?");
    $stmt->bind_param("i", $new_profile_id);
    $stmt->execute();
    $profile_name = $stmt->get_result()->fetch_assoc()['profile_name'] ?? 'default';

    // Remove from old router
    if ($old_server_id != $new_server_id && $old_router) {
        $API = new RouterosAPI();
        $API->port = $old_router['router_port'];
        if ($API->connect($old_router['router_ip'], $old_router['router_username'], $old_router['router_password'])) {
            $secrets = $API->comm("/ppp/secret/print", ["?name" => $new_username]);
            if (!empty($secrets)) {
                $API->comm("/ppp/secret/remove", [".id" => $secrets[0]['.id']]);
            }
            $API->disconnect();
        }
    }

    // Add to new router
    if ($new_router) {
        $API = new RouterosAPI();
        $API->port = $new_router['router_port'];
        if ($API->connect($new_router['router_ip'], $new_router['router_username'], $new_router['router_password'])) {
            $API->comm("/ppp/secret/add", [
                "name" => $new_username,
                "password" => $new_password,
                "profile" => $profile_name,
                "service" => "pppoe"
            ]);
            $API->disconnect();
        }
    }
}
?>

