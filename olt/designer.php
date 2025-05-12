<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Fiber Designer</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    #canvas { width: 100%; height: 600px; border: 1px solid #ccc; }
    .device { fill: blue; cursor: pointer; }
    .cable { stroke: black; stroke-width: 3; }
  </style>
</head>
<body>

<h3>Click to add a device, right-click cable to remove</h3>
<svg id="canvas"></svg>

<script>
function loadDevices() {
  $.getJSON('load_devices.php', function(devices) {
    devices.forEach(d => {
      $('#canvas').append(`<circle class="device" cx="${d.x}" cy="${d.y}" r="15" data-id="${d.id}" />
        <text x="${d.x - 10}" y="${d.y - 20}" font-size="12">${d.name}</text>`);
    });
  });
}

function loadCables() {
  $.getJSON('load_cables.php', function(cables) {
    cables.forEach(c => {
      $('#canvas').append(`<line class="cable" id="cable-${c.id}" data-id="${c.id}"
        x1="${c.x1}" y1="${c.y1}" x2="${c.x2}" y2="${c.y2}" />`);
    });
  });
}

$(document).ready(function() {
  loadDevices();
  loadCables();

  $('#canvas').on('contextmenu', '.cable', function(e) {
    e.preventDefault();
    const id = $(this).data('id');
    if (confirm('Remove this cable?')) {
      $.post('remove_cable.php', { id: id }, function(res) {
        if (res === 'success') {
          $('#cable-' + id).remove();
        } else {
          alert('Error: ' + res);
        }
      });
    }
  });

  $('#canvas').click(function(e) {
    const rect = this.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const name = prompt("Enter device name:");
    if (!name) return;

    $.post('add_device.php', { name, x, y }, function(res) {
      if (res === 'success') location.reload();
      else alert('Error adding device');
    });
  });
});
</script>

</body>
</html>
