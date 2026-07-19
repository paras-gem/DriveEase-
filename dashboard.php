<?php
// 1. Start the session to "see" the data from login_process.php
session_start();

// 2. Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, send them back to login
    header('Location: login.php');
    exit;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">
    <div class="top-bar">
        <h2 class="page-title">Overview</h2>
        <div class="user-profile">
            <button id="themeToggleBtn" class="theme-toggle" aria-label="Toggle Theme">
                <i class="fa-solid fa-moon"></i>
            </button>
            <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
        </div>
    </div>

    <div class="welcome-banner">
        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>! 👋</h1>
        <p>You have successfully connected the login process to the dashboard. Here is what's happening with your support desk today.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Active Tickets</span>
            <span class="stat-value" id="stat-tickets">...</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Pending Bookings</span>
            <span class="stat-value" id="stat-bookings">...</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Available Fleet</span>
            <span class="stat-value" id="stat-fleet">...</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Total Customers</span>
            <span class="stat-value" id="stat-customers">...</span>
        </div>
    </div>

    <div class="content-grid">
        <div class="card">
            <h3 class="card-title">Recent Activity</h3>
            <ul class="activity-list" id="recent-activity-list">
                <li class="activity-item" style="justify-content: center; color: var(--text-secondary);">Loading activity...</li>
            </ul>
            <a href="#" class="btn" style="margin-top: 16px; width: 100%; background: var(--bg-color); color: var(--text-primary);">View All Logs</a>
        </div>
        <div class="card">
            <h3 class="card-title">Quick Actions</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <a href="#" class="btn" style="background: var(--bg-color); color: var(--text-primary); border: 1px solid var(--border-color); width: 100%;"><i class="fa-solid fa-plus"></i> New Ticket</a>
                <a href="#" class="btn" style="background: var(--bg-color); color: var(--text-primary); border: 1px solid var(--border-color); width: 100%;"><i class="fa-solid fa-car-side"></i> Add Vehicle</a>
                <a href="#" class="btn" style="background: var(--bg-color); color: var(--text-primary); border: 1px solid var(--border-color); width: 100%;"><i class="fa-solid fa-address-book"></i> New Booking</a>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    fetch('api/dashboard-stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stats
                document.getElementById('stat-tickets').textContent = data.stats.tickets;
                document.getElementById('stat-bookings').textContent = data.stats.bookings;
                document.getElementById('stat-fleet').textContent = data.stats.fleet;
                document.getElementById('stat-customers').textContent = data.stats.customers;
                
                // Update activity
                const activityList = document.getElementById('recent-activity-list');
                activityList.innerHTML = ''; // clear loading
                
                if (data.activity.length === 0) {
                    activityList.innerHTML = '<li class="activity-item" style="justify-content: center; color: var(--text-secondary);">No recent activity.</li>';
                } else {
                    data.activity.forEach(act => {
                        const li = document.createElement('li');
                        li.className = 'activity-item';
                        li.innerHTML = `
                            <div class="activity-icon"><i class="fa-solid ${act.icon}"></i></div>
                            <div class="activity-details">
                                <h4>${act.title}</h4>
                                <p>${act.desc}</p>
                            </div>
                        `;
                        activityList.appendChild(li);
                    });
                }
            } else {
                console.error("Failed to load stats:", data.error);
                document.getElementById('recent-activity-list').innerHTML = '<li class="activity-item" style="justify-content: center; color: red;">Failed to load activity.</li>';
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('recent-activity-list').innerHTML = '<li class="activity-item" style="justify-content: center; color: red;">Error fetching data.</li>';
        });
});
</script>

<?php include 'includes/footer.php'; ?>