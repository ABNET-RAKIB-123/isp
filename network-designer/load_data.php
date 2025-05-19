
<?php
// Database connection
$servername = "localhost";
$username = "designer";
$password = "(w9XIEhzvHz7D9(b";
$dbname = "network_designer";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Load devices
$devices_result = $conn->query("SELECT * FROM devices");
$devices = [];
while ($row = $devices_result->fetch_assoc()) {
    $devices[] = $row;
}

// Load cables
$cables_result = $conn->query("SELECT * FROM cables");
$cables = [];
while ($row = $cables_result->fetch_assoc()) {
    $cables[] = $row;
}

$conn->close();

// Return JSON response
echo json_encode([
    'devices' => $devices,
    'cables' => $cables
]);
?>
