<?php

$accessToken = "MuYBuMz3icicSJjBFPGUPWgcpCrC85pi7iCxDre1mJmBiG8Dc8OXoGbAPd6My4N8hOmzVrw28eLA9JUlIUBAbyK08/uAn2Iadpim0tzfl8NLHT6yOFHdmIA0OJcfa91I3TOXpvbbDOk/r7sfJMtVeAdB04t89/1O/w1cDnyilFU="
;
$userId = "U794725708d6128c50d908b154f7a9999";

/* ถ้าไม่มี message จากไฟล์อื่น */
if(!isset($message)){
    $message = "📦 มีคำสั่งซื้อใหม่";
}

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
    'Authorization: Bearer ' . $accessToken
]);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

/* แก้ SSL localhost */
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$result = curl_exec($ch);

if(curl_error($ch)){
    echo curl_error($ch);
}

curl_close($ch);


?>