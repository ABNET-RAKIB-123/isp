<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/db.php';

if (isset($_POST['upload'])) {
    if ($_FILES['sql_file']['error'] == 0) {
        $sqlFilePath = $_FILES['sql_file']['tmp_name'];

        // Connect to MySQL (correct order of params)
        $conn = new mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Read the SQL file
        $sql = file_get_contents($sqlFilePath);
        if (!$sql) {
            die("Failed to read SQL file.");
        }

        // Execute SQL
        if ($conn->multi_query($sql)) {
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
            echo "✅ Database restored successfully.";
        } else {
            echo "❌ MySQL error: " . $conn->error;
        }

        $conn->close();
    } else {
        echo "❌ File upload error: " . $_FILES['sql_file']['error'];
    }
}
?>
