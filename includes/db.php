
<?php

// Database connection details
$host = 'localhost';    // your database server (localhost if same server)
$db   = 'isp_management'; // your database name
$user = 'roott';          // your MySQL username
$pass = 'StrongP@ssw0rd!';              // your MySQL password (empty by default on XAMPP)
$charset = 'utf8mb4';

// Setup MySQLi connection
$conn = new mysqli($host, $user, $pass, $db);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: set character set
$conn->set_charset("utf8mb4");




 ?>
