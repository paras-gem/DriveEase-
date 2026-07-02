<?php
/**
 * includes/get_question.php — DriveEase Support Desk
 * -------------------------------------------------------------------
 * AJAX endpoint: receives an email via POST and returns the user's
 * security question as a JSON response.
 *
 * Called by: forgot_password.php (JavaScript fetch)
 *
 * Response shape:
 *   Success  → { "found": true,  "question": "...", "user_id": 42 }
 *   Not found → { "found": false }
 *   Bad method → 405 HTTP status
 * -------------------------------------------------------------------
 */

require_once('../config/db.php');

// Only accept POST requests; reject everything else
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Always respond with JSON
header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');

// Basic validation — reject empty/malformed email early
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['found' => false]);
    exit;
}

// Look up the user's security question by email
$stmt = $pdo->prepare('SELECT id, security_question FROM users WHERE email = :email');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // Return the question and user ID so the front-end can build the answer form
    echo json_encode([
        'found'    => true,
        'question' => $user['security_question'],
        'user_id'  => (int) $user['id'],
    ]);
} else {
    // No matching account — do NOT reveal whether the email is registered
    // (just "not found" is sufficient; avoid user enumeration)
    echo json_encode(['found' => false]);
}