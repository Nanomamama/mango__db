<?php
require_once __DIR__ . '/../db/db.php';

if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// รับข้อมูลจากฟอร์ม
$courses_id = $_POST['id']; // This should come from a hidden input field in your form
$course_name = $_POST['course_name'];
$course_description = $_POST['course_description'];

// จัดการอัปโหลดรูปภาพ
$image1 = $_FILES['image1']['name'] ? basename($_FILES['image1']['name']) : null;
$image2 = $_FILES['image2']['name'] ? basename($_FILES['image2']['name']) : null;
$image3 = $_FILES['image3']['name'] ? basename($_FILES['image3']['name']) : null;

$target_dir = "../uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Upload images if they exist
if ($image1) {
    move_uploaded_file($_FILES['image1']['tmp_name'], $target_dir . $image1);
}
if ($image2) {
    move_uploaded_file($_FILES['image2']['tmp_name'], $target_dir . $image2);
}
if ($image3) {
    move_uploaded_file($_FILES['image3']['tmp_name'], $target_dir . $image3);
}

// Build the SQL query dynamically based on which images are uploaded
$sql = "UPDATE courses SET 
        course_name = ?, 
        course_description = ?";
$params = array($course_name, $course_description);
$types = "ss";

if ($image1) {
    $sql .= ", image1 = ?";
    $params[] = $image1;
    $types .= "s";
}
if ($image2) {
    $sql .= ", image2 = ?";
    $params[] = $image2;
    $types .= "s";
}
if ($image3) {
    $sql .= ", image3 = ?";
    $params[] = $image3;
    $types .= "s";
}

$sql .= " WHERE courses_id = ?";
$params[] = $courses_id;
$types .= "i";

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo "อัปเดตข้อมูลสำเร็จ!";
    header("Location: edit_courses.php");
    exit();
} else {
    echo "เกิดข้อผิดพลาด: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>