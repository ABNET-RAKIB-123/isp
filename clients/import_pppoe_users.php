<?php
session_start();

// 🔒 Check login
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
// 🧑‍💼 Logged in User Info
$employee_name = $_SESSION['name'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';

require_once '../includes/db.php';
require_once '../api/mikrotik_api.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// Fetch dropdown data
$routers = $conn->query("SELECT id, router_name FROM routers");
$servers = $conn->query("SELECT id, server_name FROM servers");
$packages = $conn->query("SELECT id, package_name, price FROM packages");
$zones = $conn->query("SELECT id, zone_name FROM zones");
$subzones = $conn->query("SELECT id, subzone_name FROM subzones");

// STEP 1: Show form if not submitted yet
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
?>

<form method="POST">
    <label for="router_id">Select Router:</label>
    <select name="router_id" id="router_id" required>
        <option value="">-- Select Router --</option>
        <?php while ($router = $routers->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($router['id']) ?>"><?= htmlspecialchars($router['router_name']) ?></option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <label for="servers">Select Server:</label>
    <select name="servers" id="servers" required>
        <option value="">-- Select Server --</option>
        <?php while ($server = $servers->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($server['id']) ?>"><?= htmlspecialchars($server['server_name']) ?></option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <label for="package_id">Select Package:</label>
    <select name="package_id" id="package_id" required>
        <option value="">-- Select Package --</option>
        <?php while ($package = $packages->fetch_assoc()): ?>
            <option value="<?= $package['id'] . '|' . $package['price'] ?>">
                <?= htmlspecialchars($package['package_name']) ?> (<?= htmlspecialchars($package['price']) ?>)
            </option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <label for="zone_id">Select Zone:</label>
    <select name="zone_id" id="zone_id" required>
        <option value="">-- Select Zone --</option>
        <?php while ($zone = $zones->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($zone['id']) ?>"><?= htmlspecialchars($zone['zone_name']) ?></option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <label for="subzone_id">Select Subzone:</label>
    <select name="subzone_id" id="subzone_id" required>
        <option value="">-- Select Subzone --</option>
        <?php while ($subzone = $subzones->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($subzone['id']) ?>"><?= htmlspecialchars($subzone['subzone_name']) ?></option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <input type="submit" value="Import PPPoE Users">
</form>

<?php
    require_once '../includes/footer.php';
    exit;
}

// STEP 2: Process form
$router_id = (int)$_POST['router_id'];
$server_id = (int)$_POST['servers'];
$zone_id = (int)$_POST['zone_id'];
$subzone_id = (int)$_POST['subzone_id'];

list($package_id, $price) = explode('|', $_POST['package_id']);

// STEP 3: Fetch router info and connect
$router = $conn->query("SELECT * FROM routers WHERE id = $router_id")->fetch_assoc();

if (!$router) {
    echo "<h3>❌ Router not found in database!</h3>";
    exit;
}

$API = new RouterosAPI();
$API->port = $router['router_port'];

if (!$API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
    echo "<h3>❌ Failed to connect to MikroTik router!</h3>";
    exit;
}

$secrets = $API->comm("/ppp/secret/print");
$imported = 0;
$skipped = 0;

foreach ($secrets as $secret) {
    $username = $secret['name'];
    $password = $secret['password'];
    $profile = $secret['profile'];

    // Check if user already exists
    $check = $conn->prepare("SELECT id FROM service_information WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows > 0) {
        $skipped++;
        continue;
    }

    // Insert into clients
    $stmt = $conn->prepare("INSERT INTO clients (customer_name) VALUES (?)");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $client_id = $conn->insert_id;

    // Insert empty contact info
    $conn->query("INSERT INTO contact_information (client_id) VALUES ($client_id)");

    // Match profile
    $profile_id = null;
    $profile_result = $conn->query("SELECT id FROM profiles WHERE profile_name = '$profile' LIMIT 1");
    if ($profile_result->num_rows > 0) {
        $profile_row = $profile_result->fetch_assoc();
        $profile_id = $profile_row['id'];
    }

    // Insert into service_information
    $stmt = $conn->prepare("
        INSERT INTO service_information (client_id, router_id, username, password, package_id, profile_id, status, billing_status, money_bill)
        VALUES (?, ?, ?, ?, ?, ?, 'active', 'paid', ?)
    ");
    $stmt->bind_param("iissiid", $client_id, $router_id, $username, $password, $package_id, $profile_id, $price);
    $stmt->execute();

    // Insert into network_product_information
    $stmt = $conn->prepare("
        INSERT INTO network_product_information (client_id, server_id, zone_id, subzone_id, router_id, package_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiiiii", $client_id, $server_id, $zone_id, $subzone_id, $router_id, $package_id);
    $stmt->execute();

    $imported++;
}

$API->disconnect();

echo "<h3>✅ Import Complete!</h3>";
echo "<p>Total Imported: <strong>$imported</strong></p>";
echo "<p>Total Skipped (Already Exists): <strong>$skipped</strong></p>";

require_once '../includes/footer.php';
?>
