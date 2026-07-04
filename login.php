<?php
/**
 * login.php - Sign-in page.
 */
$loginError = isset($_GET['error']) && $_GET['error'] === '1';
$googleError = isset($_GET['error']) && $_GET['error'] === '2';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - DriveEase Support</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');</script>
</head>
<body>
    <button class="theme-toggle" id="themeToggle" type="button" aria-label="Toggle dark mode">
        <span class="toggle-icon" id="toggleIcon">🌙</span>
        <span id="toggleLabel">Dark</span>
    </button>

    <div class="auth-card">
        <div class="auth-brand">
            <h1 class="auth-brand__title">Welcome back</h1>
            <p class="auth-brand__subtitle">Sign in to your account to continue.</p>
        </div>

        <?php if ($loginError || $googleError): ?>
            <div class="auth-alert auth-alert--error"><?= $googleError ? 'Google sign-in failed.' : 'Invalid credentials.' ?></div>
        <?php endif; ?>

        <form class="auth-form" action="includes/login_process.php" method="POST">
            <div class="form-group"><label>Email</label><input class="auth-input" type="email" name="email" required></div>
            <div class="form-group"><label>Password</label><input class="auth-input" type="password" name="password" required></div>
            <div class="forgot-password"><a href="forgot_password.php">Forgot password?</a></div>
            <button class="btn-primary" type="submit">Sign In</button>
        </form>

        <div class="auth-social">
            <div class="auth-divider">Or continue with</div>
            <div id="googleButton" class="google-button-shell"></div>
            <div class="auth-alert auth-alert--error" id="googleLoadError" role="alert" style="display:none;">Google sign-in could not load. Please try email and password.</div>
        </div>
    </div>

    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
    (function () {
        const clientId = '556945368804-9i8u0n9sihkff4kriqb72cgji03vc8ro.apps.googleusercontent.com';
        const html = document.documentElement;
        const icons = { dark: ['☀️', 'Light'], light: ['🌙', 'Dark'] };

        function applyTheme(theme) {
            html.setAttribute('data-theme', theme);
            document.getElementById('toggleIcon').textContent = icons[theme][0];
            document.getElementById('toggleLabel').textContent = icons[theme][1];
        }

        applyTheme(html.getAttribute('data-theme') || 'light');

        document.getElementById('themeToggle').addEventListener('click', function () {
            const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', next);
            applyTheme(next);
        });

        function handleGoogleCredentialResponse(response) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'includes/login_process.php';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'google_credential';
            input.value = response.credential;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }

        function showGoogleLoadError() {
            document.getElementById('googleLoadError').style.display = 'block';
        }

        window.addEventListener('load', function () {
            if (!window.google || !google.accounts || !google.accounts.id) {
                showGoogleLoadError();
                return;
            }

            google.accounts.id.initialize({
                client_id: clientId,
                callback: handleGoogleCredentialResponse
            });

            const googleButton = document.getElementById('googleButton');
            google.accounts.id.renderButton(
                googleButton,
                {
                    theme: html.getAttribute('data-theme') === 'dark' ? 'filled_black' : 'outline',
                    size: 'large',
                    width: Math.min(340, googleButton.offsetWidth),
                    text: 'signin_with',
                    shape: 'rectangular'
                }
            );
        });
    })();
    </script>
</body>
</html>