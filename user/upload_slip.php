<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "PHPMailer/PHPMailer.php";
require_once "PHPMailer/SMTP.php";
require_once "PHPMailer/Exception.php";

require_once __DIR__ . '/../db/db.php';

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

$booking_id = (int)$_POST['booking_id'];
$member_id = (int)$_SESSION['member_id'];
$slip_file = $_FILES['slip'];

// ตรวจสอบข้อผิดพลาดในการอัปโหลด
if ($slip_file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์']);
    exit;
}

// ตรวจสอบว่าเป็นเจ้าของการจองจริง
$stmt_check = $conn->prepare("SELECT bookings_id, booking_code, guest_name FROM bookings WHERE bookings_id = ? AND member_id = ?");
$stmt_check->bind_param("ii", $booking_id, $member_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบสิทธิ์ในการเข้าถึงการจองนี้']);
    exit;
}
$booking_details = $result_check->fetch_assoc();

function sendAdminSlipNotification($booking_id, $booking_code, $guest_name, $slip_path, $slip_filename) {
    $adminMail = new PHPMailer(true);
    try {
        // ... (โค้ด PHPMailer ที่เหลืออยู่ด้านล่าง)
    } catch (Exception $e) {
        // ไม่ต้องหยุดการทำงานของสคริปต์หลัก แค่บันทึก error
        error_log("Mailer Error: {$adminMail->ErrorInfo}");
    }
}
$stmt_check->close();

// กำหนด path และชื่อไฟล์ใหม่
$upload_dir = __DIR__ . '/Paymentslip-Gardenreservation/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$file_extension = pathinfo($slip_file['name'], PATHINFO_EXTENSION);
$new_filename = 'slip_' . $booking_id . '_' . time() . '.' . $file_extension;
$upload_path = $upload_dir . $new_filename;

// ย้ายไฟล์ไปยังโฟลเดอร์ที่ต้องการ
if (move_uploaded_file($slip_file['tmp_name'], $upload_path)) {
    // อัปเดตฐานข้อมูล
    $stmt_update = $conn->prepare("UPDATE bookings SET payment_slip = ? WHERE bookings_id = ?");
    if ($stmt_update) {
        $stmt_update->bind_param("si", $new_filename, $booking_id);

        // ส่งอีเมลแจ้งเตือน Admin
        try {
            $adminMail = new PHPMailer(true);
            // Debug to PHP error log for troubleshooting
            $adminMail->SMTPDebug = 2;
            $adminMail->Debugoutput = 'error_log';
            $adminMail->isSMTP();
            // Relax SSL checks for environments missing CA bundle (debug helper)
            $adminMail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ]
            ];
            $adminMail->Host       = "smtp.gmail.com";
            $adminMail->SMTPAuth   = true;
            $adminMail->Username   = "nanoone342@gmail.com";
            $adminMail->Password   = "yaud bhqb pibw lipz"; // App Password
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
                        </ul>
                        <p>กรุณาตรวจสอบความถูกต้องของสลิป (แนบในอีเมลนี้) และดำเนินการอนุมัติการจองในระบบต่อไป</p>
                        <hr>
                        <p style='font-size: 0.9em; color: #777;'>อีเมลนี้ถูกส่งจากระบบอัตโนมัติ</p>
                    </div>
                </div>
            ";

            $adminMail->send();
        } catch (Exception $e) {
            // หากส่งอีเมลไม่สำเร็จ ไม่ต้องหยุดการทำงาน แค่บันทึก log ไว้
            error_log("Slip Upload Mailer Error: " . $adminMail->ErrorInfo);
        }

        if ($stmt_update->execute()) {
            echo json_encode(['success' => true, 'message' => 'อัปโหลดสลิปสำเร็จ']);
        } else {
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
