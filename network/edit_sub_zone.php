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

$id = (int)$_POST['id'];
$subzone = $conn->query("SELECT * FROM subzones WHERE id = $id")->fetch_assoc();

?>

<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="container mt-4">
    <h3>Edit Sub Zone</h3>

    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form action="update_sub_zone.php" method="POST">
        <div class="mb-3">
            <label>Sub Zone Name</label>
            <input type="hidden" name="ids" class="form-control" value="<?= $id; ?>">
            <input type="text" name="subzone_name" class="form-control" value="<?= htmlspecialchars($subzone['subzone_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Select Zone</label>
            
            <select name="zone_id" class="form-select" required>
                <option value="">-- Select Zone --</option>
                <?php
                $zones = $conn->query("SELECT z.id, z.zone_name, s.server_name FROM zones z JOIN servers s ON z.server_id = s.id");
                while ($zone = $zones->fetch_assoc()):
                ?>
                    <option value="<?= $zone['id'] ?>" <?= $zone['id'] == $subzone['zone_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($zone['zone_name']) ?> (<?= htmlspecialchars($zone['server_name']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="list_subzones.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
