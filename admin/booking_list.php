<?php
    require_once 'auth.php';
    require_once 'db.php';

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

    // จัดการการอัปเดตสถานะการจอง
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'], $_POST['csrf_token'])) {
        // ตรวจ CSRF
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            header('Content-Type: application/json');
            http_response_code(403); // Forbidden
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
            exit;
        }

        $id = (int) $_POST['id'];
        $action = $_POST['action'];
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
                        $userMail->isSMTP();
                        $userMail->Host       = "smtp.gmail.com";
                        $userMail->SMTPAuth   = true;
                        $userMail->Username   = "nanoone342@gmail.com";
                        $userMail->Password   = "cmlt zqfp jveg jxoi"; // App Password
                        $userMail->Port       = 465;
                        $userMail->SMTPSecure = "ssl";
                        $userMail->CharSet    = 'UTF-8';

                        $userMail->setFrom('nanoone342@gmail.com', 'สวนแห่งการเรียนรู้');
                        $userMail->addAddress($booking['guest_email'], $booking['guest_name']);

                        $userMail->isHTML(true);
                        $userMail->Subject = "หนังสือยืนยันการจองเข้าชมสวน (Booking Confirmation) - รหัส: " . $booking['booking_code'];
                        
                        $thai_date = date('d/m/', strtotime($booking['booking_date'])) . (date('Y', strtotime($booking['booking_date'])) + 543);
                        $booking_time_formatted = date('H:i', strtotime($booking['booking_time']));
                        $booking_type_thai = $booking['booking_type'] === 'organization' ? 'หน่วยงาน/องค์กร' : 'บุคคลทั่วไป';
                        $lunch_request_text = $booking['lunch_request'] == 1 ? '✅ ต้องการ' : '❌ ไม่ต้องการ';
                        $price_total_formatted = number_format($booking['price_total'], 2);
                        $deposit_formatted = number_format($booking['deposit_amount'], 2);
                        $balance_formatted = number_format($booking['balance_amount'], 2);


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
                                                <td style='padding: 6px 0;'>ยอดรวมทั้งหมด:</td>
                                                <td style='padding: 6px 0; text-align: right; font-weight: 500;'>" . $price_total_formatted . " บาท</td>
                                            </tr>
                                            <tr>
                                                <td style='padding: 6px 0;'>จำนวนเงินมัดจำที่ชำระแล้ว:</td>
                                                <td style='padding: 6px 0; text-align: right; font-weight: 500; color: #27ae60;'>- " . $deposit_formatted . " บาท</td>
                                            </tr>
                                            <tr style='border-top: 1px solid #ccc;'>
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
                                        <p style='margin-top: 5px; color: #333; font-weight: 500;'>ฝ่ายบริการลูกค้า สวนแห่งการเรียนรู้</p>
                                        <hr style='border: 0; border-top: 1px solid #eee; margin: 25px 0;'>
                                        <p style='font-size: 12px;'>หากท่านมีข้อสงสัยประการใด สามารถติดต่อสอบถามเพิ่มเติมได้ที่เบอร์โทรศัพท์ 065-107-8576 <br> หรือตอบกลับอีเมลฉบับนี้</p>
                                    </div>
                                </div>
                            </div>
                        ";
                        $userMail->send();
                    } catch (Exception $e) {
                        error_log("Confirmation Mailer Error for booking ID {$id}: " . $e->getMessage());
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
                        $cancelMail->isSMTP();
                        $cancelMail->Host       = "smtp.gmail.com";
                        $cancelMail->SMTPAuth   = true;
                        $cancelMail->Username   = "nanoone342@gmail.com";
                        $cancelMail->Password   = "cmlt zqfp jveg jxoi"; // App Password
                        $cancelMail->Port       = 465;
                        $cancelMail->SMTPSecure = "ssl";
                        $cancelMail->CharSet    = 'UTF-8';

                        $cancelMail->setFrom('nanoone342@gmail.com', 'สวนแห่งการเรียนรู้');
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
                                    
                                    <p style='color: #333; font-size: 16px; text-indent: 2em;'>ทางสวนแห่งการเรียนรู้มีความเสียใจที่ต้องแจ้งให้ท่านทราบว่า รายการจองของท่านสำหรับวันที่ <strong>" . $thai_date . "</strong>) ได้ถูกยกเลิกแล้ว</p>
                                    
                                    <div style='margin: 30px 0; padding: 20px; background-color: #fffbe6; border-left: 4px solid #f59e0b; border-radius: 4px;'>
                                        <h4 style='margin-top: 0; color: #d35400; font-size: 16px;'>เหตุผลในการยกเลิก:</h4> 
                                        <p style='margin-bottom: 0; color: #854d0e; font-size: 15px;'>" . (!empty($rejection_reason) ? nl2br(htmlspecialchars($rejection_reason)) : 'ไม่ระบุ') . "</p>
                                    </div>

                                    <p style='color: #333; font-size: 16px;'>หากท่านมีข้อสงสัย หรือต้องการดำเนินการจองใหม่อีกครั้ง กรุณาติดต่อเจ้าหน้าที่โดยตรงที่เบอร์โทรศัพท์ 065-107-8576 หรือตอบกลับอีเมลฉบับนี้</p>
                                    <p style='color: #333; font-size: 16px;'>ทางเราต้องขออภัยในความไม่สะดวกมา ณ ที่นี้</p>
                                    
                                    <div style='margin-top: 40px; text-align: center; color: #888; font-size: 14px;'>
                                        <p style='margin: 0;'>ขอแสดงความนับถือ</p>
                                        <p style='margin-top: 5px; color: #333; font-weight: 500;'>ฝ่ายบริการลูกค้า สวนแห่งการเรียนรู้</p>
                                    </div>
                                </div>
                            </div>
                        ";

                        $cancelMail->send();
                    } catch (Exception $e) {
                        error_log("Cancellation Mailer Error for booking ID {$id}: " . $e->getMessage());
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
        'confirmed' => 0,
        'cancelled' => 0,
        'total' => count($bookings)
    ];
    foreach ($bookings as $b) {
        if (isset($stats[$b['status']])) {
            $stats[$b['status']]++;
        }
    }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการการจอง - ระบบ Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #2ecc71;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --light: #f8f9fa;
            --dark: #212529;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            color: #333;
            min-height: 100vh;
        }

        .dashboard-header {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: white;
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            border-radius: 50px;
            margin-bottom: 2rem;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 50px;
        }

        .admin-profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .booking-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 1.5rem;
            border: none;
            position: relative;
        }

        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
        }

        .booking-card::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .booking-card-header {
            padding: 1rem 1.5rem;
            background: linear-gradient(90deg, rgba(67, 97, 238, 0.05), transparent);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-badge {
            padding: 0.35rem 0.8rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .status-pending { 
            background: rgba(246, 194, 62, 0.15); 
            color: #d39e00; 
        }
        .status-รอตรวจสอบสลิป { background: rgba(54, 89, 226, 0.15); color: #3659e2; }
        .status-confirmed { background: rgba(46, 204, 113, 0.15); color: #27ae60; }
        .status-cancelled { background: rgba(231, 76, 60, 0.15); color: #c0392b; }

        .info-label { font-weight: 500; color: #6c757d; min-width: 120px; display: inline-block; }
        .info-value { color: #2d3436; font-weight: 400; }

        .action-btn {
            border-radius: 50px;
            padding: 0.5rem 1.2rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
        }

        .btn-confirm { background: rgba(46, 204, 113, 0.1); color: #27ae60; }
        .btn-confirm:hover { background: #27ae60; color: white; }
        
        .btn-cancel { background: rgba(231, 76, 60, 0.1); color: #c0392b; }
        .btn-cancel:hover { background: #c0392b; color: white; }

        .stats-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            border: none;
        }
        
        .stats-icon { font-size: 2rem; margin-bottom: 0.5rem; }
        .stats-value { font-size: 1.8rem; font-weight: 700; }

        .slip-image-container {
            height: 150px;
            background-color: #f0f2f5;
            border-radius: 8px;
        }

        /* Loading Overlay and Spinner Styles */
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
            /* display: flex; */
            opacity: 1;
            visibility: visible;
            margin-left: 0 !important; /* Override layout margin */

        }

        .spinner-container {
            position: relative;
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
        }

        .spinner {
            width: 70px;
            height: 70px;
            border: 6px solid #e0e0e0;
            border-top: 6px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .spinner-inner {
            width: 50px;
            height: 50px;
            border: 4px solid transparent;
            border-top: 4px solid var(--secondary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite reverse;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .spinner-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            color: var(--primary);
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        .success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            margin-bottom: 20px;
        }
        
        .success-checkmark .check-icon {
            width: 80px;
            height: 80px;
            position: relative;
            border-radius: 50%;
            box-sizing: content-box;
            border: 4px solid var(--success);
        }
        
        .success-checkmark .check-icon::before {
            top: 3px;
            left: -2px;
            width: 30px;
            transform-origin: 100% 50%;
            border-radius: 100px 0 0 100px;
        }
        
        .success-checkmark .check-icon::after {
            top: 0;
            left: 30px;
            width: 60px;
            transform-origin: 0 50%;
            border-radius: 0 100px 100px 0;
            animation: rotate-circle 4.25s ease-in;
        }
        
        .success-checkmark .check-icon .icon-line {
            height: 5px;
            background-color: var(--success);
            border-radius: 2px;
            position: absolute;
            z-index: 10;
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
        
        .success-checkmark .check-icon .icon-circle {
            top: -4px;
            left: -4px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            position: absolute;
            box-sizing: content-box;
            border: 4px solid rgba(46, 204, 113, 0.2);
        }
        
        .success-checkmark .check-icon .icon-fix {
            top: 8px;
            width: 5px;
            left: 26px;
            height: 85px;
            position: absolute;
            transform: rotate(-45deg);
            background-color: white;
        }

        @keyframes rotate-circle {
            0% { transform: rotate(-45deg); }
            5% { transform: rotate(-45deg); }
            12% { transform: rotate(-405deg); }
            100% { transform: rotate(-405deg); }
        }
        
        @keyframes icon-line-tip {
            0% { width: 0; left: 1px; top: 19px; }
            54% { width: 0; left: 1px; top: 19px; }
            70% { width: 50px; left: -8px; top: 37px; }
            84% { width: 17px; left: 21px; top: 48px; }
            100% { width: 25px; left: 14px; top: 45px; }
        }
        
        @keyframes icon-line-long {
            0% { width: 0; right: 46px; top: 54px; }
            65% { width: 0; right: 46px; top: 54px; }
            84% { width: 55px; right: 0px; top: 35px; }
            100% { width: 47px; right: 8px; top: 38px; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
<div class="p-4" style="margin-left: 250px; flex: 1;">
        <!-- Header -->
        <header class="dashboard-header pb-4 mb-4">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h2 class="dashboard-title mb-0">จัดการรายการจอง</h2>
                        </div>
                        <div class="d-flex align-items-center gap-3 mt-2 mt-md-0">
                            <div class="admin-profile">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=random&color=fff" alt="Admin">
                                <span><?= htmlspecialchars($admin_name) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
        </header>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon text-primary"><i class="bi bi-list-ul"></i></div>
                <div class="stats-value"><?= $stats['total'] ?></div>
                <div class="text-muted">ทั้งหมด</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon text-warning"><i class="bi bi-clock-history"></i></div>
                <div class="stats-value"><?= $stats['pending'] ?></div>
                <div class="text-muted">รอยืนยัน</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon text-success"><i class="bi bi-check-circle"></i></div>
                <div class="stats-value"><?= $stats['confirmed'] ?></div>
                <div class="text-muted">ยืนยันแล้ว</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon text-danger"><i class="bi bi-x-circle"></i></div>
                <div class="stats-value"><?= $stats['cancelled'] ?></div>
                <div class="text-muted">ยกเลิกแล้ว</div>
            </div>
        </div>
    </div>

    <!-- Booking List -->
    <div class="row">
        <?php if (empty($bookings)): ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="mt-3 text-muted">ไม่พบข้อมูลการจอง</p>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="booking-card">
                        <div class="booking-card-header">
                            <span class="fw-bold text-primary">#<?= htmlspecialchars($booking['booking_code']) ?></span>                            
                            <?php
                                $display_status = $booking['status'];
                                $display_status_class = $booking['status'];
                                if ($booking['status'] == 'pending' && !empty($booking['payment_slip'])) {
                                    $display_status = 'รอตรวจสอบสลิป';
                                    $display_status_class = 'รอตรวจสอบสลิป';
                                }
                            ?>
                            <span class="status-badge status-<?= $display_status_class ?>">
                                <?php
                                    if ($display_status == 'รอตรวจสอบสลิป') echo '<i class="bi bi-receipt me-1"></i> รอตรวจสอบสลิป';
                                    elseif ($booking['status'] == 'pending') echo '<i class="bi bi-hourglass-split me-1"></i> รอยืนยัน';
                                    elseif ($booking['status'] == 'confirmed') echo '<i class="bi bi-check-circle-fill me-1"></i> ยืนยันแล้ว';
                                    elseif ($booking['status'] == 'cancelled') echo '<i class="bi bi-x-circle-fill me-1"></i> ยกเลิกแล้ว';
                                    else echo '<i class="bi bi-question-circle me-1"></i> ' . htmlspecialchars($display_status);
                                ?>
                            </span>
                        </div>
                        <div class="p-4">
                            <div class="mb-2">
                                <span class="info-label"><i class="bi bi-person me-2"></i>ลูกค้า:</span>
                                <span class="info-value"><?= htmlspecialchars($booking['guest_name']) ?></span>
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

                            <?php if ($booking['booking_type'] === 'organization' && !empty($booking['attachment_path'])): ?>
                                <!-- <div class="mb-3">
                                    <span class="info-label"><i class="bi bi-file-earmark-text me-2"></i>เอกสารแนบ:</span>
                                    <a href="../user/<?= htmlspecialchars($booking['attachment_path']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> เปิดดูเอกสาร
                                    </a>
                                </div> -->
                            <?php endif; ?>

                            <div class="mb-3">
                                <span class="info-label d-block mb-2"><i class="bi bi-receipt me-2"></i>สลิปชำระเงิน:</span>
                                <?php if (!empty($booking['payment_slip'])): ?>
                                    <a href="../user/Paymentslip-Gardenreservation/<?= htmlspecialchars($booking['payment_slip']) ?>" target="_blank">
                                        <img src="../user/Paymentslip-Gardenreservation/<?= htmlspecialchars($booking['payment_slip']) ?>" class="img-fluid rounded" style="width: 100%; height: 150px; object-fit: cover;" alt="Payment Slip">
                                    </a>
                                <?php else: ?>
                                    <div class="slip-image-container d-flex align-items-center justify-content-center text-muted">
                                        <div class="text-center">
                                            <i class="bi bi-image fs-3"></i><br>ยังไม่มีสลิป
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <hr class="my-3 opacity-50">

                            <div class="d-flex gap-2 justify-content-end">
                                <?php if ($booking['status'] != 'confirmed' && $booking['status'] != 'cancelled'): ?>
                                    <button class="action-btn btn-confirm" onclick="showConfirmationModal(<?= $booking['bookings_id'] ?>, 'confirm', 'ยืนยันการจองนี้ใช่หรือไม่?')">
                                        <i class="bi bi-check2-circle me-1"></i> ยืนยัน
                                    </button>
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

<!-- Modal สำหรับดูรายละเอียด -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">รายละเอียดการจอง</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div id="modalBody" class="modal-body p-4">
                <!-- ข้อมูลจะถูกใส่ด้วย JS -->
            </div>
        </div>
    </div>
</div>

<!-- Modal for Action Confirmation -->
<div class="modal fade" id="actionConfirmModal" tabindex="-1" aria-labelledby="actionConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="actionConfirmModalLabel">ยืนยันการดำเนินการ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4" id="actionConfirmModalBody">
                <!-- Confirmation message will be injected here -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="confirmActionButton">ยืนยัน</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Cancellation Reason -->
<div class="modal fade" id="cancelReasonModal" tabindex="-1" aria-labelledby="cancelReasonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="cancelReasonModalLabel">ระบุเหตุผลการยกเลิก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

<!-- Modal for Status/Error -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0" id="statusModalHeader">
                <h5 class="modal-title" id="statusModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

    // Function to handle booking actions (confirm/cancel)
    function handleBookingAction(id, action, reason = null) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('action', action);
        formData.append('csrf_token', '<?= $csrf_token ?>');
        if (reason) {
            formData.append('reason', reason);
        }

        fetch('booking_list.php', { method: 'POST', body: formData })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success modal with checkmark animation
                showStatusModal('สำเร็จ', 'ดำเนินการเรียบร้อยแล้ว', true, true);
                
                // When success modal is closed, reload page
                statusModalEl.addEventListener('hidden.bs.modal', () => {
                    location.reload();
                }, { once: true });
            } else {
                showStatusModal('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถดำเนินการได้', false, false);
            }
        })
        .catch(error => {
            showStatusModal('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', false, false);
        });
    }

    // Function to show confirmation modal
    function showConfirmationModal(id, action, message) {
        const modalBody = document.getElementById('actionConfirmModalBody');
        const modalLabel = document.getElementById('actionConfirmModalLabel');

        if (action === 'confirm') {
            modalBody.textContent = message;
            modalLabel.textContent = 'ยืนยันการจอง';
            confirmActionButton.className = 'btn btn-success';
            
            // Set onclick for confirm button
            confirmActionButton.onclick = () => { 
                confirmModal.hide();
                handleBookingAction(id, action); 
            };
            
            confirmModal.show();
        } else if (action === 'cancel') {
            // Show cancellation reason modal instead
            const cancelReasonModalEl = document.getElementById('cancelReasonModal');
            const cancelReasonModal = new bootstrap.Modal(cancelReasonModalEl);
            const reasonTextarea = document.getElementById('cancellationReason');
            const confirmCancelBtn = document.getElementById('confirmCancelButton');

            reasonTextarea.value = ''; // Clear previous reason

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

    // Function to show status modal (success/error)
    function showStatusModal(title, message, isSuccess = true, showCheckmark = false) {
        const modalLabel = document.getElementById('statusModalLabel');
        const modalMessage = document.getElementById('statusModalMessage');
        const modalIcon = document.getElementById('statusModalIcon');
        const modalHeader = document.getElementById('statusModalHeader');
        const modalOkButton = document.getElementById('statusModalOkButton');

        modalLabel.textContent = title;
        modalMessage.textContent = message;

        // Clear previous icon
        modalIcon.innerHTML = '';
        
        if (isSuccess) {
            modalHeader.classList.remove('bg-danger', 'text-white');
            modalHeader.classList.add('bg-light');
            
            if (showCheckmark) {
                // Show checkmark animation for success
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
        
        // Show modal
        statusModal.show();
    }

    // Helper function to translate booking types
    function translateBookingType(type) {
        const types = {
            'private': 'บุคคลทั่วไป',
            'organization': 'หน่วยงาน/องค์กร'
        };
        return types[type] || type;
    }

    // Helper function to translate status
    function translateStatus(status) {
        const statuses = {
            'pending': 'รอยืนยัน',
            'confirmed': 'ยืนยันแล้ว',
            'cancelled': 'ยกเลิกแล้ว',
        };
        return statuses[status] || status;
    }

    // Function to view booking details in modal
    function viewDetails(data) {
        const modalBody = document.getElementById('modalBody');
        const bookingTypeThai = translateBookingType(data.booking_type);
        
        let statusThai = translateStatus(data.status);
        if (data.status === 'pending' && data.payment_slip) {
            statusThai = 'รอตรวจสอบสลิป';
        }

        modalBody.innerHTML = `
            <div class="text-center mb-4">
                <div class="display-6 fw-bold text-primary">#${data.booking_code}</div>
                <div class="text-muted">สถานะ: ${statusThai}</div>
            </div>
            <div class="row g-3">
                <div class="col-6"><small class="text-muted d-block">ชื่อลูกค้า</small> <strong>${data.guest_name}</strong></div>
                <div class="col-6"><small class="text-muted d-block">เบอร์โทรศัพท์</small> <strong>${data.guest_phone}</strong></div>
                <div class="col-6"><small class="text-muted d-block">อีเมล</small> <strong>${data.guest_email || '-'}</strong></div>
                <div class="col-6"><small class="text-muted d-block">ประเภทการจอง</small> <strong>${bookingTypeThai}</strong></div>
                <div class="col-12"><hr></div>
                <div class="col-6"><small class="text-muted d-block">ยอดรวม</small> <strong class="text-dark">฿${parseFloat(data.price_total).toLocaleString()}</strong></div>
                <div class="col-6"><small class="text-muted d-block">เงินมัดจำ</small> <strong class="text-success">฿${parseFloat(data.deposit_amount).toLocaleString()}</strong></div>
                <div class="col-12"><small class="text-muted d-block">หลักฐานการชำระเงิน</small> 
                    ${data.payment_slip ? `<a href="../user/Paymentslip-Gardenreservation/${data.payment_slip}" target="_blank" class="btn btn-sm btn-outline-primary mt-1 w-100">ดูสลิป</a>` : '<span class="text-danger">ยังไม่มีสลิป</span>'}
                </div>
                ${data.booking_type === 'organization' && data.attachment_path ? `<div class="col-12"><small class="text-muted d-block">เอกสารองค์กร</small><a href="../user/${data.attachment_path}" target="_blank" class="btn btn-sm btn-outline-secondary mt-1 w-100">เปิดดูเอกสาร</a></div>` : ''}
            </div>
        `;
        detailModal.show();
    }

    // Also update the onclick for cancel button to use confirmation modal
    document.addEventListener('DOMContentLoaded', function() {
        // Find all cancel buttons and update their onclick to use confirmation modal
        // This part is no longer needed as the HTML is directly updated.
    });
</script>
</body>
</html>
</html>