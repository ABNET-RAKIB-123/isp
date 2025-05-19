<?php include '../db.php'; ?>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $splitter_id = $_POST['splitter_id'];
    $location = $_POST['location'];
    $fiber_length = $_POST['fiber_length'];
    $note = $_POST['note'];

    $stmt = $mysqli->prepare("INSERT INTO joints (splitter_id, location, fiber_length, note) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $splitter_id, $location, $fiber_length, $note);
    $stmt->execute();

    header("Location: ../index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Fiber Joint</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h3>Add Fiber Joint</h3>
    <form method="POST">

        <div class="mb-3">
            <label>Select Splitter</label>
            <select name="splitter_id" class="form-control" required>
                <option value="">-- Select Splitter --</option>
                <?php
                $splitters = $mysqli->query("SELECT id, type FROM splitters");
                while ($s = $splitters->fetch_assoc()):
                ?>
                    <option value="<?= $s['id'] ?>">Splitter #<?= $s['id'] ?> (<?= $s['type'] ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Joint Location</label>
            <input type="text" name="location" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Fiber Length (in meters)</label>
            <input type="number" step="0.1" name="fiber_length" class="form-control">
        </div>

        <div class="mb-3">
            <label>Notes</label>
            <textarea name="note" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-warning">Add Joint</button>
    </form>
</div>
</body>
</html>
