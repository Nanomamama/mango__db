<?php
// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "db_mango");

if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// รับข้อมูลจากฟอร์ม
$id = $_POST['id'];
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

if ($image1) {
    move_uploaded_file($_FILES['image1']['tmp_name'], $target_dir . $image1);
}
if ($image2) {
    move_uploaded_file($_FILES['image2']['tmp_name'], $target_dir . $image2);
}
if ($image3) {
    move_uploaded_file($_FILES['image3']['tmp_name'], $target_dir . $image3);
}

// อัปเดตข้อมูลในฐานข้อมูล
$sql = "UPDATE courses SET 
        course_name = '$course_name', 
        course_description = '$course_description', 
        image1 = IF('$image1' != '', '$image1', image1), 
        image2 = IF('$image2' != '', '$image2', image2), 
        image3 = IF('$image3' != '', '$image3', image3) 
        WHERE id = $id";

if ($conn->query($sql) === TRUE) {
    echo "อัปเดตข้อมูลสำเร็จ!";
    header("Location: edit_courses.php");
    exit();
} else {
    echo "เกิดข้อผิดพลาด: " . $conn->error;
}

$conn->close();
?>