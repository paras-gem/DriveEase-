<?php
/**
 * includes/login_process.php — DriveEase Support Desk
 * -------------------------------------------------------------------
 * Handles POST requests from login.php via AJAX.
 * Returns a JSON response indicating success or failure.
 * -------------------------------------------------------------------
 */
// 1. Buffer output to prevent accidental output from breaking JSON response
ob_start();

// 2. Set the response header to JSON
header('Content-Type: application/json');

// 3. Include the database configuration (PDO connection)
require_once('../config/db.php');

// 4. Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the request is an HTTP POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Google Client ID for OAuth
const GOOGLE_CLIENT_ID = '556945368804-9i8u0n9sihkff4kriqb72cgji03vc8ro.apps.googleusercontent.com';

/**
 * Verify Google Credential using Google's tokeninfo endpoint.
 */
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

try {
    // --------------------------------------------------------
    // A. GOOGLE SIGN-IN FLOW
    // --------------------------------------------------------
    if (isset($_POST['google_credential'])) {
        $payload = verifyGoogleCredential((string) $_POST['google_credential']);

        if ($payload) {
            $email = $payload['email'];
            $name = $payload['name'] ?? $email;

            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // If user doesn't exist, create them
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
            ob_end_clean();
            // In Google flow we can either redirect directly (if traditional form) or return JSON
            // We'll return JSON because we will intercept it with AJAX.
            echo json_encode(['success' => true, 'message' => 'Google Login successful!']);
            exit;
        }

        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Google sign-in verification failed.']);
        exit;
    }

    // --------------------------------------------------------
    // B. STANDARD EMAIL/PASSWORD LOGIN FLOW
    // --------------------------------------------------------
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Check if user exists
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the password securely
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'] ?? $user['fullname']; // use fullname if username is null
            
            ob_end_clean();
            echo json_encode(['success' => true, 'message' => 'Login successful! Redirecting...']);
            exit;
        }

        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        exit;
    }
    
    // Fallback if POST array is empty
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
    
} catch (PDOException $e) {
    error_log("Login DB Error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Login Gen Error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'An unexpected error: ' . $e->getMessage()]);
}