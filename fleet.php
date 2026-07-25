<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">
    <div class="top-bar">
        <h2 class="page-title">Fleet Management</h2>
        <div class="user-profile">
            <button id="themeToggleBtn" class="theme-toggle" aria-label="Toggle Theme">
                <i class="fa-solid fa-moon"></i>
            </button>
            <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
        </div>
    </div>

    <div class="content-grid">
        <div class="card" style="grid-column: span 2;">
            <h3 class="card-title">Add New Vehicle</h3>
            <form id="addVehicleForm" style="display: grid; gap: 10px; grid-template-columns: 2fr 1fr 1fr auto; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 5px;">Search & Add Vehicle</label>
                    <input type="text" id="vehicle_name" name="vehicle_name" required style="width: 100%; padding: 8px;" placeholder="e.g. Toyota Camry 2023">
                </div>
                <div><label style="display: block; margin-bottom: 5px;">Plate Number</label><input type="text" id="plate" required style="width: 100%; padding: 8px;" placeholder="e.g. DL01AB1234"></div><button type="submit" class="btn" style="padding: 10px 20px; height: 35px; background: var(--bg-color); color: var(--text-primary); border: 1px solid var(--border-color);">Add Vehicle</button>
            </form>
            <div id="formMessage" style="margin-top: 10px;"></div>
        </div>

        <div class="card" style="grid-column: span 2;">
            <h3 class="card-title">Current Fleet</h3>
            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">ID</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Vehicle Name</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Status</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Action</th>
                    </tr>
                </thead>
                <tbody id="fleetTableBody">
                    <tr><td colspan="7" style="text-align: center; padding: 10px;">Loading fleet...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
// Fetch and display fleet
function fetchFleet() {
    fetch('api/fleet.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('fleetTableBody');
            tbody.innerHTML = '';
            
            if(data.error) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: red;">Error: ${data.error}</td></tr>`;
                return;
            }
            
            if(data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align: center;">No vehicles found.</td></tr>`;
                return;
            }
            
            data.forEach(vehicle => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">${vehicle.id}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">${vehicle.vehicle_name}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">${vehicle.status}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                        <button onclick="deleteVehicle(${vehicle.id})" class="btn" style="background: red; color: white; border: none; padding: 5px 10px; cursor: pointer;">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => {
            document.getElementById('fleetTableBody').innerHTML = `<tr><td colspan="7" style="text-align: center; color: red;">Failed to load data.</td></tr>`;
        });
}

// Add vehicle
document.getElementById('addVehicleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const vehicle_name = document.getElementById('vehicle_name').value;
    const plate = document.getElementById('plate').value;
    const messageDiv = document.getElementById('formMessage');
    
    fetch('api/fleet.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ vehicle_name, plate, status: 'available' })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            messageDiv.innerHTML = `<span style="color: green;">${data.message}</span>`;
            this.reset();
            fetchFleet(); // Reload live data
        } else {
            messageDiv.innerHTML = `<span style="color: red;">Error: ${data.error}</span>`;
        }
    })
    .catch(err => {
        messageDiv.innerHTML = `<span style="color: red;">Failed to add vehicle.</span>`;
    });
});

// Delete vehicle
function deleteVehicle(id) {
    if(!confirm("Are you sure you want to delete this vehicle?")) return;
    
    fetch('api/fleet.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            fetchFleet(); // Reload live data
        } else {
            alert("Error: " + data.error);
        }
    })
    .catch(err => {
        alert("Failed to delete vehicle.");
    });
}

// Initial fetch
fetchFleet();
</script>

<?php include 'includes/footer.php'; ?>
