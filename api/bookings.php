<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $pdo->query("
            SELECT b.*, u.name AS user_name, f.vehicle_name 
            FROM bookings b 
            LEFT JOIN customers u ON b.customer_id = u.id 
            LEFT JOIN vehicles f ON b.vehicle_id = f.id 
            ORDER BY b.created_at DESC
        ");
        $results = $stmt->fetchAll();
        
        // Map back to front-end expected names for consistency if needed, 
        // or just let frontend use the new names. The frontend uses `user_name`, `vehicle_name`, `start_date`, `end_date`.
        echo json_encode($results);
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $customer_id = $data['customer_id'] ?? $data['user_id'] ?? null;
        $vehicle_id = $data['vehicle_id'] ?? $data['fleet_id'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;

        if (!$customer_id || !$vehicle_id || !$start_date || !$end_date) { 
            http_response_code(400); 
            echo json_encode(['error' => 'Missing required booking details.']); 
            exit; 
        }
        if ($start_date > $end_date) { 
            http_response_code(400); 
            echo json_encode(['error' => 'End date must be after the start date.']); 
            exit; 
        }
        
        $stmt = $pdo->prepare("INSERT INTO bookings (customer_id, vehicle_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$customer_id, $vehicle_id, $start_date, $end_date, 'pending']);
        echo json_encode(['success' => true, 'message' => 'Booking created successfully!']);
    } else { 
        http_response_code(405); 
        echo json_encode(['error' => 'Method not allowed']); 
    }
} catch (Throwable $e) {
    error_log('Bookings API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}