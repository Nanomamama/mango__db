

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📦 ตรวจสอบสถานะคำสั่งซื้อ</title>

</head>
<body>
<?php include 'navbar.php'; ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="container" style="margin-left: 250px; flex: 2;">
    <h2 class="mt-4">📦 ตรวจสอบสถานะคำสั่งซื้อ</h2>
    <p>กรอกหมายเลขคำสั่งซื้อของคุณเพื่อดูสถานะ</p>

    <input type="text" id="orderIdInput" class="form-control w-50 mb-3" placeholder="🔍 ป้อนหมายเลขคำสั่งซื้อ">
    <button class="btn btn-primary" onclick="checkOrderStatus()">🔎 ตรวจสอบ</button>

    <div id="orderStatusResult" class="mt-4"></div>
</div>

<script>
function checkOrderStatus() {
    let orderId = document.getElementById("orderIdInput").value.trim();
    if (orderId === "") {
        alert("กรุณาป้อนหมายเลขคำสั่งซื้อ");
        return;
    }

    // ข้อมูลจำลอง (Mock Data)
    let mockOrders = {
        "1001": { status: "📦 กำลังจัดส่ง", customer: "สมชาย", address: "กรุงเทพฯ" },
        "1002": { status: "✅ สำเร็จ", customer: "สมหญิง", address: "เชียงใหม่" },
        "1003": { status: "⏳ รอดำเนินการ", customer: "อนันต์", address: "ภูเก็ต" }
    };

    let resultDiv = document.getElementById("orderStatusResult");

    if (mockOrders[orderId]) {
        let order = mockOrders[orderId];
        resultDiv.innerHTML = `
            <div class="alert alert-info">
                <h5>🆔 คำสั่งซื้อ #${orderId}</h5>
                <p>👤 ลูกค้า: ${order.customer}</p>
                <p>📍 ที่อยู่: ${order.address}</p>
                <p>📦 สถานะ: <strong>${order.status}</strong></p>
            </div>
        `;
    } else {
        resultDiv.innerHTML = `<div class="alert alert-danger">❌ ไม่พบคำสั่งซื้อ #${orderId}</div>`;
    }
}
</script>

</body>
</html>
