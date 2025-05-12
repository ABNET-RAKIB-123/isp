<?php
$pdo = new PDO("mysql:host=localhost;dbname=isp_management", "roott", "StrongP@ssw0rd!");

// Turn off foreign key checks (for related tables)
$pdo->exec("SET FOREIGN_KEY_CHECKS=0");

// Truncate multiple tables
$tables = ['service_information', 'contact_information', 'network_product_information', 'packages', 'routers', 'servers', 'subzones', 'zones','profiles'];

foreach ($tables as $table) {
    $pdo->exec("TRUNCATE TABLE `$table`");
}

// Turn foreign key checks back on
$pdo->exec("SET FOREIGN_KEY_CHECKS=1");

echo "All selected tables have been truncated.";
?>
