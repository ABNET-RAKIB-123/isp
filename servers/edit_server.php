<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if (!isset($_GET['id'])) {
    header('Location: list_servers.php');
    exit();
}

$server_id = intval($_GET['id']);
$server = $conn->query("SELECT * FROM servers WHERE id = $server_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $server_name = $_POST['server_name'];
    $router_id = $_POST['router_id'];

    $stmt = $conn->prepare("UPDATE servers SET server_name=?, router_id=? WHERE id=?");
    $stmt->bind_param("sii", $server_name, $router_id, $server_id);
    $stmt->execute();

    header('Location: list_servers.php?success=Server Updated Successfully');
    exit();
}
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Edit Server</h2>

    <form action="" method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Server Name</label>
                <input type="text" name="server_name" class="form-control" value="<?= htmlspecialchars($server['server_name']) ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Select Router</label>
                <select name="router_id" class="form-select" required>
                    <?php
                    $routers = $conn->query("SELECT * FROM routers ORDER BY router_name ASC");
                    while ($router = $routers->fetch_assoc()):
                    ?>
                        <option value="<?= $router['id'] ?>" <?= ($router['id'] == $server['router_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($router['router_name']) ?> (<?= $router['router_ip'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Update Server</button>
        <a href="list_servers.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
