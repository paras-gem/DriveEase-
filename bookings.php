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
        <!-- Create Booking Form -->
        <div class="card" style="margin-bottom: 20px;">
            <h3 class="card-title">New Booking</h3>
            <form id="createBookingForm" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                <div style="flex: 2; min-width: 250px;">
                    <label style="display: block; margin-bottom: 5px;">Select Vehicle</label>
                    <select id="fleet_id" required style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                        <option value="" disabled selected>Loading available vehicles...</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; margin-bottom: 5px;">Pickup Date</label>
                    <input type="date" id="start_date" required style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; margin-bottom: 5px;">Return Date</label>
                    <input type="date" id="end_date" required style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                </div>
                <button type="submit" class="btn" style="padding: 9px 20px; background: var(--bg-color); color: var(--text-primary); border: 1px solid var(--border-color);">Book Vehicle</button>
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

function loadAvailableVehicles() {
    fetch('api/fleet.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('fleet_id');
            select.innerHTML = '<option value="" disabled selected>Choose a vehicle</option>';
            
            if (data.error) {
                select.innerHTML = '<option value="" disabled>Error loading vehicles</option>';
                return;
            }
            
            const availableCars = data.filter(v => v.status === 'available');
            if (availableCars.length === 0) {
                select.innerHTML = '<option value="" disabled>No vehicles available</option>';
                return;
            }
            
            availableCars.forEach(vehicle => {
                const opt = document.createElement('option');
                opt.value = vehicle.id;
                opt.textContent = vehicle.vehicle_name;
                select.appendChild(opt);
            });
        })
        .catch(err => {
            document.getElementById('fleet_id').innerHTML = '<option value="" disabled>Failed to load vehicles</option>';
        });
}

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
            this.reset();
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
