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


// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Location: list_employees.php');
    exit();
}

$employee_id = intval($_GET['id']);

// 🔎 Fetch employee details
$employee = $conn->query("SELECT * FROM employees WHERE id = $employee_id")->fetch_assoc();
if (!$employee) {
    echo "<div class='alert alert-danger'>Employee not found.</div>";
    require_once '../includes/footer.php';
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $new_role  = $_POST['role'];  // role from form

    if (!empty($_POST['password'])) {
        $password = $password = md5($_POST['password']);
        $stmt = $conn->prepare("UPDATE employees SET name=?, email=?, phone=?, role=?, password=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $email, $phone, $new_role, $password, $employee_id);
    } else {
        $stmt = $conn->prepare("UPDATE employees SET name=?, email=?, phone=?, role=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $email, $phone, $new_role, $employee_id);
    }

    if ($stmt->execute()) {
        header('Location: list_employees.php?success=Employee Updated Successfully');
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error updating employee: " . $stmt->error . "</div>";
    }
}
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Edit Employee</h2>

    <form action="" method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($employee['name']) ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($employee['email']) ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($employee['phone']) ?>">
            </div>

            <div class="col-md-6 mb-3">
                <label>Role</label>
                <?php if ($role === 'admin'): ?>
                    <select name="role" class="form-select" required>
                        <option value="admin" <?= ($employee['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                        <option value="editor" <?= ($employee['role'] == 'editor') ? 'selected' : '' ?>>Editor</option>
                        <option value="support" <?= ($employee['role'] == 'support') ? 'selected' : '' ?>>Support</option>
                    </select>
                <?php else: ?>
                    <input type="hidden" name="role" value="<?= htmlspecialchars($employee['role']) ?>">
                    <input type="text" class="form-control" value="<?= ucfirst($employee['role']) ?>" disabled>
                <?php endif; ?>
            </div>

            <div class="col-md-6 mb-3">
                <label>New Password (leave blank to keep old)</label>
                <input type="password" name="password" class="form-control" maxlength="72">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Update Employee</button>
        <a href="list_employees.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
