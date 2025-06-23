<!-- หน้านี้เกี่ยวกับการแจ้งเตือนกระดิ่งการจอง -->

<?php
require_once 'db.php';
header('Content-Type: application/json');
header("Cache-Control: no-cache, must-revalidate");

session_start();

$count = $conn->query("SELECT COUNT(*) FROM bookings WHERE viewed = 0")->fetch_row()[0];
echo json_encode(['count' => $count]);

$conn->close();
?>