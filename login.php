<?php
/**
 * login.php — Sign-in page.
 * Submits to includes/login_process.php via POST.
 * On failure, login_process.php redirects back with ?error=1.
 */
$loginError = isset($_GET['error']) && $_GET['error'] === '1';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — DriveEase Support</title>
    <meta name="description" content="Sign in to your DriveEase Support account.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
    <!-- Apply saved theme before first paint (prevents flash) -->
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');</script>
</head>
<body>

    <!-- Theme toggle pill -->
    <button class="theme-toggle" id="themeToggle" type="button" aria-label="Toggle dark mode">
        <span class="toggle-icon" id="toggleIcon">🌙</span>
        <span id="toggleLabel">Dark</span>
    </button>

    <div class="auth-card" role="main">

        <div class="auth-brand">
            <p class="auth-brand__name">DriveEase Support</p>
            <h1 class="auth-brand__title">Welcome back</h1>
            <p class="auth-brand__subtitle">Sign in to your account to continue.</p>
        </div>

        <?php if ($loginError): ?>
            <div class="auth-alert auth-alert--error" role="alert">
                Invalid email or password. Please try again.
            </div>
        <?php endif; ?>

        <form class="auth-form" action="includes/login_process.php" method="POST" id="loginForm" novalidate>

            <div class="form-group">
                <label for="email">Email address</label>
                <input class="auth-input" type="email" id="email" name="email"
                       placeholder="you@example.com" autocomplete="email" required>
            </div>

            <div class="form-group">
                <div class="form-group__header">
                    <label for="password">Password</label>
                    <a class="auth-link" href="forgot_password.php" tabindex="-1">Forgot password?</a>
                </div>
                <div class="input-wrapper">
                    <input class="auth-input auth-input--password" type="password" id="password" name="password"
                           placeholder="••••••••" autocomplete="current-password" required>
                    <button class="input-eye-btn" type="button" id="eyeBtn" aria-label="Show password" tabindex="-1">👁</button>
                </div>
            </div>

            <button class="btn-primary" type="submit" id="submitBtn">
                <span class="spinner" id="spinner"></span>
                <span id="btnText">Sign In</span>
            </button>

        </form>

        <div class="auth-footer">
            Don't have an account? <a href="signup.php">Create one</a>
        </div>

    </div>

    <script>
    (function () {
        /* Theme toggle */
        const html  = document.documentElement;
        const icons = { dark: ['☀️','Light'], light: ['🌙','Dark'] };

        function applyTheme(t) {
            html.setAttribute('data-theme', t);
            document.getElementById('toggleIcon').textContent  = icons[t][0];
            document.getElementById('toggleLabel').textContent = icons[t][1];
        }

        applyTheme(html.getAttribute('data-theme'));

        document.getElementById('themeToggle').addEventListener('click', function () {
            const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', next);
            applyTheme(next);
        });

        /* Show / hide password */
        const pw  = document.getElementById('password');
        const eye = document.getElementById('eyeBtn');
        eye.addEventListener('click', function () {
            const show = pw.type === 'password';
            pw.type = show ? 'text' : 'password';
            eye.textContent = show ? '🙈' : '👁';
        });

        /* Loading state on submit */
        document.getElementById('loginForm').addEventListener('submit', function () {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('spinner').style.display = 'block';
            document.getElementById('btnText').textContent = 'Signing in…';
        });
    })();
    </script>

</body>
</html>