<!-- bank_transfer.php -->
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>🏦 โอนเงินผ่านบัญชีธนาคาร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>🏦 รายละเอียดบัญชีธนาคารสำหรับโอนเงิน</h2>
    <div class="alert alert-info">
        <p>กรุณาโอนเงินตามรายละเอียดด้านล่าง:</p>
        <ul>
            <li>💳 ธนาคาร: กสิกรไทย (KBank)</li>
            <li>📄 ชื่อบัญชี: ร้านผลไม้แสนอร่อย</li>
            <li>🔢 เลขที่บัญชี: 123-4-56789-0</li>
            <li>💰 ยอดโอน: <strong>฿<span id="amountToPay">0.00</span></strong></li>
        </ul>
        <p>📸 กรุณาแนบหลักฐานการโอนเงินด้านล่าง</p>
    </div>

    <form action="upload_slip.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="slip" class="form-label">แนบสลิปโอนเงิน</label>
            <input type="file" class="form-control" name="slip" id="slip" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-success">✅ ส่งหลักฐานการชำระเงิน</button>
    </form>
</div>

<script>
    // ดึงยอดจาก query string แล้วแสดง
    const params = new URLSearchParams(window.location.search);
    const total = params.get("total");
    if (total) {
        document.getElementById("amountToPay").textContent = parseFloat(total).toFixed(2);
    }
</script>
</body>
</html>
