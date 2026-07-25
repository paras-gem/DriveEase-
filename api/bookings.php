<?php
require_once('../config/db.php');
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Fetch all bookings with user and vehicle details
        // Schema: bookings(id, user_id, fleet_id, start_date, end_date, status, created_at)
        $stmt = $pdo->query("
            SELECT b.*, u.name as user_name, f.vehicle_name 
            FROM bookings b 
            LEFT JOIN users u ON b.user_id = u.id 
            LEFT JOIN fleet f ON b.fleet_id = f.id
            ORDER BY b.created_at DESC
        ");
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($bookings);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Map frontend fields to database columns
        $user_id = $data['customer_id'] ?? $data['user_id'] ?? null;
        $fleet_id = $data['vehicle_id'] ?? $data['fleet_id'] ?? null;
        $start_date = $data['pickup_date'] ?? $data['start_date'] ?? null;
        $end_date = $data['return_date'] ?? $data['end_date'] ?? null;

        if ($user_id && $fleet_id && $start_date && $end_date) {
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, fleet_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                $fleet_id,
                $start_date,
                $end_date,
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
