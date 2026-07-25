<?php
require_once('../config/db.php');
header('Content-Type: application/json');

try {
    // The schema uses 'users' table for customers
    $stmt = $pdo->query("SELECT id, name, email, created_at FROM users ORDER BY id DESC");
    
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($customers);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch customers: ' . $e->getMessage()]);
}
