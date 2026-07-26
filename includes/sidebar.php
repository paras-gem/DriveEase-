<?php $isEmployee = ($_SESSION['role'] ?? 'customer') === 'employee'; ?>
<aside class="sidebar">
    <div class="sidebar-header">DriveEase</div>
    <ul class="nav-links">
        <li><a href="<?php echo $isEmployee ? 'employee-dashboard.php' : 'dashboard.php'; ?>" class="nav-item"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <li><a href="tickets.php" class="nav-item"><i class="fa-solid fa-ticket"></i> Support Tickets</a></li>
        <?php if (!$isEmployee): ?>
        <li><a href="bookings.php" class="nav-item"><i class="fa-solid fa-book-open"></i> Bookings</a></li>
        <?php endif; ?>
        <li><a href="support.php" class="nav-item"><i class="fa-solid fa-headset"></i> Support</a></li>
        <li><a href="about.php" class="nav-item"><i class="fa-solid fa-circle-info"></i> About</a></li>
        <li style="margin-top: auto;"><a href="logout.php" class="nav-item logout-link" style="color: #ef4444;"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a></li>
    </ul>
</aside>