<?php
require 'config/db.php';
header('Content-Type: text/plain');

echo "=== 1. EMPLOYEES TABLE COLUMNS ===\n";
try {
    $cols = $pdo->query("SHOW COLUMNS FROM `employees`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        echo "  {$col['Field']} | {$col['Type']} | Null:{$col['Null']} | Default:{$col['Default']}\n";
    }
} catch (PDOException $e) {
    echo "TABLE ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== 2. DIRECT INSERT TEST ===\n";
try {
    $stmt = $pdo->prepare("INSERT INTO `employees` (`name`,`email`,`password`,`role`) VALUES (?,?,?,?)");
    $ok = $stmt->execute(['Debug User', 'dbg_'.time().'@x.com', password_hash('Test1234', PASSWORD_BCRYPT), 'staff']);
    echo $ok ? "SUCCESS - ID: ".$pdo->lastInsertId()."\n" : "FAILED (no exception)\n";
} catch (PDOException $e) {
    echo "ERROR: ".$e->getMessage()."\n";
}

echo "\n=== 3. ROWS IN EMPLOYEES ===\n";
try {
    $rows = $pdo->query("SELECT id, name, email, role FROM `employees`")->fetchAll(PDO::FETCH_ASSOC);
    echo count($rows) ? implode("\n", array_map(fn($r)=>"  #{$r['id']} {$r['name']} | {$r['email']} | {$r['role']}", $rows)) : "  (empty)";
} catch (PDOException $e) { echo "ERROR: ".$e->getMessage(); }

echo "\n\n=== 4. OTHER TABLES (check FKs) ===\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ', $tables) . "\n";
