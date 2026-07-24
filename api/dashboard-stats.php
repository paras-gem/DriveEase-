<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/schema_helpers.php';
header('Content-Type: application/json');

try {
    $count = function (array $tables, string $where = '1=1') use ($pdo): int {
        $table = firstExistingTable($pdo, $tables);
        return $table ? (int) $pdo->query("SELECT COUNT(*) FROM `{$table}` WHERE {$where}")->fetchColumn() : 0;
    };
    $fleetTable = firstExistingTable($pdo, ['fleet', 'vehicles']);
    $userTable = firstExistingTable($pdo, ['users', 'customers']);
    $activity = [];
    if ($fleetTable) {
        foreach ($pdo->query("SELECT vehicle_name, created_at FROM `{$fleetTable}` ORDER BY created_at DESC LIMIT 3")->fetchAll() as $vehicle) {
            $activity[] = ['icon' => 'fa-car', 'title' => 'New Vehicle Added', 'desc' => $vehicle['vehicle_name'] . ' was added to the fleet.', 'time' => $vehicle['created_at']];
        }
    }
    if ($userTable) {
        foreach ($pdo->query("SELECT name, created_at FROM `{$userTable}` ORDER BY created_at DESC LIMIT 3")->fetchAll() as $user) {
            $activity[] = ['icon' => 'fa-user-plus', 'title' => 'New User Registered', 'desc' => $user['name'] . ' created an account.', 'time' => $user['created_at']];
        }
    }
    usort($activity, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));
    echo json_encode(['success' => true, 'stats' => ['tickets' => $count(['tickets', 'support_tickets'], "status != 'closed'"), 'bookings' => $count(['bookings'], "status = 'pending'"), 'fleet' => $count(['fleet', 'vehicles'], "status = 'available'"), 'customers' => $count(['users', 'customers'])], 'activity' => array_slice($activity, 0, 4)]);
} catch (Throwable $e) {
    error_log('Dashboard API error: ' . $e->getMessage());
    echo json_encode(['success' => true, 'stats' => ['tickets' => 0, 'bookings' => 0, 'fleet' => 0, 'customers' => 0], 'activity' => []]);
}