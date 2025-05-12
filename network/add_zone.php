<?php
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $zone_name = trim($_POST['zone_name']);
    $server_id = (int)$_POST['server_id'];

    if (!empty($zone_name) && $server_id > 0) {
        $stmt = $conn->prepare("INSERT INTO zones (zone_name, server_id) VALUES (?, ?)");
        $stmt->bind_param("si", $zone_name, $server_id);
        $stmt->execute();
        $success = "✅ Zone added successfully!";
    } else {
        $error = "❌ Please fill in all fields!";
    }
}
?>

<div class="container p-4">
    <h3>Add New Zone</h3>

    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="zone_name" class="form-label">Zone Name</label>
            <input type="text" name="zone_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="server_id" class="form-label">Select Server</label>
            <select name="server_id" class="form-select" required>
                <option value="">-- Select Server --</option>
                <?php
                $servers = $conn->query("SELECT id, server_name FROM servers ORDER BY server_name ASC");
                while ($server = $servers->fetch_assoc()):
                ?>
                    <option value="<?= $server['id'] ?>"><?= htmlspecialchars($server['server_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Add Zone</button>
        <a href="list_zones.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
