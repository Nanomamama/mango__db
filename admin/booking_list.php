<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';
require_once 'sidebar.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// The path is relative from the 'admin' directory to the 'user' directory
require_once '../user/PHPMailer/PHPMailer.php';
require_once '../user/PHPMailer/SMTP.php';
require_once '../user/PHPMailer/Exception.php';

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// ดึงชื่อ admin จาก session
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_email = $_SESSION['admin_email'] ?? '';

function app_base_url()
{
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    );
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = str_replace('\\', '/', dirname(dirname($scriptName)));
    $basePath = rtrim($basePath, '/');

    if ($basePath === '.' || $basePath === DIRECTORY_SEPARATOR) {
        $basePath = '';
    }

    return $scheme . '://' . $host . $basePath;
}

function save_qr_upload(array $qr_file, int $booking_id): array
{
    $max_size = 5 * 1024 * 1024;
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
    $extension = strtolower(pathinfo($qr_file['name'] ?? '', PATHINFO_EXTENSION));

    if (($qr_file['size'] ?? 0) > $max_size) {
        return ['ok' => false, 'message' => 'QR Code file is larger than 5MB'];
    }

    if (!in_array($extension, $allowed_extensions, true)) {
        return ['ok' => false, 'message' => 'Invalid QR Code file type'];
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = finfo_file($finfo, $qr_file['tmp_name']);
            finfo_close($finfo);
            if ($mime && !in_array($mime, $allowed_mimes, true)) {
                return ['ok' => false, 'message' => 'Invalid QR Code MIME type'];
            }
        }
    }

    $upload_dir = __DIR__ . '/../user/PaymentQR-Gardenreservation/';
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
        return ['ok' => false, 'message' => 'Cannot create QR upload directory'];
    }

    try {
        $filename = 'qr_' . $booking_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    } catch (\Throwable $e) {
        $filename = 'qr_' . $booking_id . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
    }

    $target = $upload_dir . $filename;
    if (!move_uploaded_file($qr_file['tmp_name'], $target)) {
        return ['ok' => false, 'message' => 'Cannot save QR Code file'];
    }

    return ['ok' => true, 'filename' => $filename, 'path' => $target];
}

// จัดการการอัปเดตสถานะการจอง
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'], $_POST['csrf_token'])) {
    // ตรวจ CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header('Content-Type: application/json');
        http_response_code(403); // Forbidden
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
        exit;
    }

    $action = $_POST['action'];

    if ($action === 'send_qr') {
        $id = (int) $_POST['id'];
        $qr_file = $_FILES['qr_code_file'] ?? null;
        $response = ['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง'];

        if ($id > 0 && $qr_file && $qr_file['error'] === UPLOAD_ERR_OK) {
            $stmt_details = $conn->prepare("SELECT guest_name, guest_email, booking_code, deposit_amount, visitor_count FROM bookings WHERE bookings_id = ? AND status = 'pending'");
            $stmt_details->bind_param("i", $id);
            $stmt_details->execute();
            $booking = $stmt_details->get_result()->fetch_assoc();
            $stmt_details->close();

            if ($booking && !empty($booking['guest_email'])) {
                $saved_qr = save_qr_upload($qr_file, $id);
                if (!$saved_qr['ok']) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $saved_qr['message']]);
                    exit;
                }

                try {
                    $mail = new PHPMailer(true);
                    // Enable SMTP debug output to PHP error log for troubleshooting
                    $mail->SMTPDebug = 2;
                    $mail->Debugoutput = 'error_log';
                    $mail->isSMTP();
                    // Relax SSL checks for environments with missing CA bundle (helpful for debugging)
                    $mail->SMTPOptions = [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        ]
                    ];
                    $mail->Host       = "smtp.gmail.com";
                    $mail->SMTPAuth   = true;
                    $mail->Username   = "nanoone342@gmail.com";
                    $mail->Password   = "yaud bhqb pibw lipz"; // App Password
                    $mail->Port       = 465;
                    $mail->SMTPSecure = "ssl";
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom('nanoone342@gmail.com', 'สวนลุงเผือก');
                    $mail->addAddress($booking['guest_email'], $booking['guest_name']);
                    $mail->addEmbeddedImage($saved_qr['path'], 'qrcode_deposit', $saved_qr['filename']);

                    $mail->isHTML(true);
                    $mail->Subject = "ชำระเงินมัดจำสำหรับการจอง";

                    // --- คำนวณค่าใช้จ่ายใหม่ (รวมค่าสถานที่) ---
                    $visitor_count = (int)($booking['visitor_count'] ?? 1);
                    $price_per_person = 150;
                    $instructor_fee = 1800;
                    $venue_fee = 3000;
                    $deposit_rate = 0.3;

                    $entrance_fee = $visitor_count * $price_per_person;
                    $total_amount = $entrance_fee + $instructor_fee + $venue_fee;
                    $deposit_amount_calculated = round($total_amount * $deposit_rate);
                    $deposit_formatted = number_format($deposit_amount_calculated, 2);

                    // Create the direct link to open the upload modal
                    $booking_id_for_link = $id;
                    $visitor_count_for_link = $booking['visitor_count'] ?? 1;
                    $upload_link = app_base_url() . "/user/bookings.php?action=upload_slip&booking_id={$booking_id_for_link}&visitor_count={$visitor_count_for_link}";
                    $mail->Body = "
                            <div style='background-color: #f4f7f6; padding: 40px 10px; font-family: \"Sarabun\", \"Kanit\", \"Helvetica Neue\", Helvetica, Arial, sans-serif;'>
                                <div style='max-width: 600px; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: 1px solid #e1e8ed;'>
                                    
                                    <h2 style='color: #016A70; text-align: center; margin-top: 0; font-size: 24px;'>แจ้งชำระเงินมัดจำการจอง</h2>
                                    
                                    <p style='font-size: 16px; color: #333;'>เรียน คุณ <strong>" . htmlspecialchars($booking['guest_name']) . "</strong>,</p>
                                    
                                    <p style='font-size: 15px; line-height: 1.6; color: #555;'>
                                        ตามที่ท่านได้ทำการจองเข้าชมสวน กรุณาชำระเงินมัดจำเพื่อยืนยันการจองของท่าน โดยมีรายละเอียดค่าใช้จ่ายดังนี้:
                                    </p>

                                    <div style='border: 1px solid #eee; border-radius: 8px; margin: 25px 0; padding: 15px;'>
                                        <table style='width: 100%; border-collapse: collapse; font-size: 15px;'>
                                            <tr>
                                                <td style='padding: 8px 0; color: #555;'>ค่าเข้าชม (" . $visitor_count . " คน x " . number_format($price_per_person, 2) . " บาท)</td>
                                                <td style='padding: 8px 0; text-align: right;'>" . number_format($entrance_fee, 2) . " บาท</td>
                                            </tr>
                                            <tr>
                                                <td style='padding: 8px 0; color: #555; border-bottom: 1px dashed #ddd;'>ค่าวิทยากร</td>
                                                <td style='padding: 8px 0; text-align: right; border-bottom: 1px dashed #ddd;'>" . number_format($instructor_fee, 2) . " บาท</td>
                                            </tr>
                                            <tr>
                                                <td style='padding: 8px 0; color: #555; border-bottom: 1px dashed #ddd;'>ค่าสถานที่</td>
                                                <td style='padding: 8px 0; text-align: right; border-bottom: 1px dashed #ddd;'>" . number_format($venue_fee, 2) . " บาท</td>
                                            </tr>
                                            <tr style='font-weight: bold;'>
                                                <td style='padding: 12px 0 8px;'>ยอดรวมทั้งหมด</td>
                                                <td style='padding: 12px 0 8px; text-align: right;'>" . number_format($total_amount, 2) . " บาท</td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div style='text-align: center; background-color: #f0fff8; border: 1px solid #b7eb8f; padding: 15px; border-radius: 8px;'><span style='font-size: 16px; color: #333;'>ยอดมัดจำที่ต้องชำระ (30%):</span><br><span style='font-size: 24px; color: #c0392b; font-weight: bold;'>" . $deposit_formatted . " บาท</span></div>

                                    <div style='text-align: center; margin: 30px 0; padding: 20px; background-color: #f9f9f9; border-radius: 8px;'>
                                        <p style='margin-bottom: 15px; font-weight: bold; color: #016A70;'>สแกนเพื่อชำระเงิน</p>
                                        <img src='cid:qrcode_deposit' alt='QR Code' style='width: 200px; height: 200px; display: block; margin: 0 auto;'>
                                    </div>

                                    <p style='font-size: 15px; line-height: 1.6; color: #555;'>
                                        หลังจากชำระเงินเรียบร้อยแล้ว กรุณาอัปโหลดหลักฐานการชำระเงิน (สลิป) ในหน้าตรวจสอบการจองบนเว็บไซต์ เพื่อให้เจ้าหน้าที่ดำเนินการยืนยันในขั้นตอนต่อไป
                                    </p>
                                    
                                    <p style='background-color: #fff5f5; border-left: 4px solid #c0392b; padding: 10px 15px; color: #c0392b; font-size: 14px;'>
                                        <strong>สำคัญ:</strong> กรุณาชำระเงินภายใน 3 วัน มิฉะนั้นการจองของท่านจะถูกยกเลิกโดยอัตโนมัติ
                                    </p>

                                    <hr style='border: 0; border-top: 1px solid #eeeeee; margin: 30px 0;'>

                                    <p style='text-align: center; color: #555; font-size: 15px; line-height: 1.6;'>
                                        ท่านสามารถกดปุ่มด้านล่างเพื่อไปยังหน้าการจอง และทำการแนบสลิปของท่าน
                                    </p>
                                    <div style='text-align: center; margin: 25px 0;'><a href='{$upload_link}' style='background-color: #016A70; 
                                    color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>แนบสลิปการโอนเงิน</a></div>
                                    <p style='text-align: center; color: #888888; font-size: 13px; line-height: 1.5;'>
                                        ขอขอบคุณที่ไว้วางใจให้เราดูแล<br>
                                        <strong>สวนลุงเผือก</strong>
                                    </p>
                                </div>
                            </div>";

                    if ($mail->send()) {
                        // Update status to 'awaiting_payment' after sending QR
                        $stmt_update = $conn->prepare("UPDATE bookings SET payment_qr_path = ?, status = 'awaiting_payment', updated_at = NOW() WHERE bookings_id = ?");
                        if ($stmt_update) {
                            $stmt_update->bind_param("si", $saved_qr['filename'], $id);
                            $stmt_update->execute();
                            $stmt_update->close();
                        }

                        $response = ['success' => true, 'message' => 'ส่งอีเมลพร้อม QR Code สำเร็จ'];
                    } else {
                        if (!empty($saved_qr['path']) && is_file($saved_qr['path'])) {
                            unlink($saved_qr['path']);
                        }
                        $response = ['success' => false, 'message' => 'ไม่สามารถส่งอีเมลได้: ' . $mail->ErrorInfo];
                    }
                } catch (Exception $e) {
                    if (!empty($saved_qr['path']) && is_file($saved_qr['path'])) {
                        unlink($saved_qr['path']);
                    }
                    $response = ['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
                    error_log("QR Mailer Error for booking ID $id: " . $e->getMessage());
                }
            } else {
                $response = ['success' => false, 'message' => 'ไม่พบข้อมูลการจอง หรือไม่มีอีเมลลูกค้า'];
            }
        } else {
            $response = ['success' => false, 'message' => 'กรุณาแนบไฟล์ QR Code'];
        }
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $id = (int) $_POST['id'];
    $success = false;
    $rejection_reason = $_POST['reason'] ?? null;

    if ($action === 'confirm') {
        $stmt = $conn->prepare("UPDATE bookings SET status='confirmed', updated_at=NOW() WHERE bookings_id=?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();

        if ($success) {
            // ดึงข้อมูลการจองเพื่อส่งอีเมล
            $stmt_details = $conn->prepare("SELECT guest_name, guest_email, booking_code, booking_date, booking_time, visitor_count, booking_type, lunch_request, price_total, deposit_amount, balance_amount FROM bookings WHERE bookings_id = ?");
            $stmt_details->bind_param("i", $id);
            $stmt_details->execute();
            $booking = $stmt_details->get_result()->fetch_assoc();
            $stmt_details->close();

            if ($booking && !empty($booking['guest_email'])) {
                try {
                    $userMail = new PHPMailer(true);
                    // Enable SMTP debug output to PHP error log for troubleshooting
                    $userMail->SMTPDebug = 2;
                    $userMail->Debugoutput = 'error_log';
                    $userMail->isSMTP();
                    $userMail->SMTPOptions = [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        ]
                    ];
                    $userMail->Host       = "smtp.gmail.com";
                    $userMail->SMTPAuth   = true;
                    $userMail->Username   = "nanoone342@gmail.com";
                    $userMail->Password   = "yaud bhqb pibw lipz"; // App Password
                    $userMail->Port       = 465;
                    $userMail->SMTPSecure = "ssl";
                    $userMail->CharSet    = 'UTF-8';

                    $userMail->setFrom('nanoone342@gmail.com', 'สวนลุงเผือก');
                    $userMail->addAddress($booking['guest_email'], $booking['guest_name']);

                    $userMail->isHTML(true);
                    $userMail->Subject = "หนังสือยืนยันการจองเข้าชมสวน (Booking Confirmation) - รหัส: " . $booking['booking_code'];

                    $thai_date = date('d/m/', strtotime($booking['booking_date'])) . (date('Y', strtotime($booking['booking_date'])) + 543);
                    $booking_time_formatted = date('H:i', strtotime($booking['booking_time']));
                    $booking_type_thai = $booking['booking_type'] === 'organization' ? 'หน่วยงาน/องค์กร' : 'บุคคลทั่วไป';
                    $lunch_request_text = $booking['lunch_request'] == 1 ? '✅ ต้องการ' : '❌ ไม่ต้องการ';

                    // --- คำนวณค่าใช้จ่ายใหม่เพื่อความถูกต้อง (รวมค่าสถานที่) ---
                    $visitor_count = (int)($booking['visitor_count'] ?? 1);
                    $price_per_person = 150;
                    $instructor_fee = 1800;
                    $venue_fee = 3000;
                    $entrance_fee = $visitor_count * $price_per_person;
                    $total_amount_recalculated = $entrance_fee + $instructor_fee + $venue_fee;
                    $deposit_amount_paid = (float)$booking['deposit_amount'];
                    $balance_recalculated = $total_amount_recalculated - $deposit_amount_paid;

                    $price_total_formatted = number_format($total_amount_recalculated, 2);
                    $deposit_formatted = number_format($deposit_amount_paid, 2);
                    $balance_formatted = number_format($balance_recalculated, 2);


                    $userMail->Body = "
                                    <div style='font-family: \"Sarabun\", \"Kanit\", sans-serif; padding: 20px; background-color: #f4f7f6; line-height: 1.7;'>
                                        <div style='max-width: 650px; margin: auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); border-top: 5px solid #016A70;'>
                                            
                                            <div style='text-align: center; margin-bottom: 25px;'>
                                                <h1 style='color: #016A70; margin: 0; font-size: 26px; font-weight: 600;'>หนังสือยืนยันการจอง</h1>
                                                <p style='color: #555; font-size: 15px; margin-top: 5px;'>Booking Confirmation</p>
                                            </div>

                                            <p style='color: #333; font-size: 16px;'>เรียน คุณ " . htmlspecialchars($booking['guest_name']) . ",</p>
                                            
                                            <p style='color: #333; font-size: 16px; text-indent: 2em;'>ตามที่ท่านได้ดำเนินการจองคิวเข้าชมสวนและชำระเงินมัดจำเรียบร้อยแล้วนั้น ทางสวนฯ มีความยินดีที่จะยืนยันว่าการจองของท่านเสร็จสมบูรณ์ โดยมีรายละเอียดดังต่อไปนี้:</p>
                                            
                                            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 30px 0; text-align: center;'>
                                                <p style='margin: 0; font-size: 15px; color: #6c757d; text-transform: uppercase; letter-spacing: 1px;'>รหัสอ้างอิงการจอง</p>
                                                <p style='font-size: 32px; font-weight: bold; color: #016A70; margin: 8px 0 0 0; letter-spacing: 3px;'>" . htmlspecialchars($booking['booking_code']) . "</p>
                                            </div>

                                            <h3 style='color: #016A70; border-bottom: 2px solid #f1f1f1; padding-bottom: 10px; margin-top: 30px; font-size: 20px;'>สรุปข้อมูลการจอง</h3>
                                            
                                            <table style='width: 100%; border-collapse: collapse; margin-top: 15px; color: #333; font-size: 15px;'>
                                                <tr>
                                                    <td style='padding: 10px 0; font-weight: 500; width: 40%; border-bottom: 1px solid #eee;'>วันที่เข้าชม:</td>
                                                    <td style='padding: 10px 0; font-weight: 600; border-bottom: 1px solid #eee;'>" . $thai_date . "</td>
                                                </tr>
                                                <tr>
                                                    <td style='padding: 10px 0; font-weight: 500; border-bottom: 1px solid #eee;'>เวลาโดยประมาณ:</td>
                                                    <td style='padding: 10px 0; font-weight: 600; border-bottom: 1px solid #eee;'>" . $booking_time_formatted . " น.</td>
                                                </tr>
                                                <tr>
                                                    <td style='padding: 10px 0; font-weight: 500; border-bottom: 1px solid #eee;'>จำนวนผู้เข้าชม:</td>
                                                    <td style='padding: 10px 0; font-weight: 600; border-bottom: 1px solid #eee;'>" . htmlspecialchars($booking['visitor_count']) . " ท่าน</td>
                                                </tr>
                                                <tr>
                                                    <td style='padding: 10px 0; font-weight: 500; border-bottom: 1px solid #eee;'>ประเภทการจอง:</td>
                                                    <td style='padding: 10px 0; font-weight: 600; border-bottom: 1px solid #eee;'>" . $booking_type_thai . "</td>
                                                </tr>
                                                <tr>
                                                    <td style='padding: 10px 0; font-weight: 500;'>บริการอาหารกลางวัน:</td>
                                                    <td style='padding: 10px 0; font-weight: 600;'>" . $lunch_request_text . "</td>
                                                </tr>
                                            </table>

                                            <div style='margin-top: 25px; padding-top: 15px; border-top: 2px solid #f1f1f1;'>
                                                <table style='width: 100%; color: #333; font-size: 15px;'>
                                                    <tr>
                                                        <td style='padding: 6px 0;'>ค่าเข้าชม (" . $visitor_count . " คน):</td>
                                                        <td style='padding: 6px 0; text-align: right; font-weight: 500;'>" . number_format($entrance_fee, 2) . " บาท</td>
                                                    </tr>
                                                    <tr>
                                                        <td style='padding: 6px 0; border-bottom: 1px dashed #ddd;'>ค่าวิทยากร:</td>
                                                        <td style='padding: 6px 0; text-align: right; font-weight: 500; border-bottom: 1px dashed #ddd;'>" . number_format($instructor_fee, 2) . " บาท</td>
                                                    </tr>
                                                    <tr>
                                                        <td style='padding: 6px 0;'>ค่าสถานที่:</td>
                                                        <td style='padding: 6px 0; text-align: right; font-weight: 500;'>" . number_format($venue_fee, 2) . " บาท</td>
                                                    </tr>
                                                    <tr>
                                                        <td style='padding: 10px 0; font-weight: bold;'>ยอดรวมทั้งหมด:</td>
                                                        <td style='padding: 10px 0; text-align: right; font-weight: bold;'>" . $price_total_formatted . " บาท</td>
                                                    </tr>
                                                    <tr>
                                                        <td style='padding: 6px 0;'>จำนวนเงินมัดจำที่ชำระแล้ว:</td>
                                                        <td style='padding: 6px 0; text-align: right; font-weight: 500; color: #27ae60;'>- " . $deposit_formatted . " บาท</td>
                                                    </tr>
                                                    <tr style='border-top: 2px solid #ccc;'>
                                                        <td style='padding: 10px 0; font-weight: bold; font-size: 16px;'>ยอดคงเหลือชำระ ณ วันเข้าชม:</td>
                                                        <td style='padding: 10px 0; text-align: right; font-weight: bold; color: #c0392b; font-size: 18px;'>" . $balance_formatted . " บาท</td>
                                                    </tr>
                                                </table>
                                            </div>

                                            <div style='margin-top: 30px; padding: 15px; background-color: #eaf2f8; border-left: 4px solid #3498db; border-radius: 4px;'>
                                                <p style='margin: 0; font-size: 15px; color: #2874a6;'>
                                                    <strong>หมายเหตุ:</strong> กรุณาแสดงรหัสการจองหรืออีเมลฉบับนี้แก่เจ้าหน้าที่ ณ จุดลงทะเบียนเมื่อเดินทางมาถึง
                                                </p>
                                            </div>

                                            <div style='margin-top: 40px; text-align: center; color: #888; font-size: 14px;'>
                                                <p style='margin: 0;'>ขอแสดงความนับถือ</p>
                                                <p style='margin-top: 5px; color: #333; font-weight: 500;'>ฝ่ายบริการลูกค้า สวนลุงเผือก</p>
                                                <hr style='border: 0; border-top: 1px solid #eee; margin: 25px 0;'>
                                                <p style='font-size: 12px;'>หากท่านมีข้อสงสัยประการใด สามารถติดต่อสอบถามเพิ่มเติมได้ที่เบอร์โทรศัพท์ 065-107-8576 <br> หรือตอบกลับอีเมลฉบับนี้</p>
                                            </div>
                                        </div>
                                    </div>
                                ";
                    $userMail->send();
                } catch (Exception $e) {
                    error_log("Confirmation Mailer Error for booking ID $id: " . $e->getMessage());
                }
            }
        }
    } elseif ($action === 'cancel') {
        $stmt = $conn->prepare("UPDATE bookings SET status='cancelled', updated_at=NOW() WHERE bookings_id=?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();

        if ($success) {
            // ดึงข้อมูลการจองเพื่อส่งอีเมลแจ้งยกเลิก
            $stmt_details = $conn->prepare("SELECT guest_name, guest_email, booking_code, booking_date FROM bookings WHERE bookings_id = ?");
            $stmt_details->bind_param("i", $id);
            $stmt_details->execute();
            $booking = $stmt_details->get_result()->fetch_assoc();
            $stmt_details->close();

            if ($booking && !empty($booking['guest_email'])) {
                try {
                    $cancelMail = new PHPMailer(true);
                    // Enable SMTP debug output to PHP error log for troubleshooting
                    $cancelMail->SMTPDebug = 2;
                    $cancelMail->Debugoutput = 'error_log';
                    $cancelMail->isSMTP();
                    $cancelMail->SMTPOptions = [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        ]
                    ];
                    $cancelMail->Host       = "smtp.gmail.com";
                    $cancelMail->SMTPAuth   = true;
                    $cancelMail->Username   = "nanoone342@gmail.com";
                    $cancelMail->Password   = "yaud bhqb pibw lipz"; // App Password
                    $cancelMail->Port       = 465;
                    $cancelMail->SMTPSecure = "ssl";
                    $cancelMail->CharSet    = 'UTF-8';

                    $cancelMail->setFrom('nanoone342@gmail.com', 'สวนลุงเผือก');
                    $cancelMail->addAddress($booking['guest_email'], $booking['guest_name']);

                    $cancelMail->isHTML(true);
                    $cancelMail->Subject = "แจ้งยกเลิกการจอง";
                    $thai_date = date('d/m/', strtotime($booking['booking_date'])) . (date('Y', strtotime($booking['booking_date'])) + 543);

                    $cancelMail->Body = "
                                    <div style='font-family: \"Sarabun\", \"Kanit\", sans-serif; padding: 20px; background-color: #f4f7f6; line-height: 1.7;'>
                                        <div style='max-width: 650px; margin: auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); border-top: 5px solid #e74a3b;'>
                                            
                                            <div style='text-align: center; margin-bottom: 25px;'>
                                                <h1 style='color: #c0392b; margin: 0; font-size: 26px; font-weight: 600;'>แจ้งยกเลิกการจอง</h1>
                                                <p style='color: #555; font-size: 15px; margin-top: 5px;'>Booking Cancellation Notice</p>
                                            </div>

                                            <p style='color: #333; font-size: 16px;'>เรียน คุณ " . htmlspecialchars($booking['guest_name']) . ",</p>
                                            
                                            <p style='color: #333; font-size: 16px; text-indent: 2em;'>ทางสวนลุงเผือกมีความเสียใจที่ต้องแจ้งให้ท่านทราบว่า รายการจองของท่านสำหรับวันที่ <strong>" . $thai_date . "</strong>) ได้ถูกยกเลิกแล้ว</p>
                                            
                                            <div style='margin: 30px 0; padding: 20px; background-color: #fffbe6; border-left: 4px solid #f59e0b; border-radius: 4px;'>
                                                <h4 style='margin-top: 0; color: #d35400; font-size: 16px;'>เหตุผลในการยกเลิก:</h4> 
                                                <p style='margin-bottom: 0; color: #854d0e; font-size: 15px;'>" . (!empty($rejection_reason) ? nl2br(htmlspecialchars($rejection_reason)) : 'ไม่ระบุ') . "</p>
                                            </div>

                                            <p style='color: #333; font-size: 16px;'>หากท่านมีข้อสงสัย หรือต้องการดำเนินการจองใหม่อีกครั้ง กรุณาติดต่อเจ้าหน้าที่โดยตรงที่เบอร์โทรศัพท์ 065-107-8576 หรือตอบกลับอีเมลฉบับนี้</p>
                                            <p style='color: #333; font-size: 16px;'>ทางเราต้องขออภัยในความไม่สะดวกมา ณ ที่นี้</p>
                                            
                                            <div style='margin-top: 40px; text-align: center; color: #888; font-size: 14px;'>
                                                <p style='margin: 0;'>ขอแสดงความนับถือ</p>
                                                <p style='margin-top: 5px; color: #333; font-weight: 500;'>ฝ่ายบริการลูกค้า สวนลุงเผือก</p>
                                            </div>
                                        </div>
                                    </div>
                                ";

                    $cancelMail->send();
                } catch (Exception $e) {
                    error_log("Cancellation Mailer Error for booking ID $id: " . $e->getMessage());
                }
            }
        }
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM bookings WHERE bookings_id=?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'action' => $action, 'id' => $id]);
    exit;
}

// ดึงข้อมูลการจองทั้งหมด
$result = $conn->query("SELECT * FROM bookings ORDER BY bookings_id DESC");
$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

// นับจำนวนสถานะต่างๆ สำหรับ Stats Card
$stats = [
    'pending' => 0,
    'awaiting_payment' => 0,
    'awaiting_slip_check' => 0,
    'confirmed' => 0,
    'cancelled' => 0,
    'total' => count($bookings)
];
foreach ($bookings as $b) {
    // Check for awaiting slip check first
    if (($b['status'] === 'pending' || $b['status'] === 'awaiting_payment') && !empty($b['payment_slip'])) {
        $stats['awaiting_slip_check']++;
    } elseif ($b['status'] === 'pending') {
        $stats['pending']++;
    } elseif ($b['status'] === 'awaiting_payment') {
        $stats['awaiting_payment']++;
    } elseif ($b['status'] === 'confirmed') {
        $stats['confirmed']++;
    } elseif ($b['status'] === 'cancelled') {
        $stats['cancelled']++;
    }
}
$adminPageExtraHead = <<<HTML
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
HTML;
adminPageStart('จัดการรายการจอง');
?>
<style>
    * {
        box-sizing: border-box;
    }

    :root {
        --green: #016A70;
        --green-dark: #01545a;
        --green-light: #0d8a92;
        --green-soft: rgba(1, 106, 112, 0.08);

        --white: #ffffff;
        --bg: #f4f8f9;
        --bg-soft: #f8fafc;

        --text: #0f172a;
        --text-soft: #64748b;

        --border: #e2e8f0;

        --danger: #ef4444;

        --shadow-sm: 0 4px 12px rgba(15, 23, 42, 0.04);
        --shadow-md: 0 12px 30px rgba(15, 23, 42, 0.08);
        --shadow-lg: 0 20px 45px rgba(15, 23, 42, 0.10);

        --radius-lg: 28px;
        --radius-md: 20px;
        --radius-sm: 14px;
    }

    .page-content.booking-list-page {
        background: linear-gradient(180deg, #ffffff 0%, #f7fbfb 100%);
        color: var(--text);
    }

    .booking-list-shell {
        width: 100%;
    }

    .modern-sidebar .nav {
        display: flex;
        flex-direction: column;
        flex-wrap: nowrap;
        gap: 10px;
        flex: 1;
        padding-left: 0;
        margin-bottom: 0;
        list-style: none;
    }

    .modern-sidebar .nav-link {
        position: relative;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 15px 18px;
        border-radius: 18px;
        color: var(--text-soft);
        text-decoration: none;
        font-size: 15px;
        font-weight: 500;
        transition: .25s ease;
        overflow: hidden;
    }

    .modern-sidebar .nav-link.active {
        background: var(--green);
        color: var(--white);
        box-shadow: 0 10px 24px rgba(1, 106, 112, .18);
    }

    .dashboard-header {
        background: linear-gradient(120deg, var(--green), var(--green-dark));
        color: white;
        padding: 1rem;
        box-shadow: 0 4px 12px rgba(1, 106, 112, 0.18);
        border-radius: 50px;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 576px) {
        .dashboard-header {
            border-radius: 20px;
            padding: 0.75rem;
            margin-bottom: 1rem;
        }

        .dashboard-title {
            font-size: 1.25rem;
        }
    }

    .admin-profile {
        display: flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        padding: 0.5rem 1rem;
        border-radius: 50px;
    }

    @media (max-width: 576px) {
        .admin-profile {
            padding: 0.3rem 0.8rem;
        }

        .admin-profile span {
            font-size: 0.85rem;
        }
    }

    .admin-profile img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        margin-right: 10px;
        border: 2px solid rgba(255, 255, 255, 0.5);
    }

    @media (max-width: 576px) {
        .admin-profile img {
            width: 28px;
            height: 28px;
        }
    }

    .booking-card {
        background: var(--white);
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
        overflow: hidden;
        margin-bottom: 1.5rem;
        border: none;
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .booking-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .booking-card::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, rgba(1, 106, 112, 0.18), rgba(13, 138, 146, 0.18));
    }

    .booking-card-header {
        padding: 1rem 1.5rem;
        background: linear-gradient(90deg, rgba(1, 106, 112, 0.05), transparent);
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    @media (max-width: 576px) {
        .booking-card-header {
            padding: 0.75rem 1rem;
        }

        .booking-card .p-4 {
            padding: 1rem !important;
        }
    }

    .status-badge {
        padding: 0.35rem 0.8rem;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    @media (max-width: 576px) {
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.6rem;
        }
    }

    .status-pending {
        background: rgba(245, 158, 11, 0.14);
        color: #92400e;
    }

    .status-รอตรวจสอบสลิป {
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
    }

    .status-awaiting_payment {
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
    }

    .status-confirmed {
        background: rgba(22, 163, 74, 0.12);
        color: #166534;
    }

    .status-cancelled {
        background: rgba(239, 68, 68, 0.12);
        color: #991b1b;
    }

    .info-label {
        font-weight: 500;
        color: #6c757d;
        min-width: 90px;
        display: inline-block;
        font-size: 0.9rem;
    }

    @media (max-width: 576px) {
        .info-label {
            min-width: 70px;
            font-size: 0.85rem;
        }

        .info-value {
            font-size: 0.85rem;
        }
    }

    .info-value {
        color: #2d3436;
        font-weight: 400;
    }

    .action-btn {
        border-radius: 50px;
        padding: 0.5rem 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }

    @media (max-width: 576px) {
        .action-btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
        }

        .action-btn i {
            font-size: 0.75rem;
        }
    }

    .btn-confirm {
        background: rgba(46, 204, 113, 0.1);
        color: #27ae60;
    }

    .btn-confirm:hover {
        background: #27ae60;
        color: white;
    }

    .btn-cancel {
        background: rgba(231, 76, 60, 0.1);
        color: #c0392b;
    }

    .btn-cancel:hover {
        background: #c0392b;
        color: white;
    }

    .btn-info {
        background: rgba(54, 185, 204, 0.1);
        color: #36b9cc;
    }

    .btn-info:hover {
        background: #36b9cc;
        color: white;
    }

    .stats-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        padding: 1.25rem;
        text-align: center;
        margin-bottom: 1rem;
        border: none;
        height: 100%;
    }

    @media (max-width: 576px) {
        .stats-card {
            padding: 0.75rem;
        }

        .stats-value {
            font-size: 1.25rem !important;
        }

        .stats-icon {
            font-size: 1.5rem !important;
        }

        .stats-card .text-muted {
            font-size: 0.75rem;
        }
    }

    .stats-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .stats-value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .slip-image-container {
        height: 150px;
        background-color: #f0f2f5;
        border-radius: 8px;
    }

    @media (max-width: 576px) {
        .slip-image-container {
            height: 120px;
        }
    }

    /* Filter Buttons - Responsive */
    .filter-btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .filter-btn-group {
            gap: 0.5rem;
        }

        .filter-btn-group .btn {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            white-space: nowrap;
        }
    }

    @media (max-width: 576px) {
        .filter-btn-group .btn {
            font-size: 0.7rem;
            padding: 0.35rem 0.6rem;
        }

        .filter-btn-group .btn i {
            font-size: 0.7rem;
        }

        .filter-btn-group .badge {
            font-size: 0.65rem;
        }
    }

    /* Responsive Grid */
    @media (max-width: 1200px) {
        .booking-item-col {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    @media (max-width: 768px) {
        .booking-item-col {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }

    /* Modal Responsive */
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 0.5rem;
        }

        .modal-body {
            padding: 1rem !important;
        }

        .modal-header h5 {
            font-size: 1rem;
        }
    }

    /* Form Responsive */
    @media (max-width: 576px) {
        .input-group {
            flex-direction: column;
        }

        .input-group-text {
            border-radius: 8px 8px 0 0 !important;
        }

        .form-control-lg {
            border-radius: 0 0 8px 8px !important;
            font-size: 0.9rem;
        }
    }

    /* File Upload Area Responsive */
    .file-upload-area-modern {
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        background-color: #f8fafc;
        cursor: pointer;
        transition: all 0.3s;
    }

    @media (max-width: 576px) {
        .file-upload-area-modern {
            padding: 1rem;
        }

        .file-upload-icon {
            font-size: 2rem !important;
        }

        .file-upload-area-modern div {
            font-size: 0.85rem;
        }
    }

    .file-upload-area-modern:hover,
    .file-upload-area-modern.dragover {
        border-color: var(--primary);
        background-color: rgba(67, 97, 238, 0.05);
    }

    .file-upload-icon {
        font-size: 3rem;
        color: var(--primary);
        margin-bottom: 1rem;
    }

    .qr-preview-wrapper {
        position: relative;
        display: inline-block;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.5rem;
        background-color: #f8fafc;
        width: 100%;
        max-width: 300px;
    }

    @media (max-width: 576px) {
        .qr-preview-wrapper {
            max-width: 100%;
        }
    }

    .qr-remove-btn {
        position: absolute;
        top: -12px;
        right: -12px;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.95);
        display: none;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        backdrop-filter: blur(5px);
    }

    .loading-overlay.active {
        display: flex;
    }

    /* Responsive Typography */
    @media (max-width: 576px) {
        h2 {
            font-size: 1.25rem;
        }

        .text-muted {
            font-size: 0.75rem;
        }
    }

    /* Container padding adjustment */
    @media (max-width: 768px) {
        .p-4 {
            padding: 1rem !important;
        }
    }

    /* Success Checkmark Animation */
    .success-checkmark {
        width: 80px;
        height: 80px;
        margin: 0 auto;
        margin-bottom: 20px;
    }

    @media (max-width: 576px) {
        .success-checkmark {
            width: 60px;
            height: 60px;
        }
    }

    .success-checkmark .check-icon {
        width: 80px;
        height: 80px;
        position: relative;
        border-radius: 50%;
        box-sizing: content-box;
        border: 4px solid var(--success);
    }

    @media (max-width: 576px) {
        .success-checkmark .check-icon {
            width: 60px;
            height: 60px;
        }
    }

    .success-checkmark .check-icon .icon-line.line-tip {
        top: 46px;
        left: 14px;
        width: 25px;
        transform: rotate(45deg);
        animation: icon-line-tip 0.75s;
    }

    .success-checkmark .check-icon .icon-line.line-long {
        top: 38px;
        right: 8px;
        width: 47px;
        transform: rotate(-45deg);
        animation: icon-line-long 0.75s;
    }

    @keyframes icon-line-tip {
        0% {
            width: 0;
            left: 1px;
            top: 19px;
        }

        54% {
            width: 0;
            left: 1px;
            top: 19px;
        }

        70% {
            width: 50px;
            left: -8px;
            top: 37px;
        }

        84% {
            width: 17px;
            left: 21px;
            top: 48px;
        }

        100% {
            width: 25px;
            left: 14px;
            top: 45px;
        }
    }

    @keyframes icon-line-long {
        0% {
            width: 0;
            right: 46px;
            top: 54px;
        }

        65% {
            width: 0;
            right: 46px;
            top: 54px;
        }

        84% {
            width: 55px;
            right: 0px;
            top: 35px;
        }

        100% {
            width: 47px;
            right: 8px;
            top: 38px;
        }
    }

    /* Scrollbar Styling */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--secondary);
    }
</style>
<script>
    document.querySelector('.page-content')?.classList.add('booking-list-page');
</script>

<div class="booking-list-shell">
    

    <!-- Stats -->
    <div class="row g-2 g-md-3 mb-3 mb-md-4">
        <div class="col-6 col-sm-3">
            <div class="stats-card">
                <div class="stats-icon text-primary"><i class="bi bi-list-ul"></i></div>
                <div class="stats-value"><?= $stats['total'] ?></div>
                <div class="text-muted">ทั้งหมด</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="stats-card">
                <div class="stats-icon text-warning"><i class="bi bi-clock-history"></i></div>
                <div class="stats-value"><?= $stats['pending'] + $stats['awaiting_payment'] + $stats['awaiting_slip_check'] ?></div>
                <div class="text-muted">รอยืนยัน</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="stats-card">
                <div class="stats-icon text-success"><i class="bi bi-check-circle"></i></div>
                <div class="stats-value"><?= $stats['confirmed'] ?></div>
                <div class="text-muted">ยืนยันแล้ว</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="stats-card">
                <div class="stats-icon text-danger"><i class="bi bi-x-circle"></i></div>
                <div class="stats-value"><?= $stats['cancelled'] ?></div>
                <div class="text-muted">ยกเลิกแล้ว</div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="mb-3 mb-md-4 text-center py-2 py-md-3 bg-white rounded-3 shadow-sm">
        <div class="filter-btn-group" role="group">
            <button type="button" class="btn btn-light filter-btn active" data-filter="all">
                <i class="bi bi-collection me-1"></i> ทั้งหมด
                <span class="badge rounded-pill bg-secondary ms-1"><?= $stats['total'] ?></span>
            </button>
            <button type="button" class="btn btn-light filter-btn" data-filter="pending">
                <i class="bi bi-hourglass-split me-1"></i> รอยืนยัน
                <?php if ($stats['pending'] > 0): ?>
                    <span class="badge rounded-pill bg-warning text-dark ms-1"><?= $stats['pending'] ?></span>
                <?php endif; ?>
            </button>
            <button type="button" class="btn btn-light filter-btn" data-filter="awaiting_payment" title="แสดงรายการที่ส่ง QR Code ให้ลูกค้าแล้ว">
                <i class="bi bi-qr-code me-1"></i> ส่ง QR แล้ว
                <?php if ($stats['awaiting_payment'] > 0): ?>
                    <span class="badge rounded-pill ms-1" style="background-color: #fd7e14; color: white;"><?= $stats['awaiting_payment'] ?></span>
                <?php endif; ?>
            </button>
            <button type="button" class="btn btn-light filter-btn" data-filter="รอตรวจสอบสลิป">
                <i class="bi bi-receipt me-1"></i> รอสลิป
                <?php if ($stats['awaiting_slip_check'] > 0): ?>
                    <span class="badge rounded-pill bg-primary ms-1"><?= $stats['awaiting_slip_check'] ?></span>
                <?php endif; ?>
            </button>
            <button type="button" class="btn btn-light filter-btn" data-filter="confirmed">
                <i class="bi bi-check2-circle me-1"></i> ยืนยันแล้ว
                <?php if ($stats['confirmed'] > 0): ?>
                    <span class="badge rounded-pill bg-success ms-1"><?= $stats['confirmed'] ?></span>
                <?php endif; ?>
            </button>
            <button type="button" class="btn btn-light filter-btn" data-filter="cancelled">
                <i class="bi bi-x-circle me-1"></i> ยกเลิกแล้ว
                <?php if ($stats['cancelled'] > 0): ?>
                    <span class="badge rounded-pill bg-danger ms-1"><?= $stats['cancelled'] ?></span>
                <?php endif; ?>
            </button>
        </div>
    </div>

    <!-- Search Input -->
    <div class="mb-3 mb-md-4">
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
            <input type="text" id="searchInput" class="form-control form-control-lg border-start-0" placeholder="ค้นหาด้วยชื่อ หรือรหัสอ้างอิง...">
        </div>
    </div>

    <!-- Booking List -->
    <div class="row g-3" id="bookingList">
        <?php if (empty($bookings)): ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="mt-3 text-muted">ไม่พบข้อมูลการจอง</p>
            </div>
        <?php else: ?>
            <div class="col-12 text-center py-5 d-none" id="noResultsMessage">
                <i class="bi bi-search fs-1 text-muted"></i>
                <p class="mt-3 text-muted">ไม่พบรายการที่ตรงกับการค้นหา</p>
            </div>
            <?php foreach ($bookings as $booking): ?>
                <div class="col-md-6 col-lg-4 booking-item-col">
                    <div class="booking-card">
                        <div class="booking-card-header">
                            <span class="fw-bold text-primary booking-code">#<?= htmlspecialchars($booking['booking_code']) ?></span>
                            <?php
                            $isAwaitingSlipCheck = (($booking['status'] == 'pending' || $booking['status'] == 'awaiting_payment') && !empty($booking['payment_slip']));

                            $display_status_text = '';
                            $display_status_class = $booking['status'];
                            $display_status_icon = 'bi-question-circle';

                            if ($isAwaitingSlipCheck) {
                                $display_status_text = 'รอตรวจสอบสลิป';
                                $display_status_class = 'รอตรวจสอบสลิป';
                                $display_status_icon = 'bi-receipt';
                            } elseif ($booking['status'] == 'pending') {
                                $display_status_text = 'รอยืนยัน';
                                $display_status_icon = 'bi-hourglass-split';
                            } elseif ($booking['status'] == 'awaiting_payment') {
                                $display_status_text = 'ส่ง QR แล้ว';
                                $display_status_icon = 'bi-qr-code';
                            } elseif ($booking['status'] == 'confirmed') {
                                $display_status_text = 'ยืนยันแล้ว';
                                $display_status_icon = 'bi-check-circle-fill';
                            } elseif ($booking['status'] == 'cancelled') {
                                $display_status_text = 'ยกเลิกแล้ว';
                                $display_status_icon = 'bi-x-circle-fill';
                            } else {
                                $display_status_text = htmlspecialchars($booking['status']);
                            }
                            ?>
                            <span class="status-badge status-<?= $display_status_class ?>">
                                <i class="bi <?= $display_status_icon ?> me-1"></i> <?= $display_status_text ?>
                            </span>
                        </div>
                        <div class="p-4">
                            <div class="mb-2">
                                <span class="info-label"><i class="bi bi-person me-2"></i>ลูกค้า:</span>
                                <span class="info-value guest-name"><?= htmlspecialchars($booking['guest_name']) ?></span>
                            </div>
                            <div class="mb-2">
                                <span class="info-label"><i class="bi bi-calendar-event me-2"></i>วันที่:</span>
                                <span class="info-value"><?= date('d/m/Y', strtotime($booking['booking_date'])) ?></span>
                            </div>
                            <div class="mb-2">
                                <span class="info-label"><i class="bi bi-clock me-2"></i>เวลา:</span>
                                <span class="info-value"><?= date('H:i', strtotime($booking['booking_time'])) ?> น.</span>
                            </div>
                            <div class="mb-2">
                                <span class="info-label"><i class="bi bi-people me-2"></i>จำนวน:</span>
                                <span class="info-value"><?= $booking['visitor_count'] ?> ท่าน</span>
                            </div>
                            <div class="mb-3">
                                <span class="info-label"><i class="bi bi-currency-dollar me-2"></i>ยอดรวม:</span>
                                <span class="info-value fw-bold text-dark">฿<?= number_format($booking['price_total'], 2) ?></span>
                            </div>

                            <div class="mb-3">
                                <span class="info-label d-block mb-2"><i class="bi bi-receipt me-2"></i>สลิป:</span>
                                <?php if (!empty($booking['payment_slip'])): ?>
                                    <a href="../user/Paymentslip-Gardenreservation/<?= htmlspecialchars($booking['payment_slip']) ?>" target="_blank">
                                        <img src="../user/Paymentslip-Gardenreservation/<?= htmlspecialchars($booking['payment_slip']) ?>" class="img-fluid rounded" style="width: 100%; height: 150px; object-fit: cover;" alt="Payment Slip">
                                    </a>
                                <?php else: ?>
                                    <div class="slip-image-container d-flex align-items-center justify-content-center text-muted">
                                        <div class="text-center">
                                            <i class="bi bi-image fs-3"></i><br>ไม่มีสลิป
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <hr class="my-3 opacity-50">

                            <div class="d-flex gap-2 justify-content-end flex-wrap">
                                <?php if ($booking['status'] == 'pending' && !$isAwaitingSlipCheck): ?>
                                    <button class="action-btn btn-info" onclick="showQrCodeModal(<?= $booking['bookings_id'] ?>, '<?= htmlspecialchars(json_encode($booking), ENT_QUOTES, 'UTF-8') ?>')">
                                        <i class="bi bi-qr-code me-1"></i> ส่ง QR
                                    </button>
                                <?php endif; ?>

                                <?php if ($isAwaitingSlipCheck): ?>
                                    <button class="action-btn btn-confirm" onclick="showConfirmationModal(<?= $booking['bookings_id'] ?>, 'confirm', 'ยืนยันการจองนี้ใช่หรือไม่?')">
                                        <i class="bi bi-check2-circle me-1"></i> ยืนยัน
                                    </button>
                                <?php endif; ?>

                                <?php if ($booking['status'] != 'confirmed' && $booking['status'] != 'cancelled'): ?>
                                    <button class="action-btn btn-cancel" onclick="showConfirmationModal(<?= $booking['bookings_id'] ?>, 'cancel', 'ต้องการยกเลิกการจองนี้ใช่หรือไม่?')">
                                        <i class="bi bi-x-lg me-1"></i> ยกเลิก
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-light rounded-pill px-3" onclick="viewDetails(<?= htmlspecialchars(json_encode($booking)) ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modals (same as before) -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">รายละเอียดการจอง</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div id="modalBody" class="modal-body p-4"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="actionConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="actionConfirmModalLabel">ยืนยันการดำเนินการ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4" id="actionConfirmModalBody"></div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="confirmActionButton">ยืนยัน</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelReasonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="cancelReasonModalLabel">ระบุเหตุผลการยกเลิก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <textarea id="cancellationReason" class="form-control" rows="4" placeholder="กรุณาระบุเหตุผล..."></textarea>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" id="confirmCancelButton">ยืนยันการยกเลิก</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="qrCodeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-primary text-white">
                <h5 class="modal-title" id="qrCodeModalLabel"><i class="bi bi-qr-code me-2"></i>ส่ง QR Code สำหรับชำระเงิน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border-primary border-2 border-start">
                    <p class="mb-1"><strong>การจอง:</strong> <span id="qrBookingCode" class="fw-bold"></span></p>
                    <p class="mb-0"><strong>ยอดชำระมัดจำ:</strong> <span id="qrDepositAmount" class="fw-bold text-danger"></span> บาท</p>
                </div>
                <form id="qrCodeForm" class="mt-3">
                    <input type="hidden" name="id" id="qrBookingId">
                    <input type="hidden" name="action" value="send_qr">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <div id="qrUploadArea" class="file-upload-area-modern">
                        <div class="file-upload-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                        <div>ลากไฟล์ QR Code มาวาง หรือคลิกเพื่อเลือก</div>
                        <div class="text-muted small">รองรับ: JPG, PNG, GIF (ไม่เกิน 2MB)</div>
                        <input class="d-none" type="file" id="qrCodeFile" name="qr_code_file" accept="image/png, image/jpeg, image/gif" required>
                    </div>
                    <div id="qrPreviewContainer" class="text-center d-none">
                        <div class="qr-preview-wrapper">
                            <img id="qrPreview" src="#" alt="QR Code Preview" class="img-fluid rounded" style="max-height: 250px; object-fit: contain;">
                            <button type="button" id="removeQrBtn" class="btn btn-danger btn-sm rounded-circle qr-remove-btn" title="ลบไฟล์"><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="sendQrButton">
                    <span class="send-qr-text"><i class="bi bi-send me-2"></i>ส่งอีเมล</span>
                    <span class="send-qr-loading d-none">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        กำลังส่ง...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0" id="statusModalHeader">
                <h5 class="modal-title" id="statusModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4 text-center">
                <div id="statusModalIcon" class="mb-3"></div>
                <p id="statusModalMessage" class="fs-5"></p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-primary px-5" data-bs-dismiss="modal" id="statusModalOkButton">ตกลง</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Initialize Bootstrap Modals
    const confirmModalEl = document.getElementById('actionConfirmModal');
    const confirmModal = new bootstrap.Modal(confirmModalEl);
    const confirmActionButton = document.getElementById('confirmActionButton');
    const statusModalEl = document.getElementById('statusModal');
    const statusModal = new bootstrap.Modal(statusModalEl);
    const detailModalEl = document.getElementById('detailModal');
    const detailModal = new bootstrap.Modal(detailModalEl);
    const qrCodeModalEl = document.getElementById('qrCodeModal');
    const qrCodeModal = new bootstrap.Modal(qrCodeModalEl);

    function handleBookingAction(id, action, reason = null) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('action', action);
        formData.append('csrf_token', '<?= $csrf_token ?>');
        if (reason) {
            formData.append('reason', reason);
        }

        fetch('booking_list.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showStatusModal('สำเร็จ', 'ดำเนินการเรียบร้อยแล้ว', true, true);
                    statusModalEl.addEventListener('hidden.bs.modal', () => {
                        location.reload();
                    }, {
                        once: true
                    });
                } else {
                    showStatusModal('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถดำเนินการได้', false, false);
                }
            })
            .catch(error => {
                showStatusModal('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', false, false);
            });
    }

    function showConfirmationModal(id, action, message) {
        const modalBody = document.getElementById('actionConfirmModalBody');
        const modalLabel = document.getElementById('actionConfirmModalLabel');

        if (action === 'confirm') {
            modalBody.textContent = message;
            modalLabel.textContent = 'ยืนยันการจอง';
            confirmActionButton.className = 'btn btn-success';

            confirmActionButton.onclick = () => {
                confirmModal.hide();
                handleBookingAction(id, action);
            };
            confirmModal.show();
        } else if (action === 'cancel') {
            const cancelReasonModalEl = document.getElementById('cancelReasonModal');
            const cancelReasonModal = new bootstrap.Modal(cancelReasonModalEl);
            const reasonTextarea = document.getElementById('cancellationReason');
            const confirmCancelBtn = document.getElementById('confirmCancelButton');

            reasonTextarea.value = '';

            confirmCancelBtn.onclick = () => {
                const reason = reasonTextarea.value.trim();
                if (!reason) {
                    alert('กรุณาระบุเหตุผลในการยกเลิก');
                    reasonTextarea.focus();
                    return;
                }
                cancelReasonModal.hide();
                handleBookingAction(id, 'cancel', reason);
            };
            cancelReasonModal.show();
        }
    }

    function showStatusModal(title, message, isSuccess = true, showCheckmark = false) {
        const modalLabel = document.getElementById('statusModalLabel');
        const modalMessage = document.getElementById('statusModalMessage');
        const modalIcon = document.getElementById('statusModalIcon');
        const modalHeader = document.getElementById('statusModalHeader');
        const modalOkButton = document.getElementById('statusModalOkButton');

        modalLabel.textContent = title;
        modalMessage.textContent = message;
        modalIcon.innerHTML = '';

        if (isSuccess === 'loading') {
            modalHeader.classList.remove('bg-danger', 'text-white');
            modalHeader.classList.add('bg-light');
            modalIcon.innerHTML = `<div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;"><span class="visually-hidden">Loading...</span></div>`;
            modalOkButton.style.display = 'none';
        } else if (isSuccess) {
            modalHeader.classList.remove('bg-danger', 'text-white');
            modalHeader.classList.add('bg-light');
            if (showCheckmark) {
                modalIcon.innerHTML = `
                        <div class="success-checkmark">
                            <div class="check-icon">
                                <span class="icon-line line-tip"></span>
                                <span class="icon-line line-long"></span>
                                <div class="icon-circle"></div>
                                <div class="icon-fix"></div>
                            </div>
                        </div>
                    `;
                modalOkButton.className = 'btn btn-success px-5';
            } else {
                modalIcon.innerHTML = '<i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>';
                modalOkButton.className = 'btn btn-primary px-5';
            }
        } else {
            modalHeader.classList.remove('bg-light');
            modalHeader.classList.add('bg-danger', 'text-white');
            modalIcon.innerHTML = '<i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>';
            modalOkButton.className = 'btn btn-danger px-5';
        }
        statusModal.show();
    }

    // QR Code Modal Logic
    const qrUploadArea = document.getElementById('qrUploadArea');
    const qrFileInput = document.getElementById('qrCodeFile');
    const qrPreviewContainer = document.getElementById('qrPreviewContainer');
    const qrPreview = document.getElementById('qrPreview');
    const removeQrBtn = document.getElementById('removeQrBtn');
    const sendQrButton = document.getElementById('sendQrButton');

    if (qrUploadArea) {
        qrUploadArea.addEventListener('click', () => qrFileInput.click());
        qrUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            qrUploadArea.classList.add('dragover');
        });
        qrUploadArea.addEventListener('dragleave', () => {
            qrUploadArea.classList.remove('dragover');
        });
        qrUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            qrUploadArea.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                qrFileInput.files = e.dataTransfer.files;
                handleQrFile(qrFileInput.files[0]);
            }
        });
    }

    if (qrFileInput) {
        qrFileInput.addEventListener('change', () => {
            if (qrFileInput.files.length) {
                handleQrFile(qrFileInput.files[0]);
            }
        });
    }

    if (removeQrBtn) {
        removeQrBtn.addEventListener('click', () => {
            qrFileInput.value = '';
            qrPreviewContainer.classList.add('d-none');
            qrUploadArea.classList.remove('d-none');
            qrPreview.src = '#';
        });
    }

    function handleQrFile(file) {
        if (file && file.type.startsWith('image/')) {
            if (file.size > 2 * 1024 * 1024) {
                alert('ไฟล์มีขนาดใหญ่เกิน 2MB');
                qrFileInput.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                qrPreview.src = e.target.result;
                qrUploadArea.classList.add('d-none');
                qrPreviewContainer.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        } else {
            alert('กรุณาเลือกไฟล์รูปภาพเท่านั้น');
            qrFileInput.value = '';
        }
    }

    function showQrCodeModal(id, bookingJson) {
        const booking = JSON.parse(bookingJson);
        document.getElementById('qrBookingId').value = id;
        document.getElementById('qrBookingCode').textContent = '#' + booking.booking_code;
        document.getElementById('qrDepositAmount').textContent = parseFloat(booking.deposit_amount).toLocaleString('th-TH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        const qrForm = document.getElementById('qrCodeForm');
        if (qrForm) qrForm.reset();
        if (removeQrBtn) removeQrBtn.click();
        qrCodeModal.show();
    }

    if (sendQrButton) {
        sendQrButton.addEventListener('click', function() {
            const form = document.getElementById('qrCodeForm');
            const fileInput = document.getElementById('qrCodeFile');
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('กรุณาเลือกไฟล์ QR Code');
                return;
            }
            const formData = new FormData(form);
            qrCodeModal.hide();
            const btnText = this.querySelector('.send-qr-text');
            const btnLoading = this.querySelector('.send-qr-loading');
            this.disabled = true;
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            fetch('booking_list.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showStatusModal('สำเร็จ', 'ส่งอีเมลพร้อม QR Code เรียบร้อยแล้ว', true, true);
                        statusModalEl.addEventListener('hidden.bs.modal', () => {
                            window.location.reload();
                        }, {
                            once: true
                        });
                    } else {
                        showStatusModal('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถส่งอีเมลได้', false);
                    }
                })
                .catch(error => {
                    showStatusModal('เกิดข้อผิดพลาด', 'การเชื่อมต่อล้มเหลว: ' + error.message, false);
                })
                .finally(() => {
                    this.disabled = false;
                    btnText.classList.remove('d-none');
                    btnLoading.classList.add('d-none');
                });
        });
    }

    function translateBookingType(type) {
        const types = {
            'private': 'บุคคลทั่วไป',
            'organization': 'หน่วยงาน/องค์กร'
        };
        return types[type] || type;
    }

    function translateStatus(status) {
        const statuses = {
            'pending': 'รอยืนยัน',
            'awaiting_payment': 'ส่ง QR Code แล้ว',
            'confirmed': 'ยืนยันแล้ว',
            'cancelled': 'ยกเลิกแล้ว'
        };
        return statuses[status] || status;
    }

    function viewDetails(data) {
        const modalBody = document.getElementById('modalBody');
        const bookingTypeThai = translateBookingType(data.booking_type);
        let statusThai = translateStatus(data.status);
        if ((data.status === 'pending' || data.status === 'awaiting_payment') && data.payment_slip) {
            statusThai = 'รอตรวจสอบสลิป';
        }
        modalBody.innerHTML = `
                <div class="text-center mb-4">
                    <div class="display-6 fw-bold text-primary">#${data.booking_code}</div>
                    <div class="text-muted">สถานะ: ${statusThai}</div>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-sm-6"><small class="text-muted d-block">ชื่อลูกค้า</small> <strong>${data.guest_name}</strong></div>
                    <div class="col-12 col-sm-6"><small class="text-muted d-block">เบอร์โทรศัพท์</small> <strong>${data.guest_phone}</strong></div>
                    <div class="col-12 col-sm-6"><small class="text-muted d-block">อีเมล</small> <strong>${data.guest_email || '-'}</strong></div>
                    <div class="col-12 col-sm-6"><small class="text-muted d-block">ประเภทการจอง</small> <strong>${bookingTypeThai}</strong></div>
                    <div class="col-12"><hr></div>
                    <div class="col-6"><small class="text-muted d-block">ยอดรวม</small> <strong class="text-dark">฿${parseFloat(data.price_total).toLocaleString()}</strong></div>
                    <div class="col-6"><small class="text-muted d-block">เงินมัดจำ</small> <strong class="text-success">฿${parseFloat(data.deposit_amount).toLocaleString()}</strong></div>
                    <div class="col-6"><small class="text-muted d-block">เงินคงเหลือ</small> <strong class="text-danger">฿${parseFloat(data.balance_amount).toLocaleString()}</strong></div>
                    <div class="col-12"><small class="text-muted d-block">หลักฐานการชำระเงิน</small> 
                        ${data.payment_slip ? `<a href="../user/Paymentslip-Gardenreservation/${data.payment_slip}" target="_blank" class="btn btn-sm btn-outline-primary mt-1 w-100">ดูสลิป</a>` : '<span class="text-danger">ยังไม่มีสลิป</span>'}
                    </div>
                    ${data.booking_type === 'organization' && data.attachment_path ? `<div class="col-12"><small class="text-muted d-block">เอกสารองค์กร</small><a href="../user/${data.attachment_path}" target="_blank" class="btn btn-sm btn-outline-secondary mt-1 w-100">เปิดดูเอกสาร</a></div>` : ''}
                </div>
            `;
        detailModal.show();
    }

    // Search and filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const bookingList = document.getElementById('bookingList');
        const bookingItems = bookingList ? bookingList.querySelectorAll('.booking-item-col') : [];
        const noResultsMessage = document.getElementById('noResultsMessage');
        const filterButtons = document.querySelectorAll('.filter-btn');
        let currentFilter = 'all';

        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;
            bookingItems.forEach(item => {
                const statusBadge = item.querySelector('.status-badge');
                let statusMatch = false;
                if (currentFilter === 'all') {
                    statusMatch = true;
                } else {
                    if (statusBadge && statusBadge.classList.contains('status-' + currentFilter)) {
                        statusMatch = true;
                    }
                }
                const guestName = item.querySelector('.guest-name');
                const bookingCode = item.querySelector('.booking-code');
                const searchMatch = (guestName && guestName.textContent.toLowerCase().includes(searchTerm)) ||
                    (bookingCode && bookingCode.textContent.toLowerCase().replace('#', '').includes(searchTerm));
                if (statusMatch && searchMatch) {
                    item.classList.remove('d-none');
                    visibleCount++;
                } else {
                    item.classList.add('d-none');
                }
            });
            if (visibleCount === 0 && bookingItems.length > 0) {
                noResultsMessage.classList.remove('d-none');
            } else if (noResultsMessage) {
                noResultsMessage.classList.add('d-none');
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.filter;
                applyFilters();
            });
        });
        const urlParams = new URLSearchParams(window.location.search);
        const filterParam = urlParams.get('filter');
        if (filterParam) {
            const targetButton = document.querySelector(`.filter-btn[data-filter="${filterParam}"]`);
            if (targetButton) {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                targetButton.classList.add('active');
                currentFilter = filterParam;
                applyFilters();
            }
        }
    });
</script>
<?php adminPageEnd(); ?>
