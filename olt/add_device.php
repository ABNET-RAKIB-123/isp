<?php include('includes/header.php'); ?>
<div class="container mt-4">
    <h4>Add Device</h4>
    <form id="deviceForm">
        <input type="text" name="name" class="form-control mb-2" placeholder="Device Name" required>
        <select name="type" class="form-control mb-2" required>
            <option value="">Select Type</option>
            <option value="mikrotik">Mikrotik</option>
            <option value="switch">Switch</option>
            <option value="olt">OLT</option>
            <option value="onu">ONU</option>
            <option value="splitter">Splitter</option>
        </select>
        <input type="text" name="location" class="form-control mb-2" placeholder="Location">
        <textarea name="notes" class="form-control mb-2" placeholder="Notes"></textarea>
        <button type="submit" class="btn btn-primary">Add Device</button>
    </form>
</div>

<script>
document.getElementById("deviceForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const form = new FormData(this);
    fetch('ajax/save_device.php', {
        method: 'POST',
        body: form
    }).then(res => res.text()).then(data => {
        alert(data);
        this.reset();
    });
});
</script>
<?php include('includes/footer.php'); ?>
