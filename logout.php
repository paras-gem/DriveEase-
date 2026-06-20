<?php
// initialize or capture the active session trace context
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// unset all active memory refrence session parameters

$_SESSION = array();


// clear out the session cookie tracking IDs inside client browser

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// terminate the current server-side session trace
session_destroy();

// finally redirec the user back to the main login page
header("location: index.php");

exit;   