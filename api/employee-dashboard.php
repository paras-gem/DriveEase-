<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'employee') { http_response_code(403); echo json_encode(['success'=>false]); exit; }
require_once __DIR__ . '/../config/db.php';
try {
 $stats=['pendingBookings'=>(int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn(),'openTickets'=>(int)$pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status IN ('open','pending')")->fetchColumn(),'activeCustomers'=>(int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn(),'availableFleet'=>(int)$pdo->query("SELECT COUNT(*) FROM fleet WHERE status='available'")->fetchColumn()];
 $bookings=$pdo->query('SELECT b.id,b.status,b.start_date,b.end_date,c.name customer_name,c.email customer_email,f.car_label FROM bookings b JOIN customers c ON c.id=b.customer_id JOIN fleet f ON f.id=b.fleet_id ORDER BY b.created_at DESC LIMIT 10')->fetchAll();
 $activity=$pdo->query("SELECT c.name, t.subject, t.status, t.created_at FROM support_tickets t JOIN customers c ON c.id=t.customer_id ORDER BY t.created_at DESC LIMIT 10")->fetchAll();
 echo json_encode(['success'=>true,'stats'=>$stats,'bookings'=>$bookings,'activity'=>$activity]);
} catch (Throwable $e) { http_response_code(500); echo json_encode(['success'=>false]); }
