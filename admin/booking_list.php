<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../user/line_service.php';
require_once __DIR__ . '/../user/booking_utils.php';
require_once __DIR__ . '/auto_cancel_overdue_lib.php';
require_once 'sidebar.php';

date_default_timezone_set('Asia/Bangkok');

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    auto_cancel_overdue_bookings($conn, static function (string $message): void {
        error_log('[auto-cancel-on-admin] ' . $message);
    });
}

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

function payment_due_at_for_qr(): string
{   // 5 นาที
    // return (new DateTimeImmutable('+5 minutes'))->format('Y-m-d H:i:s');

    // 3 วัน
    return (new DateTimeImmutable('+3 days'))->format('Y-m-d H:i:s');
}

function ensure_booking_status_values(mysqli $conn): void
{
    $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'status'");
    if (!$result) {
        return;
    }

    $column = $result->fetch_assoc();
    $columnType = (string)($column['Type'] ?? '');
    if (strpos($columnType, "'completed'") !== false && strpos($columnType, "'no_show'") !== false) {
        return;
    }

    $conn->query("ALTER TABLE bookings CHANGE status status ENUM('pending','awaiting_payment','confirmed','completed','no_show','cancelled') DEFAULT 'pending'");
}

function booking_actual_received_amount(array $booking): float
{
    $status = (string)($booking['status'] ?? '');
    $priceTotal = (float)($booking['price_total'] ?? 0);
    $depositAmount = (float)($booking['deposit_amount'] ?? 0);

    if ($status === 'completed') {
        return $priceTotal;
    }

    if ($status === 'confirmed' || $status === 'no_show') {
        return $depositAmount;
    }

    return 0.0;
}

function booking_table_exists(mysqli $conn, string $table): bool
{
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS table_count
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
    ");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("s", $table);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $exists = ((int)($row['table_count'] ?? 0)) > 0;
    $stmt->close();

    return $exists;
}

function booking_database_context(mysqli $conn): string
{
    $database = 'unknown';
    $host = 'unknown';

    $dbResult = $conn->query("SELECT DATABASE() AS current_database");
    if ($dbResult) {
        $row = $dbResult->fetch_assoc();
        $database = (string)($row['current_database'] ?? 'unknown');
        $dbResult->close();
    }

    $hostResult = $conn->query("SELECT @@hostname AS db_host");
    if ($hostResult) {
        $row = $hostResult->fetch_assoc();
        $host = (string)($row['db_host'] ?? 'unknown');
        $hostResult->close();
    }

    return 'database=' . $database . ', db_host=' . $host;
}

function booking_column_exists(mysqli $conn, string $column): bool
{
    $stmt = $conn->prepare("SHOW COLUMNS FROM bookings LIKE ?");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("s", $column);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    return $exists;
}

function booking_status_for_log(mysqli $conn, int $booking_id): ?string
{
    $stmt = $conn->prepare("SELECT status FROM bookings WHERE bookings_id = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return isset($row['status']) ? (string)$row['status'] : null;
}

function booking_edit_date_taken(mysqli $conn, int $booking_id, string $booking_date): bool
{
    $stmt = $conn->prepare("
        SELECT bookings_id
        FROM bookings
        WHERE booking_date = ?
          AND bookings_id <> ?
          AND status IN ('pending','awaiting_payment','confirmed')
        LIMIT 1
    ");
    if (!$stmt) {
        return true;
    }

    $stmt->bind_param("si", $booking_date, $booking_id);
    $stmt->execute();
    $taken = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    return $taken;
}

function booking_edit_note(array $old_booking, string $new_date, string $new_time, int $new_visitors, array $totals): string
{
    $old_date = (string)($old_booking['booking_date'] ?? '-');
    $old_time = substr((string)($old_booking['booking_time'] ?? '-'), 0, 5);
    $old_visitors = (int)($old_booking['visitor_count'] ?? 0);

    return sprintf(
        'Booking edited by admin. Date: %s -> %s; Time: %s -> %s; Visitors: %d -> %d; Total: %.2f; Deposit: %.2f; Balance: %.2f',
        $old_date,
        $new_date,
        $old_time,
        $new_time,
        $old_visitors,
        $new_visitors,
        (float)$totals['price_total'],
        (float)$totals['deposit_amount'],
        (float)$totals['balance_amount']
    );
}

function insert_booking_status_log(mysqli $conn, int $booking_id, ?string $old_status, string $new_status, string $action, ?string $note = null): array
{
    if (!booking_table_exists($conn, 'booking_status_logs')) {
        $message = 'booking_status_logs table not found in ' . booking_database_context($conn) . '; skipped booking status log.';
        error_log($message);
        return ['success' => false, 'message' => $message];
    }

    $admin_id = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
    $stmt = $conn->prepare("
        INSERT INTO booking_status_logs
            (booking_id, old_status, new_status, action, note, changed_by_admin_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");

    if (!$stmt) {
        $message = 'booking status log prepare failed: ' . $conn->error;
        error_log($message);
        return ['success' => false, 'message' => $message];
    }

    $stmt->bind_param("issssi", $booking_id, $old_status, $new_status, $action, $note, $admin_id);
    if (!$stmt->execute()) {
        $message = 'booking status log insert failed: ' . $stmt->error;
        error_log($message);
        $stmt->close();
        return ['success' => false, 'message' => $message];
    }
    $stmt->close();

    return ['success' => true, 'message' => 'booking status log inserted'];
}

ensure_booking_status_values($conn);

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
            $stmt_details = $conn->prepare("SELECT guest_name, guest_email, booking_code, deposit_amount, visitor_count, status FROM bookings WHERE bookings_id = ? AND status IN ('pending','awaiting_payment') AND (payment_slip IS NULL OR payment_slip = '')");
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
                    $payment_due_at = payment_due_at_for_qr();
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host       = "smtp.gmail.com";
                    $mail->SMTPAuth   = true;
                    $mail->Username   = app_env('MAIL_USERNAME');
                    $mail->Password   = app_env('MAIL_PASSWORD'); // App Password
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
                        $old_status = (string)($booking['status'] ?? 'pending');
                        $stmt_update = $conn->prepare("UPDATE bookings SET payment_qr_path = ?, status = 'awaiting_payment', qr_sent_at = NOW(), payment_due_at = ?, payment_submitted_at = NULL, cancelled_at = NULL, cancel_reason = NULL, cancellation_email_sent_at = NULL, updated_at = NOW() WHERE bookings_id = ? AND status IN ('pending','awaiting_payment') AND (payment_slip IS NULL OR payment_slip = '')");
                        if ($stmt_update) {
                            $stmt_update->bind_param("ssi", $saved_qr['filename'], $payment_due_at, $id);
                            $stmt_update->execute();
                            if ($stmt_update->affected_rows !== 1) {
                                error_log("QR status update skipped for booking ID $id: booking is no longer pending.");
                                $response = ['success' => false, 'message' => 'Booking status changed before QR update. Please refresh and check this booking.'];
                            } else {
                                $response = ['success' => true, 'message' => 'ส่งอีเมลพร้อม QR Code สำเร็จ'];
                            }
                            if ($stmt_update->affected_rows === 1) {
                                $log_result = insert_booking_status_log($conn, $id, $old_status, 'awaiting_payment', $old_status === 'awaiting_payment' ? 'resend_qr' : 'send_qr', 'QR email sent; payment due at ' . $payment_due_at);
                                $response['log_success'] = $log_result['success'];
                                $response['log_message'] = $log_result['message'];
                                if (!$log_result['success']) {
                                    $response['message'] .= ' แต่บันทึกไทม์ไลน์ไม่สำเร็จ: ' . $log_result['message'];
                                }
                                line_send_booking_status($conn, $id, 'awaiting_payment');
                            }
                            $stmt_update->close();
                        } else {
                            $response = ['success' => false, 'message' => 'Cannot prepare QR status update.'];
                        }
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
                $response = ['success' => false, 'message' => 'ไม่พบข้อมูลการจอง ไม่มีอีเมลลูกค้า หรือรายการนี้ไม่อยู่ในสถานะที่ส่ง QR ได้'];
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
    $message = '';
    $log_success = null;
    $log_message = '';
    $rejection_reason = $_POST['reason'] ?? null;

    if ($action === 'edit_booking') {
        $old_status = booking_status_for_log($conn, $id);
        $booking_date = trim((string)($_POST['booking_date'] ?? ''));
        $booking_time = trim((string)($_POST['booking_time'] ?? ''));
        $visitor_count = (int)($_POST['visitor_count'] ?? 0);
        $allowed_times = ['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
        $errors = [];

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $booking_date)) {
            $errors[] = 'วันที่ไม่ถูกต้อง';
        } else {
            [$year, $month, $day] = array_map('intval', explode('-', $booking_date));
            if (!checkdate($month, $day, $year) || strtotime($booking_date) < strtotime(date('Y-m-d'))) {
                $errors[] = 'วันที่ต้องเป็นวันนี้หรือวันอนาคต';
            }
        }

        if (!in_array($booking_time, $allowed_times, true)) {
            $errors[] = 'เวลาไม่ถูกต้อง';
        }

        if ($visitor_count < 1 || $visitor_count > 500) {
            $errors[] = 'จำนวนคนต้องอยู่ระหว่าง 1-500 คน';
        }

        if (empty($errors) && booking_edit_date_taken($conn, $id, $booking_date)) {
            $errors[] = 'วันที่นี้มีการจองที่ยังใช้งานอยู่แล้ว';
        }

        if (!empty($errors)) {
            $message = implode(', ', $errors);
        } else {
            $stmt_current = $conn->prepare("SELECT bookings_id, booking_date, booking_time, visitor_count, status FROM bookings WHERE bookings_id = ? LIMIT 1");
            if ($stmt_current) {
                $stmt_current->bind_param("i", $id);
                $stmt_current->execute();
                $current_booking = $stmt_current->get_result()->fetch_assoc();
                $stmt_current->close();
            } else {
                $current_booking = null;
            }

            if (!$current_booking) {
                $message = 'ไม่พบข้อมูลการจอง';
            } elseif (in_array((string)$current_booking['status'], ['completed', 'no_show', 'cancelled'], true)) {
                $message = 'แก้ไขไม่ได้: รายการนี้ปิดงาน ไม่มา หรือยกเลิกแล้ว';
            } else {
                $totals = calculate_booking_totals($visitor_count);
                $stmt = $conn->prepare("
                    UPDATE bookings
                    SET booking_date = ?,
                        booking_time = ?,
                        visitor_count = ?,
                        price_total = ?,
                        deposit_amount = ?,
                        balance_amount = ?,
                        updated_at = NOW()
                    WHERE bookings_id = ?
                      AND status NOT IN ('completed','no_show','cancelled')
                ");
                if ($stmt) {
                    $stmt->bind_param(
                        "ssidddi",
                        $booking_date,
                        $booking_time,
                        $visitor_count,
                        $totals['price_total'],
                        $totals['deposit_amount'],
                        $totals['balance_amount'],
                        $id
                    );
                    $success = $stmt->execute() && $stmt->affected_rows >= 0;
                    $stmt->close();
                }

                if ($success) {
                    $message = 'แก้ไขการจองเรียบร้อยแล้ว';
                    $log_result = insert_booking_status_log(
                        $conn,
                        $id,
                        $old_status,
                        (string)$current_booking['status'],
                        'edit_booking',
                        booking_edit_note($current_booking, $booking_date, $booking_time, $visitor_count, $totals)
                    );
                    $log_success = $log_result['success'];
                    $log_message = $log_result['message'];
                    if (!$log_success) {
                        $message .= ' แต่บันทึกไทม์ไลน์ไม่สำเร็จ: ' . $log_message;
                    }
                } else {
                    $message = 'แก้ไขไม่ได้: รายการนี้อาจถูกเปลี่ยนสถานะไปแล้ว';
                }
            }
        }
    } elseif ($action === 'confirm') {
        $old_status = booking_status_for_log($conn, $id);
        $payment_received_amount = isset($_POST['payment_received_amount']) ? (float)$_POST['payment_received_amount'] : 0.0;
        $payment_note = trim((string)($_POST['payment_note'] ?? ''));
        $admin_id = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 0;

        if ($payment_received_amount <= 0) {
            $message = 'กรุณาระบุยอดเงินที่ตรวจสอบแล้ว';
        } elseif (
            booking_column_exists($conn, 'payment_verified_at') &&
            booking_column_exists($conn, 'payment_verified_by') &&
            booking_column_exists($conn, 'payment_received_amount') &&
            booking_column_exists($conn, 'payment_note')
        ) {
            $stmt = $conn->prepare("UPDATE bookings SET status='confirmed', payment_verified_at=NOW(), payment_verified_by=?, payment_received_amount=?, payment_note=?, updated_at=NOW() WHERE bookings_id=? AND status IN ('pending','awaiting_payment') AND payment_slip IS NOT NULL AND payment_slip <> ''");
            $stmt->bind_param("idsi", $admin_id, $payment_received_amount, $payment_note, $id);
            $success = $stmt->execute() && $stmt->affected_rows === 1;
        } else {
            $stmt = $conn->prepare("UPDATE bookings SET status='confirmed', updated_at=NOW() WHERE bookings_id=? AND status IN ('pending','awaiting_payment') AND payment_slip IS NOT NULL AND payment_slip <> ''");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute() && $stmt->affected_rows === 1;
        }

        if ($success) {
            $log_result = insert_booking_status_log($conn, $id, $old_status, 'confirmed', 'confirm', 'Payment verified amount: ' . number_format($payment_received_amount, 2) . ($payment_note !== '' ? '; note: ' . $payment_note : ''));
            $log_success = $log_result['success'];
            $log_message = $log_result['message'];
            if (!$log_success) {
                $message = 'ยืนยันสำเร็จ แต่บันทึกไทม์ไลน์ไม่สำเร็จ: ' . $log_message;
            }
            line_send_booking_status($conn, $id, 'confirmed');
            // ดึงข้อมูลการจองเพื่อส่งอีเมล
            $stmt_details = $conn->prepare("SELECT guest_name, guest_email, booking_code, booking_date, booking_time, visitor_count, booking_type, lunch_request, price_total, deposit_amount, balance_amount FROM bookings WHERE bookings_id = ?");
            $stmt_details->bind_param("i", $id);
            $stmt_details->execute();
            $booking = $stmt_details->get_result()->fetch_assoc();
            $stmt_details->close();

            if ($booking && !empty($booking['guest_email'])) {
                try {
                    $userMail = new PHPMailer(true);
                    $userMail->isSMTP();
                    $userMail->Host       = "smtp.gmail.com";
                    $userMail->SMTPAuth   = true;
                    $userMail->Username   = app_env('MAIL_USERNAME');
                    $userMail->Password   = app_env('MAIL_PASSWORD'); // App Password
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
        } elseif ($message === '') {
            $message = 'ยืนยันไม่ได้: รายการนี้อาจไม่มีสลิป หรือสถานะถูกเปลี่ยนไปแล้ว';
        }
    } elseif ($action === 'cancel') {
        $old_status = booking_status_for_log($conn, $id);
        $stmt = $conn->prepare("UPDATE bookings SET status='cancelled', cancelled_at=NOW(), cancel_reason=?, updated_at=NOW() WHERE bookings_id=? AND status NOT IN ('completed','no_show','cancelled')");
        $cancel_reason = !empty($rejection_reason) ? $rejection_reason : 'admin_cancelled';
        $stmt->bind_param("si", $cancel_reason, $id);
        $success = $stmt->execute() && $stmt->affected_rows === 1;

        if ($success) {
            $log_result = insert_booking_status_log($conn, $id, $old_status, 'cancelled', 'cancel', $cancel_reason);
            $log_success = $log_result['success'];
            $log_message = $log_result['message'];
            if (!$log_success) {
                $message = 'ยกเลิกสำเร็จ แต่บันทึกไทม์ไลน์ไม่สำเร็จ: ' . $log_message;
            }
            line_send_booking_status($conn, $id, 'cancelled', $cancel_reason);
            // ดึงข้อมูลการจองเพื่อส่งอีเมลแจ้งยกเลิก
            $stmt_details = $conn->prepare("SELECT guest_name, guest_email, booking_code, booking_date FROM bookings WHERE bookings_id = ?");
            $stmt_details->bind_param("i", $id);
            $stmt_details->execute();
            $booking = $stmt_details->get_result()->fetch_assoc();
            $stmt_details->close();

            if ($booking && !empty($booking['guest_email'])) {
                try {
                    $cancelMail = new PHPMailer(true);
                    $cancelMail->isSMTP();
                    $cancelMail->Host       = "smtp.gmail.com";
                    $cancelMail->SMTPAuth   = true;
                    $cancelMail->Username   = app_env('MAIL_USERNAME');
                    $cancelMail->Password   = app_env('MAIL_PASSWORD'); // App Password
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
        } else {
            $message = 'ยกเลิกไม่ได้: รายการนี้อาจปิดงาน ยกเลิก หรือเปลี่ยนสถานะไปแล้ว';
        }
    } elseif ($action === 'complete') {
        $old_status = booking_status_for_log($conn, $id);
        $stmt = $conn->prepare("UPDATE bookings SET status='completed', updated_at=NOW() WHERE bookings_id=? AND status='confirmed'");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute() && $stmt->affected_rows === 1;
        if ($success) {
            $log_result = insert_booking_status_log($conn, $id, $old_status, 'completed', 'complete', 'Booking completed by admin.');
            $log_success = $log_result['success'];
            $log_message = $log_result['message'];
            if (!$log_success) {
                $message = 'ปิดงานสำเร็จ แต่บันทึกไทม์ไลน์ไม่สำเร็จ: ' . $log_message;
            }
        } else {
            $message = 'ปิดงานไม่ได้: รายการนี้ต้องอยู่ในสถานะยืนยันแล้วก่อน';
        }
    } elseif ($action === 'no_show') {
        $old_status = booking_status_for_log($conn, $id);
        $stmt = $conn->prepare("UPDATE bookings SET status='no_show', updated_at=NOW() WHERE bookings_id=? AND status='confirmed'");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute() && $stmt->affected_rows === 1;
        if ($success) {
            $log_result = insert_booking_status_log($conn, $id, $old_status, 'no_show', 'no_show', 'Marked as no-show; deposit retained.');
            $log_success = $log_result['success'];
            $log_message = $log_result['message'];
            if (!$log_success) {
                $message = 'บันทึกไม่มาสำเร็จ แต่บันทึกไทม์ไลน์ไม่สำเร็จ: ' . $log_message;
            }
        } else {
            $message = 'บันทึกไม่มาไม่ได้: รายการนี้ต้องอยู่ในสถานะยืนยันแล้วก่อน';
        }
    } elseif ($action === 'delete') {
        $message = 'ระบบไม่อนุญาตให้ลบการจองโดยตรง กรุณายกเลิกหรือปิดงานแทน';
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'action' => $action,
        'id' => $id,
        'message' => $message,
        'log_success' => $log_success,
        'log_message' => $log_message
    ]);
    exit;
}

// ดึงข้อมูลการจองทั้งหมด
$result = $conn->query("SELECT * FROM bookings ORDER BY bookings_id DESC");
$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

$activeBookedDates = [];
foreach ($bookings as $bookingForDateMarker) {
    $markerDate = (string)($bookingForDateMarker['booking_date'] ?? '');
    $markerStatus = (string)($bookingForDateMarker['status'] ?? '');
    if ($markerDate !== '' && $markerDate !== '0000-00-00' && in_array($markerStatus, ['pending', 'awaiting_payment', 'confirmed'], true)) {
        $activeBookedDates[$markerDate][] = (int)($bookingForDateMarker['bookings_id'] ?? 0);
    }
}

$bookingYears = [];
foreach ($bookings as $bookingForDateFilter) {
    $bookingDateForFilter = $bookingForDateFilter['booking_date'] ?? '';
    if (!empty($bookingDateForFilter) && $bookingDateForFilter !== '0000-00-00') {
        $bookingYearForFilter = date('Y', strtotime($bookingDateForFilter));
        if ($bookingYearForFilter) {
            $bookingYears[$bookingYearForFilter] = $bookingYearForFilter;
        }
    }
}
rsort($bookingYears, SORT_NUMERIC);

// นับจำนวนสถานะต่างๆ สำหรับ Stats Card
$stats = [
    'pending' => 0,
    'awaiting_payment' => 0,
    'awaiting_slip_check' => 0,
    'confirmed' => 0,
    'completed' => 0,
    'no_show' => 0,
    'cancelled' => 0,
    'actual_received' => 0,
    'total' => count($bookings)
];
foreach ($bookings as $b) {
    $stats['actual_received'] += booking_actual_received_amount($b);
    // Check for awaiting slip check first
    if (($b['status'] === 'pending' || $b['status'] === 'awaiting_payment') && !empty($b['payment_slip'])) {
        $stats['awaiting_slip_check']++;
    } elseif ($b['status'] === 'pending') {
        $stats['pending']++;
    } elseif ($b['status'] === 'awaiting_payment') {
        $stats['awaiting_payment']++;
    } elseif ($b['status'] === 'confirmed') {
        $stats['confirmed']++;
    } elseif ($b['status'] === 'completed') {
        $stats['completed']++;
    } elseif ($b['status'] === 'no_show') {
        $stats['no_show']++;
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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

    .status-completed {
        background: rgba(20, 184, 166, 0.14);
        color: #0f766e;
    }

    .status-no_show {
        background: rgba(120, 113, 108, 0.16);
        color: #57534e;
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

    .action-btn:disabled,
    .action-btn.is-loading {
        cursor: wait;
        opacity: 0.78;
        pointer-events: none;
    }

    .action-btn.is-loading {
        box-shadow: 0 0 0 0.2rem rgba(1, 106, 112, 0.12);
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

    .btn-edit {
        background: rgba(246, 194, 62, 0.14);
        color: #b7791f;
    }

    .btn-edit:hover {
        background: #f6c23e;
        color: #2f2500;
    }

    .btn-complete {
        background: rgba(20, 184, 166, 0.12);
        color: #0f766e;
    }

    .btn-complete:hover {
        background: #0f766e;
        color: white;
    }

    .btn-no-show {
        background: rgba(120, 113, 108, 0.14);
        color: #57534e;
    }

    .btn-no-show:hover {
        background: #57534e;
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

    .flatpickr-day.booked-date,
    .flatpickr-day.booked-date:hover {
        background: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }

    .flatpickr-day.booked-date.flatpickr-disabled,
    .flatpickr-day.booked-date.flatpickr-disabled:hover {
        background: rgba(220, 53, 69, 0.18);
        border-color: rgba(220, 53, 69, 0.45);
        color: #b02a37;
        text-decoration: line-through;
    }

    .flatpickr-day.booked-date.selected {
        background: #b02a37;
        border-color: #b02a37;
        color: #fff;
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

    .booking-income-summary {
        border: 1px solid rgba(1, 106, 112, 0.16);
        background: #ffffff;
        border-left: 4px solid var(--green);
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        padding: 1rem 1.25rem;
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
        <div class="col-6 col-lg-2">
            <div class="stats-card">
                <div class="stats-icon text-primary"><i class="bi bi-list-ul"></i></div>
                <div class="stats-value"><?= $stats['total'] ?></div>
                <div class="text-muted">ทั้งหมด</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stats-card">
                <div class="stats-icon text-warning"><i class="bi bi-clock-history"></i></div>
                <div class="stats-value"><?= $stats['pending'] + $stats['awaiting_payment'] + $stats['awaiting_slip_check'] ?></div>
                <div class="text-muted">รอยืนยัน</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stats-card">
                <div class="stats-icon text-success"><i class="bi bi-check-circle"></i></div>
                <div class="stats-value"><?= $stats['confirmed'] ?></div>
                <div class="text-muted">ยืนยันแล้ว</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stats-card">
                <div class="stats-icon text-info"><i class="bi bi-flag-fill"></i></div>
                <div class="stats-value"><?= $stats['completed'] ?></div>
                <div class="text-muted">เสร็จสิ้น</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stats-card">
                <div class="stats-icon text-secondary"><i class="bi bi-person-x"></i></div>
                <div class="stats-value"><?= $stats['no_show'] ?></div>
                <div class="text-muted">ไม่มา/ยึดมัดจำ</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stats-card">
                <div class="stats-icon text-danger"><i class="bi bi-x-circle"></i></div>
                <div class="stats-value"><?= $stats['cancelled'] ?></div>
                <div class="text-muted">ยกเลิกแล้ว</div>
            </div>
        </div>
    </div>

    <div class="booking-income-summary d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3 mb-md-4">
        <div>
            <div class="fw-semibold text-dark">ยอดรับจริงรวม</div>
            <div class="text-muted small">เสร็จสิ้นนับเต็มจำนวน, ไม่มา/ยึดมัดจำนับเฉพาะมัดจำ</div>
        </div>
        <div class="fs-4 fw-bold text-primary">฿<?= number_format($stats['actual_received'], 2) ?></div>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-outline-success" href="export_bookings.php">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
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
            <button type="button" class="btn btn-light filter-btn" data-filter="completed">
                <i class="bi bi-flag-fill me-1"></i> เสร็จสิ้น
                <?php if ($stats['completed'] > 0): ?>
                    <span class="badge rounded-pill bg-info text-dark ms-1"><?= $stats['completed'] ?></span>
                <?php endif; ?>
            </button>
            <button type="button" class="btn btn-light filter-btn" data-filter="no_show">
                <i class="bi bi-person-x me-1"></i> ไม่มา/ยึดมัดจำ
                <?php if ($stats['no_show'] > 0): ?>
                    <span class="badge rounded-pill bg-secondary ms-1"><?= $stats['no_show'] ?></span>
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
        <div class="row g-2 mt-2">
            <div class="col-12 col-md-4">
                <select id="bookingDayFilter" class="form-select form-select-lg" aria-label="เลือกวันที่จอง">
                    <option value="">เลือกวันที่</option>
                    <?php for ($day = 1; $day <= 31; $day++): ?>
                        <option value="<?= sprintf('%02d', $day) ?>"><?= $day ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <select id="bookingMonthFilter" class="form-select form-select-lg" aria-label="เลือกเดือนที่จอง">
                    <option value="">เลือกเดือน</option>
                    <?php
                    $thaiMonthsForFilter = [
                        1 => 'มกราคม',
                        2 => 'กุมภาพันธ์',
                        3 => 'มีนาคม',
                        4 => 'เมษายน',
                        5 => 'พฤษภาคม',
                        6 => 'มิถุนายน',
                        7 => 'กรกฎาคม',
                        8 => 'สิงหาคม',
                        9 => 'กันยายน',
                        10 => 'ตุลาคม',
                        11 => 'พฤศจิกายน',
                        12 => 'ธันวาคม',
                    ];
                    ?>
                    <?php foreach ($thaiMonthsForFilter as $monthNumber => $monthName): ?>
                        <option value="<?= sprintf('%02d', $monthNumber) ?>"><?= htmlspecialchars($monthName, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <select id="bookingYearFilter" class="form-select form-select-lg" aria-label="เลือกปีที่จอง">
                    <option value="">เลือกปี</option>
                    <?php foreach ($bookingYears as $bookingYear): ?>
                        <option value="<?= htmlspecialchars((string) $bookingYear, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) ((int) $bookingYear + 543), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
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
                <div class="col-md-6 col-lg-4 booking-item-col" data-booking-date="<?= htmlspecialchars((string) ($booking['booking_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
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
                            } elseif ($booking['status'] == 'completed') {
                                $display_status_text = 'เสร็จสิ้น';
                                $display_status_icon = 'bi-flag-fill';
                            } elseif ($booking['status'] == 'no_show') {
                                $display_status_text = 'ไม่มา/ยึดมัดจำ';
                                $display_status_icon = 'bi-person-x-fill';
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
                            <div class="mb-2">
                                <span class="info-label"><i class="bi bi-wallet2 me-2"></i>มัดจำ:</span>
                                <span class="info-value text-success">฿<?= number_format($booking['deposit_amount'], 2) ?></span>
                            </div>
                            <div class="mb-2">
                                <span class="info-label"><i class="bi bi-cash-coin me-2"></i>คงเหลือ:</span>
                                <span class="info-value text-danger">฿<?= number_format($booking['balance_amount'], 2) ?></span>
                            </div>
                            <div class="mb-3">
                                <span class="info-label"><i class="bi bi-safe2 me-2"></i>รับจริง:</span>
                                <span class="info-value fw-bold text-primary">฿<?= number_format(booking_actual_received_amount($booking), 2) ?></span>
                            </div>

                            <div class="mb-3">
                                <span class="info-label d-block mb-2"><i class="bi bi-receipt me-2"></i>สลิป:</span>
                                <?php if (!empty($booking['payment_slip'])): ?>
                                    <?php $slipUrl = '../user/download.php?type=slip&file=' . rawurlencode(basename((string)$booking['payment_slip'])); ?>
                                    <a href="<?= htmlspecialchars($slipUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                                        <img src="<?= htmlspecialchars($slipUrl, ENT_QUOTES, 'UTF-8') ?>" class="img-fluid rounded" style="width: 100%; height: 150px; object-fit: cover;" alt="Payment Slip">
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
                                <?php if (!in_array($booking['status'], ['completed', 'no_show', 'cancelled'], true)): ?>
                                    <button class="action-btn btn-edit" onclick="showEditBookingModal(<?= $booking['bookings_id'] ?>, '<?= htmlspecialchars(json_encode($booking), ENT_QUOTES, 'UTF-8') ?>', this)">
                                        <i class="bi bi-pencil-square me-1"></i> แก้ไข
                                    </button>
                                <?php endif; ?>

                                <?php if (in_array($booking['status'], ['pending', 'awaiting_payment'], true) && !$isAwaitingSlipCheck): ?>
                                    <button class="action-btn btn-info" onclick="showQrCodeModal(<?= $booking['bookings_id'] ?>, '<?= htmlspecialchars(json_encode($booking), ENT_QUOTES, 'UTF-8') ?>')">
                                        <i class="bi bi-qr-code me-1"></i> <?= $booking['status'] === 'awaiting_payment' ? 'ส่ง QR ใหม่' : 'ส่ง QR' ?>
                                    </button>
                                <?php endif; ?>

                                <?php if ($isAwaitingSlipCheck): ?>
                                    <button class="action-btn btn-confirm" onclick="showPaymentConfirmModal(<?= $booking['bookings_id'] ?>, '<?= htmlspecialchars(json_encode($booking), ENT_QUOTES, 'UTF-8') ?>', this)">
                                        <i class="bi bi-check2-circle me-1"></i> ยืนยัน
                                    </button>
                                <?php endif; ?>

                                <?php if ($booking['status'] == 'confirmed'): ?>
                                    <button class="action-btn btn-complete" onclick="showConfirmationModal(<?= $booking['bookings_id'] ?>, 'complete', 'ยืนยันว่ากลุ่มนี้มาที่สวน ทำกิจกรรมเสร็จ และชำระเงินคงเหลือครบแล้วใช่หรือไม่?', this)">
                                        <i class="bi bi-flag-fill me-1"></i> เสร็จสิ้น
                                    </button>
                                    <button class="action-btn btn-no-show" onclick="showConfirmationModal(<?= $booking['bookings_id'] ?>, 'no_show', 'ยืนยันว่าผู้จองไม่มา และให้ยึดเฉพาะเงินมัดจำใช่หรือไม่?', this)">
                                        <i class="bi bi-person-x me-1"></i> ไม่มา
                                    </button>
                                <?php endif; ?>

                                <?php if (!in_array($booking['status'], ['confirmed', 'completed', 'no_show', 'cancelled'], true)): ?>
                                    <button class="action-btn btn-cancel" onclick="showConfirmationModal(<?= $booking['bookings_id'] ?>, 'cancel', 'ต้องการยกเลิกการจองนี้ใช่หรือไม่?', this)">
                                        <i class="bi bi-x-lg me-1"></i> ยกเลิก
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-light rounded-pill px-3" onclick="viewDetails(<?= htmlspecialchars(json_encode($booking)) ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a class="btn btn-light rounded-pill px-3" href="booking_timeline.php?booking_id=<?= (int)$booking['bookings_id'] ?>" title="ดูไทม์ไลน์">
                                    <i class="bi bi-clock-history"></i>
                                </a>
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

<div class="modal fade" id="editBookingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title">แก้ไขการจอง</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editBookingId">
                <div class="mb-3">
                    <div class="fw-semibold" id="editBookingCode"></div>
                    <div class="text-muted small" id="editBookingCustomer"></div>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="editBookingDate" class="form-label">วันที่เข้าชม</label>
                        <input type="text" class="form-control" id="editBookingDate" placeholder="เลือกวันที่" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="editBookingTime" class="form-label">เวลา</label>
                        <select class="form-select" id="editBookingTime" required>
                            <option value="08:00">08:00 น.</option>
                            <option value="09:00">09:00 น.</option>
                            <option value="10:00">10:00 น.</option>
                            <option value="11:00">11:00 น.</option>
                            <option value="12:00">12:00 น.</option>
                            <option value="13:00">13:00 น.</option>
                            <option value="14:00">14:00 น.</option>
                            <option value="15:00">15:00 น.</option>
                            <option value="16:00">16:00 น.</option>
                            <option value="17:00">17:00 น.</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="editVisitorCount" class="form-label">จำนวนผู้เข้าชม</label>
                        <input type="number" min="1" max="500" step="1" class="form-control" id="editVisitorCount" required>
                    </div>
                </div>
                <div class="alert alert-light border mt-4 mb-0">
                    <div class="d-flex justify-content-between gap-3">
                        <span>ยอดรวม</span>
                        <strong id="editPriceTotal">฿0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between gap-3">
                        <span>มัดจำ 30%</span>
                        <strong id="editDepositAmount">฿0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between gap-3">
                        <span>ยอดคงเหลือ</span>
                        <strong id="editBalanceAmount">฿0.00</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="saveEditBookingButton">
                    <i class="bi bi-save me-2"></i>บันทึก
                </button>
            </div>
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Initialize Bootstrap Modals
    const confirmModalEl = document.getElementById('actionConfirmModal');
    const confirmModal = new bootstrap.Modal(confirmModalEl);
    const confirmActionButton = document.getElementById('confirmActionButton');
    const statusModalEl = document.getElementById('statusModal');
    const statusModal = new bootstrap.Modal(statusModalEl);
    const detailModalEl = document.getElementById('detailModal');
    const detailModal = new bootstrap.Modal(detailModalEl);
    const editBookingModalEl = document.getElementById('editBookingModal');
    const editBookingModal = new bootstrap.Modal(editBookingModalEl);
    const qrCodeModalEl = document.getElementById('qrCodeModal');
    const qrCodeModal = new bootstrap.Modal(qrCodeModalEl);
    const actionLoadingLabels = {
        edit_booking: 'กำลังบันทึก...',
        confirm: 'กำลังยืนยัน...',
        cancel: 'กำลังยกเลิก...',
        complete: 'กำลังปิดรายการ...',
        no_show: 'กำลังบันทึกไม่มา...'
    };
    let activeActionButtons = [];
    let editBookingSourceButton = null;
    let editBookingDatePicker = null;
    let activeEditBookingId = null;
    const activeBookedDates = <?= json_encode($activeBookedDates, JSON_UNESCAPED_SLASHES) ?>;

    function setButtonLoading(button, label) {
        if (!button) return;
        if (!button.dataset.originalHtml) {
            button.dataset.originalHtml = button.innerHTML;
        }
        button.disabled = true;
        button.classList.add('is-loading');
        button.innerHTML = `
            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
            <span>${label}</span>
        `;
    }

    function restoreButton(button) {
        if (!button) return;
        if (button.dataset.originalHtml) {
            button.innerHTML = button.dataset.originalHtml;
            delete button.dataset.originalHtml;
        }
        button.disabled = false;
        button.classList.remove('is-loading');
    }

    function setActionLoading(sourceButton, action, modalButton = null) {
        const buttons = new Set();
        if (sourceButton) {
            buttons.add(sourceButton);
            const bookingCard = sourceButton.closest('.booking-card') || sourceButton.closest('.booking-item-col');
            if (bookingCard) {
                bookingCard.querySelectorAll('.action-btn').forEach(button => buttons.add(button));
            }
        }
        if (modalButton) {
            buttons.add(modalButton);
        }

        activeActionButtons = Array.from(buttons);
        activeActionButtons.forEach(button => {
            if (button === sourceButton || button === modalButton) {
                setButtonLoading(button, actionLoadingLabels[action] || 'กำลังดำเนินการ...');
            } else {
                button.disabled = true;
            }
        });
    }

    function restoreActionLoading() {
        activeActionButtons.forEach(restoreButton);
        activeActionButtons = [];
    }

    function handleBookingAction(id, action, reason = null, sourceButton = null, modalButton = null, extraData = {}) {
        setActionLoading(sourceButton, action, modalButton);
        showStatusModal('กำลังดำเนินการ', 'กรุณารอสักครู่ ระบบกำลังทำรายการ...', 'loading');

        const formData = new FormData();
        formData.append('id', id);
        formData.append('action', action);
        formData.append('csrf_token', '<?= $csrf_token ?>');
        if (reason) {
            formData.append('reason', reason);
        }
        Object.entries(extraData || {}).forEach(([key, value]) => {
            formData.append(key, value);
        });

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
                    showStatusModal('สำเร็จ', data.message || 'ดำเนินการเรียบร้อยแล้ว', true, true);
                    statusModalEl.addEventListener('hidden.bs.modal', () => {
                        location.reload();
                    }, {
                        once: true
                    });
                } else {
                    restoreActionLoading();
                    showStatusModal('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถดำเนินการได้', false, false);
                }
            })
            .catch(error => {
                restoreActionLoading();
                showStatusModal('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', false, false);
            });
    }

    function editBookingTotals(visitorCount) {
        const count = Math.max(1, Number.parseInt(visitorCount, 10) || 1);
        const total = (count * 150) + 1800 + 3000;
        const deposit = Math.round(total * 0.3 * 100) / 100;
        return {
            total,
            deposit,
            balance: Math.round((total - deposit) * 100) / 100
        };
    }

    function updateEditBookingTotals() {
        const countInput = document.getElementById('editVisitorCount');
        const totals = editBookingTotals(countInput ? countInput.value : 1);
        const moneyFormat = new Intl.NumberFormat('th-TH', {
            style: 'currency',
            currency: 'THB',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        document.getElementById('editPriceTotal').textContent = moneyFormat.format(totals.total);
        document.getElementById('editDepositAmount').textContent = moneyFormat.format(totals.deposit);
        document.getElementById('editBalanceAmount').textContent = moneyFormat.format(totals.balance);
    }

    function formatLocalDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function bookedByAnotherBooking(dateStr) {
        const bookingIds = activeBookedDates[dateStr] || [];
        return bookingIds.some(bookingId => Number(bookingId) !== Number(activeEditBookingId));
    }

    function markBookedDates() {
        document.querySelectorAll('.flatpickr-day').forEach(dayEl => {
            if (!dayEl.dateObj) return;
            const dateStr = formatLocalDate(dayEl.dateObj);
            if (activeBookedDates[dateStr]) {
                dayEl.classList.add('booked-date');
                dayEl.title = bookedByAnotherBooking(dateStr) ? 'มีการจองแล้ว' : 'วันที่จองเดิมของรายการนี้';
            }
        });
    }

    function ensureEditBookingDatePicker() {
        if (editBookingDatePicker || typeof flatpickr === 'undefined') {
            return;
        }

        editBookingDatePicker = flatpickr('#editBookingDate', {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            disable: [
                function(date) {
                    return bookedByAnotherBooking(formatLocalDate(date));
                }
            ],
            onReady: markBookedDates,
            onDayCreate: function(selectedDates, dateStr, instance, dayElem) {
                const dayDate = formatLocalDate(dayElem.dateObj);
                if (activeBookedDates[dayDate]) {
                    dayElem.classList.add('booked-date');
                    dayElem.title = bookedByAnotherBooking(dayDate) ? 'มีการจองแล้ว' : 'วันที่จองเดิมของรายการนี้';
                }
            },
            onMonthChange: markBookedDates,
            onYearChange: markBookedDates,
            onOpen: markBookedDates,
            onValueUpdate: markBookedDates
        });
    }

    function showEditBookingModal(id, bookingJson, sourceButton = null) {
        const booking = JSON.parse(bookingJson);
        editBookingSourceButton = sourceButton;
        activeEditBookingId = id;
        ensureEditBookingDatePicker();

        document.getElementById('editBookingId').value = id;
        document.getElementById('editBookingCode').textContent = '#' + (booking.booking_code || '');
        document.getElementById('editBookingCustomer').textContent = booking.guest_name || '';
        if (editBookingDatePicker) {
            editBookingDatePicker.set('disable', [
                function(date) {
                    return bookedByAnotherBooking(formatLocalDate(date));
                }
            ]);
            editBookingDatePicker.setDate(booking.booking_date || '', false, 'Y-m-d');
        } else {
            document.getElementById('editBookingDate').value = booking.booking_date || '';
        }
        document.getElementById('editBookingTime').value = String(booking.booking_time || '').slice(0, 5);
        document.getElementById('editVisitorCount').value = booking.visitor_count || 1;

        const today = new Date().toISOString().slice(0, 10);
        document.getElementById('editBookingDate').setAttribute('min', today);
        updateEditBookingTotals();
        editBookingModal.show();
        setTimeout(markBookedDates, 0);
    }

    const editVisitorCountInput = document.getElementById('editVisitorCount');
    if (editVisitorCountInput) {
        editVisitorCountInput.addEventListener('input', updateEditBookingTotals);
    }

    const saveEditBookingButton = document.getElementById('saveEditBookingButton');
    if (saveEditBookingButton) {
        saveEditBookingButton.addEventListener('click', function() {
            const id = document.getElementById('editBookingId').value;
            const bookingDate = document.getElementById('editBookingDate').value;
            const bookingTime = document.getElementById('editBookingTime').value;
            const visitorCount = Number.parseInt(document.getElementById('editVisitorCount').value, 10) || 0;

            if (!bookingDate || !bookingTime || visitorCount < 1 || visitorCount > 500) {
                alert('กรุณาระบุวันที่ เวลา และจำนวนคนให้ถูกต้อง');
                return;
            }

            editBookingModal.hide();
            handleBookingAction(id, 'edit_booking', null, editBookingSourceButton, saveEditBookingButton, {
                booking_date: bookingDate,
                booking_time: bookingTime,
                visitor_count: visitorCount
            });
        });
    }

    function showPaymentConfirmModal(id, bookingJson, sourceButton = null) {
        const booking = JSON.parse(bookingJson);
        const modalBody = document.getElementById('actionConfirmModalBody');
        const modalLabel = document.getElementById('actionConfirmModalLabel');
        const expectedAmount = Number.parseFloat(booking.deposit_amount) || 0;

        modalLabel.textContent = 'ยืนยันการชำระเงิน';
        modalBody.innerHTML = `
            <div class="mb-3">
                <div class="fw-semibold mb-1">#${escapeHtml(booking.booking_code || '')}</div>
                <div class="text-muted small">ยอดมัดจำตามระบบ: ฿${expectedAmount.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
            </div>
            <div class="mb-3">
                <label for="paymentReceivedAmount" class="form-label">ยอดเงินที่ตรวจสอบแล้ว</label>
                <input type="number" min="0" step="0.01" class="form-control" id="paymentReceivedAmount" value="${expectedAmount.toFixed(2)}">
            </div>
            <div class="mb-0">
                <label for="paymentVerifyNote" class="form-label">หมายเหตุ</label>
                <textarea class="form-control" id="paymentVerifyNote" rows="3" placeholder="เช่น ยอดตรงกับสลิป / ธนาคาร / เวลาโอน"></textarea>
            </div>
        `;

        confirmActionButton.className = 'btn btn-success';
        restoreButton(confirmActionButton);
        confirmActionButton.onclick = () => {
            const amountInput = document.getElementById('paymentReceivedAmount');
            const noteInput = document.getElementById('paymentVerifyNote');
            const amount = Number.parseFloat(amountInput ? amountInput.value : '0') || 0;

            if (amount <= 0) {
                alert('กรุณาระบุยอดเงินที่ตรวจสอบแล้ว');
                if (amountInput) amountInput.focus();
                return;
            }

            confirmModal.hide();
            handleBookingAction(id, 'confirm', null, sourceButton, confirmActionButton, {
                payment_received_amount: amount.toFixed(2),
                payment_note: noteInput ? noteInput.value.trim() : ''
            });
        };
        confirmModal.show();
    }

    function showConfirmationModal(id, action, message, sourceButton = null) {
        const modalBody = document.getElementById('actionConfirmModalBody');
        const modalLabel = document.getElementById('actionConfirmModalLabel');

        if (action === 'complete' || action === 'no_show') {
            modalBody.textContent = message;
            modalLabel.textContent = action === 'complete'
                ? 'ยืนยันเสร็จสิ้น'
                : 'ยืนยันไม่มา/ยึดมัดจำ';
            confirmActionButton.className = action === 'no_show' ? 'btn btn-secondary' : 'btn btn-success';
            restoreButton(confirmActionButton);

            confirmActionButton.onclick = () => {
                confirmModal.hide();
                handleBookingAction(id, action, null, sourceButton, confirmActionButton);
            };
            confirmModal.show();
        } else if (action === 'cancel') {
            const cancelReasonModalEl = document.getElementById('cancelReasonModal');
            const cancelReasonModal = new bootstrap.Modal(cancelReasonModalEl);
            const reasonTextarea = document.getElementById('cancellationReason');
            const confirmCancelBtn = document.getElementById('confirmCancelButton');

            reasonTextarea.value = '';
            restoreButton(confirmCancelBtn);

            confirmCancelBtn.onclick = () => {
                const reason = reasonTextarea.value.trim();
                if (!reason) {
                    alert('กรุณาระบุเหตุผลในการยกเลิก');
                    reasonTextarea.focus();
                    return;
                }
                cancelReasonModal.hide();
                handleBookingAction(id, 'cancel', reason, sourceButton, confirmCancelBtn);
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
            modalOkButton.style.display = '';
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
            modalOkButton.style.display = '';
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
            showStatusModal('กำลังส่ง QR Code', 'กรุณารอสักครู่ ระบบกำลังส่งอีเมล...', 'loading');
            fetch('booking_list.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showStatusModal('สำเร็จ', data.message || 'ส่งอีเมลพร้อม QR Code เรียบร้อยแล้ว', true, true);
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
            'completed': 'เสร็จสิ้น',
            'no_show': 'ไม่มา/ยึดมัดจำ',
            'cancelled': 'ยกเลิกแล้ว'
        };
        return statuses[status] || status;
    }

    function actualReceivedAmount(data) {
        const status = String(data.status || '');
        const total = Number.parseFloat(data.price_total) || 0;
        const deposit = Number.parseFloat(data.deposit_amount) || 0;

        if (status === 'completed') {
            return total;
        }

        if (status === 'confirmed' || status === 'no_show') {
            return deposit;
        }

        return 0;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function bookingDownloadUrl(type, filePath) {
        const filename = String(filePath || '').split('/').pop().split('\\').pop();
        return `../user/download.php?type=${encodeURIComponent(type)}&file=${encodeURIComponent(filename)}`;
    }

    function viewDetails(data) {
        const modalBody = document.getElementById('modalBody');
        const bookingTypeThai = translateBookingType(data.booking_type);
        let statusThai = translateStatus(data.status);
        if ((data.status === 'pending' || data.status === 'awaiting_payment') && data.payment_slip) {
            statusThai = 'รอตรวจสอบสลิป';
        }
        const slipUrl = data.payment_slip ? bookingDownloadUrl('slip', data.payment_slip) : '';
        const docUrl = data.attachment_path ? bookingDownloadUrl('doc', data.attachment_path) : '';
        const actualReceived = actualReceivedAmount(data);
        modalBody.innerHTML = `
                <div class="text-center mb-4">
                    <div class="display-6 fw-bold text-primary">#${escapeHtml(data.booking_code)}</div>
                    <div class="text-muted">สถานะ: ${escapeHtml(statusThai)}</div>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-sm-6"><small class="text-muted d-block">ชื่อลูกค้า</small> <strong>${escapeHtml(data.guest_name)}</strong></div>
                    <div class="col-12 col-sm-6"><small class="text-muted d-block">เบอร์โทรศัพท์</small> <strong>${escapeHtml(data.guest_phone)}</strong></div>
                    <div class="col-12 col-sm-6"><small class="text-muted d-block">อีเมล</small> <strong>${escapeHtml(data.guest_email || '-')}</strong></div>
                    <div class="col-12 col-sm-6"><small class="text-muted d-block">ประเภทการจอง</small> <strong>${escapeHtml(bookingTypeThai)}</strong></div>
                    <div class="col-12"><hr></div>
                    <div class="col-6"><small class="text-muted d-block">ยอดรวม</small> <strong class="text-dark">฿${parseFloat(data.price_total).toLocaleString()}</strong></div>
                    <div class="col-6"><small class="text-muted d-block">เงินมัดจำ</small> <strong class="text-success">฿${parseFloat(data.deposit_amount).toLocaleString()}</strong></div>
                    <div class="col-6"><small class="text-muted d-block">เงินคงเหลือ</small> <strong class="text-danger">฿${parseFloat(data.balance_amount).toLocaleString()}</strong></div>
                    <div class="col-6"><small class="text-muted d-block">ยอดรับจริง</small> <strong class="text-primary">฿${actualReceived.toLocaleString()}</strong></div>
                    <div class="col-12"><small class="text-muted d-block">หลักฐานการชำระเงิน</small> 
                        ${data.payment_slip ? `<a href="${slipUrl}" target="_blank" class="btn btn-sm btn-outline-primary mt-1 w-100">ดูสลิป</a>` : '<span class="text-danger">ยังไม่มีสลิป</span>'}
                    </div>
                    ${data.booking_type === 'organization' && data.attachment_path ? `<div class="col-12"><small class="text-muted d-block">เอกสารองค์กร</small><a href="${docUrl}" target="_blank" class="btn btn-sm btn-outline-secondary mt-1 w-100">เปิดดูเอกสาร</a></div>` : ''}
                </div>
            `;
        detailModal.show();
    }

    // Search and filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const bookingDayFilter = document.getElementById('bookingDayFilter');
        const bookingMonthFilter = document.getElementById('bookingMonthFilter');
        const bookingYearFilter = document.getElementById('bookingYearFilter');
        const bookingList = document.getElementById('bookingList');
        const bookingItems = bookingList ? bookingList.querySelectorAll('.booking-item-col') : [];
        const noResultsMessage = document.getElementById('noResultsMessage');
        const filterButtons = document.querySelectorAll('.filter-btn');
        let currentFilter = 'all';

        function applyFilters() {
            const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const selectedDay = bookingDayFilter ? bookingDayFilter.value : '';
            const selectedMonth = bookingMonthFilter ? bookingMonthFilter.value : '';
            const selectedYear = bookingYearFilter ? bookingYearFilter.value : '';
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

                const bookingDateParts = (item.dataset.bookingDate || '').split('-');
                const dateMatch = (!selectedYear || bookingDateParts[0] === selectedYear) &&
                    (!selectedMonth || bookingDateParts[1] === selectedMonth) &&
                    (!selectedDay || bookingDateParts[2] === selectedDay);

                if (statusMatch && searchMatch && dateMatch) {
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
        [bookingDayFilter, bookingMonthFilter, bookingYearFilter].forEach(select => {
            if (select) {
                select.addEventListener('change', applyFilters);
            }
        });
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
