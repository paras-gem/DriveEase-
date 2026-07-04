<?php
/**
 * includes/login_process.php — DriveEase Support Desk
 * -------------------------------------------------------------------
 * Handles standard login and Google sign-in callback logic.
 */

require_once('../config/db.php');

session_start();

function decodeGoogleJwtPayload(string $credential): ?array {
    $parts = explode('.', $credential);
    if (count($parts) < 2) {
        return null;
    }

    $payload = str_replace(['-', '_'], ['+', '/'], $parts[1]);
    $padding = strlen($payload) % 4;
    if ($padding) {
        $payload .= str_repeat('=', 4 - $padding);
    }

    $decoded = base64_decode($payload, true);
    if ($decoded === false) {
        return null;
    }

    $data = json_decode($decoded, true);
    return is_array($data) ? $data : null;
}

function createOrLoginUser(PDO $pdo, string $email, string $name): void {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, provider) VALUES (:name, :email, 'google')");
        $stmt->execute(['name' => $name, 'email' => $email]);
        $_SESSION['user_id'] = (int) $pdo->lastInsertId();
    } else {
        $_SESSION['user_id'] = (int) $user['id'];
    }

    $_SESSION['username'] = $name;
}

// --- 1. GOOGLE SIGN-IN HANDLING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['google_credential'])) {
    $credential = trim($_POST['google_credential']);
    $payload = decodeGoogleJwtPayload($credential);

    if (!$payload || empty($payload['email'])) {
        header('Location: ../login.php?error=2');
        exit;
    }

    try {
        session_regenerate_id(true);
        createOrLoginUser($pdo, $payload['email'], $payload['name'] ?? $payload['given_name'] ?? explode('@', $payload['email'])[0]);
        header('Location: ../dashboard.php');
    } catch (Exception $e) {
        error_log('Google login error: ' . $e->getMessage());
        header('Location: ../login.php?error=2');
    }
    exit;
}

// --- 2. STANDARD LOGIN HANDLING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        header('Location: ../login.php?error=1');
        exit;
    }

    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: ../dashboard.php');
            exit;
        } else {
            header('Location: ../login.php?error=1');
        }
    } catch (Exception $e) {
        error_log('Login error: ' . $e->getMessage());
        header('Location: ../login.php?error=1');
    }
} else {
    header('Location: ../login.php');
}