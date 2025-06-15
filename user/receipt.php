<?php
require_once '../admin/db.php';

// รับ booking_id จาก GET
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$booking_id) {
    die("ไม่พบข้อมูลการจอง");
}

$sql = "SELECT * FROM bookings WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
if (!$booking) { die("ไม่พบข้อมูลการจอง"); }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบเสร็จการจอง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .receipt-card {
            max-width: 500px;
            margin: 40px auto;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 6px 32px rgba(0,0,0,0.10);
        }
        .receipt-header {
            background: linear-gradient(90deg, #43cea2 0%, #185a9d 100%);
            color: #fff;
            padding: 32px 24px 18px 24px;
            text-align: center;
        }
        .receipt-header h4 {
            margin-bottom: 0.5rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .receipt-body {
            background: #fff;
            padding: 28px 24px 24px 24px;
        }
        .receipt-body .row {
            margin-bottom: 10px;
        }
        .receipt-label {
            color: #185a9d;
            font-weight: 500;
        }
        .receipt-amount {
            font-size: 1.2rem;
            font-weight: 600;
            color: #009688;
        }
        .receipt-divider {
            border-top: 2px dashed #43cea2;
            margin: 18px 0;
        }
        .receipt-footer {
            text-align: center;
            color: #888;
            font-size: 0.95rem;
            padding-bottom: 12px;
        }
        @media print {
            .btn-print { display: none; }
            body { background: #fff; }
        }
    </style>
</head>
<body>


<div class="receipt-card card">

    <div class="receipt-header">
        <h4>ใบเสร็จการจองเข้าชมสวนลุงเผือก</h4>
        <div style="font-size:1.1rem;">Booking ID: <?= htmlspecialchars($booking['id']) ?></div>
    </div>
    <div class="receipt-body">
        <div class="row">
            <div class="col-5 receipt-label">ชื่อคณะ</div>
            <div class="col-7"><?= htmlspecialchars($booking['name']) ?></div>
        </div>
        <div class="row">
            <div class="col-5 receipt-label">วันที่จอง</div>
            <div class="col-7"><?= htmlspecialchars($booking['date']) ?></div>
        </div>
        <div class="row">
            <div class="col-5 receipt-label">เวลา</div>
            <div class="col-7"><?= htmlspecialchars($booking['time']) ?></div>
        </div>
        <div class="row">
            <div class="col-5 receipt-label">จำนวนผู้เข้าชม</div>
            <div class="col-7"><?= htmlspecialchars($booking['people']) ?> คน</div>
        </div>
        <?php if (!empty($booking['phone'])): ?>
        <div class="row">
            <div class="col-5 receipt-label">เบอร์โทร</div>
            <div class="col-7"><?= htmlspecialchars($booking['phone']) ?></div>
        </div>
        <?php endif; ?>
        <div class="receipt-divider"></div>
        <div class="row">
            <div class="col-6 receipt-label">ยอดรวม</div>
            <div class="col-6 receipt-amount text-end"><?= number_format($booking['total_amount'], 2) ?> บาท</div>
        </div>
        <div class="row">
            <div class="col-6 receipt-label">ยอดมัดจำ (30%)</div>
            <div class="col-6 receipt-amount text-end"><?= number_format($booking['deposit_amount'], 2) ?> บาท</div>
        </div>
        <div class="row">
            <div class="col-6 receipt-label">ยอดคงเหลือ</div>
            <div class="col-6 receipt-amount text-end"><?= number_format($booking['remain_amount'], 2) ?> บาท</div>
        </div>
    </div>
    <div class="receipt-footer">
        <button class="btn btn-success btn-print mt-2" onclick="window.print()">
            พิมพ์ใบเสร็จ
        </button>
        <button class="btn btn-success btn-download mt-2" id="downloadImgBtn">
            ดาวน์โหลดใบเสร็จเป็นรูปภาพ
        </button>
        <div class="mt-2">ขอบคุณที่ใช้บริการสวนมะม่วงลุงเผือก</div>
        <button class="btn btn-secondary mt-3" onclick="window.location.href='index.php'">ย้อนกลับไปหน้าหลัก</button>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
document.getElementById('downloadImgBtn').addEventListener('click', function() {
    html2canvas(document.querySelector('.receipt-card')).then(function(canvas) {
        var link = document.createElement('a');
        link.download = 'receipt.png';
        link.href = canvas.toDataURL();
        link.click();
    });
});
</script>
</body>
</html>