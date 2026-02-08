<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

// รับข้อมูลจากฟอร์ม
$course_name = htmlspecialchars($_POST['course_name'], ENT_QUOTES, 'UTF-8');
$course_description = htmlspecialchars($_POST['course_description'], ENT_QUOTES, 'UTF-8');

// ตรวจสอบความยาวข้อมูล
if (strlen($course_name) > 255 || strlen($course_description) > 1000) {
    die("ข้อมูลยาวเกินไป");
}

// จัดการอัปโหลดรูปภาพ
$image1 = $_FILES['image1']['name'] ? uniqid() . "_" . basename($_FILES['image1']['name']) : null;
$image2 = $_FILES['image2']['name'] ? uniqid() . "_" . basename($_FILES['image2']['name']) : null;
$image3 = $_FILES['image3']['name'] ? uniqid() . "_" . basename($_FILES['image3']['name']) : null;

// กำหนดโฟลเดอร์สำหรับเก็บรูปภาพ
$target_dir = "../uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true); // ใช้ 0755 แทน 0777 เพื่อความปลอดภัย
}

// ตรวจสอบประเภทไฟล์และอัปโหลดรูปภาพ
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

function validateFile($file, $allowed_types, $allowed_extensions) {
    $file_type = $file['type'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    return in_array($file_type, $allowed_types) && in_array($file_extension, $allowed_extensions);
}

if ($image1 && validateFile($_FILES['image1'], $allowed_types, $allowed_extensions)) {
    move_uploaded_file($_FILES['image1']['tmp_name'], $target_dir . $image1);
}
if ($image2 && validateFile($_FILES['image2'], $allowed_types, $allowed_extensions)) {
    move_uploaded_file($_FILES['image2']['tmp_name'], $target_dir . $image2);
}
if ($image3 && validateFile($_FILES['image3'], $allowed_types, $allowed_extensions)) {
    move_uploaded_file($_FILES['image3']['tmp_name'], $target_dir . $image3);
}

// บันทึกข้อมูลลงฐานข้อมูล
$stmt = $conn->prepare("INSERT INTO courses (course_name, course_description, image1, image2, image3) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $course_name, $course_description, $image1, $image2, $image3);

if ($stmt->execute()) {
    header("Location: edit_courses.php");
    exit();
} else {
    echo "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
}

$stmt->close();
$conn->close();
?>