<?php
session_start();

if (!isset($_SESSION['member_id'])) {
    // ถ้าไม่ล็อกอิน ไม่ให้บันทึกการจอง
    header("Location: login.php?error=not_logged_in");
    exit();
}

require_once '../admin/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate และ Escape ข้อมูล
    $name = htmlspecialchars(trim($_POST['group_name']));
    $date = htmlspecialchars(trim($_POST['booking_date']));
    $time = htmlspecialchars(trim($_POST['visit_time']));
    $people = intval($_POST['number_of_people']);
    $phone = htmlspecialchars(trim($_POST['phone_number']));

    // Validate ตัวเลขและเบอร์โทร
    if ($people < 1 || !preg_match('/^[0-9]{10}$/', $phone)) {
        header("Location: activities.php?error=1");
        exit;
    }

    $allowed_types = ['application/pdf','image/jpeg','image/png'];
    $targetDir = "../uploads/";

    // ฟังก์ชันตรวจสอบไฟล์
    function is_valid_file($file, $allowed_types, $max_size = 5242880) {
        $file_type = mime_content_type($file['tmp_name']);
        $file_size = $file['size'];
        return in_array($file_type, $allowed_types) && $file_size <= $max_size;
    }

    // ฟังก์ชันอัปโหลดไฟล์
    function upload_file($file_input, $targetDir, $allowed_types) {
        if (isset($_FILES[$file_input]) && $_FILES[$file_input]['error'] === UPLOAD_ERR_OK) {
            if (is_valid_file($_FILES[$file_input], $allowed_types)) {
                $ext = strtolower(pathinfo($_FILES[$file_input]["name"], PATHINFO_EXTENSION));
                if ($ext) {
                    $fileName = date('Ymd_His') . '_' . substr(md5(mt_rand()),0,4) . '.' . $ext;
                    $targetFile = $targetDir . $fileName;
                    if (move_uploaded_file($_FILES[$file_input]["tmp_name"], $targetFile)) {
                        return $fileName;
                    } else {
                        return false; // upload failed
                    }
                }
            }
        }
        return null; // no file uploaded
    }

    // อัปโหลดไฟล์
    $doc = upload_file('document', $targetDir, $allowed_types);
    $slip = upload_file('slip', $targetDir, $allowed_types);

    // ตรวจสอบว่าอัปโหลดสลิปสำเร็จหรือไม่
    if (isset($_FILES['slip']) && $_FILES['slip']['error'] === UPLOAD_ERR_OK && !$slip) {
        header("Location: activities.php?error=upload_slip");
        exit;
    }

    // คำนวณยอดรวม
    $price_per_person = 150;
    $total_amount = $people * $price_per_person;
    $deposit_amount = round($total_amount * 0.3, 2);
    $remain_amount = $total_amount - $deposit_amount;

    // เพิ่มข้อมูลลงฐานข้อมูล
    $stmt = $conn->prepare("INSERT INTO bookings 
        (name, date, time, people, phone, doc, slip, status, total_amount, deposit_amount, remain_amount) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'รออนุมัติ', ?, ?, ?)");
    $stmt->bind_param("sssisssddd", $name, $date, $time, $people, $phone, $doc, $slip, $total_amount, $deposit_amount, $remain_amount);

    if ($stmt->execute()) {
        $booking_id = $stmt->insert_id;
        header("Location: activities.php?success=1&id=" . $booking_id);
        exit;
    } else {
        header("Location: activities.php?error=1");
        exit;
    }

} else {
    header("Location: activities.php");
    exit;
}
?>
