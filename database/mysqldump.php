<?php
// Show errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/header.php';
// require_once '../includes/sidebar.php';
// require_once '../includes/db.php';
// Database configuration
$host = "localhost";
$user = "roott";
$password = "StrongP@ssw0rd!";
$database = "isp_management";

// Connect to database
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Header of the SQL dump
$backup = "-- Backup of `$database` generated on " . date("Y-m-d H:i:s") . "\n\n";

// Get all tables
$tables = $conn->query("SHOW TABLES");
if (!$tables) {
    die("Error fetching tables: " . $conn->error);
}

// Loop through tables
while ($tableRow = $tables->fetch_array()) {
    $table = $tableRow[0];

    // Get CREATE TABLE
    $createTableResult = $conn->query("SHOW CREATE TABLE `$table`");
    $createRow = $createTableResult->fetch_assoc();
    $backup .= "--\n-- Structure for table `$table`\n--\n\n";
    $backup .= $createRow['Create Table'] . ";\n\n";

    // Get table data
    $dataResult = $conn->query("SELECT * FROM `$table`");
    if ($dataResult && $dataResult->num_rows > 0) {
        $backup .= "--\n-- Data for table `$table`\n--\n\n";

        while ($row = $dataResult->fetch_assoc()) {
            $escapedValues = array_map(function ($value) use ($conn) {
                if ($value === null) return "NULL";
                return "'" . $conn->real_escape_string($value) . "'";
            }, array_values($row));

            $backup .= "INSERT INTO `$table` VALUES (" . implode(", ", $escapedValues) . ");\n";
        }

        $backup .= "\n";
    } else {
        $backup .= "-- (no data in table `$table`)\n\n";
    }
}

$conn->close();

// Output the SQL backup as a downloadable file
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $database . '_backup_' . date("Y-m-d_H-i-s") . '.sql"');
echo $backup;
exit;

 require_once '../includes/footer.php'; ?>