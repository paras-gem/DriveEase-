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
            <form id="addVehicleForm" style="display: grid; gap: 10px; grid-template-columns: repeat(6, 1fr); align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 5px;">Year</label>
                    <select id="car_year" required style="width: 100%; padding: 8px;">
                        <option value="" disabled selected>Loading...</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px;">Make</label>
                    <select id="car_make" required disabled style="width: 100%; padding: 8px;">
                        <option value="" disabled selected>Select Year First</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px;">Model</label>
                    <select id="car_model" required disabled style="width: 100%; padding: 8px;">
                        <option value="" disabled selected>Select Make First</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px;">Trim</label>
                    <select id="car_trim" required disabled style="width: 100%; padding: 8px;">
                        <option value="" disabled selected>Select Model First</option>
                    </select>
                </div>
                <button type="submit" class="btn" style="padding: 10px; height: 35px; background: var(--bg-color); color: var(--text-primary); border: 1px solid var(--border-color); grid-column: span 2;">Add Vehicle</button>
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
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Cost / Day</th>
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
                let badgeColor = vehicle.status === 'available' ? '#10b981' : (vehicle.status === 'booked' ? '#3b82f6' : '#f59e0b');
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">${vehicle.id}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">${vehicle.car_label}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">$${vehicle.rent_cost_per_day}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                        <span style="padding: 4px 8px; border-radius: 12px; font-size: 12px; background: ${badgeColor}; color: white; text-transform: capitalize;">
                            ${vehicle.status}
                        </span>
                    </td>
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

// Load CarAPI Years
function loadYears() {
    fetch('api/fleet.php?proxy=years')
        .then(r => r.json())
        .then(data => {
            const select = document.getElementById('car_year');
            select.innerHTML = '<option value="" disabled selected>Select Year</option>';
            if(data.success && data.data) {
                data.data.forEach(y => {
                    select.innerHTML += `<option value="${y}">${y}</option>`;
                });
            }
        });
}

document.getElementById('car_year').addEventListener('change', function() {
    const year = this.value;
    const makeSelect = document.getElementById('car_make');
    makeSelect.disabled = true; makeSelect.innerHTML = '<option>Loading...</option>';
    document.getElementById('car_model').disabled = true; document.getElementById('car_trim').disabled = true;
    
    fetch(`api/fleet.php?proxy=makes&year=${year}`)
        .then(r => r.json())
        .then(data => {
            makeSelect.innerHTML = '<option value="" disabled selected>Select Make</option>';
            if(data.success && data.data) {
                data.data.forEach(m => {
                    makeSelect.innerHTML += `<option value="${m.id}">${m.name}</option>`;
                });
                makeSelect.disabled = false;
            }
        });
});

document.getElementById('car_make').addEventListener('change', function() {
    const year = document.getElementById('car_year').value;
    const makeId = this.value;
    const modelSelect = document.getElementById('car_model');
    modelSelect.disabled = true; modelSelect.innerHTML = '<option>Loading...</option>';
    document.getElementById('car_trim').disabled = true;
    
    fetch(`api/fleet.php?proxy=models&year=${year}&make_id=${makeId}`)
        .then(r => r.json())
        .then(data => {
            modelSelect.innerHTML = '<option value="" disabled selected>Select Model</option>';
            if(data.success && data.data) {
                data.data.forEach(m => {
                    modelSelect.innerHTML += `<option value="${m.id}">${m.name}</option>`;
                });
                modelSelect.disabled = false;
            }
        });
});

document.getElementById('car_model').addEventListener('change', function() {
    const year = document.getElementById('car_year').value;
    const modelId = this.value;
    const trimSelect = document.getElementById('car_trim');
    trimSelect.disabled = true; trimSelect.innerHTML = '<option>Loading...</option>';
    
    fetch(`api/fleet.php?proxy=trims&year=${year}&model_id=${modelId}`)
        .then(r => r.json())
        .then(data => {
            trimSelect.innerHTML = '<option value="" disabled selected>Select Trim</option>';
            if(data.success && data.data) {
                data.data.forEach(t => {
                    trimSelect.innerHTML += `<option value="${t.id}">${t.name} (${t.description})</option>`;
                });
                trimSelect.disabled = false;
            } else {
                trimSelect.innerHTML = '<option value="" disabled selected>No trims found</option>';
            }
        });
});

// Add vehicle
document.getElementById('addVehicleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const year = document.getElementById('car_year').value;
    const makeText = document.getElementById('car_make').options[document.getElementById('car_make').selectedIndex].text;
    const modelText = document.getElementById('car_model').options[document.getElementById('car_model').selectedIndex].text;
    const trimId = document.getElementById('car_trim').value;
    const trimText = document.getElementById('car_trim').options[document.getElementById('car_trim').selectedIndex].text;
    
    const car_label = `${year} ${makeText} ${modelText} ${trimText}`;
    const messageDiv = document.getElementById('formMessage');
    
    fetch('api/fleet.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            car_label: car_label,
            car_api_trim_id: trimId,
            car_api_year: year,
            status: 'available' 
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            messageDiv.innerHTML = `<span style="color: green;">${data.message}</span>`;
            this.reset();
            document.getElementById('car_make').disabled = true;
            document.getElementById('car_model').disabled = true;
            document.getElementById('car_trim').disabled = true;
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
loadYears();
</script>

<?php include 'includes/footer.php'; ?>
