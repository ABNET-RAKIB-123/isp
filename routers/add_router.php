<?php
session_start();
$role = $_SESSION['role'];
$employee_id = $_SESSION['employee_id'];
require_once '../includes/db.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $router_name = $_POST['router_name'];
    $router_ip = $_POST['router_ip'];
    $router_username = $_POST['router_username'];
    $router_password = $_POST['router_password'];
    $router_port = $_POST['router_port'] ?? 8728;

    $stmt = $conn->prepare("INSERT INTO routers (router_name, router_ip, router_username, router_password, router_port) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $router_name, $router_ip, $router_username, $router_password, $router_port);
    $stmt->execute();

    header('Location: list_routers.php?success=Router Added Successfully');
    exit();
}
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Add Router</h2>

    <form action="" method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Router Name</label>
                <input type="text" name="router_name" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Router IP</label>
                <input type="text" name="router_ip" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Username</label>
                <input type="text" name="router_username" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Password</label>
                <input type="password" name="router_password" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Port</label>
                <input type="number" name="router_port" class="form-control" value="8728">
            </div>
        </div>

        <!-- <button type="submit" class="btn btn-success">Save Router</button>
        <a href="list_routers.php" class="btn btn-secondary">Back</a> -->


            <?php if ($role !== 'support'): ?>
                <button type="submit" class="btn btn-success me-2">➕ Save Router</button>
                <a href="list_routers.php" class="btn btn-secondary">Cancel</a>
            <?php else: ?>
                <button type="button" class="btn btn-success me-2" disabled>➕ Save Router (No Permission)</button>
                <a href="list_routers.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>

    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
