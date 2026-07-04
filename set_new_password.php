<?php
/**
 * set_new_password.php - Step 3 of the password-reset flow.
 */

require_once('config/db.php');
session_start();

$resetUserId = (int) ($_SESSION['reset_user_id'] ?? 0);
$verifiedAt = (int) ($_SESSION['reset_verified_at'] ?? 0);
$isVerified = $resetUserId > 0 && $verifiedAt > 0 && (time() - $verifiedAt) <= 900;

if (!$isVerified) {
    unset($_SESSION['reset_user_id'], $_SESSION['reset_verified_at']);
    header('Location: forgot_password.php');
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
        $stmt->execute([
            'password' => $passwordHash,
            'id' => $resetUserId,
        ]);

        unset($_SESSION['reset_user_id'], $_SESSION['reset_verified_at']);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - DriveEase Support</title>
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
            <div class="step completed">
                <div class="step__dot">✓</div>
                <span class="step__label">Email</span>
            </div>
            <div class="step completed">
                <div class="step__dot">✓</div>
                <span class="step__label">Question</span>
            </div>
            <div class="step active">
                <div class="step__dot">3</div>
                <span class="step__label">Reset</span>
            </div>
        </div>

        <div class="auth-brand">
            <p class="auth-brand__name">DriveEase Support</p>
            <h1 class="auth-brand__title">Set a new password</h1>
            <p class="auth-brand__subtitle">Choose a password you have not used before.</p>
        </div>

        <?php if ($success): ?>
            <div class="auth-alert auth-alert--success" role="alert">Password updated. <a href="login.php">Sign in</a></div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="auth-alert auth-alert--error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form class="auth-form" method="POST" id="resetForm" novalidate>
                <div class="form-group">
                    <label for="password">New password</label>
                    <input class="auth-input" type="password" id="password" name="password" autocomplete="new-password" minlength="8" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm password</label>
                    <input class="auth-input" type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" minlength="8" required>
                </div>
                <button class="btn-primary" type="submit" id="resetBtn">
                    <span class="spinner" id="resetSpinner"></span>
                    <span id="resetBtnText">Update Password</span>
                </button>
            </form>
        <?php endif; ?>

        <div class="auth-footer"><a href="login.php">Back to sign in</a></div>
    </div>

    <script>
    (function () {
        const html = document.documentElement;
        const icons = { dark: ['☀️','Light'], light: ['🌙','Dark'] };

        function applyTheme(t) {
            html.setAttribute('data-theme', t);
            document.getElementById('toggleIcon').textContent = icons[t][0];
            document.getElementById('toggleLabel').textContent = icons[t][1];
        }

        applyTheme(html.getAttribute('data-theme'));

        document.getElementById('themeToggle').addEventListener('click', function () {
            const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', next);
            applyTheme(next);
        });

        const resetForm = document.getElementById('resetForm');
        if (resetForm) {
            resetForm.addEventListener('submit', function () {
                document.getElementById('resetBtn').disabled = true;
                document.getElementById('resetSpinner').style.display = 'block';
                document.getElementById('resetBtnText').textContent = 'Updating...';
            });
        }
    })();
    </script>
</body>
</html>