<?php include '../db.php'; ?>
<?php
$onu_id = $_GET['id'];
$onu_result = $mysqli->query("SELECT * FROM onus WHERE id = $onu_id");
$onu = $onu_result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View ONU Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h3>ONU: #<?php echo $onu['id']; ?></h3>
    <p><strong>Customer Name:</strong> <?php echo $onu['customer_name']; ?></p>
    <p><strong>Status:</strong> <?php echo $onu['status']; ?></p>
    <p><strong>Location:</strong> <?php echo $onu['location']; ?></p>

    <a href="../index.php" class="btn btn-primary">Back to Dashboard</a>
</div>
</body>
</html>
