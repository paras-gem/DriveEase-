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
    <link rel="stylesheet" href="assets/css/auth.css">
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');</script>
</head>
<body>
    <button class="theme-toggle" id="themeToggle" type="button"><span id="toggleIcon">🌙</span></button>

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
            <button class="btn-google" id="googleSignInButton" type="button">
                <svg class="btn-google__icon" viewBox="0 0 48 48" aria-hidden="true" focusable="false"><path fill="#EA4335" d="M24 9.5c3.3 0 6.25 1.14 8.58 3.36l6.4-6.4C35.11 2.86 29.97.75 24 .75 14.77.75 6.81 6.04 2.94 13.75l7.88 6.12C12.67 13.78 17.94 9.5 24 9.5z"/><path fill="#4285F4" d="M46.5 24.53c0-1.59-.14-3.11-.41-4.59H24v8.69h12.62c-.54 2.92-2.19 5.4-4.66 7.06l7.22 5.6C43.39 37.4 46.5 31.67 46.5 24.53z"/><path fill="#FBBC05" d="M10.82 27.89a14.47 14.47 0 0 1 0-7.78l-7.88-6.12A23.25 23.25 0 0 0 .5 24c0 3.62.87 7.05 2.44 10.01l7.88-6.12z"/><path fill="#34A853" d="M24 47.25c5.97 0 10.98-1.96 14.64-5.35l-7.22-5.6c-2.01 1.35-4.58 2.14-7.42 2.14-6.06 0-11.33-4.09-13.18-9.59l-7.88 6.12C6.81 42.46 14.77 47.25 24 47.25z"/></svg>
                <span class="btn-google__label">Sign in with Google</span>
            </button>
        </div>
    </div>

    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        function handleGoogleCredentialResponse(response) {
            const form = document.createElement('form');
            form.method = 'POST'; form.action = 'includes/login_process.php';
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'google_credential'; input.value = response.credential;
            form.appendChild(input); document.body.appendChild(form); form.submit();
        }
        window.onload = () => {
            google.accounts.id.initialize({
                client_id: 'YOUR_CLIENT_ID_HERE.apps.googleusercontent.com',
                callback: handleGoogleCredentialResponse
            });
            document.getElementById('googleSignInButton').onclick = () => google.accounts.id.prompt();
        };
    </script>
</body>
</html>