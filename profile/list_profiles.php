<?php
// ✅ Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 🔒 Check login
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../admin/login.php");
    exit;
}

// 🧑‍💼 Logged-in User Info
$employee_name = $_SESSION['name'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';

// ✅ Optional: store session ID for consistency (this line is now fixed)
$_SESSION['id'] = $_SESSION['employee_id'];


require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container p-4">
    <h2>Profiles Management</h2>

    <?php if ($_SESSION['role'] === 'admin'): ?>
    <a href="add_profile.php" class="btn btn-success mb-3">➕ Add New Profile</a>
    <a href="/profile/import_profiles_form.php" class="btn btn-success mb-3">➕ Import Profile</a>
<?php else: ?>
    <a href="#" class="btn btn-success mb-3 disabled" aria-disabled="true" tabindex="-1">➕ Add New Profile</a>
    <a href="#" class="btn btn-success mb-3 disabled" aria-disabled="true" tabindex="-1">➕ Import Profile</a>
<?php endif; ?>


    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Profile Name</th>
                <th>Local Address</th>
                <th>Remote Address</th>
                <th>Rate Limit</th>
                <th>Comment</th>
                <th>Router ID</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $profiles = $conn->query("SELECT profiles.*, servers.server_name 
            FROM profiles 
            JOIN servers ON profiles.router_id = servers.router_id 
            ORDER BY profiles.id DESC");
            
            while ($p = $profiles->fetch_assoc()):
            ?>
                <tr>
                    <td><?= htmlspecialchars($p['profile_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($p['local_address'] ?? '') ?></td>
                    <td><?= htmlspecialchars($p['remote_address'] ?? '') ?></td>
                    <td><?= htmlspecialchars($p['rate_limit'] ?? '') ?></td>
                    <td><?= htmlspecialchars($p['comment'] ?? '') ?></td>
                    <td><?= $p['server_name'] ?></td>
                    <td>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="edit_profile.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">✏️ Edit</a>
                            <a href="delete_profile.php?id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this profile?')">🗑️ Delete</a>
                        <?php else: ?>
                            <a href="#" class="btn btn-primary btn-sm disabled" aria-disabled="true" tabindex="-1">✏️ Edit</a>
                            <a href="#" class="btn btn-danger btn-sm disabled" aria-disabled="true" tabindex="-1">🗑️ Delete</a>
                        <?php endif; ?>
                    </td>

            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
