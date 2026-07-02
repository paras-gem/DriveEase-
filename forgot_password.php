<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Reset Password</h2>
        <form action="includes/get_question.php" method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Get Security Question</button>
        </form>
    </div>
</body>
</html>