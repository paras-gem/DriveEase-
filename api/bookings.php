<?php
require_once('../config/db.php');
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Fetch all bookings with user and vehicle details
        $stmt = $pdo->query("
            SELECT b.*, u.name as user_name, f.vehicle_name 
            FROM bookings b 
            LEFT JOIN users u ON b.customer_id = u.id 
            LEFT JOIN vehicles f ON b.vehicle_id = f.id
            ORDER BY b.created_at DESC
        ");
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($bookings);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['customer_id'], $data['vehicle_id'], $data['pickup_date'], $data['return_date'])) {
            $stmt = $pdo->prepare("INSERT INTO bookings (customer_id, vehicle_id, pickup_date, return_date, total_amount, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['customer_id'],
                $data['vehicle_id'],
                $data['pickup_date'],
                $data['return_date'],
                0.00,
                'pending'
            ]);
            echo json_encode(['success' => true, 'message' => 'Booking created successfully!']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields for booking.']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
