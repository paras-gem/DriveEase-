<?php
require 'config/db.php';

try {
    // Attempt to add 'fullname' column
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN fullname VARCHAR(150) NOT NULL AFTER id");
        echo "Successfully added 'fullname' column.<br>";
    } catch (PDOException $e) {
        echo "Column 'fullname' might already exist or error: " . $e->getMessage() . "<br>";
    }

    // Attempt to add 'username' column
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN username VARCHAR(100) NOT NULL AFTER fullname");
        echo "Successfully added 'username' column.<br>";
    } catch (PDOException $e) {
        echo "Column 'username' might already exist or error: " . $e->getMessage() . "<br>";
    }

    // Attempt to add 'provider' column
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN provider VARCHAR(20) DEFAULT 'email' AFTER security_answer");
        echo "Successfully added 'provider' column.<br>";
    } catch (PDOException $e) {
        echo "Column 'provider' might already exist or error: " . $e->getMessage() . "<br>";
    }
    
    // Attempt to add 'security_question' and 'security_answer'
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN security_question VARCHAR(255) DEFAULT NULL AFTER password, ADD COLUMN security_answer VARCHAR(255) DEFAULT NULL AFTER security_question");
        echo "Successfully added security questions.<br>";
    } catch (PDOException $e) {
        echo "Security columns might already exist or error: " . $e->getMessage() . "<br>";
    }

    echo "<br><b>Database schema update process finished. You can now try logging in/signing up again!</b>";
} catch (Exception $e) {
    echo "General error: " . $e->getMessage();
}
