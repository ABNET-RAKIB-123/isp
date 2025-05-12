<?php
session_start();
require_once '../includes/db.php';
require_once '../api/mikrotik_api.php'; // MikroTik API
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bridge_id = $_POST['bridge_id'];
    $vlan_id = $_POST['vlan_id'];
    $vlan_name = $_POST['vlan_name'];
    $ip_address = $_POST['ip_address'];
    $comment = $_POST['comment'] ?? '';

    // Insert into Database
    $stmt = $conn->prepare("INSERT INTO vlans (bridge_id, vlan_id, vlan_name, ip_address, comment) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $bridge_id, $vlan_id, $vlan_name, $ip_address, $comment);
    $stmt->execute();
    $vlan_db_id = $conn->insert_id;

    // Now Create on Router
    $bridge = $conn->query("
        SELECT b.*, r.router_ip, r.router_username, r.router_password, r.router_port 
        FROM bridges b 
        JOIN routers r ON b.router_id = r.id 
        WHERE b.id = $bridge_id
    ")->fetch_assoc();

    if ($bridge) {
        $API = new RouterosAPI();
        $API->port = $bridge['router_port'];

        if ($API->connect($bridge['router_ip'], $bridge['router_username'], $bridge['router_password'])) {
            $vlan_interface_name = 'vlan' . $vlan_id;

            // $API->comm("/interface/vlan/add", [
            //     "name" => $vlan_interface_name,
            //     "vlan-id" => (int)$vlan_id,
            //     "interface" => $bridge['bridge_name'],
            //     "comment" => $comment
            // ]);
            $API->comm("/interface/vlan/add", [
                "name" => $vlan_interface_name,
                "vlan-id" => (int)$vlan_id,
                "interface" => $_POST['interface_name'],
                "comment" => $comment
            ]);


            $API->disconnect();
        }
    }

    header("Location: list_vlans.php?success=VLAN created in Database and Router");
    exit;
}

$bridges = $conn->query("
    SELECT b.id, b.bridge_name, r.router_name 
    FROM bridges b
    JOIN routers r ON b.router_id = r.id
    ORDER BY r.router_name ASC
");
?>
    <div class="container-fluid p-4">
    <h2>Add New VLAN</h2>

    <form method="POST" id="vlanForm">
        <div class="mb-3">
            <label>Router</label>
            <select name="router_id" id="routerSelect" class="form-control" required>
                <option value="">Select Router</option>
                <?php
                $routers = $conn->query("SELECT * FROM routers ORDER BY router_name ASC");
                while($r = $routers->fetch_assoc()):
                ?>
                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['router_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Interface (Bridge / Port)</label>
            <select name="interface_name" id="interfaceSelect" class="form-control" required>
                <option value="">Select Interface</option>
                <!-- Loaded dynamically via Ajax -->
            </select>
        </div>

        <div class="mb-3">
            <label>VLAN ID</label>
            <input type="number" name="vlan_id" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>VLAN Name</label>
            <input type="text" name="vlan_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Comment</label>
            <textarea name="comment" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Create VLAN</button>
    </form>
</div>

</div>
<script>
$(document).ready(function(){
    $('#routerSelect').on('change', function(){
        var router_id = $(this).val();
        if(router_id){
            $.ajax({
                url: 'get_interfaces.php',
                type: 'POST',
                data: {router_id: router_id},
                success: function(data){
                    var interfaces = JSON.parse(data);
                    $('#interfaceSelect').empty();
                    $('#interfaceSelect').append('<option value="">Select Interface</option>');
                    interfaces.forEach(function(interface){
                        $('#interfaceSelect').append('<option value="'+interface.name+'">'+interface.name+' ('+interface.type+')</option>');
                    });
                }
            });
        }else{
            $('#interfaceSelect').empty();
            $('#interfaceSelect').append('<option value="">Select Interface</option>');
        }
    });
});
</script>


<?php require_once '../includes/footer.php'; ?>
