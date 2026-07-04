<?php
// 1. Start the session to "see" the data from login_process.php
session_start();

// 2. Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, send them back to login
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p>You have successfully connected the login process to the dashboard.</p>
    <a href="includes/logout.php">Logout</a>
</body>
</html>