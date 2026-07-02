<?php
/**
 * includes/register_process.php — DriveEase Support Desk
 * -------------------------------------------------------------------
 * Handles POST from signup.php.
 *
 * TODO — implement the following steps:
 *   1. Validate inputs (required fields, email format, password length)
 *   2. Check passwords match — redirect to ../signup.php?error=2 if not
 *   3. Check email uniqueness — redirect to ../signup.php?error=1 if taken
 *   4. Hash password  : password_hash($password, PASSWORD_BCRYPT)
 *   5. Hash security answer similarly
 *   6. INSERT new user record into `users` table
 *   7. On success    → redirect to ../signup.php?success=1
 *      On DB error   → log error, redirect to ../signup.php?error=3
 * -------------------------------------------------------------------
 */

require_once('../config/db.php');
session_start();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../signup.php');
    exit;
}

// --- TODO: collect inputs ---
// $fullname         = trim($_POST['fullname']         ?? '');
// $email            = trim($_POST['email']            ?? '');
// $password         = trim($_POST['password']         ?? '');
// $confirm_password = trim($_POST['confirm_password'] ?? '');
// $security_question = trim($_POST['security_question'] ?? '');
// $security_answer   = trim($_POST['security_answer']   ?? '');

// --- TODO: validate, hash, and insert ---

// Placeholder redirect until implementation is complete
header('Location: ../signup.php');
exit;
