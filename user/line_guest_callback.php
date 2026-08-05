<?php
session_start();
require_once __DIR__ . '/line_service.php';

function line_guest_callback_fail(string $message): void
{
    $_SESSION['line_link_error'] = $message;
    unset($_SESSION['line_guest_state'], $_SESSION['line_guest_nonce']);
    header('Location: order.php');
    exit;
}

if (empty($_GET['code']) || empty($_GET['state']) || !hash_equals($_SESSION['line_guest_state'] ?? '', (string) $_GET['state'])) {
    line_guest_callback_fail('การเชื่อมต่อ LINE ไม่ถูกต้อง กรุณาลองใหม่');
}

$channelId = app_env('LINE_LOGIN_CHANNEL_ID', '');
$channelSecret = app_env('LINE_LOGIN_CHANNEL_SECRET', '');
$callbackUrl = app_env('LINE_GUEST_CALLBACK_URL', '');
if ($callbackUrl === '') {
    $callbackUrl = line_user_url('line_guest_callback.php');
}

if ($channelId === '' || $channelSecret === '' || $callbackUrl === '') {
    line_guest_callback_fail('ยังไม่ได้ตั้งค่า LINE Login ให้ครบถ้วน');
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
    error_log('LINE guest token error: ' . ($tokenError ?: (string) $tokenResult));
    line_guest_callback_fail('เชื่อมต่อ LINE ไม่สำเร็จ กรุณาลองใหม่');
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
    error_log('LINE guest profile error: ' . ($profileError ?: (string) $profileResult));
    line_guest_callback_fail('ดึงข้อมูล LINE ไม่สำเร็จ กรุณาลองใหม่');
}

$lineUserId = (string) $profile['userId'];
$displayName = (string) ($profile['displayName'] ?? '');

if (!line_can_push_to_user($lineUserId)) {
    line_guest_callback_fail('กรุณาเพิ่ม LINE Official Account เป็นเพื่อนก่อน แล้วกดเชื่อมต่อ LINE อีกครั้ง');
}

$_SESSION['guest_line_user_id'] = $lineUserId;
$_SESSION['guest_line_display_name'] = $displayName;
$_SESSION['guest_line_linked_at'] = date('Y-m-d H:i:s');
$_SESSION['line_link_success'] = 'เชื่อมต่อ LINE สำเร็จ ระบบจะแจ้งเตือนสถานะคำสั่งซื้อให้คุณ';

unset($_SESSION['line_guest_state'], $_SESSION['line_guest_nonce']);
header('Location: order.php');
exit;
