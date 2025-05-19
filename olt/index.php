<?php include('includes/header.php'); include('config/db.php'); ?>

<div class="container mt-4">
    <h4>All Devices</h4>
    <table class="table table-bordered">
        <thead><tr><th>Name</th><th>Type</th><th>Location</th></tr></thead>
        <tbody>
            <?php
            $res = $conn->query("SELECT * FROM devices");
            while ($row = $res->fetch_assoc()) {
                echo "<tr><td>{$row['name']}</td><td>{$row['type']}</td><td>{$row['location']}</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <h4 class="mt-5">All Connections</h4>
    <table class="table table-bordered">
        <thead><tr><th>From</th><th>To</th><th>Cable</th><th>Module</th><th>Splitter</th></tr></thead>
        <tbody>
            <?php
            $res = $conn->query("
                SELECT c.*, 
                    p1.port_name AS from_port, d1.name AS from_device,
                    p2.port_name AS to_port, d2.name AS to_device
                FROM connections c
                JOIN ports p1 ON c.from_port_id = p1.id
                JOIN devices d1 ON p1.device_id = d1.id
                JOIN ports p2 ON c.to_port_id = p2.id
                JOIN devices d2 ON p2.device_id = d2.id
            ");
            while ($r = $res->fetch_assoc()) {
                echo "<tr>
                    <td>{$r['from_device']} - {$r['from_port']}</td>
                    <td>{$r['to_device']} - {$r['to_port']}</td>
                    <td>{$r['cable_type']}</td>
                    <td>{$r['module_type']}</td>
                    <td>{$r['splitter_ratio']}</td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include('includes/footer.php'); ?>
