<?php
include('../config/db.php');
$res = $conn->query("SELECT json_data FROM layout_data ORDER BY id DESC LIMIT 1");
$row = $res->fetch_assoc();
echo $row ? $row['json_data'] : json_encode(["devices" => [], "cables" => []]);
