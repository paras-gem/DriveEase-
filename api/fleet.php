<?php
// API for fleet management
require_once('../config/db.php');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Fetch all fleet vehicles - Schema uses 'fleet' table
        $stmt = $pdo->query("SELECT * FROM fleet ORDER BY id DESC");
        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($vehicles);

    } elseif ($method === 'POST') {
        // Insert new vehicle - Schema uses 'fleet' table with 'plate'
        $data = json_decode(file_get_contents('php://input'), true);
        if(isset($data['vehicle_name'])) {
            // Generate a random plate if not provided for simplicity
            $plate = $data['plate'] ?? 'DE-' . strtoupper(substr(md5(time()), 0, 6));
            $rent_cost = $data['rent_cost'] ?? 0.00;
            
            $stmt = $pdo->prepare("INSERT INTO fleet (vehicle_name, plate, rent_cost, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $data['vehicle_name'], 
                $plate,
                $rent_cost,
                $data['status'] ?? 'available'
            ]);
            echo json_encode(['success' => true, 'message' => 'Vehicle added successfully.', 'id' => $pdo->lastInsertId()]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
        }

    } elseif ($method === 'DELETE') {
        // Delete a vehicle
        $data = json_decode(file_get_contents('php://input'), true);
        if(isset($data['id'])) {
            $stmt = $pdo->prepare("DELETE FROM fleet WHERE id = ?");
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true, 'message' => 'Vehicle deleted successfully.']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing vehicle ID']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }

} catch(PDOException $e){
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
