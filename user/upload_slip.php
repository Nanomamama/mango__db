<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "PHPMailer/PHPMailer.php";
require_once "PHPMailer/SMTP.php";
require_once "PHPMailer/Exception.php";

require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/line_service.php';

date_default_timezone_set('Asia/Bangkok');

header('Content-Type: application/json');

// ตรวจสอบว่าผู้ใช้ login หรือไม่
if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

// ตรวจสอบว่าเป็น POST request และมีข้อมูลครบถ้วนหรือไม่
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['booking_id']) || !isset($_FILES['slip'])) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
    exit;
}

$sessionToken = $_SESSION['csrf_token'] ?? '';
$postedToken = $_POST['csrf_token'] ?? '';
if (!is_string($postedToken) || !hash_equals($sessionToken, $postedToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'invalid csrf token']);
    exit;
}

$booking_id = (int)$_POST['booking_id'];
$member_id = (int)$_SESSION['member_id'];
$slip_file = $_FILES['slip'];

// ตรวจสอบข้อผิดพลาดในการอัปโหลด
if ($slip_file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์']);
    exit;
}

$max_size = 5 * 1024 * 1024; // 5MB
$allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
$allowed_mimes = ['image/jpeg', 'image/png', 'application/pdf'];
$file_extension = strtolower(pathinfo($slip_file['name'], PATHINFO_EXTENSION));
$detected_mime = '';

if ($slip_file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'ไฟล์มีขนาดใหญ่เกิน 5MB']);
    exit;
}

if (!in_array($file_extension, $allowed_extensions, true)) {
    echo json_encode(['success' => false, 'message' => 'ประเภทไฟล์ไม่ถูกต้อง กรุณาเลือกไฟล์ JPG, PNG หรือ PDF เท่านั้น']);
    exit;
}

if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo) {
        $detected_mime = finfo_file($finfo, $slip_file['tmp_name']);
        finfo_close($finfo);
    }
}

if ($detected_mime !== '' && !in_array($detected_mime, $allowed_mimes, true)) {
    echo json_encode(['success' => false, 'message' => 'ชนิดไฟล์ไม่ตรงกับไฟล์ที่อนุญาต']);
    exit;
}

// ตรวจสอบว่าเป็นเจ้าของการจองจริง
$stmt_check = $conn->prepare("
    SELECT 
        b.bookings_id, 
        b.booking_code, 
        b.guest_name, 
        b.guest_phone,
        b.booking_date, 
        b.booking_time, 
        b.visitor_count, 
        b.deposit_amount, 
        b.price_total,
        b.status AS booking_status, 
        m.status AS member_status
    FROM bookings b
    INNER JOIN members m ON m.member_id = b.member_id
    WHERE b.bookings_id = ? AND b.member_id = ?
    LIMIT 1
");
$stmt_check->bind_param("ii", $booking_id, $member_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบสิทธิ์ในการเข้าถึงการจองนี้']);
    exit;
}
$booking_details = $result_check->fetch_assoc();
if ((int)($booking_details['member_status'] ?? 0) !== 1) {
    session_unset();
    session_destroy();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'member account is disabled']);
    exit;
}
if (!in_array((string)($booking_details['booking_status'] ?? ''), ['pending', 'awaiting_payment'], true)) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'booking cannot accept slip upload']);
    exit;
}
$stmt_check->close();

// กำหนด path และชื่อไฟล์ใหม่
$upload_dir = __DIR__ . '/Paymentslip-Gardenreservation/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$new_filename = 'slip_' . $booking_id . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
$upload_path = $upload_dir . $new_filename;

// ย้ายไฟล์ไปยังโฟลเดอร์ที่ต้องการ
if (move_uploaded_file($slip_file['tmp_name'], $upload_path)) {
    // อัปเดตฐานข้อมูล: เก็บชื่อไฟล์สลิป และเปลี่ยนสถานะเป็น awaiting_payment พร้อมอัปเดตเวลา
    $stmt_update = $conn->prepare("UPDATE bookings SET payment_slip = ?, status = ?, payment_submitted_at = NOW(), updated_at = NOW() WHERE bookings_id = ? AND member_id = ? AND status IN ('pending', 'awaiting_payment')");
    if ($stmt_update) {
        $status_after = 'awaiting_payment';
        $stmt_update->bind_param("ssii", $new_filename, $status_after, $booking_id, $member_id);

        // 1. ส่งอีเมลแจ้งเตือน Admin
        try {
            $adminMail = new PHPMailer(true);
            $adminMail->isSMTP();
            $adminMail->Host       = "smtp.gmail.com";
            $adminMail->SMTPAuth   = true;
            $adminMail->Username   = app_env('MAIL_USERNAME');
            $adminMail->Password   = app_env('MAIL_PASSWORD'); // App Password
            $adminMail->Port       = 465;
            $adminMail->SMTPSecure = "ssl";
            $adminMail->CharSet    = 'UTF-8';

            $adminMail->setFrom('nanoone342@gmail.com', 'ระบบจองคิว');
            $adminMail->addAddress('nanoone342@gmail.com'); // อีเมล Admin

            // แนบไฟล์สลิป
            $adminMail->addEmbeddedImage($upload_path, 'slip_image', $new_filename);

            $adminMail->isHTML(true);
            $adminMail->Subject = "🧾 [สลิปใหม่] มีการแนบสลิปสำหรับ Booking: " . ($booking_details['booking_code'] ?? $booking_id);
            $adminMail->Body    = "
                <div style='font-family: Kanit, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                    <div style='max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px;'>
                        <h2 style='color: #016A70;'>มีสลิปใหม่เข้าระบบ</h2>
                        <p>ผู้ใช้ได้ทำการแนบสลิปการโอนเงินสำหรับรายการจอง:</p>
                        <ul>
                            <li><strong>รหัสการจอง:</strong> " . htmlspecialchars($booking_details['booking_code'] ?? 'N/A') . "</li>
                            <li><strong>ชื่อผู้จอง:</strong> " . htmlspecialchars($booking_details['guest_name'] ?? 'N/A') . "</li>
                            <li><strong>Booking ID:</strong> " . $booking_id . "</li>
                        </ul>
                        <p style='margin-bottom: 15px; font-weight: bold; color: #016A70; text-align: center;'>สลิปการโอนเงิน</p>
                        <img src='cid:slip_image' alt='Payment Slip' style='max-width: 100%; height: auto; display: block; margin: 0 auto; border: 1px solid #eee; border-radius: 4px;'>
                        <p>กรุณาตรวจสอบความถูกต้องของสลิป และดำเนินการอนุมัติการจองในระบบต่อไป</p>
                        <hr>
                        <p style='font-size: 0.9em; color: #777;'>อีเมลนี้ถูกส่งจากระบบอัตโนมัติ</p>
                    </div>
                </div>
            ";

            $adminMail->send();
        } catch (Exception $e) {
            // หากส่งอีเมลไม่สำเร็จ ไม่ต้องหยุดการทำงาน แค่บันทึก log ไว้
            error_log("Slip Upload Mailer Error: " . $e->getMessage());
        }

        if ($stmt_update->execute() && $stmt_update->affected_rows === 1) {
            line_send_booking_slip_received($conn, $booking_id);
            // 2. ส่งการแจ้งเตือนสลิปชำระเงินมัดจำเข้า LINE (พร้อมรูปภาพสลิป)
            try {
                $bCode = $booking_details['booking_code'] ?? $booking_id;
                $gName = $booking_details['guest_name'] ?? 'N/A';
                $gPhone = !empty($booking_details['guest_phone']) ? $booking_details['guest_phone'] : '-';
                $bDate = $booking_details['booking_date'] ?? '-';
                $bTime = !empty($booking_details['booking_time']) ? substr($booking_details['booking_time'], 0, 5) . ' น.' : '-';
                $vCount = $booking_details['visitor_count'] ?? 1;
                
        $deposit = isset($booking_details['deposit_amount']) ? number_format((float)$booking_details['deposit_amount'], 2) : '0.00';
        $total = isset($booking_details['price_total']) ? number_format((float)$booking_details['price_total'], 2) : '0.00';
        // Calculate remaining balance (price_total - deposit_amount)
        $balance = number_format((float)($booking_details['price_total'] ?? 0) - (float)($booking_details['deposit_amount'] ?? 0), 2);

        $lineMsg = "🧾 มีการแนบสลิปชำระเงินมัดจำใหม่!\n";
        $lineMsg .= "-----------------------------\n";
        $lineMsg .= "รหัสการจอง: {$bCode}\n";
        $lineMsg .= "ชื่อผู้จอง: {$gName}\n";
        $lineMsg .= "เบอร์โทร: {$gPhone}\n";
        $lineMsg .= "วันที่เข้าชม: {$bDate}\n";
        $lineMsg .= "เวลา: {$bTime}\n";
        $lineMsg .= "จำนวนผู้เข้าชม: {$vCount} ท่าน\n";
        $lineMsg .= "ยอดมัดจำ: {$deposit} บาท (ยอดรวม {$total} บาท)\n";
        $lineMsg .= "ยอดคงเหลือ: {$balance} บาท\n";
        $lineMsg .= "-----------------------------\n";
        $lineMsg .= "ตรวจสอบสลิป: https://khamaon.com/mango/admin/booking_list.php";

                // URL รูปภาพสลิปสำหรับ LINE Messaging API
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'khamaon.com';
                $publicImageUrl = "{$protocol}://{$host}/mango/user/Paymentslip-Gardenreservation/" . rawurlencode($new_filename);
                $localImagePath = $upload_path;

                $lineNotifyFile = __DIR__ . '/../admin/line_notify.php';
                if (is_file($lineNotifyFile)) {
                    ob_start();
                    $message = $lineMsg;
                    $imageUrl = $publicImageUrl;
                    $imagePath = $localImagePath;
                    include $lineNotifyFile;
                    ob_end_clean();
                }
            } catch (Exception $e) {
                error_log("Slip Upload LINE Notification Error: " . $e->getMessage());
            }

            echo json_encode(['success' => true, 'message' => 'อัปโหลดสลิปสำเร็จ', 'status' => $status_after]);
        } else {
            error_log('upload_slip: DB update failed: ' . $conn->error);
            if (is_file($upload_path)) {
                unlink($upload_path);
            }
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัปเดตฐานข้อมูลได้']);
        }
        $stmt_update->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database prepare error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถบันทึกไฟล์ได้']);
}

$conn->close();
?>
