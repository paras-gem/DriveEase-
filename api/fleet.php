<?php
// API for fleet management
require_once('../config/db.php');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Fetch all fleet vehicles
        $stmt = $pdo->query("SELECT * FROM fleet ORDER BY id DESC");
        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($vehicles);

    } elseif ($method === 'POST') {
        // Insert new vehicle
        $data = json_decode(file_get_contents('php://input'), true);
        if(isset($data['vehicle_name'], $data['plate'], $data['rent_cost'])) {
            $stmt = $pdo->prepare("INSERT INTO fleet (vehicle_name, plate, rent_cost, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $data['vehicle_name'], 
                $data['plate'], 
                $data['rent_cost'], 
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