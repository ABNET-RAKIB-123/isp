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
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../api/mikrotik_api.php';

$username = $_POST['username'] ?? null;
$router_id = $_POST['router_id'] ?? null;

if (!$username || !$router_id) {
    die("Invalid request: missing username or router_id.");
}

// Fetch router info from DB
$stmt = $conn->prepare("SELECT * FROM routers WHERE id = ?");
$stmt->bind_param("i", $router_id);
$stmt->execute();
$router = $stmt->get_result()->fetch_assoc();

if (!$router) {
    die("Router not found.");
}

$API = new RouterosAPI();
$API->port = $router['router_port'];

$user_data = [
    'username' => $username,
    'online' => false,
    'tx-byte' => 0,
    'rx-byte' => 0,
    'uptime' => '',
    'address' => '',
];

if ($API->connect($router['router_ip'], $router['router_username'], $router['router_password'])) {
    $active_users = $API->comm("/ppp/active/print", [".proplist" => ".id,name,uptime,address,rx-byte,tx-byte"]);
    foreach ($active_users as $user) {
        if ($user['name'] === $username) {
            $user_data['online'] = true;
            $user_data['tx-byte'] = $user['tx-byte'] ?? 0;
            $user_data['rx-byte'] = $user['rx-byte'] ?? 0;
            $user_data['uptime'] = $user['uptime'] ?? '';
            $user_data['address'] = $user['address'] ?? '';
            break;
        }
    }

    $API->disconnect();
}
?>
<div class="container mt-4">
    <h2>User Details: <strong><?= htmlspecialchars($user_data['username']) ?></strong></h2>

    <div class="mb-3">
        <a href="online_users.php" class="btn btn-secondary btn-sm">&larr; Back</a>
    </div>

    <table class="table table-bordered">
        <tr>
            <th>Status</th>
            <td>
                <?php if ($user_data['online']): ?>
                    <span class="badge badge-online">Online</span>
                <?php else: ?>
                    <span class="badge badge-offline">Offline</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Uptime</th>
            <td><?= $user_data['online'] ? $user_data['uptime'] : '-' ?></td>
        </tr>
        <tr>
            <th>IP Address</th>
            <td><?= $user_data['online'] ? $user_data['address'] : '-' ?></td>
        </tr>
        <tr>
            <th>Upload</th>
            <td><span id="tx-speed"><?= $user_data['online'] ? 'Calculating...' : '-' ?></span></td>
        </tr>
        <tr>
            <th>Download</th>
            <td><span id="rx-speed"><?= $user_data['online'] ? 'Calculating...' : '-' ?></span></td>
        </tr>
    </table>
</div>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script>
<?php if ($user_data['online']): ?>
    // Fake speed simulation
    setInterval(function(){
        $('#tx-speed').text((Math.random() * 10).toFixed(2) + ' Mbps');
        $('#rx-speed').text((Math.random() * 10).toFixed(2) + ' Mbps');
    }, 3000);
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>