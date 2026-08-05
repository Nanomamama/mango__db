<?php
session_start();
require_once __DIR__ . '/line_service.php';

if (empty($_SESSION['member_id'])) {
    header('Location: member_login.php');
    exit;
}

$channelId = app_env('LINE_LOGIN_CHANNEL_ID', '');
$callbackUrl = app_env('LINE_LOGIN_CALLBACK_URL', '');

if ($channelId === '' || $callbackUrl === '') {
    $_SESSION['line_link_error'] = 'ยังไม่ได้ตั้งค่า LINE Login';
    header('Location: member_profile.php');
    exit;
}

$state = bin2hex(random_bytes(16));
$nonce = bin2hex(random_bytes(16));
$_SESSION['line_login_state'] = $state;
$_SESSION['line_login_nonce'] = $nonce;

$allowedRedirects = [
    'bookings' => 'bookings.php',
    'profile' => 'member_profile.php',
];
$redirectKey = (string) ($_GET['redirect'] ?? 'profile');
$_SESSION['line_login_redirect'] = $allowedRedirects[$redirectKey] ?? 'member_profile.php';

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
