<?php
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subzone_name = trim($_POST['subzone_name']);
    $zone_id = (int)$_POST['zone_id'];

    if (!empty($subzone_name) && $zone_id > 0) {
        $stmt = $conn->prepare("INSERT INTO subzones (subzone_name, zone_id) VALUES (?, ?)");
        $stmt->bind_param("si", $subzone_name, $zone_id);
        $stmt->execute();
        $success = "✅ Sub Zone added!";
    } else {
        $error = "❌ Please fill all fields.";
    }
}
?>

<div class="container mt-4">
    <h3>Add Sub Zone</h3>

    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <!-- 🔽 Server Dropdown -->
        <div class="mb-3">
            <label>Select Server</label>
            <select id="serverSelect" class="form-select" required>
                <option value="">-- Select Server --</option>
                <?php
                $servers = $conn->query("SELECT * FROM servers");
                while ($srv = $servers->fetch_assoc()):
                ?>
                    <option value="<?= $srv['id'] ?>"><?= htmlspecialchars($srv['server_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- 🔽 Zone Dropdown (Populated by AJAX) -->
        <div class="mb-3">
            <label>Select Zone</label>
            <select name="zone_id" id="zoneSelect" class="form-select" required>
                <option value="">-- Select Zone --</option>
            </select>
        </div>

        <!-- ✅ Sub Zone Name -->
        <div class="mb-3">
            <label>Sub Zone Name</label>
            <input type="text" name="subzone_name" class="form-control" required>
        </div>

        <button class="btn btn-success">Add Sub Zone</button>
    </form>
</div>

<!-- ✅ jQuery AJAX Script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#serverSelect').on('change', function () {
    const serverId = $(this).val();
    if (serverId) {
        $.ajax({
            url: 'get_zones_by_server.php',
            method: 'POST',
            data: { server_id: serverId },
            success: function (data) {
                $('#zoneSelect').html(data);
            }
        });
    } else {
        $('#zoneSelect').html('<option value="">-- Select Zone --</option>');
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
