<?php
/**
 * forgot_password.php — DriveEase Support Desk
 * -------------------------------------------------------------------
 * Step 1 of the password-reset flow.
 *   - User enters their email address
 *   - Form submits to includes/get_question.php which fetches their
 *     security question and injects the answer form into #questionArea
 *   - If email is not found, an error is shown in #emailError
 *
 * Flow:
 *   forgot_password.php  →  includes/get_question.php  →  verify_answer.php
 *                                                               ↓
 *                                                       set_new_password.php
 * -------------------------------------------------------------------
 */
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO -->
    <title>Reset Password — DriveEase Support</title>
    <meta name="description" content="Reset your DriveEase Support account password using your security question.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Auth stylesheet -->
    <link rel="stylesheet" href="assets/css/auth.css">

    <!-- Apply saved theme before paint to avoid a flash of wrong theme -->
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

        <!-- Step progress indicator (3 steps: Email → Question → Reset) -->
        <div class="step-indicator" aria-label="Password reset steps">
            <div class="step completed" id="step1" aria-current="false">
                <div class="step__dot">✓</div>
                <span class="step__label">Email</span>
            </div>
            <div class="step active" id="step2" aria-current="step">
                <div class="step__dot">2</div>
                <span class="step__label">Question</span>
            </div>
            <div class="step" id="step3">
                <div class="step__dot">3</div>
                <span class="step__label">Reset</span>
            </div>
        </div>

        <!-- Brand header -->
        <div class="auth-brand">
            <p class="auth-brand__name">DriveEase Support</p>
            <h1 class="auth-brand__title" id="pageTitle">Reset your password</h1>
            <p class="auth-brand__subtitle" id="pageSubtitle">
                Enter your email and we'll fetch your security question.
            </p>
        </div>

        <!-- ================================================================
             STEP 1: Email lookup form
             Submits via AJAX to includes/get_question.php
        ================================================================= -->
        <form
            class="auth-form"
            id="emailForm"
            novalidate
        >
            <!-- Error alert — shown via JS if email not found -->
            <div class="auth-alert auth-alert--error" id="emailError" role="alert" style="display:none;"></div>

            <!-- Email input -->
            <div class="form-group">
                <label for="email">Registered email address</label>
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

            <!-- Submit — find account -->
            <button class="btn-primary" type="submit" id="emailSubmitBtn">
                <span class="spinner" id="emailSpinner"></span>
                <span id="emailBtnText">Find My Account</span>
            </button>
        </form>

        <!-- ================================================================
             STEP 2: Security question answer form
             Injected here by AJAX response from includes/get_question.php.
             Hidden until the email step succeeds.
        ================================================================= -->
        <div id="questionArea" style="display:none;" aria-live="polite">

            <!-- The security question text is populated by JS -->
            <p class="auth-brand__subtitle" style="margin-bottom:16px;">
                Answer your security question to verify your identity.
            </p>

            <!-- Displays the user's security question (populated by JS) -->
            <div class="security-question-box" id="questionText"></div>

            <form
                class="auth-form"
                id="answerForm"
                action="verify_answer.php"
                method="POST"
                style="margin-top:16px;"
                novalidate
            >
                <!-- Hidden user_id forwarded from the email lookup response -->
                <input type="hidden" name="user_id" id="hiddenUserId">

                <!-- Error alert for wrong answer -->
                <div class="auth-alert auth-alert--error" id="answerError" role="alert" style="display:none;"></div>

                <!-- Answer input -->
                <div class="form-group">
                    <label for="answer">Your answer</label>
                    <input
                        class="auth-input"
                        type="text"
                        id="answer"
                        name="answer"
                        placeholder="Type your answer…"
                        autocomplete="off"
                        required
                    >
                </div>

                <!-- Submit answer -->
                <button class="btn-primary" type="submit" id="answerSubmitBtn">
                    <span class="spinner" id="answerSpinner"></span>
                    <span id="answerBtnText">Verify & Continue</span>
                </button>
            </form>

        </div><!-- /#questionArea -->

        <!-- Footer: back to login -->
        <div class="auth-footer">
            Remembered your password?
            <a href="login.php">Sign in</a>
        </div>

    </div><!-- /.auth-card -->

    <!-- =====================================================================
         JAVASCRIPT
         1. Theme toggle
         2. Email form — AJAX lookup, reveals question area on success
         3. Answer form — loading state on submit
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

        function syncToggleUI(theme) {
            if (theme === 'dark') {
                toggleIcon.textContent  = '☀️';
                toggleLabel.textContent = 'Light';
            } else {
                toggleIcon.textContent  = '🌙';
                toggleLabel.textContent = 'Dark';
            }
        }

        syncToggleUI(html.getAttribute('data-theme'));

        toggleBtn.addEventListener('click', function () {
            const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            syncToggleUI(next);
        });

        /* ------------------------------------------------------------------
           2. EMAIL FORM — AJAX fetch security question
        ------------------------------------------------------------------ */
        const emailForm      = document.getElementById('emailForm');
        const emailInput     = document.getElementById('email');
        const emailError     = document.getElementById('emailError');
        const emailSubmitBtn = document.getElementById('emailSubmitBtn');
        const emailSpinner   = document.getElementById('emailSpinner');
        const emailBtnText   = document.getElementById('emailBtnText');

        // Step indicator elements
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        const step3 = document.getElementById('step3');

        // Sections
        const questionArea   = document.getElementById('questionArea');
        const questionText   = document.getElementById('questionText');
        const hiddenUserId   = document.getElementById('hiddenUserId');
        const pageSubtitle   = document.getElementById('pageSubtitle');

        function setEmailLoading(loading) {
            emailSubmitBtn.disabled     = loading;
            emailSpinner.style.display  = loading ? 'block' : 'none';
            emailBtnText.textContent    = loading ? 'Searching…' : 'Find My Account';
        }

        function showEmailError(msg) {
            emailError.textContent    = msg;
            emailError.style.display  = 'block';
        }

        function hideEmailError() {
            emailError.style.display = 'none';
        }

        emailForm.addEventListener('submit', function (e) {
            e.preventDefault();
            hideEmailError();

            const email = emailInput.value.trim();
            if (!email) {
                showEmailError('Please enter your email address.');
                return;
            }

            setEmailLoading(true);

            // POST to get_question.php; it returns JSON with {found, question, user_id}
            const formData = new FormData();
            formData.append('email', email);

            fetch('includes/get_question.php', {
                method: 'POST',
                body: formData
            })
            .then(function (res) {
                if (!res.ok) throw new Error('Server error');
                return res.json();
            })
            .then(function (data) {
                setEmailLoading(false);

                if (data.found) {
                    // Populate question area and reveal it
                    questionText.textContent    = data.question;
                    hiddenUserId.value          = data.user_id;

                    emailForm.style.display     = 'none';
                    questionArea.style.display  = 'block';

                    // Update step indicator: step1 stays completed, step2 becomes active
                    pageSubtitle.textContent = 'Your security question is shown below.';

                } else {
                    showEmailError('No account found with that email address.');
                }
            })
            .catch(function () {
                setEmailLoading(false);
                showEmailError('Something went wrong. Please try again.');
            });
        });

        /* ------------------------------------------------------------------
           3. ANSWER FORM — loading state on submit
        ------------------------------------------------------------------ */
        const answerForm      = document.getElementById('answerForm');
        const answerSubmitBtn = document.getElementById('answerSubmitBtn');
        const answerSpinner   = document.getElementById('answerSpinner');
        const answerBtnText   = document.getElementById('answerBtnText');

        answerForm.addEventListener('submit', function () {
            answerSubmitBtn.disabled    = true;
            answerSpinner.style.display = 'block';
            answerBtnText.textContent   = 'Verifying…';
        });

    })();
    </script>

</body>
</html>