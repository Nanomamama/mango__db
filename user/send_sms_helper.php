<?php
// Simple SMS helper using Twilio REST API via cURL.
// Configure credentials via environment variables: TWILIO_SID, TWILIO_TOKEN, TWILIO_FROM
// If TWILIO_* are not set or SMS_DEBUG=1, OTPs will be written to user/otp_log.txt for testing.
function sendSmsOtp($phone, $code, $name = '') {
    $sid = getenv('TWILIO_SID') ?: '';
    $token = getenv('TWILIO_TOKEN') ?: '';
    $from = getenv('TWILIO_FROM') ?: '';
    $debug = getenv('SMS_DEBUG') === '1';

    $body = "รหัส OTP สำหรับการกู้คืนรหัสผ่าน: $code (ใช้ได้ 5 นาที)";

    // If debug mode or missing credentials, write OTP to a local log for testing.
    if ($debug || empty($sid) || empty($token) || empty($from)) {
        if (empty($sid) || empty($token) || empty($from)) {
            error_log('Twilio credentials not set, using debug log for OTP');
        } else {
            error_log('SMS_DEBUG enabled, writing OTP to log');
        }

        $logDir = __DIR__;
        $logFile = $logDir . DIRECTORY_SEPARATOR . 'otp_log.txt';
        $entry = sprintf("[%s] To: %s Name: %s OTP: %s\n", date('Y-m-d H:i:s'), $phone, $name, $code);
        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
        return true;
    }

    $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

    $data = http_build_query([
        'To' => $phone,
        'From' => $from,
        'Body' => $body,
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_USERPWD, $sid . ':' . $token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false || $httpcode >= 400) {
        error_log("SMS send failed (http: $httpcode): $err resp: $resp");
        return false;
    }

    return true;
}

?>
