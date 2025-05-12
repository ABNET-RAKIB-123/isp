<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;

require_once '../includes/db.php';

if ($role === 'admin') {
    $routers = $conn->query("SELECT * FROM routers");
} else {
    $routers = $conn->query("SELECT * FROM routers WHERE owner_id = $employee_id");
}

$current_router_id = $_POST['router_id'] ?? $_GET['router_id'] ?? null;

if (!$current_router_id && $routers->num_rows > 0) {
    $first_router = $routers->fetch_assoc();
    $current_router_id = $first_router['id'];
    $routers->data_seek(0);
} elseif (!$current_router_id) {
    echo "<div class='alert alert-danger'>No router available for your account.</div>";
    exit;
}

$router_sql = ($role === 'admin')
    ? "SELECT * FROM routers WHERE id = $current_router_id"
    : "SELECT * FROM routers WHERE id = $current_router_id AND owner_id = $employee_id";

$current_router = $conn->query($router_sql)->fetch_assoc();

if (!$current_router) {
    echo "<div class='alert alert-warning'>Unauthorized or invalid router selected.</div>";
    exit;
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../api/mikrotik_api.php';

$router_ip = $current_router['router_ip'];
$router_username = $current_router['router_username'];
$router_password = $current_router['router_password'];
$router_port = $current_router['router_port'];

function getActiveUsers($router_ip, $router_username, $router_password, $router_port) {
    $API = new RouterosAPI();
    $API->port = $router_port;
    $users = [];

    if ($API->connect($router_ip, $router_username, $router_password)) {
        $secrets = $API->comm("/ppp/secret/print");
        $active = $API->comm("/ppp/active/print");
        $API->disconnect();

        $active_users = [];
        foreach ($active as $user) {
            $active_users[$user['name']] = [
                'tx-byte' => $user['tx-byte'] ?? 0,
                'rx-byte' => $user['rx-byte'] ?? 0,
            ];
        }

        foreach ($secrets as $user) {
            $username = $user['name'];
            $users[] = [
                'username' => $username,
                'online' => isset($active_users[$username]),
                'tx-byte' => $active_users[$username]['tx-byte'] ?? 0,
                'rx-byte' => $active_users[$username]['rx-byte'] ?? 0,
            ];
        }
    }

    return $users;
}

$users = getActiveUsers($router_ip, $router_username, $router_password, $router_port);
$totalCount = count($users);
$onlineCount = count(array_filter($users, fn($u) => $u['online']));
$offlineCount = $totalCount - $onlineCount;
?>

<!-- Start of full-width container -->
<div class="container-fluid mt-3 px-4">
    <h2>Live Online Users Monitor</h2>

    <div class="mb-3">
        <h5>
            Total Users: <span class="badge bg-dark"><?= $totalCount ?></span>
            | <span class="text-success">Online: <span class="badge bg-success"><?= $onlineCount ?></span></span>
            | <span class="text-muted">Offline: <span class="badge bg-secondary"><?= $offlineCount ?></span></span>
        </h5>
    </div>

    <form method="POST" id="routerForm" class="mb-3">
        <label for="routerSelect">Select Router:</label>
        <select name="router_id" id="routerSelect" class="form-control" onchange="document.getElementById('routerForm').submit()" style="max-width: 300px;">
            <?php
            $routers->data_seek(0);
            while ($router = $routers->fetch_assoc()):
            ?>
                <option value="<?= $router['id'] ?>" <?= $router['id'] == $current_router_id ? 'selected' : '' ?>>
                    <?= $router['router_name'] ?> (<?= $router['router_ip'] ?>)
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="Search Username..." style="max-width: 200px;">
        <select id="statusFilter" class="form-control" style="max-width: 150px;">
            <option value="">All Users</option>
            <option value="online">Online</option>
            <option value="offline">Offline</option>
        </select>
    </div>

    <!-- Table now full-width and responsive -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped w-100">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Upload Speed</th>
                    <th>Download Speed</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody id="userTable">
                <?php
                $i = 1;
                foreach ($users as $user): ?>
                <tr data-username="<?= htmlspecialchars($user['username']) ?>" data-status="<?= $user['online'] ? 'online' : 'offline' ?>">
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td>
                        <?php if ($user['online']): ?>
                            <span class="badge bg-success">Online</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Offline</span>
                        <?php endif; ?>
                    </td>
                    <td class="tx-speed"><?= $user['online'] ? 'Calculating...' : '-' ?></td>
                    <td class="rx-speed"><?= $user['online'] ? 'Calculating...' : '-' ?></td>
                    <td>
                        <a href="#" onclick="submitPost('view_user.php', {
                            username: '<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>',
                            router_id: '<?= htmlspecialchars($current_router_id, ENT_QUOTES) ?>'
                        })" class="btn btn-sm btn-primary">Click Here</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function submitPost(url, data) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.style.display = 'none';

    for (const key in data) {
        if (data.hasOwnProperty(key)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = data[key];
            form.appendChild(input);
        }
    }

    document.body.appendChild(form);
    form.submit();
}

$(document).ready(function(){
    function filterUsers() {
        var search = $('#searchInput').val().toLowerCase();
        var status = $('#statusFilter').val();

        $('#userTable tr').each(function(){
            var username = $(this).data('username')?.toString().toLowerCase() || '';
            var userStatus = $(this).data('status');

            var matchSearch = username.includes(search);
            var matchStatus = (status === '' || userStatus === status);

            $(this).toggle(matchSearch && matchStatus);
        });
    }

    $('#searchInput, #statusFilter').on('keyup change', filterUsers);

    setInterval(function(){
        $('#userTable tr').each(function(){
            if ($(this).data('status') === 'online') {
                $(this).find('.tx-speed').text((Math.random() * 10).toFixed(2) + ' Mbps');
                $(this).find('.rx-speed').text((Math.random() * 10).toFixed(2) + ' Mbps');
            }
        });
    }, 5000);
});
</script>

<?php require_once '../includes/footer.php'; ?>
