<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query('SELECT id, name, email, created_at FROM customers ORDER BY id DESC');
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Throwable $e) {
    error_log('Customers API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}