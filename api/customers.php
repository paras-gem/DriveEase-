<?php
require_once('../config/db.php');
header('Content-Type: application/json');

try {
    // Only get role if it exists, otherwise just get users since the schema may lack role
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    $hasRole = $stmt->fetch();
    
    if ($hasRole) {
        $stmt = $pdo->query("SELECT id, name, email, role, created_at FROM users WHERE role = 'customer' ORDER BY id DESC");
    } else {
        $stmt = $pdo->query("SELECT id, name, email, created_at FROM users ORDER BY id DESC");
    }
    
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($customers);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch customers: ' . $e->getMessage()]);
}
