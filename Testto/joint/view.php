<?php include '../db.php'; ?>
<?php
$joint_id = $_GET['id'];
$joint_result = $mysqli->query("SELECT * FROM joints WHERE id = $joint_id");
$joint = $joint_result->fetch_assoc();

$onu_result = $mysqli->query("SELECT * FROM onus WHERE joint_id = $joint_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Joint Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h3>Joint: #<?php echo $joint['id']; ?></h3>
    <p><strong>Location:</strong> <?php echo $joint['location']; ?></p>
    <p><strong>Fiber Length:</strong> <?php echo $joint['fiber_length']; ?> meters</p>
    <p><strong>Notes:</strong> <?php echo $joint['note']; ?></p>

    <h4>ONUs Connected</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer Name</th>
                <th>Status</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($onu = $onu_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $onu['id']; ?></td>
                    <td><?php echo $onu['customer_name']; ?></td>
                    <td><?php echo $onu['status']; ?></td>
                    <td><?php echo $onu['location']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="../index.php" class="btn btn-primary">Back to Dashboard</a>
</div>
</body>
</html>
