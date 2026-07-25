<?php
ini_set('display_errors', 0);
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $pdo->query("
            SELECT f.*, u.name AS user_name 
            FROM feedback f 
            LEFT JOIN customers u ON f.customer_id = u.id 
            ORDER BY f.created_at DESC
        ");
        echo json_encode($stmt->fetchAll());
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $customer_id = $data['customer_id'] ?? $data['user_id'] ?? null;
        
        if (empty($customer_id) || empty($data['subject']) || empty($data['description'])) { 
            http_response_code(400); 
            echo json_encode(['error' => 'Missing required feedback details.']); 
            exit; 
        }
        $pdo->prepare("INSERT INTO feedback (customer_id, type, subject, description) VALUES (?, ?, ?, ?)")
            ->execute([$customer_id, $data['type'] ?? 'feedback', trim($data['subject']), trim($data['description'])]);
        echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully!']);
    } else { 
        http_response_code(405); 
        echo json_encode(['error' => 'Method not allowed']); 
    }
} catch (Throwable $e) {
    error_log('Feedback API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
