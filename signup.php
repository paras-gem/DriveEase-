<?php
/**
 * signup.php — DriveEase Support Desk
 * -------------------------------------------------------------------
 * Registration page utilizing AJAX for a seamless, page-reload-free
 * user experience. Fully commented to explain the logic.
 * -------------------------------------------------------------------
 */
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — DriveEase Support</title>
    <meta name="description" content="Create a DriveEase Support account to submit and track tickets.">
    <!-- Load required Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Link to our fully redesigned auth CSS -->
    <link rel="stylesheet" href="assets/css/auth.css">
    <!-- Apply theme before body load to prevent flickering -->
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');</script>
</head>
<body>

    <!-- Theme toggle button -->
    <button class="theme-toggle" id="themeToggle" type="button" aria-label="Toggle dark mode">
        <span class="toggle-icon" id="toggleIcon">🌙</span>
        <span id="toggleLabel">Dark</span>
    </button>

    <div class="auth-card" role="main">

        <!-- Branding Header -->
        <div class="auth-brand">
            <p class="auth-brand__name">DriveEase Support</p>
            <h1 class="auth-brand__title">Create an account</h1>
            <p class="auth-brand__subtitle">Fill in the details below to get started.</p>
        </div>

        <!-- Alert Container: Used by AJAX to display error/success messages dynamically -->
        <div id="ajaxAlert" class="auth-alert" style="display: none;" role="alert"></div>

        <!-- Signup form: default action prevented via JS, submitted via AJAX -->
        <form class="auth-form" id="signupForm" novalidate>

            <!-- Full name -->
            <div class="form-group">
                <label for="fullname">Full name</label>
                <input class="auth-input" type="text" id="fullname" name="fullname"
                       placeholder="Jane Smith" autocomplete="name" required>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email address</label>
                <input class="auth-input" type="email" id="email" name="email"
                       placeholder="you@example.com" autocomplete="email" required>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input class="auth-input auth-input--password" type="password" id="password" name="password"
                           placeholder="Min. 8 characters" autocomplete="new-password" required>
                    <button class="input-eye-btn" type="button" id="eyeBtn" aria-label="Show password" tabindex="-1">👁</button>
                </div>
            </div>

            <!-- Confirm password -->
            <div class="form-group">
                <label for="confirm_password">Confirm password</label>
                <input class="auth-input auth-input--password" type="password" id="confirm_password" name="confirm_password"
                       placeholder="Repeat password" autocomplete="new-password" required>
            </div>

            <!-- Security question -->
            <div class="form-group">
                <label for="security_question">Security question</label>
                <select class="auth-input" id="security_question" name="security_question" required>
                    <option value="" disabled selected>Choose a question…</option>
                    <option>What is your mother's maiden name?</option>
                    <option>What was the name of your first pet?</option>
                    <option>What city were you born in?</option>
                    <option>What is your oldest sibling's middle name?</option>
                    <option>What was the make of your first car?</option>
                </select>
            </div>

            <!-- Security answer -->
            <div class="form-group">
                <label for="security_answer">Your answer</label>
                <input class="auth-input" type="text" id="security_answer" name="security_answer"
                       placeholder="Answer to the question above" autocomplete="off" required>
            </div>

            <!-- Submit Button with loading spinner -->
            <button class="btn-primary" type="submit" id="submitBtn">
                <span class="spinner" id="spinner"></span>
                <span id="btnText">Create Account</span>
            </button>

        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Sign in</a>
        </div>

    </div>

    <!-- JavaScript block handling Theme, Password Visibility, and AJAX Form Submission -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        
        // ----------------------------------------------------
        // 1. Theme Toggle Logic
        // ----------------------------------------------------
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

        // ----------------------------------------------------
        // 2. Password Visibility Toggle
        // ----------------------------------------------------
        const pw  = document.getElementById('password');
        const eye = document.getElementById('eyeBtn');
        eye.addEventListener('click', function () {
            const show = pw.type === 'password';
            pw.type = show ? 'text' : 'password';
            eye.textContent = show ? '🙈' : '👁';
        });

        // ----------------------------------------------------
        // 3. AJAX Form Submission
        // ----------------------------------------------------
        const signupForm = document.getElementById('signupForm');
        const submitBtn  = document.getElementById('submitBtn');
        const spinner    = document.getElementById('spinner');
        const btnText    = document.getElementById('btnText');
        const alertBox   = document.getElementById('ajaxAlert');

        signupForm.addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent standard page reload

            // Reset Alert Box UI
            alertBox.style.display = 'none';
            alertBox.className = 'auth-alert'; // reset classes

            // Set Loading UI
            submitBtn.disabled = true;
            spinner.style.display = 'block';
            btnText.textContent = 'Creating account…';

            // Gather form data using FormData API
            const formData = new FormData(signupForm);

            // Execute the AJAX request using Fetch API
            fetch('includes/register_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Parse JSON response
            .then(data => {
                // Show Alert Box
                alertBox.style.display = 'block';
                alertBox.innerHTML = data.message;

                if (data.success) {
                    // Success: Add success styling and redirect user
                    alertBox.classList.add('auth-alert--success');
                    alertBox.innerHTML += ' <a href="login.php">Sign in</a>';
                    
                    // Reset form fields
                    signupForm.reset();
                    
                    // Redirect to login page after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    // Failure: Add error styling
                    alertBox.classList.add('auth-alert--error');
                }
            })
            .catch(error => {
                // Network or parsing error handling
                alertBox.style.display = 'block';
                alertBox.className = 'auth-alert auth-alert--error';
                alertBox.textContent = 'An unexpected network error occurred.';
                console.error('Error:', error);
            })
            .finally(() => {
                // Restore button UI after request finishes
                submitBtn.disabled = false;
                spinner.style.display = 'none';
                btnText.textContent = 'Create Account';
            });
        });
    });
    </script>

</body>
</html>
