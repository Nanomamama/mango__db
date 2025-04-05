<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✅ ดำเนินการสั่งซื้อ</title>
   
</head>
<body>
<?php include 'navbar.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<div class="container mt-4">
    <h2>ดำเนินการสั่งซื้อ</h2>
    <form id="checkout-form">
    <div class="mb-3">
        <label for="customerName" class="form-label">👤 ชื่อลูกค้า</label>
        <input type="text" class="form-control" id="customerName" required>
    </div>

    <div class="mb-3">
        <label for="customerAddress" class="form-label">📍 ที่อยู่จัดส่ง</label>
        <textarea class="form-control" id="customerAddress" rows="3" required></textarea>
    </div>

    <div class="mb-3">
        <label for="customerPhone" class="form-label">📞 เบอร์โทรศัพท์</label>
        <input type="tel" class="form-control" id="customerPhone" required>
    </div>

    <h4 class="text-end">ยอดรวม: ฿<span id="total-price">0.00</span></h4>

    <div class="mb-3">
        <label class="form-label">💳 วิธีการชำระเงิน</label>
        <select class="form-select" id="paymentMethod" required>
            <option value="">-- กรุณาเลือกวิธีชำระเงิน --</option>
            <option value="cod">💸 เก็บเงินปลายทาง</option>
            <option value="transfer">🏦 โอนเงินผ่านบัญชีธนาคาร</option>
        </select>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="cart.php" class="btn btn-secondary">🔙 กลับไปตะกร้า</a>
        <button type="submit" class="btn btn-info">ยืนยันการสั่งซื้อ</button>
    </div>
    </form>
    <br>
</div>
<?php include 'footer.php'; ?>
<script>
    // โหลดข้อมูลจากตะกร้า
    function loadCartSummary() {
        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        let total = 0;

        cart.forEach(item => {
            total += item.price * item.quantity;
        });

        $("#total-price").text(total.toFixed(2));
    }

    // เมื่อกดยืนยันคำสั่งซื้อ
    $("#checkout-form").submit(function (event) {
        event.preventDefault();

        let customerName = $("#customerName").val();
        let customerAddress = $("#customerAddress").val();
        let customerPhone = $("#customerPhone").val();
        let paymentMethod = $("#paymentMethod").val();
        let cart = JSON.parse(localStorage.getItem("cart")) || [];

        if (cart.length === 0) {
            alert("❌ ตะกร้าสินค้าว่างอยู่!");
            return;
        }

        if (!paymentMethod) {
            alert("❗ กรุณาเลือกวิธีการชำระเงิน");
            return;
        }

        let orderData = {
            name: customerName,
            address: customerAddress,
            phone: customerPhone,
            payment: paymentMethod,
            items: cart,
            total: $("#total-price").text()
        };

        // ส่งข้อมูลไปยังเซิร์ฟเวอร์
        $.post("process_order.php", orderData, function (response) {
            alert(response);
            localStorage.removeItem("cart");

            if (paymentMethod === "transfer") {
                window.location.href = "bank_transfer.php?total=" + orderData.total;
            } else {
                window.location.href = "order_success.php";
            }
        });
    });

    $(document).ready(loadCartSummary);
</script>

</body>
</html>
