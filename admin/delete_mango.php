<?php
// คำสั่ง SQL สำหรับการลบข้อมูล (ต้องเป็น admin และมี CSRF token)
require_once 'auth.php';
require_once 'db.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    // ตรวจสอบ CSRF
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $postedToken)) {
        http_response_code(403);
        echo 'Invalid CSRF token';
        exit;
    }

    $id = intval($_POST['delete_id']);

    // ลบข้อมูล
    $stmt = $conn->prepare("DELETE FROM mango_varieties WHERE mango_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: manage_mango.php?deleted=1");
    } else {
        echo "เกิดข้อผิดพลาดในการลบข้อมูล";
    }
}
?>
