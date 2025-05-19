<?php

$host = 'localhost';
$user = 'test';
$pass = 'X)CwrJ6@vHY]Wj83';
$dbname = 'network_designer';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>