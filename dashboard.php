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
            <span class="stat-value">12</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Pending Bookings</span>
            <span class="stat-value">4</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Available Fleet</span>
            <span class="stat-value">18</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Total Customers</span>
            <span class="stat-value">1,240</span>
        </div>
    </div>

    <div class="content-grid">
        <div class="card">
            <h3 class="card-title">Recent Activity</h3>
            <ul class="activity-list">
                <li class="activity-item">
                    <div class="activity-icon"><i class="fa-solid fa-ticket"></i></div>
                    <div class="activity-details">
                        <h4>Ticket #1042 Updated</h4>
                        <p>John Doe replied to "Engine issue with Sedan".</p>
                    </div>
                </li>
                <li class="activity-item">
                    <div class="activity-icon"><i class="fa-solid fa-car"></i></div>
                    <div class="activity-details">
                        <h4>New Vehicle Added</h4>
                        <p>Toyota Camry 2023 was added to the fleet.</p>
                    </div>
                </li>
                <li class="activity-item">
                    <div class="activity-icon"><i class="fa-solid fa-user-plus"></i></div>
                    <div class="activity-details">
                        <h4>New Customer Registered</h4>
                        <p>Jane Smith created an account.</p>
                    </div>
                </li>
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

<?php include 'includes/footer.php'; ?>