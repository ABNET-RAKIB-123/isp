<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fiber Network Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Fiber Network Overview</h2>

    <div class="mb-3">
        <a href="olt/add.php" class="btn btn-success">Add OLT</a>
        <a href="splitter/add.php" class="btn btn-primary">Add Splitter</a>
        <a href="joint/add.php" class="btn btn-warning">Add Joint</a>
        <a href="onu/add.php" class="btn btn-info">Add ONU</a>
    </div>

    <h4>OLT Ports</h4>
    <div class="row">
        <?php
        $result = $mysqli->query("SELECT * FROM olt_ports");
        while ($olt = $result->fetch_assoc()):
        ?>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5><?php echo $olt['name']; ?></h5>
                        <p><?php echo $olt['location']; ?></p>
                        <a href="olt/view.php?id=<?php echo $olt['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>
