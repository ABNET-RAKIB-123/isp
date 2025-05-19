<?php include '../db.php'; ?>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $desc = $_POST['description'];

    $stmt = $mysqli->prepare("INSERT INTO olt_ports (name, location, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $location, $desc);
    $stmt->execute();
    header("Location: ../index.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add OLT Port</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h3>Add OLT Port</h3>
    <form method="POST">
        <div class="mb-3">
            <label>OLT Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Location</label>
            <input type="text" name="location" class="form-control">
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Add OLT</button>
    </form>
</div>
</body>
</html>
