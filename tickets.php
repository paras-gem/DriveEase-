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
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Actions</th>
                    </tr>
                </thead>
                <tbody id="ticketsTableBody">
                    <tr><td colspan="7" style="text-align: center; padding: 20px;">Loading tickets...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
const currentUserId = <?php echo $_SESSION['user_id']; ?>;

function loadTickets() {
    const ticketRequest = new AbortController();
    const ticketTimeout = setTimeout(() => ticketRequest.abort(), 12000);
    fetch(`api/tickets.php?refresh=${Date.now()}`, { signal: ticketRequest.signal, cache: 'no-store' })
        .then(response => response.text())
        .then(text => {
            clearTimeout(ticketTimeout);
            const tbody = document.getElementById('ticketsTableBody');
            tbody.innerHTML = '';
            
            // Strip any HTML warnings that InfinityFree may inject before JSON
            let jsonStr = text;
            const jsonStart = text.indexOf('[');
            const jsonStartObj = text.indexOf('{');
            if (jsonStart === -1 && jsonStartObj === -1) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align: center;">No tickets found.</td></tr>`;
                return;
            }
            const start = jsonStart !== -1 && (jsonStartObj === -1 || jsonStart < jsonStartObj) ? jsonStart : jsonStartObj;
            jsonStr = text.substring(start);
            
            let data;
            try { data = JSON.parse(jsonStr); } catch(e) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align: center;">No tickets found.</td></tr>`;
                return;
            }
            
            if (data.error) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: red;">Error: ${data.error}</td></tr>`;
                return;
            }
            if (!Array.isArray(data) || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align: center;">No tickets found.</td></tr>`;
                return;
            }
            
            data.forEach(ticket => {
                const tr = document.createElement('tr');
                const safeSubject = (ticket.subject || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                tr.innerHTML = `
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">#${ticket.id}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); font-weight: 500;">${ticket.user_name || 'User ' + ticket.customer_id}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">${ticket.subject}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); text-transform: capitalize;">${ticket.priority}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                        <span style="padding: 4px 8px; border-radius: 12px; font-size: 12px; background: ${ticket.status === 'open' ? '#3b82f6' : (ticket.status === 'resolved' ? '#10b981' : '#f59e0b')}; color: white;">
                            ${ticket.status}
                        </span>
                    </td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">${new Date(ticket.created_at).toLocaleDateString()}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                        <button onclick="openComments(${ticket.id}, '${safeSubject}')" class="btn" style="background: var(--sidebar-hover); color: white; border: none; padding: 4px 8px; font-size: 12px; cursor: pointer; margin-right: 5px;">Comments</button>
                        ${ticket.status !== 'resolved' && ticket.status !== 'closed' ? `<button onclick="resolveTicket(${ticket.id})" class="btn" style="background: #10b981; color: white; border: none; padding: 4px 8px; font-size: 12px; cursor: pointer;">Resolve</button>` : ''}
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => {
            clearTimeout(ticketTimeout);
            document.getElementById('ticketsTableBody').innerHTML = `<tr><td colspan="7" style="text-align: center;">No tickets found.</td></tr>`;
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

function resolveTicket(id) {
    if (!confirm("Are you sure you want to mark this ticket as resolved?")) return;
    fetch('api/tickets.php', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, status: 'resolved' })
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            loadTickets();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

// Comments logic
let currentTicketId = null;

function openComments(id, subject) {
    currentTicketId = id;
    document.getElementById('commentsModal').style.display = 'block';
    document.getElementById('commentsTicketSubject').textContent = subject;
    loadComments();
}

function closeComments() {
    document.getElementById('commentsModal').style.display = 'none';
    currentTicketId = null;
}

function loadComments() {
    const list = document.getElementById('commentsList');
    list.innerHTML = '<div style="text-align: center; padding: 10px;">Loading comments...</div>';
    
    fetch(`api/tickets.php?action=comments&ticket_id=${currentTicketId}`)
        .then(r => r.json())
        .then(data => {
            list.innerHTML = '';
            if (data.error) {
                list.innerHTML = `<div style="color: red;">Error: ${data.error}</div>`;
                return;
            }
            if (data.length === 0) {
                list.innerHTML = `<div style="text-align: center; color: var(--text-secondary); padding: 10px;">No comments yet.</div>`;
                return;
            }
            data.forEach(c => {
                const author = c.customer_name ? c.customer_name : (c.employee_name ? c.employee_name + ' (Staff)' : 'Unknown');
                const isMe = c.customer_id == currentUserId;
                const div = document.createElement('div');
                div.style.marginBottom = '10px';
                div.style.padding = '10px';
                div.style.borderRadius = '8px';
                div.style.background = isMe ? 'rgba(59, 130, 246, 0.1)' : 'var(--bg-color)';
                div.style.border = '1px solid var(--border-color)';
                div.innerHTML = `
                    <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 4px;">
                        <strong>${author}</strong> &bull; ${new Date(c.created_at).toLocaleString()}
                    </div>
                    <div>${c.comment}</div>
                `;
                list.appendChild(div);
            });
            list.scrollTop = list.scrollHeight;
        });
}

document.getElementById('addCommentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!currentTicketId) return;
    const commentInput = document.getElementById('newComment');
    const comment = commentInput.value;
    
    fetch('api/tickets.php?action=comment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ticket_id: currentTicketId, user_id: currentUserId, comment: comment })
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            commentInput.value = '';
            loadComments();
        } else {
            alert('Error: ' + data.error);
        }
    });
});

// Initial fetch with a visible recovery state if the network request never settles.
loadTickets();
setTimeout(() => {
    const tbody = document.getElementById('ticketsTableBody');
    if (tbody && tbody.textContent.trim() === 'Loading tickets...') {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#dc2626; padding:20px;">Ticket loading timed out. Please refresh and try again.</td></tr>';
    }
}, 12000);
</script>

<!-- Comments Modal -->
<div id="commentsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 500px; max-height: 80vh; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
            <h3 style="margin: 0;">Ticket Comments: <span id="commentsTicketSubject" style="font-weight: normal; font-size: 16px;"></span></h3>
            <button onclick="closeComments()" style="background: transparent; border: none; font-size: 20px; cursor: pointer; color: var(--text-primary);">&times;</button>
        </div>
        <div id="commentsList" style="flex: 1; overflow-y: auto; margin-bottom: 15px; min-height: 150px;">
        </div>
        <form id="addCommentForm" style="display: flex; gap: 10px;">
            <input type="text" id="newComment" required placeholder="Type your reply..." style="flex: 1; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
            <button type="submit" class="btn" style="padding: 8px 15px; background: var(--sidebar-hover); color: white; border: none; cursor: pointer;">Send</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
