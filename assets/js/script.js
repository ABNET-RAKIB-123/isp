// assets/js/script.js
document.addEventListener("DOMContentLoaded", function () {
    // Example: Handle Add Router Form
    const addRouterForm = document.getElementById("addRouterForm");
  
    if (addRouterForm) {
      addRouterForm.addEventListener("submit", function (e) {
        e.preventDefault();
  
        const name = document.getElementById("routerName").value;
        const ip = document.getElementById("routerIP").value;
        const port = document.getElementById("routerPort").value;
  
        const table = document.getElementById("routerTable");
        const newRow = document.createElement("tr");
  
        newRow.innerHTML = `
          <td>#</td>
          <td>${name}</td>
          <td>${ip}</td>
          <td>${port}</td>
          <td>
            <button class="btn btn-sm btn-warning">Edit</button>
            <button class="btn btn-sm btn-danger">Delete</button>
          </td>
        `;
  
        table.appendChild(newRow);
  
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('addRouterModal'));
        modal.hide();
  
        // Reset form
        addRouterForm.reset();
      });
    }
  });
  