<!-- ไฟล์ delete_course.php ให้เพิ่มโค้ดสำหรับลบข้อมูลในฐานข้อมูล -->
<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

// ควรใช้ POST method สำหรับการลบข้อมูลเพื่อความปลอดภัย
// และควรมี CSRF token เพื่อป้องกันการโจมตี
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header("Location: edit_courses.php?error=invalid_request");
    exit();
}

$id = (int)$_POST['id'];

// ใช้ Prepared Statements เพื่อป้องกัน SQL Injection
$stmt = $conn->prepare("DELETE FROM courses WHERE courses_id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: edit_courses.php?success=delete");
} else {
    header("Location: edit_courses.php?error=" . urlencode($stmt->error));
}

$stmt->close();
$conn->close();
?>