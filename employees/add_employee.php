<?php
session_start();

// 🔒 Check login
if (!isset($_SESSION['id'])) {
    header("Location: ../admin/login.php");
    exit;
}
require_once '../includes/db.php';
$employee_id = $_SESSION['id'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = md5($_POST['password']); // ✅ MD5 hash
    $role = $_POST['role'] ?? 'support';

    if (!empty($name) && !empty($email) && !empty($password)) {
        $stmt = $conn->prepare("INSERT INTO employees (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $password, $role);
        $stmt->execute();
        $success = "✅ Employee added successfully!";
    } else {
        $error = "❌ All fields are required.";
    }
}
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

?>

<div class="container mt-4">
    <h3>Add Employee</h3>

    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control">
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <!-- Only admin can choose role -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-select" required>
                    <option value="">-- Select Role --</option>
                    <option value="editor">Editor</option>
                    <option value="support">Support</option>
                </select>
            </div>
        <?php else: ?>
            <input type="hidden" name="role" value="support">
        <?php endif; ?>

        <button class="btn btn-success">Add Employee</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
