<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if (!isset($_GET['id'])) {
    header('Location: list_packages.php');
    exit();
}

$package_id = intval($_GET['id']);
$package = $conn->query("SELECT * FROM packages WHERE id = $package_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $package_name = $_POST['package_name'];
    $speed = $_POST['speed'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("UPDATE packages SET package_name=?, speed=?, price=? WHERE id=?");
    $stmt->bind_param("ssdi", $package_name, $speed, $price, $package_id);
    $stmt->execute();

    header('Location: list_packages.php?success=Package Updated Successfully');
    exit();
}
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Edit Package</h2>

    <form action="" method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Package Name</label>
                <input type="text" name="package_name" class="form-control" value="<?= htmlspecialchars($package['package_name']) ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Speed</label>
                <input type="text" name="speed" class="form-control" value="<?= htmlspecialchars($package['speed']) ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Price (BDT)</label>
                <input type="number" name="price" step="0.01" class="form-control" value="<?= htmlspecialchars($package['price']) ?>" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Update Package</button>
        <a href="list_packages.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
