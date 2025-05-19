<?php include('includes/header.php'); include('config/db.php'); ?>

<div class="container mt-4">
    <h4>Add Connection</h4>
    <form id="connectionForm">
        <div class="mb-2">
            <label>From Port</label>
            <select name="from_port_id" class="form-control" required>
                <option value="">Select From</option>
                <?php
                $res = $conn->query("SELECT p.id, d.name AS device, p.port_name FROM ports p JOIN devices d ON p.device_id = d.id");
                while ($r = $res->fetch_assoc()) {
                    echo "<option value='{$r['id']}'>{$r['device']} - {$r['port_name']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-2">
            <label>To Port</label>
            <select name="to_port_id" class="form-control" required>
                <option value="">Select To</option>
                <?php
                $res = $conn->query("SELECT p.id, d.name AS device, p.port_name FROM ports p JOIN devices d ON p.device_id = d.id");
                while ($r = $res->fetch_assoc()) {
                    echo "<option value='{$r['id']}'>{$r['device']} - {$r['port_name']}</option>";
                }
                ?>
            </select>
        </div>
        <select name="cable_type" class="form-control mb-2" required>
            <option value="">Cable Type</option>
            <option value="fiber">Fiber</option>
            <option value="ethernet">Ethernet</option>
            <option value="patch_cord">Patch Cord</option>
        </select>
        <select name="module_type" class="form-control mb-2">
            <option value="none">Module Type (Optional)</option>
            <option value="short">Short</option>
            <option value="long">Long</option>
        </select>
        <select name="splitter_ratio" class="form-control mb-2">
            <option value="none">Splitter (If any)</option>
            <option value="1:2">1:2</option>
            <option value="1:4">1:4</option>
            <option value="1:8">1:8</option>
            <option value="1:16">1:16</option>
        </select>
        <button type="submit" class="btn btn-primary">Add Connection</button>
    </form>
</div>

<script>
document.getElementById("connectionForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const form = new FormData(this);
    fetch('ajax/save_connection.php', {
        method: 'POST',
        body: form
    }).then(res => res.text()).then(data => {
        alert(data);
        this.reset();
    });
});
</script>

<?php include('includes/footer.php'); ?>
