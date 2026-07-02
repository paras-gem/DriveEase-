<?php
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? ''; 

    $stmt = $pdo->prepare('SELECT id, security_question FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // FIX: Added missing '=' and quotes in the HTML/Form attributes
        echo "<h3>Security Question</h3>";
        echo "<p>" . htmlspecialchars($user['security_question']) . "</p>";
        echo "<form action='../verify_answer.php' method='POST'>";
        echo "<input type='hidden' name='user_id' value='" . $user['id'] . "'>";
        echo "<input type='text' name='answer' placeholder='Your Answer' required>"; // Fixed 'requred' typo
        echo "<button type='submit'>Verify and Reset Password</button>";
        echo "</form>";
    } else {
        echo "User not found. <a href='../forgot_password.php'>Try again</a>";
    }
}
?>