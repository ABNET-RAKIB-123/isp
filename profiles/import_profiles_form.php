<form method="POST" action="import_profiles.php">
    <label for="routerSelect">Select Router:</label>
    <select name="router_id" id="routerSelect" class="form-select" required>
        <?php
        $routers = $conn->query("SELECT * FROM routers");
        while ($r = $routers->fetch_assoc()):
        ?>
            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['router_name']) ?> (<?= htmlspecialchars($r['router_ip']) ?>)</option>
        <?php endwhile; ?>
    </select>

    <button type="submit" class="btn btn-success mt-3">Import Profiles</button>
</form>
