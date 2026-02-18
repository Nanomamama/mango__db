<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');

// --- 1. การเชื่อมต่อ Database และโหลดข้อมูลเดิม (กรณีเรียกผ่าน booking_code) ---
$booking_code_post = $_POST['booking_code'] ?? null;
if ($booking_code_post && empty($_POST['name']) && empty($_POST['email'])) {
    if (file_exists(__DIR__ . '/../db/db.php')) {
        include __DIR__ . '/../db/db.php';
    } elseif (file_exists(__DIR__ . '/../db.php')) {
        include __DIR__ . '/../db.php';
    }

    if (!isset($pdo) && isset($servername, $username, $password, $dbname)) {
        try {
            $dsn = "mysql:host={$servername};dbname={$dbname};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        } catch (PDOException $e) {
            exit(json_encode(['status' => 'error', 'response' => 'DB connection failed']));
        }
    }

    if (!isset($pdo)) exit(json_encode(['status' => 'error', 'response' => 'No DB available']));

    $stmt = $pdo->prepare('SELECT * FROM bookings WHERE booking_code = :code LIMIT 1');
    $stmt->execute([':code' => $booking_code_post]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) exit(json_encode(['status' => 'error', 'response' => 'booking not found']));

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
function convertToThaiDate($dateStr)
{
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
    $instructor_fee = 1800;
    $entrance_fee = $visitor_count * $price_per_person;
    $total_price = $entrance_fee + $instructor_fee;
    $deposit_amount = round($total_price * 0.3);
    $balance_amount = $total_price - $deposit_amount;

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
        $adminMail->Body = "<!DOCTYPE html><html><head><meta charset='utf-8'></head><body>
            <div style='font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; max-width: 650px; margin: 20px auto; border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden;'>
                
                <div style='background-color: #f59e0b; padding: 25px 30px; color: white; display: flex; justify-content: space-between; align-items: center;'>
                    <div style='width: 70%;'>
                        <h2 style='margin: 0; font-size: 20px;'>🔔 มีรายการจองใหม่ (รอดำเนินการ)</h2>
                        <p style='margin: 5px 0 0 0; font-size: 13px; opacity: 0.9;'>โปรดตรวจสอบสถานะวันว่างและแจ้งลูกค้าภายใน 24 ชม.</p>
                    </div>
                    <div style='width: 30%; text-align: right;'>
                        <span style='background: rgba(0,0,0,0.1); padding: 5px 12px; border-radius: 4px; font-weight: bold; font-size: 14px;'>#$booking_code</span>
                    </div>
                </div>

                <div style='padding: 30px; background-color: #ffffff;'>
                    
                    <h4 style='margin: 0 0 15px 0; color: #475569; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; font-size: 16px;'>👤 ข้อมูลลูกค้าและผู้ติดต่อ</h4>
                    <table style='width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 25px;'>
                        <tr>
                            <td style='padding: 8px 0; color: #64748b; width: 35%;'>ชื่อผู้จอง:</td>
                            <td style='padding: 8px 0; font-weight: 600; color: #1e293b;'>$name</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #64748b;'>เบอร์โทรศัพท์:</td>
                            <td style='padding: 8px 0; font-weight: 600;'><a href='tel:$phone' style='color: #2563eb; text-decoration: none;'>$phone</a></td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #64748b;'>วันที่ส่งคำขอ:</td>
                            <td style='padding: 8px 0; font-weight: 600;'>" . date('d/m/Y') . "</td>
                        </tr>
                    </table>

                    <h4 style='margin: 0 0 15px 0; color: #475569; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; font-size: 16px;'>📅 รายละเอียดการเข้าชม</h4>
                    <table style='width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 25px;'>
                        <tr style='background: #fffbeb;'>
                            <td style='padding: 12px 15px; color: #92400e; width: 35%;'>วันที่ต้องการเข้าชม:</td>
                            <td style='padding: 12px 15px; font-weight: bold; color: #92400e; font-size: 16px;'>" . convertToThaiDate($selected_date) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 15px; color: #64748b;'>เวลานัดหมาย:</td>
                            <td style='padding: 10px 15px; font-weight: 600;'>$booking_time น.</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 15px; color: #64748b;'>จำนวนผู้เข้าชม:</td>
                            <td style='padding: 10px 15px; font-weight: 600;'>$visitor_count ท่าน</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 15px; color: #64748b;'>รายการอาหาร:</td>
                            <td style='padding: 10px 15px; font-weight: 600;'>" . ($lunch_request === 'ต้องการ' ? '✅ รับอาหารกลางวัน' : '❌ ไม่รับอาหาร') . "</td>
                        </tr>
                    </table>

                    <div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px;'>
                        <h4 style='margin: 0 0 15px 0; color: #1e3a8a; font-size: 15px;'>💰 สรุปยอดการจัดเก็บเงิน</h4>
                        <table style='width: 100%; border-collapse: collapse; font-size: 14px;'>
                             <tr>
                                <td style='padding: 5px 0; color: #64748b;'>ค่าเข้าชม (" . $visitor_count . " คน):</td>
                                <td style='padding: 5px 0; text-align: right; font-weight: 600;'>฿" . number_format($entrance_fee, 2) . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 5px 0; color: #64748b;'>ค่าวิทยากร:</td>
                                <td style='padding: 5px 0; text-align: right; font-weight: 600;'>฿" . number_format($instructor_fee, 2) . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 5px 0; color: #64748b; border-top: 1px dashed #ccc;'>ราคาแพ็กเกจรวมทั้งหมด:</td>
                                <td style='padding: 5px 0; text-align: right; font-weight: bold; border-top: 1px dashed #ccc;'>฿" . number_format($total_price, 2) . "</td>
                            </tr>
                            <tr style='color: #b45309; font-size: 16px;'>
                                <td style='padding: 10px 0; font-weight: bold;'>ยอดมัดจำที่ต้องเรียกเก็บ (30%):</td>
                                <td style='padding: 10px 0; text-align: right; font-weight: bold; border-bottom: 2px double #b45309;'>฿" . number_format($deposit_amount, 2) . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 10px 0 0 0; color: #64748b; font-size: 13px;'>ยอดคงเหลือต้องตามเก็บหน้างาน:</td>
                                <td style='padding: 10px 0 0 0; text-align: right; color: #64748b;'>฿" . number_format($balance_amount, 2) . "</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div style='background: #f1f5f9; padding: 15px; text-align: center; border-top: 1px solid #e2e8f0;'>
                    <p style='margin: 0; font-size: 12px; color: #64748b;'>Internal Reservation System | สวนแห่งการเรียนรู้</p>
                </div>
            </div>
            </body>
            </html>";

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
        $userMail->Subject = "ได้รับคำขอจองคิวของคุณเรียบร้อยแล้ว ";
        $userMail->Body = "<!DOCTYPE html><html><head><meta charset='utf-8'></head><body>
            <div style='font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; max-width: 600px; margin: 20px auto; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);'>
                
                <div style='background-color: #1e40af; background-image: linear-gradient(to right, #1e40af, #2563eb); color: white; padding: 40px 20px; text-align: center;'>
                    <h2 style='margin: 0; font-size: 24px; letter-spacing: 0.5px;'>คำขอจองคิวของคุณ</h2>
                </div>

                <div style='padding: 30px; line-height: 1.6; color: #374151;'>
                    <p style='margin-top: 0;'>เรียน <strong>คุณ $name</strong>,</p>
                    <p>ขอบคุณที่สนใจเข้าชมสวนแห่งการเรียนรู้ ขณะนี้เจ้าหน้าที่ได้รับข้อมูลของท่านแล้ว และกำลังตรวจสอบตารางการเข้าชมในวันที่ <b>" . convertToThaiDate($selected_date) . "</b></p>
                    
                    <div style='margin: 25px 0; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;'>
                        <div style='background: #f8fafc; padding: 12px 15px; border-bottom: 1px solid #e5e7eb; font-weight: bold; color: #1e40af;'>
                            📊 สรุปรายละเอียดค่าใช้จ่ายเบื้องต้น
                        </div>
                        <div style='padding: 15px;'>
                            <table style='width: 100%; border-collapse: collapse; font-size: 15px;'>
                                 <tr>
                                    <td style='padding: 8px 0; color: #64748b;'>ค่าเข้าชม (" . $visitor_count . " คน):</td>
                                    <td style='padding: 8px 0; text-align: right; font-weight: 500;'>฿" . number_format($entrance_fee, 2) . "</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #64748b;'>ค่าวิทยากร:</td>
                                    <td style='padding: 8px 0; text-align: right; font-weight: 500;'>฿" . number_format($instructor_fee, 2) . "</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #64748b; font-weight: bold; border-top: 1px solid #e5e7eb;'>ยอดรวมทั้งหมด:</td>
                                    <td style='padding: 8px 0; text-align: right; font-weight: bold;'>฿" . number_format($total_price, 2) . "</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #1e40af; font-weight: bold;'>เงินมัดจำที่ต้องชำระ (30%):</td>
                                    <td style='padding: 8px 0; text-align: right; color: #1e40af; font-weight: bold;'>฿" . number_format($deposit_amount, 2) . "</td>
                                </tr>
                                <tr style='border-top: 1px dashed #e5e7eb;'>
                                    <td style='padding: 12px 0 0 0; color: #64748b;'>ยอดคงเหลือชำระหน้างาน:</td>
                                    <td style='padding: 12px 0 0 0; text-align: right; font-weight: bold; color: #10b981;'>฿" . number_format($balance_amount, 2) . "</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div style='background: #fdfdfd; padding: 25px; border-radius: 8px; border: 1px solid #e5e7eb; margin: 25px 0;'>
                        <h4 style='margin-top: 0; color: #1e40af; font-size: 16px;'>📋 ขั้นตอนการดำเนินการต่อไป</h4>
                        <ul style='margin: 10px 0; padding-left: 20px; color: #4b5563;'>
                            <li style='margin-bottom: 10px;'>
                                <b>เช็กอีเมล:</b> รอรับ QR Code สำหรับชำระเงิน (เจ้าหน้าที่จะตรวจสอบคิวให้ภายใน 24 ชม.)
                            </li>
                            <li style='margin-bottom: 10px;'>
                                <b>ชำระเงิน:</b> เมื่อได้รับ QR Code แล้ว สามารถโอนเงินมัดจำ 30% เพื่อรักษาสิทธิ์การจอง
                            </li>
                            <li style='margin-bottom: 10px;'>
                                <b>แนบสลิป:</b> แจ้งชำระเงินโดยการแนบหลักฐานผ่านที่หน้าเว็บไซต์ จองคิวออนไลน์
                            </li>
                            <li style='margin-bottom: 10px;'>
                                <b>รอการยืนยัน:</b> เจ้าหน้าที่จะตรวจสอบและยืนยันการชำระเงินผ่านอีเมลอีกครั้งภายใน 24 ชม.
                            </li>
                        </ul>
                        
                        <div style='background: #fef2f2; border-left: 4px solid #ef4444; padding: 12px 15px; margin-top: 15px;'>
                            <p style='margin: 0; color: #991b1b; font-size: 14px;'>
                                <strong>⚠️ ข้อควรทราบ:</strong> รบกวนชำระเงินและแนบสลิปภายใน 3 วันหลังจากได้รับ QR Code มิเช่นนั้นระบบจะยกเลิกการจองโดยอัตโนมัตินะครับ
                            </p>
                        </div>
                    </div>

                    <div style='margin-top: 30px; padding: 20px; background: #f8fafc; border-radius: 8px; text-align: center;'>
                        <p style='margin: 0 0 10px 0; color: #64748b; font-size: 14px;'>หากมีข้อสงสัยเพิ่มเติม ติดต่อเราได้ที่</p>
                        <p style='margin: 0; color: #1e40af; font-size: 18px; font-weight: bold;'>
                            📞 โทร: <a href='tel:0651078576' style='color: #1e40af; text-decoration: none;'>065-107-8576</a>
                        </p>
                        <p style='margin: 5px 0 0 0; color: #64748b; font-size: 13px;'>ให้บริการในเวลาทำการ: จันทร์ - ศุกร์ (09:00 - 17:00 น.)</p>
                    </div>
                </div>

                <div style='background: #f3f4f6; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;'>
                    <p style='margin: 0; font-size: 12px; color: #9ca3af;'>
                        อีเมลฉบับนี้เป็นการแจ้งเตือนอัตโนมัติ<br>
                        © สวนแห่งการเรียนรู้. All Rights Reserved.
                    </p>
                </div>
            </div>
            </body>
            </html>";

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
