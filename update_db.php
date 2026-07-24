<?php
require 'config/db.php';

try {
    echo "<h3>Database Schema Fixer</h3>";
    
    // Drop the old users table
    $pdo->exec("DROP TABLE IF EXISTS `ticket_comments`");
    $pdo->exec("DROP TABLE IF EXISTS `tickets`");
    $pdo->exec("DROP TABLE IF EXISTS `bookings`");
    $pdo->exec("DROP TABLE IF EXISTS `users`");
    echo "Old tables dropped successfully.<br>";

    // Recreate the users table
    $createUsersQuery = "
    CREATE TABLE `users` (
        `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name`              VARCHAR(150)    NOT NULL,
        `email`             VARCHAR(255)    NOT NULL UNIQUE,
        `password`          VARCHAR(255)    DEFAULT NULL,
        `google_id`         VARCHAR(255)    DEFAULT NULL,
        `security_question` VARCHAR(255)    DEFAULT NULL,
        `security_answer`   VARCHAR(255)    DEFAULT NULL,
        `created_at`        TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($createUsersQuery);
    echo "<br><strong style='color:green;'>Success: `users` table recreated with all correct columns!</strong>";
    
    // We should also recreate other tables from database.sql to avoid foreign key issues
    $sqlFile = file_get_contents('database.sql');
    if ($sqlFile) {
        $pdo->exec($sqlFile);
        echo "<br><strong style='color:green;'>Success: All other tables synchronized.</strong>";
    }

    echo "<br><br><b>You can now try signing up / logging in again!</b>";
} catch (Exception $e) {
    echo "<strong style='color:red;'>Error: " . $e->getMessage() . "</strong>";
}

