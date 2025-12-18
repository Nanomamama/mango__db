<?php
session_start();
require_once '../admin/db.php';

// รับค่าจากฟอร์ม
$fullname = trim($_POST['fullname']);
$address = trim($_POST['address']);
$province_id = $_POST['province'];
$district_id = $_POST['district'];
$subdistrict_id = $_POST['subdistrict'];
$zipcode = $_POST['zipcode'];
$phone = trim($_POST['phone']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// ตรวจสอบรหัสผ่านตรงกัน
if ($password !== $confirm_password) {
    echo "<script>alert('รหัสผ่านไม่ตรงกัน');history.back();</script>";
    exit;
}

$stmt = $conn->prepare("SELECT member_id FROM members WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo "<script>alert('อีเมล์นี้ถูกใช้แล้ว');history.back();</script>";
    exit;
}
$stmt->close();

// hash รหัสผ่าน
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// บันทึกข้อมูล
$stmt = $conn->prepare("INSERT INTO members (fullname, address, province_id, district_id, subdistrict_id, zipcode, phone, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssiiissss", $fullname, $address, $province_id, $district_id, $subdistrict_id, $zipcode, $phone, $email, $hashed_password);

if ($stmt->execute()) {
    echo "<script>alert('สมัครสมาชิกสำเร็จ!');window.location='member_login.php';</script>";
} else {
    echo "<script>alert('เกิดข้อผิดพลาด กรุณาลองใหม่');history.back();</script>";
}
$stmt->close();
$conn->close();
?>