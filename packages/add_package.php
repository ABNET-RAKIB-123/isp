<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container p-4">
    <h2>Add New Package</h2>

    <form method="POST" action="save_package.php">
        <div class="mb-3">
            <label>Package Name:</label>
            <input type="text" name="package_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Speed (Rate Limit):</label>
            <input type="text" name="rate_limit" class="form-control" placeholder="Example: 5M/5M" required>
        </div>

        <div class="mb-3">
            <label>Price (Monthly):</label>
            <input type="number" name="price" class="form-control" placeholder="Example: 500" required>
        </div>

        <!-- ✅ Select Server Dropdown -->
        <div class="mb-3">
            <label>Select Router:</label>
            <select name="server_id" class="form-select" required>
                <option value="">-- Select Server --</option>
                <?php
                $servers = $conn->query("SELECT * FROM servers");
                while ($server = $servers->fetch_assoc()):
                ?>
                    <option value="<?= $server['id'] ?>">
                        <?= htmlspecialchars($server['server_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Save Package</button>
        <a href="list_packages.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
