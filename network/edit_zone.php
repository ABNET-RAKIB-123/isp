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
$id = (int)$_POST['id'];
$zone = $conn->query("SELECT * FROM zones WHERE id = $id")->fetch_assoc();
?>

<div class="container mt-4">
    <h3>Edit Zone</h3>
    <form action="update_zone.php" method="POST">
        <div class="mb-3">
            <label>Zone Name</label>
            <input type="hidden" name="ids" class="form-control" value="<?= $id; ?>">
            <input type="text" name="zone_name" class="form-control" value="<?= $zone['zone_name'] ?>" required>
        </div>

        <div class="mb-3">
            <label>Server</label>
            <select name="server_id" class="form-select" required>
                <?php
                $servers = $conn->query("SELECT * FROM servers");
                while ($server = $servers->fetch_assoc()):
                ?>
                    <option value="<?= $server['id'] ?>" <?= ($server['id'] == $zone['server_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($server['server_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="list_zones.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php require_once '../includes/footer.php'; ?>