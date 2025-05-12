<?php include '../db.php'; ?>
<?php
$olt_id = $_GET['id'];
$olt_result = $mysqli->query("SELECT * FROM olt_ports WHERE id = $olt_id");
$olt = $olt_result->fetch_assoc();

$splitter_result = $mysqli->query("SELECT * FROM splitters WHERE olt_port_id = $olt_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View OLT Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h3>OLT: <?php echo $olt['name']; ?></h3>
    <p><strong>Location:</strong> <?php echo $olt['location']; ?></p>
    <p><strong>Description:</strong> <?php echo $olt['description']; ?></p>

    <h4>Splitters Connected</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Location</th>
                <th>Parent Splitter</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($splitter = $splitter_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $splitter['id']; ?></td>
                    <td><?php echo $splitter['type']; ?></td>
                    <td><?php echo $splitter['location']; ?></td>
                    <td>
                        <?php
                        if ($splitter['parent_splitter_id']) {
                            $parent_result = $mysqli->query("SELECT type FROM splitters WHERE id = " . $splitter['parent_splitter_id']);
                            $parent = $parent_result->fetch_assoc();
                            echo $parent['type'];
                        } else {
                            echo "None";
                        }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="../index.php" class="btn btn-primary">Back to Dashboard</a>
</div>
</body>
</html>
