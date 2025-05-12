<?php
$connect = new PDO("mysql:host=localhost;dbname=isp_management", "root", "");

if (isset($_POST['query'])) {
    $search = "%".$_POST['query']."%";
    $stmt = $connect->prepare("SELECT * FROM clients WHERE customer_name LIKE ? OR id LIKE ?");
    $stmt->execute([$search, $search]);

    if ($stmt->rowCount() > 0) {
        echo "<ul>";
        while ($row = $stmt->fetch()) {
            echo "<li>{$row['customer_name']} ({$row['id']})</li>";
        }
        echo "</ul>";
    } else {
        echo "No users found.";
    }
}
?>
