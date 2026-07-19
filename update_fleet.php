<?php
require 'config/db.php';

try {
    echo "<h3>Fleet Schema Fixer</h3>";
    
    // Add vehicle_name
    try {
        $pdo->exec("ALTER TABLE fleet ADD COLUMN vehicle_name VARCHAR(150) NOT NULL AFTER id");
        echo "Successfully added 'vehicle_name' column.<br>";
    } catch (PDOException $e) {
        echo "Column 'vehicle_name' might already exist or error: " . $e->getMessage() . "<br>";
    }

    // Add rent_cost
    try {
        $pdo->exec("ALTER TABLE fleet ADD COLUMN rent_cost DECIMAL(10,2) DEFAULT 0.00 AFTER plate");
        echo "Successfully added 'rent_cost' column.<br>";
    } catch (PDOException $e) {
        echo "Column 'rent_cost' might already exist or error: " . $e->getMessage() . "<br>";
    }

    // Drop unwanted columns
    try {
        $pdo->exec("ALTER TABLE fleet DROP COLUMN make");
        $pdo->exec("ALTER TABLE fleet DROP COLUMN model");
        $pdo->exec("ALTER TABLE fleet DROP COLUMN year");
        echo "Successfully dropped make, model, and year columns.<br>";
    } catch (PDOException $e) {
        echo "Columns might already be dropped or error: " . $e->getMessage() . "<br>";
    }

    echo "<br><b>Fleet schema update process finished.</b>";
} catch (Exception $e) {
    echo "General error: " . $e->getMessage();
}
