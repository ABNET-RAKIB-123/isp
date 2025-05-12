<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    die("Invalid Profile ID!");
}

// Load Profile
$profile = $conn->query("SELECT * FROM profiles WHERE id = $id")->fetch_assoc();
if (!$profile) {
    die("Profile not found!");
}
?>

<div class="container p-4">
    <h2>Edit PPPoE Profile</h2>

    <form method="POST" action="update_profile.php">
        <input type="hidden" name="id" value="<?= $profile['id'] ?>">

        <div class="mb-3">
            <label>Profile Name:</label>
            <input type="text" name="profile_name" class="form-control" value="<?= htmlspecialchars($profile['profile_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Local Address:</label>
            <input type="text" name="local_address" class="form-control" value="<?= htmlspecialchars($profile['local_address']) ?>">
        </div>

        <div class="mb-3">
            <label>Remote Address:</label>
            <input type="text" name="remote_address" class="form-control" value="<?= htmlspecialchars($profile['remote_address']) ?>">
        </div>

        <div class="mb-3">
            <label>Rate Limit:</label>
            <input type="text" name="rate_limit" class="form-control" value="<?= htmlspecialchars($profile['rate_limit']) ?>">
        </div>

        <div class="mb-3">
            <label>Comment:</label>
            <input type="text" name="comment" class="form-control" value="<?= htmlspecialchars($profile['comment']) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
        <a href="list_profiles.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
