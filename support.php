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
        <h2 class="page-title">Help & Support</h2>
        <div class="user-profile">
            <button id="themeToggleBtn" class="theme-toggle" aria-label="Toggle Theme">
                <i class="fa-solid fa-moon"></i>
            </button>
            <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
        </div>
    </div>

    <div class="content-grid" style="grid-template-columns: 1fr; max-width: 800px; margin: 0 auto;">
        <div class="card">
            <h3 class="card-title">We'd love to hear from you!</h3>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">
                Have a suggestion, found a bug, or just need some help? Let us know below.
            </p>
            
            <form id="supportForm" style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 500;">Type of Request</label>
                    <select id="type" name="type" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-color); color: var(--text-primary);">
                        <option value="feedback">General Feedback</option>
                        <option value="feature">Suggest a Feature</option>
                        <option value="issue">Report an Issue / Bug</option>
                        <option value="help">Need Help</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 500;">Subject</label>
                    <input type="text" id="subject" name="subject" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-color); color: var(--text-primary);" placeholder="Brief summary of your request">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 500;">Description</label>
                    <textarea id="description" name="description" rows="5" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-color); color: var(--text-primary); resize: vertical;" placeholder="Provide as much detail as possible..."></textarea>
                </div>
                
                <button type="submit" class="btn" style="padding: 12px; background: var(--primary-color); color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;">
                    Submit Request
                </button>
                <div id="supportMessage" style="margin-top: 10px; font-weight: 500; text-align: center;"></div>
            </form>
        </div>
    </div>
</main>

<script>
document.getElementById('supportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button');
    const msg = document.getElementById('supportMessage');
    
    btn.style.opacity = '0.7';
    btn.textContent = 'Submitting...';
    
    // Simulate API request
    setTimeout(() => {
        msg.style.color = 'green';
        msg.innerHTML = '<i class="fa-solid fa-circle-check"></i> Thank you! Your request has been received and our team will review it shortly.';
        this.reset();
        btn.style.opacity = '1';
        btn.textContent = 'Submit Request';
        
        // Hide message after 5 seconds
        setTimeout(() => msg.innerHTML = '', 5000);
    }, 1500);
});
</script>

<?php include 'includes/footer.php'; ?>
