<!-- ลบวันที่อัปเดต -->
<?php
require_once 'db.php';
$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['dates']) && is_array($data['dates'])) {
    $placeholders = implode(',', array_fill(0, count($data['dates']), '?'));
    $types = str_repeat('s', count($data['dates']));
    $sql = "DELETE FROM calendar_dates WHERE date IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$data['dates']);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>