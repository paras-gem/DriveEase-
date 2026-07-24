<?php
require_once('../config/db.php');
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $pdo->query("
            SELECT t.*, u.name as user_name 
            FROM support_tickets t 
            LEFT JOIN users u ON t.user_id = u.id 
            ORDER BY t.created_at DESC
        ");
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($tickets);
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['user_id'], $data['subject'], $data['description'])) {
            $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, subject, description, priority) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $data['user_id'],
                $data['subject'],
                $data['description'],
                $data['priority'] ?? 'medium'
            ]);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing fields']);
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
