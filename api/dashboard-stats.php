<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

try {
    $count = function (string $table, string $where = '1=1') use ($pdo): int {
        return (int) $pdo->query("SELECT COUNT(*) FROM `{$table}` WHERE {$where}")->fetchColumn();
    };

    $activity = [];
    
    foreach ($pdo->query("SELECT car_label AS vehicle_name, created_at FROM fleet ORDER BY created_at DESC LIMIT 3")->fetchAll() as $vehicle) {
        $activity[] = ['icon' => 'fa-car', 'title' => 'New Vehicle Added', 'desc' => $vehicle['vehicle_name'] . ' was added to the fleet.', 'time' => $vehicle['created_at']];
    }
    
    foreach ($pdo->query("SELECT name, created_at FROM customers ORDER BY created_at DESC LIMIT 3")->fetchAll() as $user) {
        $activity[] = ['icon' => 'fa-user-plus', 'title' => 'New Customer Registered', 'desc' => $user['name'] . ' created an account.', 'time' => $user['created_at']];
    }

    usort($activity, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));

    echo json_encode([
        'success' => true, 
        'stats' => [
            'tickets' => $count('support_tickets', "status != 'closed'"), 
            'bookings' => $count('bookings', "status = 'pending'"), 
            'fleet' => $count('fleet', "status = 'available'")
        ], 
        'activity' => array_slice($activity, 0, 4)
    ]);
} catch (Throwable $e) {
    error_log('Dashboard API error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Database error: ' . $e->getMessage(),
        'stats' => ['tickets' => 0, 'bookings' => 0, 'fleet' => 0], 
        'activity' => []
    ]);
}