<?php
/**
 * includes/login_process.php — DriveEase Support Desk
 * -------------------------------------------------------------------
 * Handles standard login AND Google OAuth callback logic.
 */

require_once('../config/db.php');
require_once('../vendor/autoload.php'); // Ensure Google API Client is loaded

session_start();

// --- 1. GOOGLE OAUTH HANDLING ---
if (isset($_GET['code'])) {
    $client = new Google_Client();
    $client->setClientId('YOUR_GOOGLE_CLIENT_ID');
    $client->setClientSecret('YOUR_GOOGLE_CLIENT_SECRET');
    $client->setRedirectUri('http://localhost/your_project/includes/login_process.php');

    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);

    $google_oauth = new Google_Service_Oauth2($client);
    $user_info = $google_oauth->userinfo->get();

    $email = $user_info->email;
    $name = $user_info->name;

    // Check if user exists, otherwise create (JIT Provisioning)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, provider) VALUES (:name, :email, 'google')");
        $stmt->execute(['name' => $name, 'email' => $email]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
    } else {
        $_SESSION['user_id'] = $user['id'];
    }

    $_SESSION['username'] = $name;
    header('Location: ../dashboard.php');
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