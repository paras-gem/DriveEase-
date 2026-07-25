<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/schema_helpers.php';
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

try {
    $table = requireTable($pdo, ['fleet', 'vehicles'], 'fleet');
    $isCurrent = $table === 'fleet';
    if ($method === 'GET') {
        $fields = $isCurrent ? 'id, vehicle_name, plate, rent_cost, status, created_at' : 'id, vehicle_name, status, created_at';
        $stmt = $pdo->query("SELECT {$fields} FROM `{$table}` ORDER BY id DESC");
        echo json_encode($stmt->fetchAll());
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        if (empty($data['vehicle_name']) || ($isCurrent && empty($data['plate']))) {
            http_response_code(400);
            echo json_encode(['error' => $isCurrent ? 'Vehicle name and plate number are required.' : 'Vehicle name is required.']);
            exit;
        }
        $sql = $isCurrent
            ? 'INSERT INTO fleet (vehicle_name, plate, rent_cost, status) VALUES (?, ?, ?, ?)'
            : 'INSERT INTO vehicles (vehicle_name, status) VALUES (?, ?)';
        $values = $isCurrent
            ? [trim($data['vehicle_name']), trim($data['plate']), $data['rent_cost'] ?? 0, $data['status'] ?? 'available']
            : [trim($data['vehicle_name']), $data['status'] ?? 'available'];
        $pdo->prepare($sql)->execute($values);
        echo json_encode(['success' => true, 'message' => 'Vehicle added successfully.', 'id' => $pdo->lastInsertId()]);
    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        if (empty($data['id'])) { http_response_code(400); echo json_encode(['error' => 'Missing vehicle ID.']); exit; }
        $pdo->prepare("DELETE FROM `{$table}` WHERE id = ?")->execute([$data['id']]);
        echo json_encode(['success' => true, 'message' => 'Vehicle deleted successfully.']);
    } else { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); }
} catch (Throwable $e) {
    error_log('Fleet API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Fleet data is not available yet.']);
}