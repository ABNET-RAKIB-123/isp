<?php
require('../api/mikrotik_api.php');
// Target PPPoE username
$username = "kb001";

// MikroTik credentials
$targetUsername = "kb017"; // change to your user's PPPoE name
$host = "103.14.151.25";
$login = "NOC";
$password = "NOC@#321";
$port = 8028;


// MySQL connection
$pdo = new PDO("mysql:host=localhost;dbname=isp_management", "roott", "StrongP@ssw0rd!");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Helper: Convert uptime (e.g., 2d3h5m6s) to seconds
function parseUptimeToSeconds($uptime) {
    preg_match_all('/(\d+)([dhms])/', $uptime, $matches, PREG_SET_ORDER);
    $seconds = 0;
    foreach ($matches as $match) {
        $value = (int)$match[1];
        switch ($match[2]) {
            case 'd': $seconds += $value * 86400; break;
            case 'h': $seconds += $value * 3600; break;
            case 'm': $seconds += $value * 60; break;
            case 's': $seconds += $value; break;
        }
    }
    return $seconds;
}

// Initialize
$isOnline = false;
$connectTime = null;
$disconnectTime = null;

// Connect to MikroTik
$API = new RouterosAPI();
$API->port = $port;
if ($API->connect($host, $login, $password)) {
    $API->write('/ppp/active/print', false);
    $API->write('?name=' . $username, true);
    $result = $API->read();
    $API->disconnect();

    if (!empty($result)) {
        // ✅ User is online
        $isOnline = true;
        $uptime = $result[0]['uptime'];
        $secondsAgo = parseUptimeToSeconds($uptime);
        $connectTimestamp = time() - $secondsAgo;
        $connectTime = date('Y-m-d H:i:s', $connectTimestamp);

        // Log to DB if not already
        $stmt = $pdo->prepare("SELECT * FROM pppoe_user_log WHERE username=? AND disconnect_time IS NULL");
        $stmt->execute([$username]);
        if ($stmt->rowCount() === 0) {
            $pdo->prepare("INSERT INTO pppoe_user_log (username, connect_time) VALUES (?, NOW())")
                ->execute([$username]);
        }

    } else {
        // ❌ User is offline, close previous session
        $stmt = $pdo->prepare("SELECT * FROM pppoe_user_log WHERE username=? AND disconnect_time IS NULL");
        $stmt->execute([$username]);
        $session = $stmt->fetch();

        if ($session) {
            $pdo->prepare("UPDATE pppoe_user_log SET disconnect_time = NOW() WHERE id=?")
                ->execute([$session['id']]);
        }

        // Get last disconnect time
        $stmt = $pdo->prepare("SELECT disconnect_time FROM pppoe_user_log WHERE username=? AND disconnect_time IS NOT NULL ORDER BY id DESC LIMIT 1");
        $stmt->execute([$username]);
        $last = $stmt->fetch();
        if ($last) {
            $disconnectTime = $last['disconnect_time'];
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PPPoE User Status: <?= htmlspecialchars($username) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="10"> <!-- Auto-refresh every 10 seconds -->
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3>PPPoE User Status: <strong><?= htmlspecialchars($username) ?></strong></h3>

    <div class="card border-<?= $isOnline ? 'success' : 'danger' ?> mt-4 shadow">
        <div class="card-body">
            <h5>Status:
                <span class="badge bg-<?= $isOnline ? 'success' : 'danger' ?>">
                    <?= $isOnline ? 'Online' : 'Offline' ?>
                </span>
            </h5>

            <?php if ($isOnline): ?>
                <p><strong>Connected Since:</strong> <?= $connectTime ?></p>
            <?php elseif ($disconnectTime): ?>
                <p><strong>Last Disconnected:</strong> <?= $disconnectTime ?></p>
            <?php else: ?>
                <p class="text-muted">No session history found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
