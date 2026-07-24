<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $pdo->query('SELECT id, vehicle_name, plate, rent_cost, status, created_at FROM fleet ORDER BY created_at DESC');
        echo json_encode($stmt->fetchAll());
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        if (empty($data['vehicle_name']) || empty($data['plate'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Vehicle name and plate number are required.']);
            exit;
        }
        $stmt = $pdo->prepare('INSERT INTO fleet (vehicle_name, plate, rent_cost, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([trim($data['vehicle_name']), trim($data['plate']), $data['rent_cost'] ?? 0, $data['status'] ?? 'available']);
        echo json_encode(['success' => true, 'message' => 'Vehicle added successfully.', 'id' => $pdo->lastInsertId()]);
    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        if (empty($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing vehicle ID.']);
            exit;
        }
        $stmt = $pdo->prepare('DELETE FROM fleet WHERE id = ?');
        $stmt->execute([$data['id']]);
        echo json_encode(['success' => true, 'message' => 'Vehicle deleted successfully.']);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Throwable $e) {
    error_log('Fleet API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Unable to process fleet data right now.']);
}