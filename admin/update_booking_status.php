<?php
// filepath: c:\xampp\htdocs\mango\admin\update_booking_status.php
require_once 'db.php';

// รับข้อมูล JSON
$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];
$status = $data['status'];

// อัปเดตสถานะ
$stmt = $conn->prepare("UPDATE bookings SET status=? WHERE bookings_id=?");
$stmt->bind_param("si", $status, $id);
$success = $stmt->execute();

header('Content-Type: application/json');
echo json_encode(['success' => $success]);
exit;