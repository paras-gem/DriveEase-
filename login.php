<?php
/**
 * login.php — DriveEase Support Desk
 * -------------------------------------------------------------------
 * Authentication entry point.
 * - Submits credentials to includes/login_process.php via POST
 * - Shows inline error message when PHP sets ?error=1 in redirect
 * - Includes dark/light mode toggle (persisted in localStorage)
 * -------------------------------------------------------------------
 */

// Display an error banner if login_process.php redirected back with ?error=1
$loginError = isset($_GET['error']) && $_GET['error'] === '1';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO -->
    <title>Sign In — DriveEase Support</title>
    <meta name="description" content="Sign in to your DriveEase Support account to manage tickets and requests.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Auth stylesheet -->
    <link rel="stylesheet" href="assets/css/auth.css">

    <!-- Apply saved theme before paint to avoid flash of wrong theme -->
    <script>
        (function () {
            const saved = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', saved);
        })();
    </script>
</head>
<body>

    <!-- =====================================================================
         DARK / LIGHT MODE TOGGLE
         Saves preference to localStorage so it persists across pages.
    ====================================================================== -->
    <button
        class="theme-toggle"
        id="themeToggle"
        type="button"
        aria-label="Toggle dark mode"
        title="Toggle dark / light mode"
    >
        <span class="toggle-icon" id="toggleIcon">🌙</span>
        <span id="toggleLabel">Dark</span>
    </button>

    <!-- =====================================================================
         AUTH CARD
    ====================================================================== -->
    <div class="auth-card" role="main">

        <!-- Brand header -->
        <div class="auth-brand">
            <p class="auth-brand__name">DriveEase Support</p>
            <h1 class="auth-brand__title">Welcome back</h1>
            <p class="auth-brand__subtitle">Sign in to your account to continue.</p>
        </div>

        <!-- Error alert — only rendered when PHP detects ?error=1 -->
        <?php if ($loginError): ?>
            <div class="auth-alert auth-alert--error" role="alert" id="loginAlert">
                Invalid email or password. Please try again.
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form
            class="auth-form"
            action="includes/login_process.php"
            method="POST"
            id="loginForm"
            novalidate
        >

            <!-- Email field -->
            <div class="form-group">
                <label for="email">Email address</label>
                <input
                    class="auth-input"
                    type="email"
                    id="email"
                    name="email"
                    placeholder="you@example.com"
                    autocomplete="email"
                    required
                >
            </div>

            <!-- Password field with show/hide toggle -->
            <div class="form-group">
                <div class="form-group__header">
                    <label for="password">Password</label>
                    <a class="auth-link" href="forgot_password.php" tabindex="-1">Forgot password?</a>
                </div>
                <div class="input-wrapper">
                    <input
                        class="auth-input auth-input--password"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <!-- Eye icon toggles input type between password/text -->
                    <button
                        class="input-eye-btn"
                        type="button"
                        id="eyeBtn"
                        aria-label="Show or hide password"
                        tabindex="-1"
                    >👁</button>
                </div>
            </div>

            <!-- Submit -->
            <button class="btn-primary" type="submit" id="submitBtn">
                <span class="spinner" id="btnSpinner"></span>
                <span id="btnText">Sign In</span>
            </button>

        </form>

    </div><!-- /.auth-card -->

    <!-- =====================================================================
         JAVASCRIPT
         1. Theme toggle (dark / light)
         2. Password show/hide
         3. Loading state on form submit
    ====================================================================== -->
    <script>
    (function () {
        'use strict';

        /* ------------------------------------------------------------------
           1. THEME TOGGLE
        ------------------------------------------------------------------ */
        const html        = document.documentElement;
        const toggleBtn   = document.getElementById('themeToggle');
        const toggleIcon  = document.getElementById('toggleIcon');
        const toggleLabel = document.getElementById('toggleLabel');

        // Sync the button label to match the current theme
        function syncToggleUI(theme) {
            if (theme === 'dark') {
                toggleIcon.textContent  = '☀️';
                toggleLabel.textContent = 'Light';
            } else {
                toggleIcon.textContent  = '🌙';
                toggleLabel.textContent = 'Dark';
            }
        }

        // Initialise button to match the theme already applied by the inline script
        syncToggleUI(html.getAttribute('data-theme'));

        toggleBtn.addEventListener('click', function () {
            const current = html.getAttribute('data-theme');
            const next    = current === 'dark' ? 'light' : 'dark';

            html.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            syncToggleUI(next);
        });

        /* ------------------------------------------------------------------
           2. SHOW / HIDE PASSWORD
        ------------------------------------------------------------------ */
        const passwordInput = document.getElementById('password');
        const eyeBtn        = document.getElementById('eyeBtn');

        eyeBtn.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type      = isPassword ? 'text' : 'password';
            eyeBtn.textContent      = isPassword ? '🙈' : '👁';
            eyeBtn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        });

        /* ------------------------------------------------------------------
           3. LOADING STATE ON SUBMIT
              Shows a spinner and disables the button while the request is
              in-flight, preventing double-submits.
        ------------------------------------------------------------------ */
        const form      = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const spinner   = document.getElementById('btnSpinner');
        const btnText   = document.getElementById('btnText');

        form.addEventListener('submit', function () {
            submitBtn.disabled         = true;
            spinner.style.display      = 'block';
            btnText.textContent        = 'Signing in…';
        });

    })();
    </script>

</body>
</html>