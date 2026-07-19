<?php
require_once('../config/db.php');
header('Content-Type: application/json');

try {
    // Get counts
    $ticketsStmt = $pdo->query("SELECT COUNT(*) FROM tickets WHERE status != 'closed'");
    $activeTickets = $ticketsStmt->fetchColumn();

    $bookingsStmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'");
    $pendingBookings = $bookingsStmt->fetchColumn();

    $fleetStmt = $pdo->query("SELECT COUNT(*) FROM fleet WHERE status = 'available'");
    $availableFleet = $fleetStmt->fetchColumn();

    $customersStmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalCustomers = $customersStmt->fetchColumn();
    
    // Get recent activity (last 5 tickets or bookings)
    // For now we will just return empty or simple DB logic
    $activity = [];
    
    $recentVehicles = $pdo->query("SELECT id, make, model, created_at FROM fleet ORDER BY created_at DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    foreach($recentVehicles as $v) {
        $activity[] = [
            'icon' => 'fa-car',
            'title' => 'New Vehicle Added',
            'desc' => "{$v['make']} {$v['model']} was added to the fleet.",
            'time' => $v['created_at']
        ];
    }
    
    $recentUsers = $pdo->query("SELECT id, name, created_at FROM users ORDER BY created_at DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    foreach($recentUsers as $u) {
        $activity[] = [
            'icon' => 'fa-user-plus',
            'title' => 'New User Registered',
            'desc' => "{$u['name']} created an account.",
            'time' => $u['created_at']
        ];
    }
    
    // Sort activity by time descending
    usort($activity, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
    
    // Take top 4
    $activity = array_slice($activity, 0, 4);

    echo json_encode([
        'success' => true,
        'stats' => [
            'tickets' => $activeTickets,
            'bookings' => $pendingBookings,
            'fleet' => $availableFleet,
            'customers' => $totalCustomers
        ],
        'activity' => $activity
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
