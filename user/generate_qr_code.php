<?php
// ตัวอย่างโค้ดเพื่อสร้าง QR Code สำหรับการชำระเงิน
include 'phpqrcode/qrlib.php'; // เชื่อมต่อกับไลบรารี PHP QR Code (ดาวน์โหลดและติดตั้ง)

$amount = isset($_GET['amount']) ? $_GET['amount'] : 0; // จำนวนเงินจาก URL
$paymentLink = "https://payment-gateway.com/pay?amount=" . $amount; // ตัวอย่างลิงค์ชำระเงิน

// สร้าง QR Code จากลิงค์การชำระเงิน
QRcode::png($paymentLink);
?>
