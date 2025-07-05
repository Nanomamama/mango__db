<?php
session_start();
require_once '../admin/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate และ Escape ข้อมูล
    $name = htmlspecialchars(trim($_POST['group_name']));
    $date = htmlspecialchars(trim($_POST['booking_date']));
    $time = htmlspecialchars(trim($_POST['visit_time']));
    $people = intval($_POST['number_of_people']);
    $phone = htmlspecialchars(trim($_POST['phone_number']));
    $doc = null;
    $slip = null;

    // Validate ตัวเลข
    if ($people < 1 || !preg_match('/^[0-9]{10}$/', $phone)) {
        header("Location: activities.php?error=1");
        exit;
    }

    // ฟังก์ชันตรวจสอบไฟล์
    function is_valid_file($file, $allowed_types, $max_size = 5242880) { // 5MB
        $file_type = mime_content_type($file['tmp_name']);
        $file_size = $file['size'];
        return in_array($file_type, $allowed_types) && $file_size <= $max_size;
    }

    $allowed_types = [
        'application/pdf',
        'image/jpeg',
        'image/png'
    ];

    // อัปโหลดเอกสาร
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        if (is_valid_file($_FILES['document'], $allowed_types)) {
            $targetDir = "../uploads/";
            $ext = pathinfo($_FILES["document"]["name"], PATHINFO_EXTENSION);
            $fileName = uniqid() . "." . $ext;
            $targetFile = $targetDir . $fileName;
            if (move_uploaded_file($_FILES["document"]["tmp_name"], $targetFile)) {
                $doc = $fileName;
            }
        }
    }

    // อัปโหลดสลิป
    if (isset($_FILES['slip']) && $_FILES['slip']['error'] === UPLOAD_ERR_OK) {
        if (is_valid_file($_FILES['slip'], $allowed_types)) {
            $targetDir = "../uploads/";
            $ext = pathinfo($_FILES["slip"]["name"], PATHINFO_EXTENSION);
            $fileName = uniqid() . "." . $ext;
            $targetFile = $targetDir . $fileName;
            if (move_uploaded_file($_FILES["slip"]["tmp_name"], $targetFile)) {
                $slip = $fileName;
            }
        }
    }

    // คำนวณยอดรวม ยอดมัดจำ และยอดคงเหลือ
    $price_per_person = 150;
    $total_amount = $people * $price_per_person;
    $deposit_amount = round($total_amount * 0.3, 2);
    $remain_amount = $total_amount - $deposit_amount;

    // เพิ่มข้อมูลลง bookings (status เริ่มต้น "รออนุมัติ")
    $stmt = $conn->prepare("INSERT INTO bookings 
        (name, date, time, people, phone, doc, slip, status, total_amount, deposit_amount, remain_amount) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'รออนุมัติ', ?, ?, ?)");
    $stmt->bind_param("sssissdddd", $name, $date, $time, $people, $phone, $doc, $slip, $total_amount, $deposit_amount, $remain_amount);
    if ($stmt->execute()) {
        $booking_id = $stmt->insert_id; // รับ id ล่าสุด  
        header("Location: activities.php?success=1&id=" . $booking_id);
        exit;
    } else {
        header("Location: activities.php?error=1");
        exit;
    }
} else {
    header("Location: activities.php?success=1");
    exit;
}
?>
