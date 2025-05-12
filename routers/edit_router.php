<?php
session_start();
require_once '../includes/db.php';


if (!isset($_GET['id'])) {
    header('Location: list_routers.php');
    exit();
}

$router_id = intval($_GET['id']);
$router = $conn->query("SELECT * FROM routers WHERE id = $router_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $router_name = $_POST['router_name'];
    $router_ip = $_POST['router_ip'];
    $router_username = $_POST['router_username'];
    $router_password = $_POST['router_password'];
    $router_port = $_POST['router_port'];

        $router_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $role = $_SESSION['role'] ?? '';
        $employee_id = $_SESSION['employee_id'] ?? 0;

        // Fetch router only if access allowed
        $query = "SELECT * FROM routers WHERE id = ?";
        $params = [$router_id];
        $types = "i";

        if ($role !== 'admin') {
            $query .= " AND owner_id = ?";
            $params[] = $employee_id;
            $types .= "i";
        }

        $stmt = $conn->prepare("UPDATE routers SET router_name=?, router_ip=?, router_username=?, router_password=?, router_port=? WHERE id=?");
        $stmt->bind_param("ssssii", $router_name, $router_ip, $router_username, $router_password, $router_port, $router_id);
        $stmt->execute();
        // $result = $stmt->get_result();
        // $router = $result->fetch_assoc();

        if (!$router) {
            die("Access denied or router not found.");
        }
        header('Location: list_routers.php?success=Router Updated Successfully');
        exit();
}
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Edit Router</h2>

    <form action="" method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Router Name</label>
                <input type="text" name="router_name" class="form-control" value="<?= htmlspecialchars($router['router_name']) ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Router IP</label>
                <input type="text" name="router_ip" class="form-control" value="<?= htmlspecialchars($router['router_ip']) ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Username</label>
                <input type="text" name="router_username" class="form-control" value="<?= htmlspecialchars($router['router_username']) ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Password</label>
                <input type="password" name="router_password" class="form-control" value="<?= htmlspecialchars($router['router_password']) ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Port</label>
                <input type="number" name="router_port" class="form-control" value="<?= htmlspecialchars($router['router_port']) ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Update Router</button>
        <a href="list_routers.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
