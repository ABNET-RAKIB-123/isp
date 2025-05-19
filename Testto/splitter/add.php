<?php include '../db.php'; ?>

<?php
// ফর্ম সাবমিট করলে ডেটা ইনসার্ট হবে
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $olt_port_id = $_POST['olt_port_id'];
    $type = $_POST['type'];
    $parent_splitter_id = $_POST['parent_splitter_id'] ?: NULL;
    $location = $_POST['location'];

    $stmt = $mysqli->prepare("INSERT INTO splitters (olt_port_id, type, parent_splitter_id, location) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $olt_port_id, $type, $parent_splitter_id, $location);
    $stmt->execute();

    header("Location: ../index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Splitter</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h3>Add Splitter</h3>
    <form method="POST">

        <div class="mb-3">
            <label>Select OLT Port</label>
            <select name="olt_port_id" class="form-control" required>
                <option value="">-- Select OLT --</option>
                <?php
                $result = $mysqli->query("SELECT id, name FROM olt_ports");
                while ($row = $result->fetch_assoc()):
                ?>
                    <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Splitter Type</label>
            <select name="type" class="form-control" required>
                <option value="1:2">1:2</option>
                <option value="1:4">1:4</option>
                <option value="1:8">1:8</option>
                <option value="1:16">1:16</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Parent Splitter (optional)</label>
            <select name="parent_splitter_id" class="form-control">
                <option value="">-- None --</option>
                <?php
                $splitters = $mysqli->query("SELECT id, type FROM splitters");
                while ($s = $splitters->fetch_assoc()):
                ?>
                    <option value="<?= $s['id'] ?>">Splitter #<?= $s['id'] ?> (<?= $s['type'] ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Location</label>
            <input type="text" name="location" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Add Splitter</button>
    </form>
</div>
</body>
</html>
