<?php
/**
 * includes/login_process.php — DriveEase Support Desk
 * -------------------------------------------------------------------
 * Handles POST from login.php.
 *   1. Validates submitted email & password
 *   2. Looks up the user record via PDO prepared statement
 *   3. Verifies password hash with password_verify()
 *   4. On success → starts session and redirects to dashboard.php
 *   5. On failure → redirects back to login.php?error=1 so the UI
 *      can display the styled error banner without echoing raw HTML
 * -------------------------------------------------------------------
 */

require_once('../config/db.php');

// Start session before any output or headers
session_start();

// Reject non-POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

// Collect and sanitise inputs; null-coalescing prevents undefined index warnings
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

// Basic presence check before hitting the database
if (empty($email) || empty($password)) {
    header('Location: ../login.php?error=1');
    exit;
}

try {
    // Fetch the user record matching this email
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify the submitted password against the stored bcrypt hash
    if ($user && password_verify($password, $user['password'])) {

        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);

        // Store only what is needed in the session — avoid putting sensitive data here
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Successful login — send to dashboard
        header('Location: ../dashboard.php');
        exit;

    } else {
        // Invalid credentials — redirect back with an error flag
        header('Location: ../login.php?error=1');
        exit;
    }

} catch (Exception $e) {
    // Log the real error server-side; never expose DB details to the browser
    error_log('Login error: ' . $e->getMessage());
    header('Location: ../login.php?error=1');
    exit;
}