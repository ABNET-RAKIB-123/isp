
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

// Save devices
if (isset($_POST['devices'])) {
    $devices = json_decode($_POST['devices'], true);
    foreach ($devices as $device) {
        $type = $device['type'];
        $x_position = $device['x_position'];
        $y_position = $device['y_position'];
        $image = $device['image'];

        $stmt = $conn->prepare("INSERT INTO devices (type, x_position, y_position, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siis", $type, $x_position, $y_position, $image);
        $stmt->execute();
    }
}

// Save cables
if (isset($_POST['cables'])) {
    $cables = json_decode($_POST['cables'], true);
    foreach ($cables as $cable) {
        $device1_id = $cable['device1_id'];
        $device2_id = $cable['device2_id'];
        $cable_type = $cable['cable_type'];

        $stmt = $conn->prepare("INSERT INTO cables (device1_id, device2_id, cable_type) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $device1_id, $device2_id, $cable_type);
        $stmt->execute();
    }
}

$conn->close();
?>
