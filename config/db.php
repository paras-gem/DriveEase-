<?php

/** This file establishes a secure connection with the databases */

// database info

$host = 'sql302.infinityfree.com';
$db = 'if0_42220998_XXX';
$user = 'if0_42220998';
$pass = 'GDrive2026';


// DSN: Data source name: it tells that where the host is located 

$dsn = "mysql:host=$host;dbname=$db;charset";

// connection logic 

try {
    $pdo = new PDO($dsn, $user, $pass );

    // set error mode 
    $pdo ->setAttribute( PDO::ERRMODE_EXCEPTION, PDO::ERRMODE_EXCEPTION);

    // Set default fetch mode to associative array
    $pdo -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);

    // echo connected to the database successfully
} catch(PDOException $e){
    die("Database connection failed: " . $e->getMessage()); 
}

?>
    

