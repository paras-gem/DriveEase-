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
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: var(--text-primary);">Type of Request</label>
                    <select id="type" name="type" required class="driveease-input">
                        <option value="feedback">🗣️ General Feedback</option>
                        <option value="feature">💡 Suggest a Feature</option>
                        <option value="issue">🐛 Report an Issue / Bug</option>
                        <option value="help">🆘 Need Help</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: var(--text-primary);">Subject</label>
                    <input type="text" id="subject" name="subject" required class="driveease-input" placeholder="Brief summary of your request">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: var(--text-primary);">Description</label>
                    <textarea id="description" name="description" rows="5" required class="driveease-input" placeholder="Provide as much detail as possible..." style="resize: vertical;"></textarea>
                </div>
                
                <button type="submit" id="submitBtn" class="btn">
                    <i class="fa-solid fa-paper-plane"></i> Submit Request
                </button>
                <div id="supportMessage" style="margin-top: 10px; font-weight: 500; text-align: center;"></div>
            </form>
        </div>
    </div>
</main>

<style>
.driveease-input {
    width: 100%;
    padding: 14px 16px;
    background: var(--surface-solid);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    color: var(--text-primary);
    transition: var(--transition);
    outline: none;
}
.driveease-input:focus {
    border-color: var(--accent-color);
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
}
.driveease-input:hover {
    border-color: var(--accent-color);
}
select.driveease-input {
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%3E%3Cpath%20fill%3D%22%236b7280%22%20d%3D%22M7%2010l5%205%205-5z%22%2F%3E%3C%2Fsvg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 20px;
    padding-right: 40px;
}
</style>

<script>
const currentUserId = <?php echo $_SESSION['user_id']; ?>;

document.getElementById('supportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    const msg = document.getElementById('supportMessage');
    
    btn.style.opacity = '0.7';
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting...';
    btn.disabled = true;
    
    const payload = {
        user_id: currentUserId,
        type: document.getElementById('type').value,
        subject: document.getElementById('subject').value,
        description: document.getElementById('description').value
    };
    
    fetch('api/feedback.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            msg.style.color = '#10b981';
            msg.innerHTML = '<i class="fa-solid fa-circle-check"></i> Thank you! Your feedback has been saved and our team will review it shortly.';
            this.reset();
        } else {
            msg.style.color = '#ef4444';
            msg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Error: ' + (data.error || 'Something went wrong.');
        }
        btn.style.opacity = '1';
        btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Submit Request';
        btn.disabled = false;
        setTimeout(() => msg.innerHTML = '', 5000);
    })
    .catch(err => {
        msg.style.color = '#ef4444';
        msg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Failed to submit. Please try again.';
        btn.style.opacity = '1';
        btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Submit Request';
        btn.disabled = false;
    });
});
</script>

<?php include 'includes/footer.php'; ?>
