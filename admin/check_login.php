<?php
// ✅ Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = md5($_POST['password']); // ❗ Note: MD5 is insecure. Use password_hash() in production.

    // ✅ Prepare SQL safely
    $stmt = $conn->prepare("SELECT * FROM employees WHERE email = ? AND password = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // ✅ Save to session
        $_SESSION['id']          = $user['id'];
        $_SESSION['employee_id'] = $user['access_if']; // fixed from 'access_if'
        $_SESSION['name']        = $user['name'];
        $_SESSION['role']        = $user['role'];
        $_SESSION['email']       = $user['email'];
        $_SESSION['phone']       = $user['phone'];
        header("Location: dashboard.php");
        exit();
    } else {
        echo "❌ Wrong email or password!";
    }

    $stmt->close();
}
?>
