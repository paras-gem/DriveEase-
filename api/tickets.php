<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['action']) && $_GET['action'] === 'comments' && isset($_GET['ticket_id'])) {
            $stmt = $pdo->prepare("
                SELECT c.*, 
                       u.name AS customer_name, 
                       e.name AS employee_name
                FROM ticket_comments c
                LEFT JOIN customers u ON c.customer_id = u.id
                LEFT JOIN employees e ON c.employee_id = e.id
                WHERE c.ticket_id = ?
                ORDER BY c.created_at ASC
            ");
            $stmt->execute([$_GET['ticket_id']]);
            echo json_encode($stmt->fetchAll());
        } else {
            $stmt = $pdo->query("
                SELECT t.*, u.name AS user_name 
                FROM support_tickets t 
                LEFT JOIN customers u ON t.customer_id = u.id 
                ORDER BY t.created_at DESC
            ");
            echo json_encode($stmt->fetchAll());
        }
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        
        if (isset($_GET['action']) && $_GET['action'] === 'comment') {
            $ticket_id = $data['ticket_id'] ?? null;
            $customer_id = $data['customer_id'] ?? $data['user_id'] ?? null;
            if (empty($ticket_id) || empty($customer_id) || empty($data['comment'])) {
                http_response_code(400); 
                echo json_encode(['error' => 'Missing comment details.']); 
                exit; 
            }
            $pdo->prepare("INSERT INTO ticket_comments (ticket_id, customer_id, comment) VALUES (?, ?, ?)")
                ->execute([$ticket_id, $customer_id, trim($data['comment'])]);
            echo json_encode(['success' => true, 'message' => 'Comment added successfully!']);
        } else {
            $customer_id = $data['customer_id'] ?? $data['user_id'] ?? null;
            
            if (empty($customer_id) || empty($data['subject']) || empty($data['description'])) { 
                http_response_code(400); 
                echo json_encode(['error' => 'Missing required ticket details.']); 
                exit; 
            }
            $pdo->prepare("INSERT INTO support_tickets (customer_id, subject, description, priority) VALUES (?, ?, ?, ?)")
                ->execute([$customer_id, trim($data['subject']), trim($data['description']), $data['priority'] ?? 'medium']);
            echo json_encode(['success' => true, 'message' => 'Ticket created successfully!']);
        }
    } elseif ($method === 'PATCH') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        if (isset($data['id']) && isset($data['status'])) {
            $pdo->prepare("UPDATE support_tickets SET status = ? WHERE id = ?")->execute([$data['status'], $data['id']]);
            echo json_encode(['success' => true, 'message' => 'Ticket updated successfully.']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id or status.']);
        }
    } else { 
        http_response_code(405); 
        echo json_encode(['error' => 'Method not allowed']); 
    }
} catch (Throwable $e) {
    error_log('Tickets API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}