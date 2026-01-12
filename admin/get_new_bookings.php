<?php
/**
 * get_new_bookings.php
 * ใช้สำหรับแจ้งเตือนกระดิ่งการจองแบบ realtime
 */

require_once 'db.php';

/* -----------------------------
   Header: JSON + No Cache
------------------------------ */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* -----------------------------
   ค่าเริ่มต้น
------------------------------ */
$response = [
    'count'   => 0,   // จำนวนรายการใหม่
    'last_id' => 0,   // booking_id ล่าสุด
    'success' => false
];

try {

    $sql = "
        SELECT 
            COUNT(*) AS cnt,
            COALESCE(MAX(bookings_id), 0) AS last_id
        FROM bookings
        WHERE viewed = 0
    ";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception($conn->error);
    }

    $row = $result->fetch_assoc();

    $response['count']   = (int)$row['cnt'];
    $response['last_id'] = (int)$row['last_id'];
    $response['success'] = true;

} catch (Exception $e) {

    // ถ้า error ไม่ต้องให้ client พัง
    http_response_code(500);
    $response['error'] = 'Database error';

}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
$conn->close();
exit;
?>