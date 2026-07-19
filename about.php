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
        <h2 class="page-title">About DriveEase</h2>
        <div class="user-profile">
            <button id="themeToggleBtn" class="theme-toggle" aria-label="Toggle Theme">
                <i class="fa-solid fa-moon"></i>
            </button>
            <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
        </div>
    </div>

    <div class="content-grid" style="grid-template-columns: 1fr; max-width: 800px; margin: 0 auto;">
        <div class="card" style="text-align: center; padding: 40px 20px;">
            <div style="font-size: 48px; color: var(--primary-color); margin-bottom: 20px;">
                <i class="fa-solid fa-car-side"></i>
            </div>
            <h1 style="margin-bottom: 15px; font-size: 28px;">DriveEase Support Desk</h1>
            <p style="color: var(--text-secondary); line-height: 1.6; margin-bottom: 30px; font-size: 16px;">
                Welcome to DriveEase, the premier fleet management and customer support platform. 
                Our mission is to streamline your vehicle operations, enhance customer satisfaction, 
                and provide real-time insights into your entire fleet workflow.
            </p>
            
            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                <div style="background: var(--bg-color); padding: 20px; border-radius: 8px; border: 1px solid var(--border-color); width: 200px;">
                    <i class="fa-solid fa-bolt" style="font-size: 24px; color: #eab308; margin-bottom: 10px;"></i>
                    <h4 style="margin-bottom: 8px;">Fast</h4>
                    <p style="font-size: 14px; color: var(--text-secondary);">Optimized for speed and quick resolutions.</p>
                </div>
                <div style="background: var(--bg-color); padding: 20px; border-radius: 8px; border: 1px solid var(--border-color); width: 200px;">
                    <i class="fa-solid fa-shield-halved" style="font-size: 24px; color: #3b82f6; margin-bottom: 10px;"></i>
                    <h4 style="margin-bottom: 8px;">Secure</h4>
                    <p style="font-size: 14px; color: var(--text-secondary);">Industry standard security for your data.</p>
                </div>
                <div style="background: var(--bg-color); padding: 20px; border-radius: 8px; border: 1px solid var(--border-color); width: 200px;">
                    <i class="fa-solid fa-chart-pie" style="font-size: 24px; color: #10b981; margin-bottom: 10px;"></i>
                    <h4 style="margin-bottom: 8px;">Insightful</h4>
                    <p style="font-size: 14px; color: var(--text-secondary);">Real-time analytics and tracking.</p>
                </div>
            </div>
            
            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid var(--border-color); color: var(--text-secondary); font-size: 14px;">
                <p>Version 1.0.0 &copy; <?php echo date('Y'); ?> DriveEase Inc. All rights reserved.</p>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
