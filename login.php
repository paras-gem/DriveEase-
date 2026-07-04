<?php
/**
 * login.php — Sign-in page.
 */
$loginError = isset($_GET['error']) && $_GET['error'] === '1';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — DriveEase Support</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');</script>
</head>
<body>

    <button class="theme-toggle" id="themeToggle" type="button">
        <span class="toggle-icon" id="toggleIcon">🌙</span>
        <span id="toggleLabel">Dark</span>
    </button>

    <div class="auth-card">
        <div class="auth-brand">
            <p class="auth-brand__name">DriveEase Support</p>
            <h1 class="auth-brand__title">Welcome back</h1>
            <p class="auth-brand__subtitle">Sign in to your account to continue.</p>
        </div>

        <?php if ($loginError): ?>
            <div class="auth-alert auth-alert--error">Invalid email or password.</div>
        <?php endif; ?>

        <form class="auth-form" action="includes/login_process.php" method="POST" id="loginForm">
            <div class="form-group">
                <label for="email">Email address</label>
                <input class="auth-input" type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <div class="form-group__header">
                    <label for="password">Password</label>
                    <a class="auth-link" href="forgot_password.php">Forgot password?</a>
                </div>
                <div class="input-wrapper">
                    <input class="auth-input auth-input--password" type="password" id="password" name="password" placeholder="••••••••" required>
                    <button class="input-eye-btn" type="button" id="eyeBtn">👁</button>
                </div>
            </div>

            <button class="btn-primary" type="submit" id="submitBtn">
                <span class="spinner" id="spinner"></span>
                <span id="btnText">Sign In</span>
            </button>
        </form>

        <div style="margin: 20px 0;">
            <div class="auth-divider">Or continue with</div>
            <br>
            <a href="includes/login_process.php?google=1" class="btn-primary" style="background: #ffffff; color: #333; border: 1px solid var(--border);">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="20" style="margin-right: 10px;">
                Sign in with Google
            </a>
        </div>

        <div class="auth-footer">
            Don't have an account? <a href="signup.php">Create one</a>
        </div>
    </div>

    <script>
        // (Your existing JS here...)
        document.getElementById('eyeBtn').addEventListener('click', function() {
            const pw = document.getElementById('password');
            pw.type = pw.type === 'password' ? 'text' : 'password';
        });
    </script>
</body>
</html>