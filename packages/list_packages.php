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

<div class="container-fluid p-4">
    <h2 class="mb-4">Package List</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if($role =='admin') :?>
    <a href="add_package.php" class="btn btn-success mb-3">Add New Package</a>
    <?php endif; ?>
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Package Name</th>
                        <th>Speed</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $packages = $conn->query("SELECT * FROM packages ORDER BY id DESC");
                    if ($packages->num_rows > 0):
                        $i = 1;
                        while ($package = $packages->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($package['package_name']) ?></td>
                        <td><?= htmlspecialchars($package['speed']) ?></td>
                        <td><?= htmlspecialchars($package['price']) ?> ৳</td>
                        <td class="d-flex gap-2">
                            <a href="edit_package.php?id=<?= $package['id'] ?>" 
                            class="btn btn-sm btn-primary <?= ($_SESSION['role'] !== 'admin') ? 'disabled' : '' ?>" 
                            <?= ($_SESSION['role'] !== 'admin') ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
                                <i class="bi bi-pencil-square"></i>✏️ Edit</a>

                            <a href="delete_package.php?id=<?= $package['id'] ?>"
                            onclick="return <?= ($_SESSION['role'] === 'admin') ? "confirm('Are you sure to delete this package?');" : "false;" ?>"
                            class="btn btn-sm btn-danger <?= ($_SESSION['role'] !== 'admin') ? 'disabled' : '' ?>" 
                            <?= ($_SESSION['role'] !== 'admin') ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
                                <i class="bi bi-trash"></i>🗑️ Delete</a>
                        </td>


                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No packages found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
