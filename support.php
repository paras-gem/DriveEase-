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
                    <select id="type" name="type" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--card-bg, var(--bg-color)); color: var(--text-primary); font-size: 14px; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2220%22%20height%3D%2220%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20fill%3D%22%236b7280%22%20d%3D%22M7%207l3%203%203-3%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 10px center; cursor: pointer; transition: border-color 0.2s, box-shadow 0.2s;">
                        <option value="feedback">🗣️ General Feedback</option>
                        <option value="feature">💡 Suggest a Feature</option>
                        <option value="issue">🐛 Report an Issue / Bug</option>
                        <option value="help">🆘 Need Help</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 500;">Subject</label>
                    <input type="text" id="subject" name="subject" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--card-bg, var(--bg-color)); color: var(--text-primary); font-size: 14px; transition: border-color 0.2s, box-shadow 0.2s;" placeholder="Brief summary of your request">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 500;">Description</label>
                    <textarea id="description" name="description" rows="5" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--card-bg, var(--bg-color)); color: var(--text-primary); font-size: 14px; resize: vertical; transition: border-color 0.2s, box-shadow 0.2s;" placeholder="Provide as much detail as possible..."></textarea>
                </div>
                
                <button type="submit" id="submitBtn" class="btn" style="padding: 12px; background: var(--sidebar-hover, #6366f1); color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: opacity 0.2s, transform 0.1s; font-size: 14px;">
                    Submit Request
                </button>
                <div id="supportMessage" style="margin-top: 10px; font-weight: 500; text-align: center;"></div>
            </form>
        </div>
    </div>
</main>

<style>
    #supportForm select:focus,
    #supportForm input:focus,
    #supportForm textarea:focus {
        outline: none;
        border-color: var(--sidebar-hover, #6366f1);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    #supportForm select:hover,
    #supportForm input:hover,
    #supportForm textarea:hover {
        border-color: var(--sidebar-hover, #6366f1);
    }
    #submitBtn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    #submitBtn:active {
        transform: translateY(0);
    }
</style>

<script>
const currentUserId = <?php echo $_SESSION['user_id']; ?>;

document.getElementById('supportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    const msg = document.getElementById('supportMessage');
    
    btn.style.opacity = '0.7';
    btn.textContent = 'Submitting...';
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
        btn.textContent = 'Submit Request';
        btn.disabled = false;
        setTimeout(() => msg.innerHTML = '', 5000);
    })
    .catch(err => {
        msg.style.color = '#ef4444';
        msg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Failed to submit. Please try again.';
        btn.style.opacity = '1';
        btn.textContent = 'Submit Request';
        btn.disabled = false;
    });
});
</script>

<?php include 'includes/footer.php'; ?>
