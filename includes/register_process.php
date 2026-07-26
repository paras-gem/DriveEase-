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

// 4.5 Ensure employees table exists with the correct schema (including password for auth)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `employees` (
        `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name`        VARCHAR(150)  NOT NULL,
        `email`       VARCHAR(255)  NOT NULL UNIQUE,
        `password`    VARCHAR(255)  NOT NULL DEFAULT '',
        `role`        VARCHAR(100)  DEFAULT 'staff',
        `phone`       VARCHAR(20)   DEFAULT NULL,
        `salary`      DECIMAL(10,2) DEFAULT 0.00,
        `status`      ENUM('active','on_leave','terminated') DEFAULT 'active',
        `joined_date` DATE          DEFAULT (CURRENT_DATE),
        `created_at`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // If table already existed without password column, add it
    try {
        $pdo->exec("ALTER TABLE `employees` ADD COLUMN `password` VARCHAR(255) NOT NULL DEFAULT '' AFTER `email`");
    } catch (PDOException $ae) {
        // Column already exists — that's fine, ignore
    }
} catch (Exception $e) {
    // Ignore table creation errors, will fail on insert if truly broken
}

// 5. Collect and sanitize input data
$role              = $_POST['role'] ?? 'customer';
$fullname          = trim($_POST['fullname'] ?? '');
$email             = trim($_POST['email'] ?? '');
$password          = trim($_POST['password'] ?? '');
$confirm_password  = trim($_POST['confirm_password'] ?? '');
$security_question = trim($_POST['security_question'] ?? '');
$security_answer   = trim($_POST['security_answer'] ?? '');

// 6. Validate inputs (check for empty fields)
if (empty($fullname) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if ($role === 'customer' && (empty($security_question) || empty($security_answer))) {
    echo json_encode(['success' => false, 'message' => 'Please fill in security question and answer.']);
    exit;
}

// 7. Check if the password and confirm password match
if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit;
}

try {
    // 8. Handle registration based on role
    if ($role === 'employee') {
        // Check email uniqueness (prevent duplicate registrations)
        $stmt = $pdo->prepare('SELECT id FROM employees WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'That email is already registered as an employee.']);
            exit;
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // INSERT — matches actual employees table schema
        $insertStmt = $pdo->prepare('
            INSERT INTO `employees` (`name`, `email`, `password`, `role`)
            VALUES (:name, :email, :password, :role)
        ');
        $insertStmt->execute([
            'name'     => $fullname,
            'email'    => $email,
            'password' => $hashed_password,
            'role'     => 'staff'
        ]);
    } else {
        // Check email uniqueness (prevent duplicate registrations)
        $stmt = $pdo->prepare('SELECT id FROM customers WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'That email is already registered.']);
            exit;
        }

        // Hash the password and security answer securely using BCRYPT
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $hashed_answer   = password_hash($security_answer, PASSWORD_BCRYPT);

        // Prepare the INSERT statement for the new user record
        $insertStmt = $pdo->prepare('
            INSERT INTO `customers` (`name`, `email`, `password`, `security_question`, `security_answer`)
            VALUES (:name, :email, :password, :security_question, :security_answer)
        ');

        // Execute the query with the sanitized and hashed data
        $insertStmt->execute([
            'name'              => $fullname,
            'email'             => $email,
            'password'          => $hashed_password,
            'security_question' => $security_question,
            'security_answer'   => $hashed_answer
        ]);
    }

    // 12. Send a success response back to the AJAX handler
    echo json_encode(['success' => true, 'message' => 'Account created successfully!']);
    
} catch (PDOException $e) {
    // 13. Catch any database errors and log them
    error_log("Registration Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}