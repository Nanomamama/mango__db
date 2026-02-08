<?php
// filepath: c:\xampp\htdocs\mango\admin\update_calendar.php
require_once __DIR__ . '/../db/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['dates'], $data['status'])) {
    foreach ($data['dates'] as $date) {
        // upsert
        $stmt = $conn->prepare("INSERT INTO calendar_dates (date, status) VALUES (?, ?) ON DUPLICATE KEY UPDATE status=?");
        $stmt->bind_param("sss", $date, $data['status'], $data['status']);
        $stmt->execute();
        $stmt->close();
    }
    echo json_encode(['success' => true]);
    exit;
}
echo json_encode(['success' => false]);