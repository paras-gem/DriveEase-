<?php
/**
 * login.php - Sign-in page.
 * Fully rewritten to use AJAX for seamless sign-in.
 */
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - DriveEase Support</title>
    <!-- Use our custom auth stylesheet -->
    <link rel="stylesheet" href="assets/css/auth.css">
    <!-- Apply theme quickly -->
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');</script>
</head>
<body>
    
    <!-- Theme Toggle -->
    <button class="theme-toggle" id="themeToggle" type="button" aria-label="Toggle dark mode">
        <span class="toggle-icon" id="toggleIcon">🌙</span>
        <span id="toggleLabel">Dark</span>
    </button>

    <div class="auth-card">
        
        <!-- Header -->
        <div class="auth-brand">
            <h1 class="auth-brand__title">Welcome back</h1>
            <p class="auth-brand__subtitle">Sign in to your account to continue.</p>
        </div>

        <!-- Alert Container for AJAX Responses -->
        <div id="ajaxAlert" class="auth-alert" style="display: none;" role="alert"></div>

        <!-- Traditional Login Form (AJAX attached below) -->
        <form class="auth-form" id="loginForm" novalidate>
            <div class="form-group">
                <label>Email</label>
                <input class="auth-input" type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input class="auth-input" type="password" name="password" required>
            </div>
            <div class="forgot-password"><a href="forgot_password.php">Forgot password?</a></div>
            
            <button class="btn-primary" type="submit" id="submitBtn">
                <span class="spinner" id="spinner"></span>
                <span id="btnText">Sign In</span>
            </button>
        </form>

        <!-- Social Login -->
        <div class="auth-social">
            <div class="auth-divider">Or continue with</div>
            <div id="googleButton" class="google-button-shell"></div>
            <div class="auth-alert auth-alert--error" id="googleLoadError" role="alert" style="display:none;">Google sign-in could not load. Please try email and password.</div>
        </div>

        <div class="auth-footer">
            New here? <a href="signup.php">Create an account</a>
        </div>
    </div>

    <!-- Google Identity API script -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    
    <!-- Application Logic -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        
        // ----------------------------------------------------
        // 1. Theme Configuration
        // ----------------------------------------------------
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

        // ----------------------------------------------------
        // 2. Google OAuth Handling (AJAX)
        // ----------------------------------------------------
        window.handleGoogleCredentialResponse = function(response) {
            
            // Send the Google credential token via AJAX
            const formData = new FormData();
            formData.append('google_credential', response.credential);

            fetch('includes/login_process.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    document.getElementById('ajaxAlert').style.display = 'block';
                    document.getElementById('ajaxAlert').className = 'auth-alert auth-alert--error';
                    document.getElementById('ajaxAlert').textContent = data.message;
                }
            })
            .catch(error => console.error('Error:', error));
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

        // ----------------------------------------------------
        // 3. Standard Login Form Submission (AJAX)
        // ----------------------------------------------------
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const spinner   = document.getElementById('spinner');
        const btnText   = document.getElementById('btnText');
        const alertBox  = document.getElementById('ajaxAlert');

        loginForm.addEventListener('submit', function (e) {
            e.preventDefault(); // Stop standard redirect

            // Reset UI states
            alertBox.style.display = 'none';
            alertBox.className = 'auth-alert';
            submitBtn.disabled = true;
            spinner.style.display = 'block';
            btnText.textContent = 'Authenticating…';

            // Gather inputs
            const formData = new FormData(loginForm);

            // Execute POST request to backend
            fetch('includes/login_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alertBox.style.display = 'block';
                alertBox.textContent = data.message;

                if (data.success) {
                    alertBox.classList.add('auth-alert--success');
                    // On success, quickly redirect to the dashboard
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 800);
                } else {
                    // Show error, re-enable button
                    alertBox.classList.add('auth-alert--error');
                    submitBtn.disabled = false;
                    spinner.style.display = 'none';
                    btnText.textContent = 'Sign In';
                }
            })
            .catch(error => {
                alertBox.style.display = 'block';
                alertBox.className = 'auth-alert auth-alert--error';
                alertBox.textContent = 'A network error occurred.';
                submitBtn.disabled = false;
                spinner.style.display = 'none';
                btnText.textContent = 'Sign In';
            });
        });
    });
    </script>
</body>
</html>