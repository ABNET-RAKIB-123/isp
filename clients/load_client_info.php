<?php
 // Your database connection
require_once '../includes/db.php';

if (isset($_GET['id'])) {
    $client_id = $_GET['id'];

    $stmt = $conn->prepare("SELECT c.id as client_id, c.client_code, si.username, ci.mobile_number, np.package_name, np.monthly_bill
        FROM clients c
        JOIN service_information si ON c.id = si.client_id
        JOIN contact_information ci ON c.id = ci.client_id
        JOIN network_product_information np ON c.id = np.client_id
        WHERE c.id = ?");
    $stmt->bind_param('i', $client_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    $data = [
        'client_code' => $result['client_code'],
        'username' => $result['username'],
        'mobile_number' => $result['mobile_number'],
        'package' => $result['package_name'],
        'monthly_bill' => $result['monthly_bill'],
        'due_amount' => $result['monthly_bill'] // assuming due = bill
    ];

    echo json_encode($data);
}
?>
