<?php
require('../api/mikrotik_api.php');


    $API = new RouterosAPI();
    $API->port = 18710;
    $statusFilter = $_GET['status'] ?? 'all'; // Get filter from dropdown
$statusFilter = $_GET['status'] ?? 'all';
$search = strtolower($_GET['search'] ?? '');

if ($API->connect('103.14.151.24', 'NOC', 'NOC@#321')) {
    $onlineUsers = $API->comm('/ppp/active/print');
    $allUsers = $API->comm('/ppp/secret/print');
    $onlineNames = array_column($onlineUsers, 'name');
?>
<!DOCTYPE html>
<html>
<head>
    <title>PPPoE Users Filter & Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">PPPoE User List</h2>

    <!-- Filter & Search Form -->
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="all" <?= $statusFilter == 'all' ? 'selected' : '' ?>>All Users</option>
                <option value="online" <?= $statusFilter == 'online' ? 'selected' : '' ?>>Online Users</option>
                <option value="offline" <?= $statusFilter == 'offline' ? 'selected' : '' ?>>Offline Users</option>
            </select>
        </div>
        <div class="col-md-6">
            <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="form-control" placeholder="Search by Username">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
    </form>

    <!-- User Table -->
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>Service</th>
                <th>Profile</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $count = 1;
        foreach ($allUsers as $user):
            $isOnline = in_array($user['name'], $onlineNames);

            // Filter status
            if (
                ($statusFilter == 'online' && !$isOnline) ||
                ($statusFilter == 'offline' && $isOnline)
            ) continue;

            // Filter by search (case-insensitive)
            if ($search && stripos($user['name'], $search) === false) continue;
        ?>
            <tr>
                <td><?= $count++ ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['service']) ?></td>
                <td><?= htmlspecialchars($user['profile']) ?></td>
                <td>
                    <?php if ($isOnline): ?>
                        <span class="badge bg-success">Online</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Offline</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
<?php
    $API->disconnect();
} else {
    echo "Failed to connect to MikroTik.";
}
?>
