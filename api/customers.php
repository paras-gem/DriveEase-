<?php
require_once('../config/db.php');
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, name, email, phone, license_number, created_at FROM customers ORDER BY id DESC");
    
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($customers);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch customers: ' . $e->getMessage()]);
}
