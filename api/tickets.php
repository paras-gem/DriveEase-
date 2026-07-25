<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $pdo->query("
            SELECT t.*, u.name AS user_name 
            FROM support_tickets t 
            LEFT JOIN customers u ON t.customer_id = u.id 
            ORDER BY t.created_at DESC
        ");
        echo json_encode($stmt->fetchAll());
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $customer_id = $data['customer_id'] ?? $data['user_id'] ?? null;
        
        if (empty($customer_id) || empty($data['subject']) || empty($data['description'])) { 
            http_response_code(400); 
            echo json_encode(['error' => 'Missing required ticket details.']); 
            exit; 
        }
        $pdo->prepare("INSERT INTO support_tickets (customer_id, subject, description, priority) VALUES (?, ?, ?, ?)")
            ->execute([$customer_id, trim($data['subject']), trim($data['description']), $data['priority'] ?? 'medium']);
        echo json_encode(['success' => true, 'message' => 'Ticket created successfully!']);
    } else { 
        http_response_code(405); 
        echo json_encode(['error' => 'Method not allowed']); 
    }
} catch (Throwable $e) {
    error_log('Tickets API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}