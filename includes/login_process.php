<?php
require_once('../config/db.php');
session_start();

const GOOGLE_CLIENT_ID = '556945368804-9i8u0n9sihkff4kriqb72cgji03vc8ro.apps.googleusercontent.com';

function verifyGoogleCredential(string $credential): ?array
{
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential);
    $json = false;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $json = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status !== 200) {
            return null;
        }
    } elseif (ini_get('allow_url_fopen')) {
        $json = file_get_contents($url);
    }

    if (!$json) {
        return null;
    }

    $payload = json_decode($json, true);
    if (!is_array($payload)) {
        return null;
    }

    $emailVerified = $payload['email_verified'] ?? false;
    if (($payload['aud'] ?? '') !== GOOGLE_CLIENT_ID || !in_array($emailVerified, [true, 'true', '1', 1], true)) {
        return null;
    }

    return $payload;
}

// --- GOOGLE SIGN-IN FLOW ---
if (isset($_POST['google_credential'])) {
    $payload = verifyGoogleCredential((string) $_POST['google_credential']);

    if ($payload) {
        $email = $payload['email'];
        $name = $payload['name'] ?? $email;

        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, provider) VALUES (:name, :email, 'google')");
            $stmt->execute(['name' => $name, 'email' => $email]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $name;
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'] ?: $name;
        }

        session_regenerate_id(true);
        header('Location: ../dashboard.php');
        exit;
    }

    header('Location: ../login.php?error=2');
    exit;
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
        header('Location: ../dashboard.php');
        exit;
    }

    header('Location: ../login.php?error=1');
    exit;
}

header('Location: ../login.php');
exit;