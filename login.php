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
            <button class="btn-primary" type="submit">Sign In</button>
        </form>

        <div class="auth-social">
            <div class="auth-divider">Or continue with</div>
            <button class="btn-google" id="googleSignInButton" type="button">
                <svg class="btn-google__icon" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.5 24.5c0-1.54-.15-3.02-.43-4.45H24v8.43h12.43c-.54 2.9-2.18 5.36-4.65 7.02l7.2 5.6C43.9 37.01 46.5 31.2 46.5 24.5z"/><path fill="#FBBC05" d="M10.54 28.41A14.5 14.5 0 0 1 10.54 19.6l-7.98-6.19A24.0 24.0 0 0 0 0 24.5c0 3.87.93 7.54 2.56 10.78l7.98-6.19z"/><path fill="#34A853" d="M24 46.5c6.47 0 11.9-2.14 15.87-5.81l-7.2-5.6c-2.01 1.35-4.58 2.15-8.67 2.15-6.26 0-11.57-4.22-13.46-9.91l-7.98 6.19C6.51 42.62 14.62 46.5 24 46.5z"/></svg>
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