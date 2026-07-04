<?php
$resetError = isset($_GET['error']) ? (int) $_GET['error'] : 0;
/**
 * forgot_password.php — Step 1 of the password-reset flow.
 *
 * Flow: forgot_password.php → includes/get_question.php (AJAX, JSON)
 *         → verify_answer.php → set_new_password.php
 *
 * The email form (Step 1) posts via fetch() to get_question.php.
 * On success, the question area (Step 2) is revealed without a page reload.
 */
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — DriveEase Support</title>
    <meta name="description" content="Reset your DriveEase Support account password.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');</script>
</head>
<body>

    <button class="theme-toggle" id="themeToggle" type="button" aria-label="Toggle dark mode">
        <span class="toggle-icon" id="toggleIcon">🌙</span>
        <span id="toggleLabel">Dark</span>
    </button>

    <div class="auth-card" role="main">
        <div class="step-indicator" aria-label="Password reset progress">
            <div class="step completed" id="step1">
                <div class="step__dot">✓</div>
                <span class="step__label">Email</span>
            </div>
            <div class="step active" id="step2">
                <div class="step__dot">2</div>
                <span class="step__label">Question</span>
            </div>
            <div class="step" id="step3">
                <div class="step__dot">3</div>
                <span class="step__label">Reset</span>
            </div>
        </div>

        <div class="auth-brand">
            <p class="auth-brand__name">DriveEase Support</p>
            <h1 class="auth-brand__title">Reset your password</h1>
            <p class="auth-brand__subtitle" id="subtitle">Enter your email to find your account.</p>
        </div>

        <?php if ($resetError === 1): ?>
            <div class="auth-alert auth-alert--error" role="alert">We could not verify that reset request. Please try again.</div>
        <?php elseif ($resetError === 2): ?>
            <div class="auth-alert auth-alert--error" role="alert">That security answer did not match. Please try again.</div>
        <?php endif; ?>

        <form class="auth-form" id="emailForm" novalidate>
            <div class="auth-alert auth-alert--error" id="emailError" role="alert" style="display:none;"></div>
            <div class="form-group">
                <label for="email">Registered email</label>
                <input class="auth-input" type="email" id="email" name="email"
                       placeholder="you@example.com" autocomplete="email" required>
            </div>
            <button class="btn-primary" type="submit" id="emailBtn">
                <span class="spinner" id="emailSpinner"></span>
                <span id="emailBtnText">Find My Account</span>
            </button>
        </form>

        <div id="questionArea" style="display:none;" aria-live="polite">
            <p class="auth-brand__subtitle" style="margin-bottom:14px;">Answer your security question below.</p>
            <div class="security-question-box" id="questionText"></div>

            <form class="auth-form" id="answerForm" action="verify_answer.php" method="POST" style="margin-top:14px;" novalidate>
                <input type="hidden" name="user_id" id="hiddenUserId">
                <div class="auth-alert auth-alert--error" id="answerError" role="alert" style="display:none;"></div>
                <div class="form-group">
                    <label for="answer">Your answer</label>
                    <input class="auth-input" type="text" id="answer" name="answer"
                           placeholder="Type your answer..." autocomplete="off" required>
                </div>
                <button class="btn-primary" type="submit" id="answerBtn">
                    <span class="spinner" id="answerSpinner"></span>
                    <span id="answerBtnText">Verify & Continue</span>
                </button>
            </form>
        </div>

        <div class="auth-footer">Remembered it? <a href="login.php">Sign in</a></div>
    </div>

    <script>
    (function () {
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

        const emailForm  = document.getElementById('emailForm');
        const emailError = document.getElementById('emailError');
        const emailBtn   = document.getElementById('emailBtn');
        const emailSpinner = document.getElementById('emailSpinner');

        function setEmailLoading(on) {
            emailBtn.disabled = on;
            emailSpinner.style.display = on ? 'block' : 'none';
            document.getElementById('emailBtnText').textContent = on ? 'Searching...' : 'Find My Account';
        }

        emailForm.addEventListener('submit', function (e) {
            e.preventDefault();
            emailError.style.display = 'none';
            const email = document.getElementById('email').value.trim();
            if (!email) { emailError.textContent = 'Please enter your email.'; emailError.style.display = 'block'; return; }

            setEmailLoading(true);
            const fd = new FormData(); fd.append('email', email);

            fetch('includes/get_question.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(function (d) {
                    setEmailLoading(false);
                    if (d.found) {
                        document.getElementById('questionText').textContent = d.question;
                        document.getElementById('hiddenUserId').value = d.user_id;
                        emailForm.style.display = 'none';
                        document.getElementById('questionArea').style.display = 'block';
                        document.getElementById('subtitle').textContent = 'Your security question is shown below.';
                    } else {
                        emailError.textContent = 'No account found with that email.';
                        emailError.style.display = 'block';
                    }
                })
                .catch(function () {
                    setEmailLoading(false);
                    emailError.textContent = 'Something went wrong. Please try again.';
                    emailError.style.display = 'block';
                });
        });

        document.getElementById('answerForm').addEventListener('submit', function () {
            document.getElementById('answerBtn').disabled = true;
            document.getElementById('answerSpinner').style.display = 'block';
            document.getElementById('answerBtnText').textContent = 'Verifying...';
        });
    })();
    </script>

</body>
</html>