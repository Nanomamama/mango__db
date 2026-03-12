<?php

$accessToken = "MuYBuMz3icicSJjBFPGUPWgcpCrC85pi7iCxDre1mJmBiG8Dc8OXoGbAPd6My4N8hOmzVrw28eLA9JUlIUBAbyK08/uAn2Iadpim0tzfl8NLHT6yOFHdmIA0OJcfa91I3TOXpvbbDOk/r7sfJMtVeAdB04t89/1O/w1cDnyilFU=";

$userId = "U794725708d6128c50d908b154f7a9999";

$message = "📦 มีคำสั่งซื้อใหม่\n";
$message .= "ลูกค้า: สมชาย\n";
$message .= "โทร: 089xxxx\n";
$message .= "สินค้า: ผักสลัด 2 ถุง";

$data = [
    "to" => $userId,
    "messages" => [
        [
            "type" => "text",
            "text" => $message
        ]
    ]
];

$ch = curl_init("https://api.line.me/v2/bot/message/push");

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer '.$accessToken
]);

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
curl_close($ch);

echo $result;

?>