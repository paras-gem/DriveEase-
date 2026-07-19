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
        <h2 class="page-title">Support Tickets</h2>
        <div class="user-profile">
            <button id="themeToggleBtn" class="theme-toggle" aria-label="Toggle Theme">
                <i class="fa-solid fa-moon"></i>
            </button>
            <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
        </div>
    </div>

    <div class="content-grid" style="grid-template-columns: 1fr;">
        
        <!-- Create Ticket Form -->
        <div class="card" style="margin-bottom: 20px;">
            <h3 class="card-title">Open a New Ticket</h3>
            <form id="createTicketForm" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; margin-bottom: 5px;">Subject</label>
                    <input type="text" id="subject" required style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;" placeholder="What is the issue?">
                </div>
                <div style="flex: 2; min-width: 300px;">
                    <label style="display: block; margin-bottom: 5px;">Description</label>
                    <input type="text" id="description" required style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;" placeholder="Provide details...">
                </div>
                <div style="width: 150px;">
                    <label style="display: block; margin-bottom: 5px;">Priority</label>
                    <select id="priority" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <button type="submit" class="btn" style="padding: 9px 20px; background: var(--bg-color); color: var(--text-primary); border: 1px solid var(--border-color);">Submit Ticket</button>
            </form>
            <div id="ticketMessage" style="margin-top: 10px;"></div>
        </div>

        <div class="card">
            <h3 class="card-title">All Tickets</h3>
            <table style="width: 100%; text-align: left; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">ID</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">User</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Subject</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Priority</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Status</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Date</th>
                    </tr>
                </thead>
                <tbody id="ticketsTableBody">
                    <tr><td colspan="6" style="text-align: center; padding: 20px;">Loading tickets...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
const currentUserId = <?php echo $_SESSION['user_id']; ?>;

function loadTickets() {
    fetch('api/tickets.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('ticketsTableBody');
            tbody.innerHTML = '';
            
            if (data.error) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: red;">Error: ${data.error}</td></tr>`;
                return;
            }
            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align: center;">No tickets found.</td></tr>`;
                return;
            }
            
            data.forEach(ticket => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">#${ticket.id}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); font-weight: 500;">${ticket.user_name || 'User ' + ticket.user_id}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">${ticket.subject}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); text-transform: capitalize;">${ticket.priority}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                        <span style="padding: 4px 8px; border-radius: 12px; font-size: 12px; background: ${ticket.status === 'open' ? '#3b82f6' : (ticket.status === 'resolved' ? '#10b981' : '#f59e0b')}; color: white;">
                            ${ticket.status}
                        </span>
                    </td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">${new Date(ticket.created_at).toLocaleDateString()}</td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => {
            document.getElementById('ticketsTableBody').innerHTML = `<tr><td colspan="6" style="text-align: center; color: red;">Failed to load data.</td></tr>`;
        });
}

document.getElementById('createTicketForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const msg = document.getElementById('ticketMessage');
    
    const payload = {
        user_id: currentUserId,
        subject: document.getElementById('subject').value,
        description: document.getElementById('description').value,
        priority: document.getElementById('priority').value
    };
    
    fetch('api/tickets.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            msg.innerHTML = '<span style="color: green;">Ticket created successfully!</span>';
            this.reset();
            loadTickets();
        } else {
            msg.innerHTML = `<span style="color: red;">Error: ${data.error}</span>`;
        }
    })
    .catch(err => {
        msg.innerHTML = '<span style="color: red;">Failed to submit ticket.</span>';
    });
});

document.addEventListener("DOMContentLoaded", loadTickets);
</script>

<?php include 'includes/footer.php'; ?>
