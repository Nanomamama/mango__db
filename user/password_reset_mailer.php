<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

function sendPasswordResetCodeEmail(string $email, string $code, string $name = ''): bool
{
    $displayName = trim($name) !== '' ? $name : $email;
    $fromEmail = app_env('MAIL_FROM') ?: app_env('MAIL_USERNAME') ?: 'nanoone342@gmail.com';
    $fromName = app_env('MAIL_FROM_NAME') ?: 'สวนลุงเผือก';
    $mailPassword = app_env('MAIL_PASSWORD_ALT') ?: app_env('MAIL_PASSWORD');

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = app_env('MAIL_USERNAME');
        $mail->Password = $mailPassword;
        $mail->Port = 465;
        $mail->SMTPSecure = 'ssl';
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($email, $displayName);
        $mail->isHTML(true);
        $mail->Subject = 'รหัสยืนยันสำหรับกู้คืนรหัสผ่าน';
        $mail->Body = "
            <!DOCTYPE html>
            <html lang='th'>
            <head><meta charset='UTF-8'></head>
            <body style='margin:0; padding:0; background:#f3f4f6; font-family:Arial, Tahoma, sans-serif; color:#1f2937;'>
                <div style='max-width:560px; margin:24px auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;'>
                    <div style='background:#143a2c; color:#ffffff; padding:24px; text-align:center;'>
                        <h2 style='margin:0; font-size:22px;'>กู้คืนรหัสผ่าน</h2>
                    </div>
                    <div style='padding:28px; line-height:1.7;'>
                        <p style='margin-top:0;'>เรียน คุณ " . htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . "</p>
                        <p>ระบบได้รับคำขอกู้คืนรหัสผ่านของคุณแล้ว กรุณาใช้รหัสยืนยันด้านล่างภายใน 5 นาที</p>
                        <div style='margin:24px 0; padding:18px; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; text-align:center;'>
                            <div style='font-size:34px; letter-spacing:8px; font-weight:700; color:#065f46;'>" . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . "</div>
                        </div>
                        <p style='font-size:14px; color:#6b7280;'>หากคุณไม่ได้เป็นผู้ขอรหัสนี้ สามารถละเว้นอีเมลฉบับนี้ได้</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        $mail->AltBody = "รหัสยืนยันสำหรับกู้คืนรหัสผ่านของคุณคือ {$code} ใช้ได้ภายใน 5 นาที";

        return $mail->send();
    } catch (Exception $e) {
        error_log('Password reset email failed: ' . $e->getMessage());
        return false;
    }
}

