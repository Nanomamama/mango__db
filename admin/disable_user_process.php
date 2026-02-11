<?php
require_once 'auth.php'; // For admin authentication and session_start()
require_once __DIR__ . '/../db/db.php'; // Database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Path to PHPMailer files (relative to this script)
// Adjust path if necessary based on your project structure
require_once '../user/PHPMailer/PHPMailer.php';
require_once '../user/PHPMailer/SMTP.php';
require_once '../user/PHPMailer/Exception.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_users.php?error=" . urlencode("Invalid request method."));
    exit;
}

// CSRF Token validation
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    header("Location: admin_users.php?error=" . urlencode("Invalid CSRF token."));
    exit;
}

$member_id = (int) $_POST['id'];
$reason = trim($_POST['reason'] ?? '');

if ($member_id <= 0 || empty($reason)) {
    header("Location: admin_users.php?error=" . urlencode("Missing user ID or reason."));
    exit;
}

$conn->begin_transaction();
$success = false;
$user_email = '';
$user_fullname = '';

try {
    // 1. Get user details before disabling
    $stmt_select = $conn->prepare("SELECT email, fullname FROM members WHERE member_id = ?");
    if (!$stmt_select) {
        throw new Exception("Failed to prepare select statement: " . $conn->error);
    }
    $stmt_select->bind_param("i", $member_id);
    $stmt_select->execute();
    $result_select = $stmt_select->get_result();
    if ($result_select->num_rows === 0) {
        throw new Exception("User not found.");
    }
    $user_data = $result_select->fetch_assoc();
    $user_email = $user_data['email'];
    $user_fullname = $user_data['fullname'];
    $stmt_select->close();

    // 2. Update user status to disabled
    $stmt_update = $conn->prepare("UPDATE members SET status = 0 WHERE member_id = ?");
    if (!$stmt_update) {
        throw new Exception("Failed to prepare update statement: " . $conn->error);
    }
    $stmt_update->bind_param("i", $member_id);
    if (!$stmt_update->execute()) {
        throw new Exception("Failed to update user status: " . $stmt_update->error);
    }
    $stmt_update->close();

    // 3. Send email to the user
    if (!empty($user_email)) {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = "smtp.gmail.com";
        $mail->SMTPAuth   = true;
        $mail->Username   = "nanoone342@gmail.com"; // Replace with your actual email
        $mail->Password   = "cmlt zqfp jveg jxoi"; // Replace with your App Password
        $mail->Port       = 465;
        $mail->SMTPSecure = "ssl";
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('nanoone342@gmail.com', 'สวนแห่งการเรียนรู้'); // Replace with your actual email and name
        $mail->addAddress($user_email, $user_fullname);

        $mail->isHTML(true);
        $mail->Subject = "แจ้งปิดใช้งานบัญชีผู้ใช้ของคุณ - สวนแห่งการเรียนรู้";
        $mail->Body = "
            <div style='font-family: \"Sarabun\", \"Kanit\", sans-serif; padding: 20px; background-color: #f4f7f6; line-height: 1.7;'>
                <div style='max-width: 650px; margin: auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); border-top: 5px solid #e74a3b;'>
                    <div style='text-align: center; margin-bottom: 25px;'>
                        <h1 style='color: #c0392b; margin: 0; font-size: 26px; font-weight: 600;'>แจ้งปิดใช้งานบัญชีผู้ใช้</h1>
                        <p style='color: #555; font-size: 15px; margin-top: 5px;'>Account Deactivation Notice</p>
                    </div>
                    <p style='color: #333; font-size: 16px;'>เรียน คุณ " . htmlspecialchars($user_fullname) . ",</p>
                    <p style='color: #333; font-size: 16px; text-indent: 2em;'>ทางสวนแห่งการเรียนรู้ขอแจ้งให้ท่านทราบว่า บัญชีผู้ใช้ของท่านได้ถูกปิดใช้งานเรียบร้อยแล้ว</p>
                    <div style='margin: 30px 0; padding: 20px; background-color: #fffbe6; border-left: 4px solid #f59e0b; border-radius: 4px;'>
                        <h4 style='margin-top: 0; color: #d35400; font-size: 16px;'>เหตุผลในการปิดใช้งาน:</h4> 
                        <p style='margin-bottom: 0; color: #854d0e; font-size: 15px;'>" . nl2br(htmlspecialchars($reason)) . "</p>
                    </div>
                    <p style='color: #333; font-size: 16px;'>หากท่านมีข้อสงสัย หรือต้องการสอบถามข้อมูลเพิ่มเติมเกี่ยวกับสถานะบัญชีของท่าน กรุณาติดต่อเจ้าหน้าที่โดยตรงที่เบอร์โทรศัพท์ 065-107-8576 หรือตอบกลับอีเมลฉบับนี้</p>
                    <p style='color: #333; font-size: 16px;'>ทางเราต้องขออภัยในความไม่สะดวกมา ณ ที่นี้</p>
                    <div style='margin-top: 40px; text-align: center; color: #888; font-size: 14px;'>
                        <p style='margin: 0;'>ขอแสดงความนับถือ</p>
                        <p style='margin-top: 5px; color: #333; font-weight: 500;'>ฝ่ายบริการลูกค้า สวนแห่งการเรียนรู้</p>
                    </div>
                </div>
            </div>";
        $mail->send();
    }
    
    $conn->commit();
    $success = true;

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error disabling user ID $member_id: " . $e->getMessage());
    header("Location: admin_users.php?error=" . urlencode("Failed to disable user: " . $e->getMessage()));
    exit;
} finally {
    $conn->close();
}

if ($success) {
    header("Location: admin_users.php?success=" . urlencode("User account disabled and email sent to " . $user_fullname . "."));
} else {
    // This part might not be reached due to exit in catch block, but it's good for completeness.
    header("Location: admin_users.php?error=" . urlencode("Failed to disable user. Please check logs."));
}
exit;

?>