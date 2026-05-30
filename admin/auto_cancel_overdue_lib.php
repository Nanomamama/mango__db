<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../user/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../user/PHPMailer/SMTP.php';
require_once __DIR__ . '/../user/PHPMailer/Exception.php';

if (!function_exists('auto_cancel_send_timeout_email')) {
    function auto_cancel_send_timeout_email(array $booking): bool
    {
        if (empty($booking['guest_email'])) {
            return false;
        }

        $guestName = (string)($booking['guest_name'] ?? '');
        $bookingCode = (string)($booking['booking_code'] ?? '');
        $dueAt = !empty($booking['payment_due_at'])
            ? date('d/m/', strtotime((string)$booking['payment_due_at'])) . (date('Y', strtotime((string)$booking['payment_due_at'])) + 543) . ' เวลา ' . date('H:i', strtotime((string)$booking['payment_due_at'])) . ' น.'
            : 'ตามเวลาที่กำหนด';

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = app_env('MAIL_USERNAME');
            $mail->Password   = app_env('MAIL_PASSWORD_ALT') ?: app_env('MAIL_PASSWORD');
            $mail->Port       = 465;
            $mail->SMTPSecure = 'ssl';
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('nanoone342@gmail.com', 'สวนลุงเผือก');
            $mail->addAddress((string)$booking['guest_email'], $guestName);

            $mail->isHTML(true);
            $mail->Subject = 'แจ้งยกเลิกการจองอัตโนมัติ ';
            $mail->Body = "
                <div style='font-family: Sarabun, Kanit, Arial, sans-serif; padding: 24px; color: #333; line-height: 1.7;'>
                    <h2 style='color: #c0392b; margin-top: 0;'>แจ้งยกเลิกการจอง</h2>
                    <p>เรียน คุณ <strong>" . htmlspecialchars($guestName, ENT_QUOTES, 'UTF-8') . "</strong></p>
                    <p>ระบบได้ยกเลิกการจองของคุณ โดยอัตโนมัติ เนื่องจากไม่พบการแนบหลักฐานการชำระเงินภายในกำหนด</p>
                    <p><strong>กำหนดชำระเงิน:</strong> " . htmlspecialchars($dueAt, ENT_QUOTES, 'UTF-8') . "</p>
                    <p>หากท่านยังต้องการเข้าชมสวน กรุณาทำรายการจองใหม่ผ่านเว็บไซต์อีกครั้ง</p>
                    <hr>
                    <p style='font-size: 14px; color: #777;'>อีเมลนี้ส่งจากระบบจองคิวอัตโนมัติ</p>
                </div>";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Auto cancel mailer error for booking ID ' . ($booking['bookings_id'] ?? '-') . ': ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('auto_cancel_overdue_bookings')) {
    function auto_cancel_overdue_bookings(mysqli $conn, ?callable $logger = null): array
    {
        $log = static function (string $message) use ($logger): void {
            if ($logger) {
                $logger($message);
            }
        };

        $sql = "
            SELECT bookings_id, guest_name, guest_email, booking_code, payment_due_at
            FROM bookings
            WHERE status = 'awaiting_payment'
              AND payment_due_at IS NOT NULL
              AND payment_due_at < NOW()
              AND (payment_slip IS NULL OR payment_slip = '')
              AND cancelled_at IS NULL
        ";

        $result = $conn->query($sql);
        if (!$result) {
            $log('Auto cancel query failed: ' . $conn->error);
            return ['found' => 0, 'cancelled' => 0, 'emails_sent' => 0, 'errors' => 1];
        }

        $overdueBookings = [];
        while ($row = $result->fetch_assoc()) {
            $overdueBookings[] = $row;
        }

        $summary = [
            'found' => count($overdueBookings),
            'cancelled' => 0,
            'emails_sent' => 0,
            'errors' => 0,
        ];

        if (empty($overdueBookings)) {
            $log('No overdue unpaid bookings found.');
            return $summary;
        }

        $updateStmt = $conn->prepare("
            UPDATE bookings
            SET status = 'cancelled',
                cancelled_at = NOW(),
                cancel_reason = 'payment_timeout',
                updated_at = NOW()
            WHERE bookings_id = ?
              AND status = 'awaiting_payment'
              AND payment_due_at < NOW()
              AND (payment_slip IS NULL OR payment_slip = '')
              AND cancelled_at IS NULL
        ");

        $emailStmt = $conn->prepare("
            UPDATE bookings
            SET cancellation_email_sent_at = NOW(), updated_at = NOW()
            WHERE bookings_id = ?
              AND status = 'cancelled'
              AND cancellation_email_sent_at IS NULL
        ");

        if (!$updateStmt || !$emailStmt) {
            $log('Auto cancel prepare failed: ' . $conn->error);
            $summary['errors']++;
            return $summary;
        }

        foreach ($overdueBookings as $booking) {
            $bookingId = (int)$booking['bookings_id'];
            $bookingCode = (string)$booking['booking_code'];

            $updateStmt->bind_param('i', $bookingId);
            if (!$updateStmt->execute()) {
                $summary['errors']++;
                $log("Failed to cancel booking {$bookingCode}: " . $updateStmt->error);
                continue;
            }

            if ($updateStmt->affected_rows !== 1) {
                $log("Skipped booking {$bookingCode}: status changed or slip was submitted.");
                continue;
            }

            $summary['cancelled']++;
            $log("Cancelled booking {$bookingCode}.");

            if (auto_cancel_send_timeout_email($booking)) {
                $emailStmt->bind_param('i', $bookingId);
                if ($emailStmt->execute()) {
                    $summary['emails_sent']++;
                    $log("Cancellation email sent for booking {$bookingCode}.");
                } else {
                    $summary['errors']++;
                    $log("Email sent but timestamp update failed for booking {$bookingCode}: " . $emailStmt->error);
                }
            } else {
                $log("Booking {$bookingCode} cancelled, but cancellation email was not sent.");
            }
        }

        $updateStmt->close();
        $emailStmt->close();

        return $summary;
    }
}
