<?php include '../db.php'; ?>
<?php
$splitter_id = $_GET['id'];
$splitter_result = $mysqli->query("SELECT * FROM splitters WHERE id = $splitter_id");
$splitter = $splitter_result->fetch_assoc();

$joint_result = $mysqli->query("SELECT * FROM joints WHERE splitter_id = $splitter_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Splitter Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h3>Splitter: #<?php echo $splitter['id']; ?> (<?php echo $splitter['type']; ?>)</h3>
    <p><strong>Location:</strong> <?php echo $splitter['location']; ?></p>
    <p><strong>Parent Splitter:</strong>
        <?php
        if ($splitter['parent_splitter_id']) {
            $parent_result = $mysqli->query("SELECT type FROM splitters WHERE id = " . $splitter['parent_splitter_id']);
            $parent = $parent_result->fetch_assoc();
            echo $parent['type'];
        } else {
            echo "None";
        }
        ?>
    </p>

    <h4>Joints Connected</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Location</th>
                <th>Fiber Length</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($joint = $joint_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $joint['id']; ?></td>
                    <td><?php echo $joint['location']; ?></td>
                    <td><?php echo $joint['fiber_length']; ?> meters</td>
                    <td><?php echo $joint['note']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="../index.php" class="btn btn-primary">Back to Dashboard</a>
</div>
</body>
</html>
