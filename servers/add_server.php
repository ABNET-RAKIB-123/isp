<?php
session_start();
$role = $_SESSION['role'];
$employee_id = $_SESSION['employee_id'];
require_once '../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $server_name = $_POST['server_name'];
    $router_id = $_POST['router_id'];

    $stmt = $conn->prepare("INSERT INTO servers (server_name, router_id) VALUES (?, ?)");
    $stmt->bind_param("si", $server_name, $router_id);
    $stmt->execute();

    header('Location: list_servers.php?success=Server Added Successfully');
    exit();
}
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Add Server</h2>

    <form action="" method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Server Name</label>
                <input type="text" name="server_name" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Select Router</label>
                <select name="router_id" class="form-select" required>
                    <option value="">Select Router</option>
                    <?php
                    $routers = $conn->query("SELECT * FROM routers ORDER BY router_name ASC");
                    while ($router = $routers->fetch_assoc()):
                    ?>
                        <option value="<?= $router['id'] ?>"><?= htmlspecialchars($router['router_name']) ?> (<?= $router['router_ip'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <?php if ($role !== 'support'): ?>
                <button type="submit" class="btn btn-success me-2">➕ Save Server</button>
                <a href="list_routers.php" class="btn btn-secondary">Cancel</a>
            <?php else: ?>
                <button type="button" class="btn btn-success me-2" disabled>➕ Save Server (No Permission)</button>
                <a href="list_routers.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
