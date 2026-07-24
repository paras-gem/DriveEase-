<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/schema_helpers.php';
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

try {
    $bookingTable = requireTable($pdo, ['bookings'], 'bookings');
    $vehicleTable = requireTable($pdo, ['fleet', 'vehicles'], 'fleet');
    $userTable = requireTable($pdo, ['users', 'customers'], 'users');
    $bookingColumns = columnsFor($pdo, $bookingTable);
    $userColumn = in_array('user_id', $bookingColumns, true) ? 'user_id' : 'customer_id';
    $vehicleColumn = in_array('fleet_id', $bookingColumns, true) ? 'fleet_id' : 'vehicle_id';
    $startColumn = in_array('start_date', $bookingColumns, true) ? 'start_date' : 'pickup_date';
    $endColumn = in_array('end_date', $bookingColumns, true) ? 'end_date' : 'return_date';

    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT b.*, b.{$userColumn} AS user_id, b.{$vehicleColumn} AS fleet_id, b.{$startColumn} AS start_date, b.{$endColumn} AS end_date, u.name AS user_name, f.vehicle_name FROM `{$bookingTable}` b LEFT JOIN `{$userTable}` u ON b.{$userColumn} = u.id LEFT JOIN `{$vehicleTable}` f ON b.{$vehicleColumn} = f.id ORDER BY b.created_at DESC");
        echo json_encode($stmt->fetchAll());
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        if (!isset($data['user_id'], $data['fleet_id'], $data['start_date'], $data['end_date'])) { http_response_code(400); echo json_encode(['error' => 'Missing required booking details.']); exit; }
        if ($data['start_date'] > $data['end_date']) { http_response_code(400); echo json_encode(['error' => 'End date must be after the start date.']); exit; }
        $columns = "{$userColumn}, {$vehicleColumn}, {$startColumn}, {$endColumn}, status";
        $pdo->prepare("INSERT INTO `{$bookingTable}` ({$columns}) VALUES (?, ?, ?, ?, ?)")->execute([$data['user_id'], $data['fleet_id'], $data['start_date'], $data['end_date'], 'pending']);
        echo json_encode(['success' => true, 'message' => 'Booking created successfully!']);
    } else { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); }
} catch (Throwable $e) {
    error_log('Bookings API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Booking data is not available yet.']);
}