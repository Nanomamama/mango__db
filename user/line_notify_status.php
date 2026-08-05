
<?php

/**
 * =========================================================
 * LINE Status Notification
 * =========================================================
 *
 * ใช้สำหรับส่งข้อความแจ้งเตือนสถานะ Order
 * ผ่าน LINE Messaging API
 *
 * โหมด TEST
 * สามารถเปิดไฟล์นี้โดยตรงเพื่อทดสอบการส่ง LINE ได้
 * =========================================================
 */


/**
 * =========================================================
 * Function: sendLineStatusNotification()
 * =========================================================
 *
 * @param string $lineUserId LINE User ID ของลูกค้า
 * @param string $orderId    เลข Order
 * @param string $status     สถานะ Order
 *
 * @return bool
 * true  = ส่งสำเร็จ
 * false = ส่งไม่สำเร็จ
 * =========================================================
 */

function sendLineStatusNotification($lineUserId, $orderId, $status)
{
    // =====================================================
    // 1. ตรวจสอบข้อมูลเบื้องต้น
    // =====================================================

    if (empty($lineUserId)) {

        error_log('[LINE] ไม่พบ LINE User ID');

        return false;
    }

    if (empty($orderId)) {

        error_log('[LINE] ไม่พบ Order ID');

        return false;
    }

    if (empty($status)) {

        error_log('[LINE] ไม่พบสถานะ Order');

        return false;
    }


    // =====================================================
    // 2. LINE Channel Access Token
    // =====================================================

    // ใส่ Channel Access Token ของ LINE Messaging API
    $channelAccessToken = 'igWk2Fym5GQ5Mg0HakuC/2EzwXTm2t7mjn3fSy0LG1eBvywbKmGdtrXtRZ07xWl9hOmzVrw28eLA9JUlIUBAbyK08/uAn2Iadpim0tzfl8OK0bWYKiUQ/7DDLWs7kPY8SIPwhISK4kULrSQK9fZZ7QdB04t89/1O/w1cDnyilFU=';


    // =====================================================
    // 3. สร้างข้อความ
    // =====================================================

    $message =
        "📦 แจ้งเตือนสถานะคำสั่งซื้อ\n\n" .
        "เลขที่คำสั่งซื้อ: {$orderId}\n" .
        "สถานะปัจจุบัน: {$status}\n\n" .
        "ขอบคุณที่ใช้บริการค่ะ";


    // =====================================================
    // 4. LINE Messaging API URL
    // =====================================================

    $url = 'https://api.line.me/v2/bot/message/push';


    // =====================================================
    // 5. เตรียมข้อมูลที่จะส่ง
    // =====================================================

    $data = [

        'to' => $lineUserId,

        'messages' => [

            [
                'type' => 'text',
                'text' => $message
            ]

        ]

    ];


    // =====================================================
    // 6. แปลงข้อมูลเป็น JSON
    // =====================================================

    $jsonData = json_encode(
        $data,
        JSON_UNESCAPED_UNICODE
    );


    // ตรวจสอบกรณี JSON แปลงไม่สำเร็จ
    if ($jsonData === false) {

        $jsonError = json_last_error_msg();

        error_log(
            '[LINE] JSON Error: ' . $jsonError
        );

        echo '<pre>';
        echo "JSON Error: {$jsonError}";
        echo '</pre>';

        return false;
    }


    // =====================================================
    // 7. เปิด cURL
    // =====================================================

    $ch = curl_init($url);


// =====================================================
// 8. กำหนด Request
// =====================================================

curl_setopt_array($ch, [

    CURLOPT_POST => true,

    CURLOPT_HTTPHEADER => [

        'Content-Type: application/json',

        'Authorization: Bearer ' . $channelAccessToken

    ],

    CURLOPT_POSTFIELDS => $jsonData,

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_TIMEOUT => 15,

    // -------------------------------------------------
    // TEST ONLY
    // ปิดการตรวจสอบ SSL ชั่วคราว
    // -------------------------------------------------
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false

]);


    // =====================================================
    // 9. ส่ง Request ไปยัง LINE
    // =====================================================

    $response = curl_exec($ch);


    // =====================================================
    // 10. ตรวจสอบ HTTP Status Code
    // =====================================================

    $httpCode = curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


    // =====================================================
    // 11. ตรวจสอบ cURL Error
    // =====================================================

    $curlError = curl_error($ch);


    // =====================================================
    // 12. ปิด cURL
    // =====================================================

    curl_close($ch);


    // =====================================================
    // 13. แสดงผลสำหรับ TEST
    // =====================================================

    echo '<div style="
        max-width:800px;
        margin:40px auto;
        padding:25px;
        font-family:Arial,sans-serif;
        background:#f8f9fa;
        border:1px solid #ddd;
        border-radius:10px;
    ">';

    echo '<h2>LINE API Test</h2>';

    echo '<p><strong>HTTP Code:</strong> '
        . htmlspecialchars((string)$httpCode)
        . '</p>';


    // =====================================================
    // 14. ตรวจสอบ cURL Error
    // =====================================================

    if (!empty($curlError)) {

        echo '<p style="color:red;">';
        echo '<strong>cURL Error:</strong><br>';
        echo htmlspecialchars($curlError);
        echo '</p>';

        echo '</div>';

        error_log(
            '[LINE] cURL Error: ' . $curlError
        );

        return false;
    }


    // =====================================================
    // 15. แสดง Response จาก LINE
    // =====================================================

    echo '<p><strong>Response จาก LINE:</strong></p>';

    echo '<pre style="
        background:#222;
        color:#fff;
        padding:15px;
        border-radius:6px;
        overflow:auto;
    ">';

    echo htmlspecialchars(
        $response ?: '(ไม่มี Response)'
    );

    echo '</pre>';


    // =====================================================
    // 16. ตรวจสอบผลลัพธ์
    // =====================================================

    if ($httpCode >= 200 && $httpCode < 300) {

        echo '<h3 style="color:green;">';
        echo 'ส่ง LINE สำเร็จ ✅';
        echo '</h3>';

        echo '</div>';


        error_log(
            "[LINE] ส่งแจ้งเตือนสำเร็จ | Order: {$orderId}"
        );

        return true;
    }


    // =====================================================
    // 17. ส่งไม่สำเร็จ
    // =====================================================

    echo '<h3 style="color:red;">';
    echo 'ส่ง LINE ไม่สำเร็จ ❌';
    echo '</h3>';

    echo '</div>';


    error_log(
        "[LINE] ส่งไม่สำเร็จ | " .
        "HTTP {$httpCode} | " .
        "Response: {$response}"
    );

    return false;
}


/**
 * =========================================================
 * TEST SEND LINE
 * =========================================================
 *
 * ส่วนนี้ใช้สำหรับทดสอบเท่านั้น
 *
 * หลังจากทดสอบสำเร็จแล้ว
 * ให้ลบส่วน TEST ด้านล่างออก
 * =========================================================
 */


// =========================================================
// LINE User ID ของบัญชีที่เพิ่มเพื่อน LINE OA แล้ว
// =========================================================

$testLineUserId = 'U52d48e93493fa31f9f1afc2ce3469493';


// =========================================================
// เรียก Function เพื่อส่งข้อความทดสอบ
// =========================================================

$result = sendLineStatusNotification(

    $testLineUserId,

    'TEST-001',

    'approved'

);

