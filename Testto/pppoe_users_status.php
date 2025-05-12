<?php
require('../api/mikrotik_api.php');

// MikroTik login info

$host = "103.14.151.28";
$user = "NOC";
$pass = "NOC@321";
$port = 5057;


$API = new RouterosAPI();
$API->port = $port;
$allUsers = [];

if ($API->connect($host, $user, $pass)) {

    // Get ALL secrets (configured PPPoE users)
    $API->write('/ppp/secret/print');
    $secrets = $API->read();

    // Get ALL active users
    $API->write('/ppp/active/print');
    $activeUsers = $API->read();

    $activeMap = [];
    foreach ($activeUsers as $active) {
        $activeMap[$active['name']] = $active;
    }

    foreach ($secrets as $secret) {
        $username = $secret['name'];
        $isOnline = isset($activeMap[$username]);
           // Format last-logged-out time (24-hour format only)
           $lastLogout = '-';
           if (!$isOnline && !empty($secret['last-logged-out']) && strtotime($secret['last-logged-out'])) {
               $lastLogout = date('Y-m-d H:i:s', strtotime($secret['last-logged-out']));
           }
           
   

        $userData = [
            'username' => $username,
            'status' => $isOnline ? 'Online' : 'Offline',
            'uptime' => $isOnline ? $activeMap[$username]['uptime'] : '-',
            'caller_id' => $isOnline ? $activeMap[$username]['caller-id'] : '-',
            // 'last_logout' => !$isOnline && isset($secret['last-logged-out']) ? $secret['last-logged-out'] : '-'
            'last_logout' => $lastLogout
        ];

        $allUsers[] = $userData;
    }

    $API->disconnect();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>All PPPoE Users Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="500"> <!-- Refresh every 15s -->
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3 class="mb-4">PPPoE User Status Overview</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-hover shadow bg-white">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Uptime</th>
                    <th>Caller ID</th>
                    <th>Last Logged Out</th>
                </tr>
            </thead>
            <tbody>
            <?php $I = 1; if (count($allUsers)): ?>
                <?php foreach ($allUsers as $user): ?>
                    
                    <tr class="<?= $user['status'] == 'Online' ? 'table-success' : 'table-danger' ?>">
                    <td><?= $I++; ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><strong><?= $user['status'] ?></strong></td>
                        <td><?= $user['uptime'] ?></td>
                        <td><?= $user['caller_id'] ?></td>
                        <td><?= $user['last_logout'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No users found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
