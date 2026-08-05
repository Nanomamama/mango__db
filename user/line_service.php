<?php
require_once __DIR__ . '/../config/env.php';

function line_app_base_url(): string
{
    $configuredUrl = rtrim((string) app_env('APP_BASE_URL', ''), '/');
    if ($configuredUrl !== '') {
        return $configuredUrl;
    }

    return 'https://khamaon.com/mango';
}

function line_user_url(string $path): string
{
    return line_app_base_url() . '/user/' . ltrim($path, '/');
}

function line_link_url(string $redirect = 'profile'): string
{
    $redirect = $redirect === 'bookings' ? 'bookings' : 'profile';

    $linkMethod = strtolower(trim((string) app_env('LINE_LINK_METHOD', 'oauth')));
    $liffId = trim((string) app_env('LINE_LIFF_ID', ''));
    if ($linkMethod === 'liff' && $liffId !== '') {
        return 'https://liff.line.me/' . rawurlencode($liffId) . '?' . http_build_query(['redirect' => $redirect]);
    }

    return line_user_url('line_login.php?' . http_build_query(['redirect' => $redirect]));
}

function line_bot_add_friend_url(): string
{
    $configuredUrl = trim((string) app_env('LINE_BOT_ADD_FRIEND_URL', ''));
    if ($configuredUrl !== '') {
        return $configuredUrl;
    }

    $basicId = trim((string) app_env('LINE_BOT_BASIC_ID', '@755pzlcs'));
    if ($basicId === '') {
        $basicId = '@755pzlcs';
    }

    return 'https://line.me/R/ti/p/' . $basicId;
}

function line_request(string $url, array $headers, ?array $payload = null): array
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    $body = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'ok' => !$error && $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'error' => $error,
        'body' => (string) $body,
    ];
}

function line_can_push_to_user(string $lineUserId): bool
{
    $lineUserId = trim($lineUserId);
    $accessToken = app_env('LINE_ACCESS_TOKEN', '');

    if ($lineUserId === '' || empty($accessToken)) {
        return false;
    }

    $result = line_request(
        'https://api.line.me/v2/bot/profile/' . rawurlencode($lineUserId),
        ['Authorization: Bearer ' . $accessToken]
    );

    if (!$result['ok']) {
        error_log('LINE bot profile check failed HTTP ' . $result['http_code'] . ': ' . ($result['error'] ?: $result['body']));
        return false;
    }

    return true;
}

function line_push_message(string $lineUserId, string $message): bool
{
    $lineUserId = trim($lineUserId);
    $message = trim($message);
    $accessToken = app_env('LINE_ACCESS_TOKEN', '');

    if ($lineUserId === '' || $message === '' || empty($accessToken)) {
        return false;
    }

    $result = line_request(
        'https://api.line.me/v2/bot/message/push',
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ],
        [
            'to' => $lineUserId,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message,
                ],
            ],
        ]
    );

    if (!$result['ok']) {
        error_log('LINE push failed HTTP ' . $result['http_code'] . ': ' . ($result['error'] ?: $result['body']));
        return false;
    }

    return true;
}

function line_booking_status_label(string $status): string
{
    $labels = [
        'pending' => 'รอเจ้าหน้าที่ตรวจสอบ',
        'awaiting_payment' => 'รอชำระเงินมัดจำ',
        'confirmed' => 'ยืนยันการจองแล้ว',
        'completed' => 'เสร็จสิ้น',
        'no_show' => 'ไม่มา/ยึดมัดจำ',
        'cancelled' => 'ยกเลิกการจองแล้ว',
    ];

    return $labels[$status] ?? $status;
}

function line_format_booking_date(?string $date, ?string $time): string
{
    $dateText = $date ?: '-';
    if (!empty($time)) {
        $dateText .= ' เวลา ' . substr($time, 0, 5) . ' น.';
    }

    return $dateText;
}

function line_send_member_booking_created(
    string $lineUserId,
    string $bookingCode,
    string $bookingDate,
    string $bookingTime,
    int $visitorCount
): bool {
    if (!line_can_push_to_user($lineUserId)) {
        return false;
    }

    $message = "รับคำขอจองเรียบร้อยแล้ว\n";
    $message .= "รหัสการจอง: {$bookingCode}\n";
    $message .= "วันที่เข้าชม: " . line_format_booking_date($bookingDate, $bookingTime) . "\n";
    $message .= "จำนวนผู้เข้าชม: {$visitorCount} คน\n";
    $message .= "สถานะ: รอเจ้าหน้าที่ตรวจสอบ\n\n";
    $message .= "ตรวจสอบการจอง: " . line_user_url('member_profile.php');

    return line_push_message($lineUserId, $message);
}

function line_send_booking_status(mysqli $conn, int $bookingId, string $status, ?string $note = null): bool
{
    $sql = "
        SELECT
            b.booking_code,
            b.booking_date,
            b.booking_time,
            b.visitor_count,
            b.deposit_amount,
            b.balance_amount,
            m.line_user_id
        FROM bookings b
        LEFT JOIN members m ON m.member_id = b.member_id
        WHERE b.bookings_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$booking || empty($booking['line_user_id']) || !line_can_push_to_user((string) $booking['line_user_id'])) {
        return false;
    }

    $message = "แจ้งเตือนสถานะการจอง\n";
    $message .= "รหัสการจอง: " . ($booking['booking_code'] ?? '-') . "\n";
    $message .= "วันที่เข้าชม: " . line_format_booking_date($booking['booking_date'] ?? null, $booking['booking_time'] ?? null) . "\n";
    $message .= "จำนวนผู้เข้าชม: " . (int) ($booking['visitor_count'] ?? 0) . " คน\n";
    $message .= "สถานะ: " . line_booking_status_label($status);

    if ($status === 'awaiting_payment') {
        $message .= "\nยอดมัดจำ: " . number_format((float) ($booking['deposit_amount'] ?? 0), 2) . " บาท";
        $message .= "\nกรุณาเปิดหน้าโปรไฟล์/การจองเพื่อแนบสลิปชำระเงิน";
    } elseif ($status === 'confirmed') {
        $message .= "\nยอดคงเหลือชำระวันเข้าชม: " . number_format((float) ($booking['balance_amount'] ?? 0), 2) . " บาท";
    } elseif ($status === 'cancelled' && !empty($note)) {
        $message .= "\nเหตุผล: " . trim($note);
    }

    $message .= "\n\nตรวจสอบการจอง: " . line_user_url('member_profile.php');

    return line_push_message((string) $booking['line_user_id'], $message);
}

function line_send_booking_slip_received(mysqli $conn, int $bookingId): bool
{
    $sql = "
        SELECT
            b.booking_code,
            b.booking_date,
            b.booking_time,
            b.visitor_count,
            b.deposit_amount,
            b.balance_amount,
            m.line_user_id
        FROM bookings b
        LEFT JOIN members m ON m.member_id = b.member_id
        WHERE b.bookings_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$booking || empty($booking['line_user_id']) || !line_can_push_to_user((string) $booking['line_user_id'])) {
        return false;
    }

    $message = "ได้รับสลิปชำระเงินแล้ว\n";
    $message .= "รหัสการจอง: " . ($booking['booking_code'] ?? '-') . "\n";
    $message .= "วันที่เข้าชม: " . line_format_booking_date($booking['booking_date'] ?? null, $booking['booking_time'] ?? null) . "\n";
    $message .= "จำนวนผู้เข้าชม: " . (int) ($booking['visitor_count'] ?? 0) . " คน\n";
    $message .= "สถานะ: รอเจ้าหน้าที่ตรวจสอบสลิป\n";
    $message .= "ยอดมัดจำ: " . number_format((float) ($booking['deposit_amount'] ?? 0), 2) . " บาท\n";
    $message .= "ยอดคงเหลือชำระวันเข้าชม: " . number_format((float) ($booking['balance_amount'] ?? 0), 2) . " บาท";
    $message .= "\n\nตรวจสอบการจอง: " . line_user_url('member_profile.php');

    return line_push_message((string) $booking['line_user_id'], $message);
}
