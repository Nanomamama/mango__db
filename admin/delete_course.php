<!-- ไฟล์ delete_course.php ให้เพิ่มโค้ดสำหรับลบข้อมูลในฐานข้อมูล -->
<?php
// $conn = new mysqli("localhost", "root", "", "db_mango");
require_once 'db.php';


if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

$id = $_GET['id'];
$sql = "DELETE FROM courses WHERE courses_id = $id";

if ($conn->query($sql) === TRUE) {
    echo "ลบข้อมูลสำเร็จ!";
    header("Location: edit_courses.php");
    exit();
} else {
    echo "เกิดข้อผิดพลาด: " . $conn->error;
}

$conn->close();
?>