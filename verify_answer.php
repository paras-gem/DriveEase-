<?php
/**
 * verify_answer.php - Step 2 of the password-reset flow.
 */

require_once('config/db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot_password.php');
    exit;
}

$userId = (int) ($_POST['user_id'] ?? 0);
$answer = trim($_POST['answer'] ?? '');

if ($userId <= 0 || $answer === '') {
    header('Location: forgot_password.php?error=1');
    exit;
}

$stmt = $pdo->prepare('SELECT id, security_answer FROM users WHERE id = :id');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: forgot_password.php?error=1');
    exit;
}

$savedAnswer = (string) ($user['security_answer'] ?? '');
$answerMatches = password_verify($answer, $savedAnswer);

if (!$answerMatches) {
    $answerMatches = hash_equals(
        strtolower(trim($savedAnswer)),
        strtolower($answer)
    );
}

if (!$answerMatches) {
    header('Location: forgot_password.php?error=2');
    exit;
}

session_regenerate_id(true);
$_SESSION['reset_user_id'] = (int) $user['id'];
$_SESSION['reset_verified_at'] = time();

header('Location: set_new_password.php');
exit;