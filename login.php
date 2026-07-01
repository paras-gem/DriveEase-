<?php

require_once('../config/db.php');

// when the session start
session_start();
header('Content-Type: application/json');

// defining request method

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // inputs 

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // fetch user from database

        $stmt = $pdo -> prepare("SELECT * FROM users WHERE email: email");
        $stmt -> execute(['email' => $email]);
        $user = $stmt -> fetch(PDO::FETCH_ASSOC);

        // verify password

        
    }
}
