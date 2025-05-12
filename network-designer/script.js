function saveToDatabase() {
  const devices = [];
  document.querySelectorAll('.device').forEach(el => {
    devices.push({
      id: el.id,
      type: el.dataset.type,
      top: el.style.top,
      left: el.style.left
    });
  });

  const conn = cables.map(c => ({
    from: c.from.id,
    to: c.to.id,
    type: c.label.textContent
  }));

  $.post('save_data.php', { devices, cables: conn }, function (res) {
    console.log("Saved to DB:", res);
  });
}

function loadFromDatabase() {
  $.get('load_data.php', function (data) {
    const parsed = JSON.parse(data);
    loadDevices(parsed.devices);
    loadCables(parsed.cables);
  });
}

function loadDevices(devices) {
  devices.forEach(d => {
    const el = createDeviceElement(d.type);
    el.id = d.id;
    el.style.top = d.top;
    el.style.left = d.left;
    document.getElementById('canvas').appendChild(el);
    makeDraggable(el);
  });
}

function loadCables(cablesData) {
  cablesData.forEach(c => {
    const fromEl = document.getElementById(c.from);
    const toEl = document.getElementById(c.to);
    if (fromEl && toEl) {
      drawCable(fromEl, toEl, c.type);
    }
  });
}
