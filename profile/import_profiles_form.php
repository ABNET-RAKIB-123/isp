<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;

require_once '../includes/db.php';
require_once '../includes/header.php'; // Bootstrap + CSS
require_once '../includes/sidebar.php';
?>

<div class="container p-4">
    <h2>Import PPPoE Profiles from Router</h2>

    <form method="POST" action="import_profiles_from_router.php">
        <div class="mb-3">
            <label>Select Router:</label>
            <select name="router_id" class="form-select" required>
                <option value="">-- Select Router --</option>
                <?php
                $routers = $conn->query("SELECT * FROM routers");
                while ($r = $routers->fetch_assoc()):
                ?>
                    <option value="<?= $r['id'] ?>">
                        <?= htmlspecialchars($r['router_name']) ?> (<?= htmlspecialchars($r['router_ip']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success mt-3">Import Profiles</button>
        <a href="list_profiles.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
