<?php
session_start();

if (!isset($_SESSION['member_id'])) {
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

    $max_file_size = 5 * 1024 * 1024; // 5MB

    // ฟังก์ชันตรวจสอบไฟล์: คืนค่า true หรือรหัสข้อผิดพลาด ('size','invalid_type','error_upload')
    function is_valid_file($file, $allowed_types, $allowed_ext, $max_size = 5242880) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'error_upload';
        }

        // ตรวจสอบขนาดไฟล์
        if ($file['size'] > $max_size) {
            return 'size';
        }

        // ตรวจสอบประเภทไฟล์และนามสกุล
        $file_type = mime_content_type($file['tmp_name']);
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_type, $allowed_types) || !in_array($ext, $allowed_ext)) {
            return 'invalid_type';
        }

        return true;
    }

    // ฟังก์ชันอัปโหลดไฟล์: คืนค่าสตริงชื่อไฟล์, null (ไม่มีไฟล์), หรือรหัสข้อผิดพลาด ('size','invalid_type','move_failed')
    function upload_file($file_input, $targetDir, $allowed_types, $allowed_ext, $max_size = 5242880) {
        if (!isset($_FILES[$file_input]) || $_FILES[$file_input]['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // ไม่มีไฟล์ที่อัปโหลด
        }

        $file = $_FILES[$file_input];

        $valid = is_valid_file($file, $allowed_types, $allowed_ext, $max_size);
        if ($valid !== true) {
            return $valid; // 'size' or 'invalid_type' or 'error_upload'
        }

        // สร้างโฟลเดอร์ถ้ายังไม่มี
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // สร้างชื่อไฟล์ใหม่
        $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $fileName = date('Ymd_His') . '_' . uniqid() . '.' . $ext;
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return $fileName;
        } else {
            error_log("[process_booking] move_uploaded_file failed for input {$file_input} to {$targetFile}");
            return 'move_failed';
        }
    }

    // กำหนดเส้นทางโฟลเดอร์ให้อยู่ภายในโฟลเดอร์ `user/`
    // เพื่อให้ตรงกับที่หน้า admin ใช้ (../user/Doc และ ../user/Paymentslip-Gardenreservation)
    $attachmentsDir = __DIR__ . DIRECTORY_SEPARATOR . 'Doc' . DIRECTORY_SEPARATOR;
    $slipDir = __DIR__ . DIRECTORY_SEPARATOR . 'Paymentslip-Gardenreservation' . DIRECTORY_SEPARATOR;

    // กำหนด allowed types แบบแยกกัน
    $allowed_types_doc = ['application/pdf'];
    $allowed_ext_doc = ['pdf'];

    $allowed_types_slip = ['application/pdf','image/jpeg','image/png','image/jpg'];
    $allowed_ext_slip = ['pdf','jpg','jpeg','png'];

    // อัปโหลดไฟล์ (document ต้องเป็น PDF เท่านั้น)
    $doc = upload_file('document', $attachmentsDir, $allowed_types_doc, $allowed_ext_doc, $max_file_size);
    $slip = upload_file('slip', $slipDir, $allowed_types_slip, $allowed_ext_slip, $max_file_size);

    // ตรวจสอบการอัปโหลด และให้ข้อความข้อผิดพลาดชัดเจน
    if ($doc === 'invalid_type') {
        header("Location: activities.php?error=invalid_doc_type");
        exit;
    } elseif ($doc === 'size') {
        header("Location: activities.php?error=doc_too_large");
        exit;
    } elseif ($doc === 'move_failed') {
        header("Location: activities.php?error=doc_move_failed");
        exit;
    }

    if ($slip === 'invalid_type') {
        header("Location: activities.php?error=invalid_slip_type");
        exit;
    } elseif ($slip === 'size') {
        header("Location: activities.php?error=slip_too_large");
        exit;
    } elseif ($slip === 'move_failed') {
        header("Location: activities.php?error=slip_move_failed");
        exit;
    }

    // คำนวณยอดรวม
    $price_per_person = 150;
    $total_amount = $people * $price_per_person;
    $deposit_amount = round($total_amount * 0.3, 2);
    $remain_amount = $total_amount - $deposit_amount;

    // อ่านค่าว่าผู้ใช้ต้องการอาหารกลางวันหรือไม่ (checkbox จะส่งค่า '1' เมื่อถูกติ๊ก)
    $lunch = (isset($_POST['lunch']) && ($_POST['lunch'] == '1' || $_POST['lunch'] === 'on')) ? 1 : 0;

    // เพิ่มข้อมูลลงฐานข้อมูล (เพิ่ม member_id)
    $member_id = $_SESSION['member_id'];
    $stmt = $conn->prepare("INSERT INTO bookings 
        (name, date, time, people, phone, doc, slip, lunch, status, total_amount, deposit_amount, remain_amount, member_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'รออนุมัติ', ?, ?, ?, ?)");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        header("Location: activities.php?error=db_prepare");
        exit;
    }

    $stmt->bind_param("sssisssidddi", $name, $date, $time, $people, $phone, $doc, $slip, $lunch, $total_amount, $deposit_amount, $remain_amount, $member_id);

    if ($stmt->execute()) {
        $booking_id = $stmt->insert_id;
        header("Location: activities.php?success=1&id=" . $booking_id);
        exit;
    } else {
        error_log("Execute failed: " . $stmt->error);
        header("Location: activities.php?error=db_execute");
        exit;
    }

} else {
    header("Location: activities.php");
    exit;
}
?>