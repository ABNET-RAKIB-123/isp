<!DOCTYPE html>
<html>
<head>
    <title>Fiber Network Designer</title>
    <style>
        body {
            font-family: Arial;
        }
        #toolbar {
            background: #f2f2f2;
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }
        #canvas {
            width: 100%;
            height: 90vh;
            position: relative;
            border: 1px solid #ccc;
        }
        .device {
            width: 120px;
            height: 60px;
            background: #3498db;
            color: white;
            text-align: center;
            line-height: 60px;
            border-radius: 10px;
            position: absolute;
            cursor: move;
            font-weight: bold;
        }
    </style>

    <!-- jQuery and jQuery UI -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
</head>
<body>

<div id="toolbar">
    <label for="deviceType">Choose Device: </label>
    <select id="deviceType">
        <option value="MikroTik">MikroTik</option>
        <option value="Switch">Switch</option>
        <option value="OLT">OLT</option>
        <option value="ONU">ONU</option>
        <option value="ONT">ONT</option>
    </select>
    <button onclick="addDevice()">Add Device</button>
</div>

<div id="canvas"></div>

<script>
    let deviceCount = 0;

    function addDevice() {
        const deviceType = document.getElementById('deviceType').value;
        deviceCount++;
        const deviceId = `device${deviceCount}`;

        const div = document.createElement('div');
        div.className = 'device';
        div.id = deviceId;
        div.innerText = deviceType + ' ' + deviceCount;

        // Random initial position
        div.style.left = Math.floor(Math.random() * 600) + 'px';
        div.style.top = Math.floor(Math.random() * 300) + 'px';

        document.getElementById('canvas').appendChild(div);

        // Make draggable
        $(`#${deviceId}`).draggable();
    }
</script>

</body>
</html>
