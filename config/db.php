<?php

/** This file establishes a secure connection with the databases */

// database info

$host = 'localhost';
$db = 'driveease_db';
$user = 'root';
$pass = '';


// DSN: Data source name: it tells that where the host is located 

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

// connection logic 

try {
    $pdo = new PDO($dsn, $user, $pass );

    // set error mode 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // echo connected to the database successfully
} catch(PDOException $e){
    die("Database connection failed: " . $e->getMessage()); 
}
