<?php
require_once '../includes/db.php';

// আজকের তারিখ
$today = date('Y-m-d');

// Step 1: Find all clients where expire_date = today
$sql = "
    SELECT si.id, si.client_id, si.money_bill, si.package_id, p.price
    FROM service_information si
    JOIN packages p ON si.package_id = p.id
    WHERE si.expire_date = '$today' AND si.status = 'active'
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $client_id = $row['client_id'];
        $current_money_bill = (float)$row['money_bill'];
        $package_price = (float)$row['price'];

        // New Money Bill = Old Money Bill + Package Price (Only 1 month)
        $new_money_bill = $current_money_bill + $package_price;

        // Step 2: Update ONLY money_bill (expire_date will remain unchanged)
        $update = "
            UPDATE service_information
            SET money_bill = $new_money_bill
            WHERE client_id = $client_id
        ";

        $conn->query($update);
    }

    echo "<h3>✅ Billing updated successfully for clients expiring today ($today)!</h3>";
} else {
    echo "<h3>ℹ️ No client expiring today ($today).</h3>";
}
?>
