<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header("Location: list_vlans.php?error=VLAN ID missing");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM vlans WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$vlan = $stmt->get_result()->fetch_assoc();

if (!$vlan) {
    header("Location: list_vlans.php?error=VLAN not found");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vlan_id = $_POST['vlan_id'];
    $vlan_name = $_POST['vlan_name'];
    $ip_address = $_POST['ip_address'];
    $comment = $_POST['comment'];

    $stmt = $conn->prepare("UPDATE vlans SET vlan_id=?, vlan_name=?, ip_address=?, comment=? WHERE id=?");
    $stmt->bind_param("isssi", $vlan_id, $vlan_name, $ip_address, $comment, $id);
    $stmt->execute();

    header("Location: list_vlans.php?success=VLAN updated");
    exit;
}
?>

<div class="container-fluid p-4">
    <h2>Edit VLAN</h2>

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label>VLAN ID</label>
            <input type="number" name="vlan_id" value="<?= htmlspecialchars($vlan['vlan_id']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>VLAN Name</label>
            <input type="text" name="vlan_name" value="<?= htmlspecialchars($vlan['vlan_name']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>IP Address</label>
            <input type="text" name="ip_address" value="<?= htmlspecialchars($vlan['ip_address']) ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Comment</label>
            <textarea name="comment" class="form-control"><?= htmlspecialchars($vlan['comment']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update VLAN</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
