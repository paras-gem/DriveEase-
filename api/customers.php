<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/schema_helpers.php';
header('Content-Type: application/json');

try {
    $table = requireTable($pdo, ['users', 'customers'], 'customers');
    $columns = columnsFor($pdo, $table);
    $fields = ['id', 'name', 'email', 'created_at'];
    $available = array_values(array_intersect($fields, $columns));
    $stmt = $pdo->query('SELECT ' . implode(', ', $available) . " FROM `{$table}` ORDER BY id DESC");
    echo json_encode($stmt->fetchAll());
} catch (Throwable $e) {
    error_log('Customers API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Customer data is not available yet.']);
}