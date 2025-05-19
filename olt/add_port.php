<?php include('includes/header.php'); include('config/db.php'); ?>

<div class="container mt-4">
    <h4>Add Port</h4>
    <form id="portForm">
        <div class="mb-2">
            <label>Device</label>
            <select name="device_id" class="form-control" required>
                <option value="">Select Device</option>
                <?php
                $result = $conn->query("SELECT id, name FROM devices");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                }
                ?>
            </select>
        </div>
        <input type="text" name="port_name" class="form-control mb-2" placeholder="Port Name (e.g., sfp2, port1)" required>
        <select name="port_type" class="form-control mb-2" required>
            <option value="">Port Type</option>
            <option value="sfp">SFP</option>
            <option value="ethernet">Ethernet</option>
            <option value="pon">PON</option>
            <option value="lan">LAN</option>
            <option value="uplink">Uplink</option>
        </select>
        <button type="submit" class="btn btn-success">Add Port</button>
    </form>
</div>

<script>
document.getElementById("portForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const form = new FormData(this);
    fetch('ajax/save_port.php', {
        method: 'POST',
        body: form
    }).then(res => res.text()).then(data => {
        alert(data);
        this.reset();
    });
});
</script>

<?php include('includes/footer.php'); ?>
