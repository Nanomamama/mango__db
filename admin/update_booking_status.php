<?php
// filepath: c:\xampp\htdocs\mango\admin\update_booking_status.php
require_once 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id'], $data['status'])) {
    $stmt = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
    if ($stmt) {
        $stmt->bind_param("si", $data['status'], $data['id']);
        $success = $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => $success]);
        exit;
    }
}
echo json_encode(['success' => false]);