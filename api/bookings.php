<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $pdo->query("
            SELECT b.*, u.name AS user_name, f.vehicle_name
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN fleet f ON b.fleet_id = f.id
            ORDER BY b.created_at DESC
        ");
        echo json_encode($stmt->fetchAll());
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        if (!isset($data['user_id'], $data['fleet_id'], $data['start_date'], $data['end_date'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required booking details.']);
            exit;
        }
        if ($data['start_date'] > $data['end_date']) {
            http_response_code(400);
            echo json_encode(['error' => 'End date must be after the start date.']);
            exit;
        }
        $stmt = $pdo->prepare('INSERT INTO bookings (user_id, fleet_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$data['user_id'], $data['fleet_id'], $data['start_date'], $data['end_date'], 'pending']);
        echo json_encode(['success' => true, 'message' => 'Booking created successfully!']);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Throwable $e) {
    error_log('Bookings API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Unable to process bookings right now.']);
}