<?php
/**
 * login.php — Sign-in page.
 */
$loginError = isset($_GET['error']) && $_GET['error'] === '1';
$googleError = isset($_GET['error']) && $_GET['error'] === '2';
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

    <!-- Subtle theme switch so the auth screen keeps its clean layout. -->
    <button class="theme-toggle" id="themeToggle" type="button" aria-label="Toggle dark mode">
        <span class="toggle-icon" id="toggleIcon">🌙</span>
    </button>

    <div class="auth-card">
        <div class="auth-brand">
            <p class="auth-brand__name">DriveEase Support</p>
            <h1 class="auth-brand__title">Welcome back</h1>
            <p class="auth-brand__subtitle">Sign in to your account to continue.</p>
        </div>

        <?php if ($loginError || $googleError): ?>
            <div class="auth-alert auth-alert--error">
                <?= $googleError ? 'Google sign-in failed. Please try again.' : 'Invalid email or password.' ?>
            </div>
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

        <div class="auth-social">
            <div class="auth-divider">Or continue with</div>
            <!-- Google-branded icon for the sign-in button. -->
            <button class="btn-google" id="googleSignInButton" type="button" aria-label="Sign in with Google">
                <svg class="btn-google__icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#4285F4" d="M21.6 12.23c0-.66-.06-1.29-.17-1.9H12v3.6h5.39a4.61 4.61 0 0 1-2 3.03v2.5h3.24c1.89-1.74 2.97-4.3 2.97-7.23Z"/>
                    <path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.61-2.43l-3.24-2.5c-.9.61-2.06.97-3.37.97-2.59 0-4.79-1.74-5.58-4.09H3.07v2.57A10 10 0 0 0 12 22Z"/>
                    <path fill="#FBBC05" d="M6.42 13.95A6.01 6.01 0 0 1 6.42 10.05V7.48H3.07a10 10 0 0 0 0 12.94l3.35-2.57Z"/>
                    <path fill="#EA4335" d="M12 6.04c1.46 0 2.78.5 3.82 1.49l2.86-2.86A9.96 9.96 0 0 0 12 2 9.98 9.98 0 0 0 3.07 7.48l3.35 2.57C7.21 7.78 9.41 6.04 12 6.04Z"/>
                </svg>
                <span class="btn-google__label">Continue with Google</span>
            </button>
        </div>

        <div class="auth-footer">
            Don't have an account? <a href="signup.php">Create one</a>
        </div>
    </div>

    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        const html = document.documentElement;
        const toggleBtn = document.getElementById('themeToggle');
        const toggleIcon = document.getElementById('toggleIcon');
        const eyeBtn = document.getElementById('eyeBtn');
        const googleBtn = document.getElementById('googleSignInButton');

        // Keep the auth experience readable in both themes without changing the card layout.
        const themeIcons = {
            light: '🌙',
            dark: '☀️'
        };

        function applyTheme(theme) {
            html.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            toggleIcon.textContent = themeIcons[theme];
        }

        applyTheme(html.getAttribute('data-theme') || localStorage.getItem('theme') || 'light');

        toggleBtn.addEventListener('click', function () {
            const nextTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme);
        });

        eyeBtn.addEventListener('click', function () {
            const pw = document.getElementById('password');
            pw.type = pw.type === 'password' ? 'text' : 'password';
        });

        // Google sign-in uses the popup flow so the user stays on the same page.
        function handleGoogleCredentialResponse(response) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'includes/login_process.php';

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = 'google_credential';
            tokenInput.value = response.credential;

            form.appendChild(tokenInput);
            document.body.appendChild(form);
            form.submit();
        }

        window.addEventListener('load', function () {
            if (window.google && google.accounts && google.accounts.id) {
                google.accounts.id.initialize({
                    client_id: '556945368804-9i8u0n9sihkff4kriqb72cgji03vc8ro.apps.googleusercontent.com',
                    callback: handleGoogleCredentialResponse,
                    auto_select: false,
                    ux_mode: 'popup'
                });

                googleBtn.addEventListener('click', function () {
                    google.accounts.id.prompt();
                });
            } else {
                googleBtn.disabled = true;
                googleBtn.setAttribute('aria-disabled', 'true');
                const label = googleBtn.querySelector('.btn-google__label');
                if (label) {
                    label.textContent = 'Google unavailable';
                }
            }
        });
    </script>
</body>
</html>