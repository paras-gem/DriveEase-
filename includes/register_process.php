<?php
/**
 * includes/register_process.php — DriveEase Support Desk
 * -------------------------------------------------------------------
 * Handles POST requests from signup.php via AJAX.
 * Returns a JSON response indicating success or failure.
 * -------------------------------------------------------------------
 */

// 1. Set the response header to JSON so the frontend knows how to parse it
header('Content-Type: application/json');

// 2. Include the database configuration (PDO connection)
require_once('../config/db.php');

// 3. Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 4. Ensure the request is an HTTP POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// 5. Collect and sanitize input data
$fullname          = trim($_POST['fullname'] ?? '');
$email             = trim($_POST['email'] ?? '');
$password          = trim($_POST['password'] ?? '');
$confirm_password  = trim($_POST['confirm_password'] ?? '');
$security_question = trim($_POST['security_question'] ?? '');
$security_answer   = trim($_POST['security_answer'] ?? '');

// 6. Validate inputs (check for empty fields)
if (empty($fullname) || empty($email) || empty($password) || empty($security_question) || empty($security_answer)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

// 7. Check if the password and confirm password match
if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit;
}

try {
    // 8. Check email uniqueness (prevent duplicate registrations)
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        // If an ID is returned, the email is already in the database
        echo json_encode(['success' => false, 'message' => 'That email is already registered.']);
        exit;
    }

    // 9. Hash the password and security answer securely using BCRYPT
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $hashed_answer   = password_hash($security_answer, PASSWORD_BCRYPT);

    // 10. Prepare the INSERT statement for the new user record
    $insertStmt = $pdo->prepare('
        INSERT INTO users (name, email, password, security_question, security_answer)
        VALUES (:name, :email, :password, :security_question, :security_answer)
    ');

    // 11. Execute the query with the sanitized and hashed data
    $insertStmt->execute([
        'name'              => $fullname,
        'email'             => $email,
        'password'          => $hashed_password,
        'security_question' => $security_question,
        'security_answer'   => $hashed_answer
    ]);

    // 12. Send a success response back to the AJAX handler
    echo json_encode(['success' => true, 'message' => 'Account created successfully!']);
    
} catch (PDOException $e) {
    // 13. Catch any database errors and log them
    error_log("Registration Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}