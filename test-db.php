<?php
/**
 * DRIVE-EASE - TEMPORARY DATABASE CONNECTION TESTER
 */
header('Content-Type: text/plain; charset=utf-8');

// Step 1: Attempt to pull in the core database configurations
if (!file_exists(__DIR__ . '/config/db.php')) {
    die("❌ Error: config/db.php file cannot be found! Check your pathing.");
}

// Intercept the direct-access block inside db.php by using the same filename scope
require_once __DIR__ . '/config/db.php';

// Step 2: Check if the connection variable is fully initialized and operational
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("❌ Error: The \$pdo variable was not properly initialized inside config/db.php.");
}

try {
    // Step 3: Run a lightweight server check query to test connectivity
    $stmt = $pdo->query("SELECT VERSION() AS db_version");
    $row = $stmt->fetch();
    
    echo "✅ SUCCESS: DriveEase is successfully connected to your InfinityFree live database!\n";
    echo "📊 MySQL Server Version: " . $row['db_version'] . "\n";
    
    // Step 4: Run a quick integrity check to confirm our 7 tables are present
    $tables = ['users', 'customers', 'vehicles', 'bookings', 'payments', 'maintenance', 'support_tickets'];
    echo "\n📋 Running Core Database Table Integrity Scan:\n";
    
    foreach ($tables as $table) {
        try {
            $pdo->query("SELECT 1 FROM $table LIMIT 1");
            echo "  - Table '{$table}': FOUND & ACTIVE Line State ✅\n";
        } catch (PDOException $e) {
            echo "  - Table '{$table}': MISSING OR INACCESSIBLE ❌ (Did you run your SQL schemas?)\n";
        }
    }

} catch (PDOException $e) {
    echo "❌ CONNECTION CRITICAL ERROR:\n";
    echo $e->getMessage();
}