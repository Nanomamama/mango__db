<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');

// --- 1. การเชื่อมต่อ Database และโหลดข้อมูลเดิม (กรณีเรียกผ่าน booking_code) ---
$booking_code_post = $_POST['booking_code'] ?? null;
if ($booking_code_post && empty($_POST['name']) && empty($_POST['email'])) {
    if (file_exists(__DIR__ . '/db/db.php')) {
        include __DIR__ . '/db/db.php';
    } elseif (file_exists(__DIR__ . '/db.php')) {
        include __DIR__ . '/db.php';
    }

    if (!isset($pdo) && isset($servername, $username, $password, $dbname)) {
        try {
            $dsn = "mysql:host={$servername};dbname={$dbname};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
        } catch (PDOException $e) {
            exit(json_encode(['status'=>'error','response'=>'DB connection failed']));
        }
    }

    if (!isset($pdo)) exit(json_encode(['status'=>'error','response'=>'No DB available']));

    $stmt = $pdo->prepare('SELECT * FROM bookings WHERE booking_code = :code LIMIT 1');
    $stmt->execute([':code'=>$booking_code_post]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) exit(json_encode(['status'=>'error','response'=>'booking not found']));

    $_POST['name'] = $row['guest_name'] ?? '';
    $_POST['email'] = $row['guest_email'] ?? '';
    $_POST['phone'] = $row['guest_phone'] ?? '';
    if (isset($row['booking_type']) && $row['booking_type'] !== '') {
        $_POST['booking_type'] = $row['booking_type'];
    } else {
        $_POST['booking_type'] = 'private';
    }
    $_POST['selected_date'] = $row['booking_date'] ?? '';
    $_POST['booking_time'] = $row['booking_time'] ?? '';
    $_POST['visitor_count'] = $row['visitor_count'] ?? 1;
    if (isset($row['lunch_request'])) {
        $_POST['lunch_request'] = $row['lunch_request'];
    }
    $server_attachment_path = $row['attachment_path'] ?? null;
} else {
    $server_attachment_path = null;
}

// --- 2. ฟังก์ชันช่วยจัดการข้อมูล ---
function convertToThaiDate($dateStr) {
    if (!$dateStr) return "";
    $dateObj = new DateTime($dateStr);
    $thaiMonths = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
    return $dateObj->format('d') . " " . $thaiMonths[(int)$dateObj->format('m') - 1] . " " . ($dateObj->format('Y') + 543);
}

// --- 3. ประมวลผลข้อมูลและคำนวณราคา ---
if (isset($_POST['name']) && isset($_POST['email'])) {
    $name          = $_POST['name'];
    $email         = $_POST['email'];
    $phone         = $_POST['phone'];
    $type          = $_POST['booking_type'] ?? 'private';
    $selected_date = $_POST['selected_date']; 
    $booking_time  = $_POST['booking_time'];  
    $visitor_count = intval($_POST['visitor_count']);
    $lunch_request_value = $_POST['lunch_request'] ?? 0;
    $lunch_request = ($lunch_request_value == 1 || $lunch_request_value === 'yes') ? "ต้องการ" : "ไม่ต้องการ";

    $price_per_person = 150;
    $total_price = isset($row['price_total']) ? (float)$row['price_total'] : ($visitor_count * $price_per_person);
    $deposit_amount = isset($row['deposit_amount']) ? (float)$row['deposit_amount'] : round($total_price * 0.3, 2);

    if (!empty($booking_code_post)) {
        $booking_code = $booking_code_post;
    } else {
        $booking_code = "GV" . date("Ymd") . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    require_once "PHPMailer/PHPMailer.php";
    require_once "PHPMailer/SMTP.php";
    require_once "PHPMailer/Exception.php";

    try {
        // --- 4. จัดการไฟล์แนบ (ถ้ามี) ---
        $attachedFile = null;
        $attachedName = null;
        if (!empty($server_attachment_path)) {
            $attachedFile = $server_attachment_path;
            $attachedName = basename($server_attachment_path);
        } elseif (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = time() . '_' . basename($_FILES['document']['name']);
            $targetFilePath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['document']['tmp_name'], $targetFilePath)) {
                $attachedFile = $targetFilePath;
                $attachedName = $_FILES['document']['name'];
            }
        }

        // --- 5. ส่งอีเมลหา ADMIN (เพื่อแจ้งเตือนให้ไปตรวจวันและส่ง QR) ---
        $adminMail = new PHPMailer(true);
        $adminMail->isSMTP();
        $adminMail->Host       = "smtp.gmail.com";
        $adminMail->SMTPAuth   = true;
        $adminMail->Username   = "nanoone342@gmail.com";
        $adminMail->Password   = "cmlt zqfp jveg jxoi"; 
        $adminMail->Port       = 465;
        $adminMail->SMTPSecure = "ssl";
        $adminMail->CharSet    = 'UTF-8';

        $adminMail->setFrom('nanoone342@gmail.com', 'ระบบจองคิว');
        $adminMail->addAddress('nanoone342@gmail.com'); 
        $adminMail->addReplyTo($email, $name);
        if ($attachedFile) $adminMail->addAttachment($attachedFile, $attachedName);

        $adminMail->isHTML(true);
        $adminMail->Subject = "🔔 [รอตรวจสอบ] $booking_code | $name | " . convertToThaiDate($selected_date);
        $adminMail->Body = "
        <div style='font-family: sans-serif; max-width: 600px; border: 1px solid #eee;'>
            <div style='background: #f39c12; color: white; padding: 20px;'>
                <h2 style='margin:0;'>มีคำขอจองคิวใหม่ (รอตรวจสอบวันว่าง)</h2>
            </div>
            <div style='padding: 20px;'>
                <p><strong>โปรดตรวจสอบ:</strong> หากวันเวลาที่ลูกค้าเลือกว่าง ให้ตอบกลับอีเมลฉบับนี้พร้อมแนบ <b>QR Code พร้อมเพย์</b> เพื่อเรียกเก็บมัดจำ</p>
                <hr>
                <p>รหัสจอง: <b>$booking_code</b></p>
                <p>ลูกค้า: $name ($phone)</p>
                <p>วันที่ต้องการ: <b>" . convertToThaiDate($selected_date) . " เวลา $booking_time น.</b></p>
                <p>จำนวน: $visitor_count ท่าน (อาหาร: $lunch_request)</p>
                <p>ยอดมัดจำที่ต้องเก็บ: <b>" . number_format($deposit_amount) . " บาท</b></p>
            </div>
        </div>";

        // --- 6. ส่งอีเมลหา USER (เพื่อแจ้งรับทราบคำขอและแจ้งขั้นตอนจ่ายเงิน) ---
        $userMail = new PHPMailer(true);
        $userMail->isSMTP();
        $userMail->Host       = "smtp.gmail.com";
        $userMail->SMTPAuth   = true;
        $userMail->Username   = "nanoone342@gmail.com";
        $userMail->Password   = "cmlt zqfp jveg jxoi"; 
        $userMail->Port       = 465;
        $userMail->SMTPSecure = "ssl";
        $userMail->CharSet    = 'UTF-8';

        $userMail->setFrom('nanoone342@gmail.com', 'สวนแห่งการเรียนรู้');
        $userMail->addAddress($email, $name); 

        $userMail->isHTML(true);
        $userMail->Subject = "ได้รับคำขอจองคิวของคุณเรียบร้อยแล้ว [#$booking_code]";
        $userMail->Body = "
        <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 10px; overflow: hidden;'>
            <div style='background: #2563eb; color: white; padding: 25px; text-align: center;'>
                <h2 style='margin: 0;'>เราได้รับคำขอจองของคุณแล้ว</h2>
                <p>รหัสอ้างอิง: <strong>$booking_code</strong></p>
            </div>
            <div style='padding: 25px;'>
                <p>เรียน คุณ $name,</p>
                <p>ขอบคุณที่สนใจเข้าชมสวนแห่งการเรียนรู้ ขณะนี้เจ้าหน้าที่กำลังตรวจสอบตารางวันเข้าชมวันที่ <b>" . convertToThaiDate($selected_date) . "</b></p>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 5px solid #2563eb; margin: 20px 0;'>
                    <h4 style='margin-top: 0; color: #2563eb;'>📌 ขั้นตอนถัดไป:</h4>
                    <ol>
                        <li><b>รอเจ้าหน้าที่ยืนยันวัน:</b> เราจะแจ้งผลให้ทราบทางอีเมลภายใน 24 ชม.</li>
                        <li><b>รับ QR Code:</b> หากวันดังกล่าวว่าง เราจะส่ง QR Code ให้ท่านชำระเงิน</li>
                        <li><b>ชำระเงินและส่งสลิป:</b> เมื่อท่านโอนเงินแล้ว <b>กรุณาตอบกลับอีเมลฉบับนั้นพร้อมแนบรูปสลิป</b></li>
                    </ol>
                </div>
                <p style='font-size: 14px; color: #666;'>*การจองจะสมบูรณ์เมื่อท่านได้รับอีเมล 'ยืนยันการจองสำเร็จ' หลังจากส่งหลักฐานการโอนเงินแล้วเท่านั้น</p>
            </div>
        </div>";

        $adminSent = $adminMail->send();
        $userSent = $userMail->send();

        if ($adminSent && $userSent) {
            $status = "success";
            $response = "ส่งคำขอจองสำเร็จ! กรุณารอเจ้าหน้าที่ตรวจสอบวันและส่ง QR Code ทางอีเมล";
        } else {
            $status = "partial";
            $response = "บันทึกข้อมูลแล้ว แต่การส่งอีเมลแจ้งเตือนติดขัด";
        }

    } catch (Exception $e) {
        $status = "failed";
        $response = "ผิดพลาด: " . $e->getMessage();
    }

    exit(json_encode(["status" => $status, "response" => $response, "booking_code" => $booking_code]));
}