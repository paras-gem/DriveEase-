<?php
require_once('../config/db.php');


session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 4. Collect inputs and use null coalescing operator to prevent errors
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // 5. Prepare the SQL statement. 
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 6. Verify the user exists and the password matches the hash
        if ($user && password_verify($password, $user['password'])) {
            // Password correct: Store user info in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Redirect to dashboard
            header('Location: ../dashboard.php');
            exit(); // Always exit after a header redirect
        } else {
            
            echo "Invalid credentials. <a href='../login.php'>Try again</a>";
        }
    } catch (Exception $e) {
        // Handle database errors securely
        echo "Error: " . $e->getMessage();
    } 
}
?>