<?php
require_once('config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $user_answer = $_POST['answer'];

    // Securely fetch the stored answer hash
    $stmt = $pdo->prepare("SELECT security_answer FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify the answer (Assuming you hash answers like passwords)
    if ($user && password_verify($user_answer, $user['security_answer'])) {
        // Success: Redirect to set_new_password.php
        session_start();
        $_SESSION['reset_user_id'] = $user_id;
        header("Location: set_new_password.php");
    } else {
        echo "Incorrect answer. <a href='forgot_password.php'>Try again</a>";
    }
}
?>