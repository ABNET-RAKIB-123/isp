<?php
session_start();
require_once '../includes/db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if (empty($username) || empty($password) || empty($role)) {
        die('All fields are required!');
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into employees table
    $stmt = $conn->prepare("INSERT INTO employees (name, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $username, $hashed_password, $role);
    
    if ($stmt->execute()) {
        echo "✅ Employee registered successfully!";
    } else {
        echo "❌ Error: " . $stmt->error;
    }
}
?>

<!-- Simple Registration Form -->
<form method="POST" action="">
    <h2>Register New Employee</h2>
    <input type="text" name="username" placeholder="Username" class="form-control mb-2" required>
    <input type="password" name="password" placeholder="Password" class="form-control mb-2" required>
    <select name="role" class="form-control mb-2" required>
        <option value="">Select Role</option>
        <option value="admin">Admin</option>
        <option value="editor">Editor</option>
    </select>
    <button type="submit" class="btn btn-primary">Register</button>
</form>
