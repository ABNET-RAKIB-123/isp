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

    if (is_array($active_users)) {
        foreach ($active_users as $user) {
            if ($user['name'] === $username) {
                $user_data['online'] = true;
                $user_data['tx-byte'] = $user['tx-byte'] ?? 0;
                $user_data['rx-byte'] = $user['rx-byte'] ?? 0;
                $user_data['uptime'] = $user['uptime'] ?? '';
                $user_data['address'] = $user['address'] ?? '';

                // ✅ Insert session if not already inserted
                $check = $conn->prepare("SELECT id FROM user_sessions WHERE username = ? AND router_id = ? AND DATE(login_time) = CURDATE() AND logout_time IS NULL");
                $check->bind_param("si", $username, $router_id);
                $check->execute();
                $res = $check->get_result();

                if ($res->num_rows === 0) {
                    $insert = $conn->prepare("INSERT INTO user_sessions (username, router_id, login_time, ip_address, tx_bytes, rx_bytes) VALUES (?, ?, NOW(), ?, ?, ?)");
                    $insert->bind_param("sisii", $username, $router_id, $user_data['address'], $user_data['tx-byte'], $user_data['rx-byte']);
                    $insert->execute();
                }

                break;
            }
        }
    }

    $API->disconnect();
}

// 🕒 Update logout if user is offline and has open session
if (!$user_data['online']) {
    $check = $conn->prepare("SELECT id, login_time FROM user_sessions WHERE username = ? AND router_id = ? AND logout_time IS NULL");
    $check->bind_param("si", $username, $router_id);
    $check->execute();
    $result = $check->get_result();

    if ($row = $result->fetch_assoc()) {
        $session_id = $row['id'];
        $login_time = new DateTime($row['login_time']);
        $logout_time = new DateTime();
        $duration = $login_time->diff($logout_time)->i + $login_time->diff($logout_time)->h * 60;

        // add this before the UPDATE query
            $last_tx = $user_data['tx-byte'] ?? 0;
            $last_rx = $user_data['rx-byte'] ?? 0;

            // update query now with bytes
            $update = $conn->prepare("UPDATE user_sessions SET logout_time = NOW(), session_duration = ?, tx_bytes = ?, rx_bytes = ? WHERE id = ?");
            $update->bind_param("iiii", $duration, $last_tx, $last_rx, $session_id);
            $update->execute();

    }
}

// 🧾 Fetch last seen info
$inactive_minutes = '-';
if (!$user_data['online']) {
    $stmt = $conn->prepare("SELECT TIMESTAMPDIFF(MINUTE, logout_time, NOW()) AS inactive_minutes FROM user_sessions WHERE username = ? AND router_id = ? ORDER BY logout_time DESC LIMIT 1");
    $stmt->bind_param("si", $username, $router_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $inactive_minutes = $row['inactive_minutes'] . ' minutes ago';
    }
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
        <tr>
            <th>Last Seen</th>
            <td><?= $user_data['online'] ? '-' : $inactive_minutes ?></td>
        </tr>
    </table>
</div>

<script>
<?php if ($user_data['online']): ?>
    // Simulate live speed
    setInterval(function(){
        document.getElementById('tx-speed').innerText = (Math.random() * 10).toFixed(2) + ' Mbps';
        document.getElementById('rx-speed').innerText = (Math.random() * 10).toFixed(2) + ' Mbps';
    }, 3000);
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>
