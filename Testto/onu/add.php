<?php include '../db.php'; ?>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $joint_id = $_POST['joint_id'];
    $customer_name = $_POST['customer_name'];
    $status = $_POST['status'];
    $location = $_POST['location'];

    $stmt = $mysqli->prepare("INSERT INTO onus (joint_id, customer_name, status, location) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $joint_id, $customer_name, $status, $location);
    $stmt->execute();

    header("Location: ../index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add ONU</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h3>Add ONU</h3>
    <form method="POST">

        <div class="mb-3">
            <label>Select Joint</label>
            <select name="joint_id" class="form-control" required>
                <option value="">-- Select Joint --</option>
                <?php
                $joints = $mysqli->query("SELECT id, location FROM joints");
                while ($j = $joints->fetch_assoc()):
                ?>
                    <option value="<?= $j['id'] ?>"><?= $j['location'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Customer Name</label>
            <input type="text" name="customer_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Location</label>
            <input type="text" name="location" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-info">Add ONU</button>
    </form>
</div>
</body>
</html>
