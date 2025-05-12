<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $password = md5($_POST['password']); // ✅ MD5 hash
    $role     = $_POST['role'];
    $access_if     = $_POST['access_if'];

    $stmt = $conn->prepare("INSERT INTO employees (access_if, name, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $access_if, $name, $email, $phone, $password, $role);
    $stmt->execute();

    echo "✅ Registered successfully!";
}
?>

<!-- HTML Form -->
<form method="POST">
    <input name="name" required placeholder="Name"><br>
    <input name="email" type="email" required placeholder="Email"><br>
    <input name="phone" placeholder="Phone"><br>
    <input name="password" type="password" required placeholder="Password"><br>
    <input name="access_if" placeholder="Router Access"><br>
    <select name="role">
        <option value="admin">Admin</option>
        <option value="editor">Editor</option>
        <option value="support">Support</option>
    </select><br>
    <button type="submit">Register</button>
</form>
