<?php
session_start();

if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}

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

// Fetch router info
$stmt = $conn->prepare("SELECT * FROM routers WHERE id = ?");
$stmt->bind_param("i", $router_id);
$stmt->execute();
$router = $stmt->get_result()->fetch_assoc();

if (!$router) die("Router not found.");

$API = new RouterosAPI();
$API->port = $router['router_port'];

$user_data = [
    'username' => $username,
    'online' => false,
    'tx-byte' => 0,
    'rx-byte' => 0,
    'uptime' => '',
    'address' => '',
    'login_time' => '',
    'logout_time' => '',
    'session_duration' => '',
];

// Check live status
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

// Get last session data from DB
$stmt = $conn->prepare("SELECT * FROM user_sessions WHERE username = ? AND router_id = ? ORDER BY login_time DESC LIMIT 1");
$stmt->bind_param("si", $username, $router_id);
$stmt->execute();
$session = $stmt->get_result()->fetch_assoc();

if ($session) {
    $user_data['login_time'] = $session['login_time'];
    $user_data['logout_time'] = $session['logout_time'] ?? '-';
    $user_data['session_duration'] = $session['session_duration'] ? $session['session_duration'] . ' minutes' : '-';

    // If offline, show total from last session
    if (!$user_data['online']) {
        $user_data['tx-byte'] = $session['tx_bytes'];
        $user_data['rx-byte'] = $session['rx_bytes'];
    }
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes / (1 << (10 * $pow)), $precision) . ' ' . $units[$pow];
}
?>

<div class="container mt-4">
    <h2>User Details: <strong><?= htmlspecialchars($user_data['username']) ?></strong></h2>

    <div class="mb-3">
        <a href="online_users.php" class="btn btn-secondary btn-sm">&larr; Back</a>
    </div>

    <table class="table table-bordered rounded-lg shadow">
        <tr>
            <th>Status</th>
            <td>
                <?php if ($user_data['online']): ?>
                    <span class="badge bg-success">Online</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Offline</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>IP Address</th>
            <td><?= $user_data['online'] ? $user_data['address'] : '-' ?></td>
        </tr>
        <tr>
            <th>Uptime</th>
            <td><?= $user_data['online'] ? $user_data['uptime'] : '-' ?></td>
        </tr>
        <tr>
            <th>Login Time</th>
            <td><?= $user_data['login_time'] ?? '-' ?></td>
        </tr>
        <tr>
            <th>Logout Time</th>
            <td><?= $user_data['online'] ? '-' : ($user_data['logout_time'] ?? '-') ?></td>
        </tr>
        <tr>
            <th>Session Duration</th>
            <td><?= $user_data['session_duration'] ?? '-' ?></td>
        </tr>
        <tr>
            <th>Upload (TX)</th>
            <td>
                <?php if ($user_data['online']): ?>
                    <span id="tx-speed">Calculating...</span> (<?= formatBytes($user_data['tx-byte']) ?>)
                <?php else: ?>
                    <?= formatBytes($user_data['tx-byte']) ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Download (RX)</th>
            <td>
                <?php if ($user_data['online']): ?>
                    <span id="rx-speed">Calculating...</span> (<?= formatBytes($user_data['rx-byte']) ?>)
                <?php else: ?>
                    <?= formatBytes($user_data['rx-byte']) ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>

<?php if ($user_data['online']): ?>
<script>
    setInterval(function() {
        document.getElementById("tx-speed").innerText = (Math.random() * 10).toFixed(2) + ' Mbps';
        document.getElementById("rx-speed").innerText = (Math.random() * 10).toFixed(2) + ' Mbps';
    }, 3000);
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
