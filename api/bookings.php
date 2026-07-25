<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $pdo->query("
            SELECT b.*, u.name AS user_name, f.car_label AS vehicle_name, f.plate, f.rent_cost_per_day
            FROM bookings b 
            LEFT JOIN customers u ON b.customer_id = u.id 
            LEFT JOIN fleet f ON b.fleet_id = f.id 
            ORDER BY b.created_at DESC
        ");
        $results = $stmt->fetchAll();
        
        // Map back to front-end expected names for consistency if needed, 
        // or just let frontend use the new names. The frontend uses `user_name`, `vehicle_name`, `start_date`, `end_date`.
        echo json_encode($results);
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $customer_id = $data['customer_id'] ?? $data['user_id'] ?? null;
        $fleet_id = $data['fleet_id'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;

        if (!$customer_id || !$fleet_id || !$start_date || !$end_date) { 
            http_response_code(400); 
            echo json_encode(['error' => 'Missing required booking details. (customer_id, fleet_id, start_date, end_date)']); 
            exit; 
        }
        if ($start_date > $end_date) { 
            http_response_code(400); 
            echo json_encode(['error' => 'End date must be after the start date.']); 
            exit; 
        }

        // Calculate total cost from fleet's daily rate
        $fleetRow = $pdo->prepare("SELECT rent_cost_per_day FROM fleet WHERE id = ? AND status = 'available'");
        $fleetRow->execute([$fleet_id]);
        $car = $fleetRow->fetch();
        if (!$car) {
            http_response_code(409);
            echo json_encode(['error' => 'This vehicle is not available for booking.']);
            exit;
        }
        $days = (int) round((strtotime($end_date) - strtotime($start_date)) / 86400) + 1;
        $total_cost = round($car['rent_cost_per_day'] * $days, 2);
        
        $stmt = $pdo->prepare("INSERT INTO bookings (customer_id, fleet_id, start_date, end_date, total_cost, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$customer_id, $fleet_id, $start_date, $end_date, $total_cost, 'pending']);
        
        // Mark fleet car as booked
        $pdo->prepare("UPDATE fleet SET status = 'booked' WHERE id = ?")->execute([$fleet_id]);
        
        echo json_encode(['success' => true, 'message' => 'Booking created successfully!', 'total_cost' => $total_cost]);
    } else { 
        http_response_code(405); 
        echo json_encode(['error' => 'Method not allowed']); 
    }
} catch (Throwable $e) {
    error_log('Bookings API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}