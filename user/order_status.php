<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
</head>
<body>

<?php include 'navbar.php'; ?>
<title>📦 ตรวจสอบสถานะคำสั่งซื้อ</title>
<link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container py-4" style="max-width: 700px;">
    <h2 class="text-center mb-4">📦 ตรวจสอบสถานะคำสั่งซื้อ</h2>
    <p class="text-center text-muted">กรอกหมายเลขคำสั่งซื้อของคุณเพื่อดูสถานะ</p>

    <div class="input-group mb-3">
        <input type="text" id="orderIdInput" class="form-control" placeholder="ป้อนหมายเลขคำสั่งซื้อ">
        <input type="text" id="customerNameInput" class="form-control" placeholder="หรือป้อนชื่อ-นามสกุล">
        <button class="btn btn-primary" onclick="checkOrderStatus()">ตรวจสอบ</button>
    </div>

    <div id="orderStatusResult" class="mt-4"></div>
</div>

<script>
function checkOrderStatus() {
    let orderId = document.getElementById("orderIdInput").value.trim();
    let customerName = document.getElementById("customerNameInput").value.trim();
    let resultDiv = document.getElementById("orderStatusResult");

    if (orderId === "" && customerName === "") {
        alert("กรุณาป้อนหมายเลขคำสั่งซื้อหรือชื่อ-นามสกุล");
        return;
    }

    let url = "order_status_api.php?";
    if (orderId !== "") {
        url += "order_id=" + encodeURIComponent(orderId);
    } else {
        url += "customer_name=" + encodeURIComponent(customerName);
    }

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.orders && data.orders.length > 0) {
                let html = '';
                data.orders.forEach(order => {
                    html += `
                        <div class="card border-primary mb-2">
                            <div class="card-body">
                                <h5 class="card-title">🆔 คำสั่งซื้อ #${order.id}</h5>
                                <p>👤 <strong>ลูกค้า:</strong> ${order.customer_name}</p>
                                <p>📍 <strong>ที่อยู่:</strong> ${order.address_number}</p>
                                <p>📦 <strong>สถานะ:</strong> ${order.status}</p>
                            </div>
                        </div>
                    `;
                });
                resultDiv.innerHTML = html;
            } else if (data.success && data.order) {
                let order = data.order;
                resultDiv.innerHTML = `
                    <div class="card border-primary">
                        <div class="card-body">
                            <h5 class="card-title">🆔 คำสั่งซื้อ #${order.id}</h5>
                            <p>👤 <strong>ลูกค้า:</strong> ${order.customer}</p>
                            <p>📍 <strong>ที่อยู่:</strong> ${order.address}</p>
                            <p>📦 <strong>สถานะ:</strong> ${order.status}</p>
                        </div>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `<div class="alert alert-danger">❌ ไม่พบข้อมูลคำสั่งซื้อ</div>`;
            }
        });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
