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
        <h2 class="page-title">Customers</h2>
        <div class="user-profile">
            <button id="themeToggleBtn" class="theme-toggle" aria-label="Toggle Theme">
                <i class="fa-solid fa-moon"></i>
            </button>
            <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
        </div>
    </div>

    <div class="content-grid" style="grid-template-columns: 1fr;">
        <div class="card">
            <h3 class="card-title">Registered Customers</h3>
            <table style="width: 100%; text-align: left; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">ID</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Name</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Email</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Joined Date</th>
                    </tr>
                </thead>
                <tbody id="customersTableBody">
                    <tr><td colspan="4" style="text-align: center; padding: 20px;">Loading customers...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    fetch('api/customers.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('customersTableBody');
            tbody.innerHTML = '';
            
            if (data.error) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: red;">Error: ${data.error}</td></tr>`;
                return;
            }
            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align: center;">No customers found.</td></tr>`;
                return;
            }
            
            data.forEach(user => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">${user.id}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); font-weight: 500;">${user.name}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">${user.email}</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">${new Date(user.created_at).toLocaleDateString()}</td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => {
            document.getElementById('customersTableBody').innerHTML = `<tr><td colspan="4" style="text-align: center; color: red;">Failed to load data.</td></tr>`;
        });
});
</script>

<?php include 'includes/footer.php'; ?>
