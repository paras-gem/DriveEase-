<?php
/**
 * ====================================================================
 * DRIVE-EASE LOGISTICS - CORE DATABASE CONNECTION INFRASTRUCTURE
 * ====================================================================
 */

// Secure, server-agnostic direct URL entry restriction
if (count(get_included_files()) === 1) {
    header('HTTP/1.0 403 Forbidden');
    die('Direct access not permitted.');
}

// Host connection strings matching your InfinityFree profile dashboard
$host     = 'YOUR_INFINITYFREE_MYSQL_HOST'; // e.g., sql303.infinityfree.com
$db       = 'YOUR_INFINITYFREE_DB_NAME';    // e.g., if0_3648291_driveease
$user     = 'YOUR_INFINITYFREE_DB_USER';    // e.g., if0_3648291
$pass     = 'YOUR_INFINITYFREE_FTP_PASS';   // Your main hosting panel password
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    error_log($e->getMessage());
    die("Database connection failed. Please try again later.");
}