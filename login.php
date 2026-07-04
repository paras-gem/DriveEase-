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
                <svg class="btn-google__icon" viewBox="0 0 48 48" aria-hidden="true">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.5 24.5c0-1.54-.15-3.02-.43-4.45H24v8.43h12.43c-.54 2.9-2.18 5.36-4.65 7.02l7.2 5.6C43.9 37.01 46.5 31.2 46.5 24.5z"/>
                    <path fill="#FBBC05" d="M10.54 28.41A14.5 14.5 0 0 1 10.54 19.6l-7.98-6.19A24.0 24.0 0 0 0 0 24.5c0 3.87.93 7.54 2.56 10.78l7.98-6.19z"/>
                    <path fill="#34A853" d="M24 46.5c6.47 0 11.9-2.14 15.87-5.81l-7.2-5.6c-2.01 1.35-4.58 2.15-8.67 2.15-6.26 0-11.57-4.22-13.46-9.91l-7.98 6.19C6.51 42.62 14.62 46.5 24 46.5z"/>
                </svg>
                <span>Continue with Google</span>
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
                googleBtn.innerHTML = '<span>Google SDK unavailable</span>';
            }
        });
    </script>
</body>
</html>