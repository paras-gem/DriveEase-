<?php
/**
 * signup.php — DriveEase Support Desk
 * -------------------------------------------------------------------
 * Registration page. Backend: includes/register_process.php
 * TODO: wire up fields to register_process.php and add validation.
 * -------------------------------------------------------------------
 */

// Show error/success banners if register_process.php redirects with flags
$signupError   = isset($_GET['error'])   ? (int) $_GET['error']   : 0;
$signupSuccess = isset($_GET['success']) && $_GET['success'] === '1';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — DriveEase Support</title>
    <meta name="description" content="Create a DriveEase Support account to submit and track tickets.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');</script>
</head>
<body>

    <!-- Theme toggle -->
    <button class="theme-toggle" id="themeToggle" type="button" aria-label="Toggle dark mode">
        <span class="toggle-icon" id="toggleIcon">🌙</span>
        <span id="toggleLabel">Dark</span>
    </button>

    <div class="auth-card" role="main">

        <div class="auth-brand">
            <p class="auth-brand__name">DriveEase Support</p>
            <h1 class="auth-brand__title">Create an account</h1>
            <p class="auth-brand__subtitle">Fill in the details below to get started.</p>
        </div>

        <!-- Error / success alerts -->
        <?php if ($signupError === 1): ?>
            <div class="auth-alert auth-alert--error" role="alert">That email is already registered.</div>
        <?php elseif ($signupError === 2): ?>
            <div class="auth-alert auth-alert--error" role="alert">Passwords do not match.</div>
        <?php elseif ($signupSuccess): ?>
            <div class="auth-alert auth-alert--success" role="alert">Account created! <a href="login.php">Sign in</a></div>
        <?php endif; ?>

        <!-- Signup form — action wired to register_process.php -->
        <form class="auth-form" action="includes/register_process.php" method="POST" id="signupForm" novalidate>

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

            <!-- Security question (used for password recovery) -->
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

            <button class="btn-primary" type="submit" id="submitBtn">
                <span class="spinner" id="spinner"></span>
                <span id="btnText">Create Account</span>
            </button>

        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Sign in</a>
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
        document.getElementById('signupForm').addEventListener('submit', function () {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('spinner').style.display = 'block';
            document.getElementById('btnText').textContent = 'Creating account…';
        });
    })();
    </script>

</body>
</html>
