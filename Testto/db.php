<?php
// db.php
// $mysqli = new mysqli("localhost", "network", "WmLD8uK@RdeJ3hMi", "fiber_network");
// if ($mysqli->connect_error) {
//     die("Connection failed: " . $mysqli->connect_error);
// }


$host = "localhost";
$user = "network";
$pass = "WmLD8uK@RdeJ3hMi";
$db = "fiber_network";

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
