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
        <h2 class="page-title">Bookings</h2>
        <div class="user-profile">
            <button id="themeToggleBtn" class="theme-toggle" aria-label="Toggle Theme">
                <i class="fa-solid fa-moon"></i>
            </button>
            <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
        </div>
    </div>

    <div class="content-grid" style="grid-template-columns: 1fr;">
        <!-- Fleet Search & Book -->
        <div class="card" style="margin-bottom: 20px;">
            <h3 class="card-title">Available Vehicles</h3>
            <div style="margin-bottom: 15px;">
                <input type="text" id="fleetSearch" placeholder="Search by make, model, or year..." style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;">
            </div>
            <div id="fleetGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
                <div style="grid-column: 1 / -1; text-align: center; padding: 20px;">Loading vehicles...</div>
            </div>
        </div>

        <!-- Hidden Booking Form (shown when a car is selected) -->
        <div class="card" id="bookingFormCard" style="display: none; margin-bottom: 20px; border: 2px solid var(--sidebar-hover);">
            <h3 class="card-title">Complete Booking for: <span id="selectedCarName" style="color: var(--sidebar-hover);"></span></h3>
            <form id="createBookingForm" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                <input type="hidden" id="fleet_id" required>
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; margin-bottom: 5px;">Pickup Date</label>
                    <input type="date" id="start_date" required style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; margin-bottom: 5px;">Return Date</label>
                    <input type="date" id="end_date" required style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; margin-bottom: 5px;">Estimated Cost</label>
                    <div id="estCostDisplay" style="padding: 8px; font-weight: bold;">$0.00</div>
                </div>
                <button type="submit" class="btn" style="padding: 9px 20px; background: var(--bg-color); color: var(--text-primary); border: 1px solid var(--border-color);">Confirm Booking</button>
                <button type="button" class="btn" onclick="cancelBooking()" style="padding: 9px 20px; background: transparent; color: red; border: 1px solid red;">Cancel</button>
            </form>
            <div id="bookingMessage" style="margin-top: 10px;"></div>
        </div>

        <div class="card">
            <h3 class="card-title">All Bookings</h3>
            <table style="width: 100%; text-align: left; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">ID</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">User</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Vehicle</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Dates</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Status</th>
                    </tr>
                </thead>
                <tbody id="bookingsTableBody">
                    <tr><td colspan="5" style="text-align: center; padding: 20px;">Loading bookings...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
const currentUserId = <?php echo $_SESSION['user_id']; ?>;

let allVehicles = [];
let selectedCarRate = 0;

function loadAvailableVehicles() {
    fetch('api/fleet.php')
        .then(response => response.json())
        .then(data => {
            const grid = document.getElementById('fleetGrid');
            if (data.error) {
                grid.innerHTML = '<div style="color: red;">Error loading vehicles</div>';
                return;
            }
            
            allVehicles = data.filter(v => v.status === 'available');
            renderVehicles(allVehicles);
        })
        .catch(err => {
            document.getElementById('fleetGrid').innerHTML = '<div style="color: red;">Failed to load vehicles</div>';
        });
}

function renderVehicles(vehicles) {
    const grid = document.getElementById('fleetGrid');
    grid.innerHTML = '';
    
    if (vehicles.length === 0) {
        grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center;">No vehicles found.</div>';
        return;
    }
    
    vehicles.forEach(vehicle => {
        const card = document.createElement('div');
        card.style.border = '1px solid var(--border-color)';
        card.style.borderRadius = '8px';
        card.style.padding = '15px';
        card.style.background = 'var(--bg-color)';
        
        card.innerHTML = `
            <h4 style="margin: 0 0 10px 0;">${vehicle.car_label}</h4>
            <div style="margin-bottom: 15px; font-size: 14px; color: var(--text-secondary);">
                <div><strong>Engine:</strong> ${vehicle.engine || 'N/A'}</div>
                <div><strong>Body:</strong> ${vehicle.body_style || 'N/A'}</div>
                <div style="margin-top: 5px; font-size: 18px; font-weight: bold; color: var(--text-primary);">
                    $${vehicle.rent_cost_per_day} <span style="font-size: 12px; font-weight: normal;">/ day</span>
                </div>
            </div>
            <button onclick="startBooking(${vehicle.id}, '${vehicle.car_label.replace(/'/g, "\\'")}', ${vehicle.rent_cost_per_day})" class="btn" style="width: 100%; padding: 8px; background: var(--sidebar-hover); color: white; border: none; border-radius: 4px; cursor: pointer;">Book Now</button>
        `;
        grid.appendChild(card);
    });
}

document.getElementById('fleetSearch').addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    const filtered = allVehicles.filter(v => 
        v.car_label.toLowerCase().includes(query) || 
        (v.engine && v.engine.toLowerCase().includes(query))
    );
    renderVehicles(filtered);
});

function startBooking(id, name, rate) {
    document.getElementById('fleet_id').value = id;
    document.getElementById('selectedCarName').textContent = name;
    selectedCarRate = parseFloat(rate);
    
    document.getElementById('bookingFormCard').style.display = 'block';
    document.getElementById('bookingFormCard').scrollIntoView({ behavior: 'smooth' });
    updateCost();
}

function cancelBooking() {
    document.getElementById('bookingFormCard').style.display = 'none';
    document.getElementById('createBookingForm').reset();
    document.getElementById('estCostDisplay').textContent = '$0.00';
    selectedCarRate = 0;
}

function updateCost() {
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    
    if(start && end && selectedCarRate > 0) {
        const days = Math.round((new Date(end) - new Date(start)) / 86400000) + 1;
        if(days > 0) {
            const cost = days * selectedCarRate;
            document.getElementById('estCostDisplay').textContent = '$' + cost.toFixed(2);
        } else {
            document.getElementById('estCostDisplay').textContent = 'Invalid dates';
        }
    }
}

document.getElementById('start_date').addEventListener('change', updateCost);
document.getElementById('end_date').addEventListener('change', updateCost);

function loadBookings() {
    fetch('api/bookings.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('bookingsTableBody');
            tbody.innerHTML = '';
            
            if (data.error) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: red;">Error: ${data.error}</td></tr>`;
                return;
            }
            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center;">No bookings found.</td></tr>`;
                return;
            }
            
            data.forEach(booking => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">#${booking.id}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); font-weight: 500;">${booking.user_name || 'User ' + booking.user_id}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">${booking.vehicle_name}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">${booking.start_date} to ${booking.end_date}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                        <span style="padding: 4px 8px; border-radius: 12px; font-size: 12px; background: ${booking.status === 'pending' ? '#f59e0b' : (booking.status === 'confirmed' ? '#3b82f6' : (booking.status === 'completed' ? '#10b981' : '#ef4444'))}; color: white; text-transform: capitalize;">
                            ${booking.status}
                        </span>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => {
            document.getElementById('bookingsTableBody').innerHTML = `<tr><td colspan="5" style="text-align: center; color: red;">Failed to load data.</td></tr>`;
        });
}

document.getElementById('createBookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const msg = document.getElementById('bookingMessage');
    
    const payload = {
        user_id: currentUserId,
        fleet_id: document.getElementById('fleet_id').value,
        start_date: document.getElementById('start_date').value,
        end_date: document.getElementById('end_date').value
    };
    
    if (new Date(payload.start_date) > new Date(payload.end_date)) {
        msg.innerHTML = '<span style="color: red;">Return date must be after pickup date.</span>';
        return;
    }
    
    fetch('api/bookings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            msg.innerHTML = `<span style="color: green;">${data.message}</span>`;
            document.getElementById('createBookingForm').reset();
            setTimeout(() => {
                cancelBooking();
                msg.innerHTML = '';
            }, 2000);
            loadAvailableVehicles(); // refresh list to hide booked car
            loadBookings();
        } else {
            msg.innerHTML = `<span style="color: red;">Error: ${data.error}</span>`;
        }
    })
    .catch(err => {
        msg.innerHTML = '<span style="color: red;">Failed to submit booking.</span>';
    });
});

document.addEventListener("DOMContentLoaded", () => {
    loadAvailableVehicles();
    loadBookings();
});
</script>

<?php include 'includes/footer.php'; ?>
