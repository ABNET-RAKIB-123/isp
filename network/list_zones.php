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
$_SESSION['id']          = $user['id'];
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// Filters
$server_id = isset($_GET['server_id']) ? (int)$_GET['server_id'] : 0;

$where = [];
if ($server_id > 0) {
    $where[] = "z.server_id = $server_id";
}
$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Fetch all zones with server names
$sql = "
    SELECT z.id, z.zone_name, s.server_name, z.created_at
    FROM zones z
    JOIN servers s ON z.server_id = s.id
    $where_sql
    ORDER BY z.id DESC
";
$result = $conn->query($sql);
?>

<div class="container p-4">
    <h3>All Zones</h3>
    <?php if($role !=='support') :?>
    <a href="add_zone.php" class="btn btn-primary mb-3">➕ Add Zone</a>
    <?php endif; ?>
    <!-- Filter Form -->
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <label>Server</label>
            <select name="server_id" class="form-select">
                <option value="">-- All Servers --</option>
                <?php
                $servers = $conn->query("SELECT * FROM servers");
                while ($srv = $servers->fetch_assoc()): ?>
                    <option value="<?= $srv['id'] ?>" <?= ($srv['id'] == $server_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($srv['server_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-success">🔍 Filter</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Zone Name</th>
            <th>Server</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['zone_name']) ?></td>
                <td><?= htmlspecialchars($row['server_name']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <?php if ($role === 'admin'): ?>
                        <a href="#" class="btn btn-info btn-sm" onclick="submitPost('edit_zone.php', { id: '<?= $row['id'] ?>' })"> ✏️ Edit</a>
                        <a href="#" class="btn btn-danger btn-sm" onclick="if(confirm('Are you sure?')) submitPost('delete_zone.php', { id: '<?= $row['id'] ?>' })">🗑️ Delete</a>
                    <?php else: ?>
                        <a href="#" class="btn btn-info btn-sm disabled"> ✏️ Edit</a>
                        <a href="#" class="btn btn-danger btn-sm disabled">🗑️ Delete</a>
                    <?php endif; ?>
                </td>

            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
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
</script>


<?php require_once '../includes/footer.php'; ?>