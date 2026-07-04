<?php
require_once('../config/db.php');
require_once('../vendor/autoload.php');
session_start();

// --- GOOGLE OAUTH FLOW ---
if (isset($_POST['google_credential'])) {
    $client = new Google_Client(['client_id' => 'YOUR_CLIENT_ID_HERE.apps.googleusercontent.com']);
    $payload = $client->verifyIdToken($_POST['google_credential']);
    
    if ($payload) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $payload['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, provider) VALUES (:name, :email, 'google')");
            $stmt->execute(['name' => $payload['name'], 'email' => $payload['email']]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
        } else {
            $_SESSION['user_id'] = $user['id'];
        }
        $_SESSION['username'] = $payload['name'];
        header('Location: ../dashboard.php'); exit;
    }
    header('Location: ../login.php?error=2'); exit;
}

// --- STANDARD LOGIN FLOW ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: ../dashboard.php'); exit;
    }
    header('Location: ../login.php?error=1');
}