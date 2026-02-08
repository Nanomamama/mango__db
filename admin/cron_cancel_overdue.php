<?php
// 1. ตั้งค่าการเชื่อมต่อและ Timezone
date_default_timezone_set('Asia/Bangkok');
require_once __DIR__ . '/../db/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Path ไปยังไฟล์ PHPMailer
require_once '../user/PHPMailer/PHPMailer.php';
require_once '../user/PHPMailer/SMTP.php';
require_once '../user/PHPMailer/Exception.php';

// 2. กำหนดเงื่อนไขเวลา (3 วันที่แล้ว)
$three_days_ago = date('Y-m-d H:i:s', strtotime('-3 days'));

// 3. ค้นหาการจองที่ "ต้องยกเลิก"
// เงื่อนไข: สถานะ pending + ไม่มีสลิป + จองมาแล้วเกิน 3 วัน (created_at)
$sql = "SELECT bookings_id, guest_name, guest_email, booking_code 
        FROM bookings 
        WHERE status = 'pending' 
        AND (payment_slip IS NULL OR payment_slip = '') 
        AND created_at < ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $three_days_ago);
$stmt->execute();
$result = $stmt->get_result();

$overdue_bookings = [];
while ($row = $result->fetch_assoc()) {
    $overdue_bookings[] = $row;
}
$stmt->close();

// ถ้าไม่มีรายการค้าง ให้จบการทำงาน
if (empty($overdue_bookings)) {
    echo "[" . date('Y-m-d H:i:s') . "] ระบบตรวจสอบแล้ว: ไม่มีรายการค้างชำระที่เกินกำหนด\n";
    exit;
}

echo "พบรายการค้างชำระ " . count($overdue_bookings) . " รายการ กำลังเริ่มยกเลิกอัตโนมัติ...\n";

// 4. วนลูปยกเลิกและส่งอีเมล
$update_sql = "UPDATE bookings SET status = 'cancelled' WHERE bookings_id = ?";
$update_stmt = $conn->prepare($update_sql);

foreach ($overdue_bookings as $booking) {
    $b_id = $booking['bookings_id'];
    $b_code = $booking['booking_code'];
    $b_email = $booking['guest_email'];
    $b_name = $booking['guest_name'];

    // --- ส่วนที่ 1: เปลี่ยนสถานะใน Database เป็น ยกเลิก (cancelled) ---
    $update_stmt->bind_param("i", $b_id);
    if ($update_stmt->execute()) {
        
        echo "ยกเลิกการจอง $b_code สำเร็จ";

        // --- ส่วนที่ 2: ส่งอีเมลแจ้งผู้ใช้ ---
        if (!empty($b_email)) {
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = "smtp.gmail.com";
                $mail->SMTPAuth   = true;
                $mail->Username   = "nanoone342@gmail.com";
                $mail->Password   = "cmlt zqfp jveg jxoi"; // App Password ของคุณ
                $mail->Port       = 465;
                $mail->SMTPSecure = "ssl";
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('nanoone342@gmail.com', 'สวนแห่งการเรียนรู้');
                $mail->addAddress($b_email, $b_name);

                $mail->isHTML(true);
                $mail->Subject = "แจ้งยกเลิกการจองอัตโนมัติ (ไม่ได้ชำระเงินตามกำหนด) - รหัส: $b_code";
                
                $mail->Body = "
                    <div style='font-family: \"Sarabun\", sans-serif; padding: 20px; border: 1px solid #ddd;'>
                        <h2 style='color: #c0392b;'>แจ้งยกเลิกการจองของท่าน</h2>
                        <p>เรียน คุณ <strong>$b_name</strong>,</p>
                        <p>ขอแจ้งให้ทราบว่า รายการจองรหัส <strong>$b_code</strong> ได้ถูกยกเลิกโดยระบบเรียบร้อยแล้ว</p>
                        <p style='color: #d35400;'><strong>เหตุผล:</strong> ไม่ได้ทำการชำระค่ามัดจำภายในเวลาที่กำหนด (3 วัน) ทางระบบจึงจำเป็นต้องยกเลิกสิทธิ์การจองของท่านเพื่อเปิดโอกาสให้ผู้ใช้อื่น</p>
                        <p>หากท่านยังต้องการใช้บริการ กรุณาดำเนินการจองใหม่อีกครั้งผ่านหน้าเว็บไซต์</p>
                        <hr>
                        <p>ขอแสดงความนับถือ,<br>ฝ่ายบริการลูกค้า สวนแห่งการเรียนรู้</p>
                    </div>";

                $mail->send();
                echo " [ส่งอีเมลแจ้งแล้ว]\n";
            } catch (Exception $e) {
                echo " [อัปเดตสถานะแล้ว แต่ส่งอีเมลไม่สำเร็จ: {$mail->ErrorInfo}]\n";
            }
        } else {
            echo " [ไม่พบอีเมลผู้ใช้]\n";
        }
    }
}

$update_stmt->close();
$conn->close();
echo "--- ดำเนินการเสร็จสิ้น ---";
?>