<?php
session_start();
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/line_service.php';

function line_callback_redirect_to(): string
{
    $target = $_SESSION['line_login_redirect'] ?? 'member_profile.php';
    return in_array($target, ['member_profile.php', 'bookings.php'], true) ? $target : 'member_profile.php';
}

function line_callback_fail(string $message): void
{
    $_SESSION['line_link_error'] = $message;
    unset($_SESSION['line_login_state'], $_SESSION['line_login_nonce']);
    header('Location: ' . line_callback_redirect_to());
    unset($_SESSION['line_login_redirect']);
    exit;
}

if (empty($_SESSION['member_id'])) {
    header('Location: member_login.php');
    exit;
}

if (empty($_GET['code']) || empty($_GET['state']) || !hash_equals($_SESSION['line_login_state'] ?? '', (string) $_GET['state'])) {
    line_callback_fail('การเชื่อมต่อ LINE ไม่ถูกต้อง กรุณาลองใหม่');
}

$channelId = app_env('LINE_LOGIN_CHANNEL_ID', '');
$channelSecret = app_env('LINE_LOGIN_CHANNEL_SECRET', '');
$callbackUrl = app_env('LINE_LOGIN_CALLBACK_URL', '');

if ($channelId === '' || $channelSecret === '' || $callbackUrl === '') {
    line_callback_fail('ยังไม่ได้ตั้งค่า LINE Login ให้ครบถ้วน');
}

$ch = curl_init('https://api.line.me/oauth2/v2.1/token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'authorization_code',
    'code' => (string) $_GET['code'],
    'redirect_uri' => $callbackUrl,
    'client_id' => $channelId,
    'client_secret' => $channelSecret,
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$tokenResult = curl_exec($ch);
$tokenHttpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$tokenError = curl_error($ch);
curl_close($ch);

$tokenData = json_decode((string) $tokenResult, true);
if ($tokenError || $tokenHttpCode !== 200 || empty($tokenData['access_token'])) {
    error_log('LINE Login token error: ' . ($tokenError ?: (string) $tokenResult));
    line_callback_fail('เชื่อมต่อ LINE ไม่สำเร็จ กรุณาลองใหม่');
}

$ch = curl_init('https://api.line.me/v2/profile');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenData['access_token']]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$profileResult = curl_exec($ch);
$profileHttpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$profileError = curl_error($ch);
curl_close($ch);

$profile = json_decode((string) $profileResult, true);
if ($profileError || $profileHttpCode !== 200 || empty($profile['userId'])) {
    error_log('LINE profile error: ' . ($profileError ?: (string) $profileResult));
    line_callback_fail('ดึงข้อมูล LINE ไม่สำเร็จ กรุณาลองใหม่');
}

$memberId = (int) $_SESSION['member_id'];
$lineUserId = (string) $profile['userId'];
$displayName = (string) ($profile['displayName'] ?? '');

if (!line_can_push_to_user($lineUserId)) {
    line_callback_fail('กรุณาเพิ่ม LINE Official Account สวนลุงเผือกเป็นเพื่อนก่อน แล้วกดเชื่อมต่อ LINE อีกครั้ง');
}

$stmt = $conn->prepare('UPDATE members SET line_user_id = ?, line_display_name = ?, line_linked_at = NOW() WHERE member_id = ?');
if (!$stmt) {
    line_callback_fail('ไม่สามารถบันทึกข้อมูล LINE ได้ กรุณาลองใหม่');
}

$stmt->bind_param('ssi', $lineUserId, $displayName, $memberId);

try {
    $stmt->execute();
    $_SESSION['line_link_success'] = 'เชื่อมต่อ LINE สำเร็จ ระบบจะส่งแจ้งเตือนการจองผ่าน LINE ให้คุณ';
} catch (mysqli_sql_exception $e) {
    error_log('LINE link DB error: ' . $e->getMessage());
    $_SESSION['line_link_error'] = 'LINE บัญชีนี้อาจถูกผูกกับสมาชิกอื่นแล้ว';
}

$stmt->close();
unset($_SESSION['line_login_state'], $_SESSION['line_login_nonce']);

header('Location: ' . line_callback_redirect_to());
unset($_SESSION['line_login_redirect']);
exit;
