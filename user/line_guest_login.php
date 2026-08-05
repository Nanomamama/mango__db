<?php
session_start();
require_once __DIR__ . '/line_service.php';

$channelId = app_env('LINE_LOGIN_CHANNEL_ID', '');
$callbackUrl = app_env('LINE_GUEST_CALLBACK_URL', '');
if ($callbackUrl === '') {
    $callbackUrl = line_user_url('line_guest_callback.php');
}

if ($channelId === '' || $callbackUrl === '') {
    $_SESSION['line_link_error'] = 'ยังไม่ได้ตั้งค่า LINE Login สำหรับคำสั่งซื้อ';
    header('Location: order.php');
    exit;
}

$state = bin2hex(random_bytes(16));
$nonce = bin2hex(random_bytes(16));
$_SESSION['line_guest_state'] = $state;
$_SESSION['line_guest_nonce'] = $nonce;

$params = [
    'response_type' => 'code',
    'client_id' => $channelId,
    'redirect_uri' => $callbackUrl,
    'state' => $state,
    'scope' => 'profile openid',
    'nonce' => $nonce,
];

header('Location: https://access.line.me/oauth2/v2.1/authorize?' . http_build_query($params));
exit;
