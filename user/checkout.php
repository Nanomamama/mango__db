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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="container mt-4">
    <h2>ดำเนินการสั่งซื้อ</h2>
    <form id="checkout-form" method="POST" action="process_checkout.php">
        <h2>ข้อมูลลูกค้า</h2>
        <div class="mb-3">
            <label for="customer-name" class="form-label">ชื่อ-นามสกุล</label>
            <input type="text" class="form-control" id="customer-name" name="customer_name" required>
        </div>
        <div class="mb-3">
            <label for="customer-phone" class="form-label">เบอร์โทรศัพท์</label>
            <input type="text" class="form-control" id="customer-phone" name="customer_phone" required>
        </div>

        <div class="mb-3">
            <label for="address-number" class="form-label">เลขที่</label>
            <input type="text" class="form-control" id="address-number" name="address_number" required>
        </div>
        <div class="mb-3">
            <label for="sub-district" class="form-label">ตำบล</label>
            <input type="text" class="form-control" id="sub-district" name="sub_district" required>
        </div>
        <div class="mb-3">
            <label for="district" class="form-label">อำเภอ</label>
            <input type="text" class="form-control" id="district" name="district" required>
        </div>
        <div class="mb-3">
            <label for="province" class="form-label">จังหวัด</label>
            <input type="text" class="form-control" id="province" name="province" required>
        </div>
        <div class="mb-3">
            <label for="postal-code" class="form-label">รหัสไปรษณีย์</label>
            <input type="text" class="form-control" id="postal-code" name="postal_code" required>
        </div>

        <div class="mb-3">
            <label for="payment-method" class="form-label">วิธีการชำระเงิน</label>
            <div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="payment-bank" value="bank" required>
                    <label class="form-check-label" for="payment-bank">
                        โอนเงินผ่านธนาคาร
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="payment-cod" value="cod">
                    <label class="form-check-label" for="payment-cod">
                        เก็บเงินปลายทาง
                    </label>
                </div>
            </div>
        </div>

        <h2>รายการสินค้า</h2>
        <div id="cart-summary"></div>
        <a href="cart.php" class="btn btn-warning">🔙 กลับ</a>
        <button type="submit" class="btn btn-primary">ยืนยันการสั่งซื้อ</button>
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

        let summaryHtml = cart.map(item => `
            <div class="d-flex justify-content-between">
                <span>${item.name} x ${item.quantity}</span>
                <span>฿${(item.price * item.quantity).toFixed(2)}</span>
            </div>
        `).join("");

        summaryHtml += `<div class="text-end mt-3"><strong>ยอดรวม: ฿${total.toFixed(2)}</strong></div>`;

        $("#cart-summary").html(summaryHtml);
    }

    $(document).ready(function () {
        loadCartSummary();
    });

    document.getElementById("checkout-form").addEventListener("submit", function (e) {
        e.preventDefault(); // ป้องกันการส่งฟอร์มแบบปกติ

        let cart = JSON.parse(localStorage.getItem("cart")) || []; // ดึงข้อมูลจาก localStorage
        let formData = new FormData(this); // สร้าง FormData จากฟอร์ม
        formData.append("cart", JSON.stringify(cart)); // เพิ่มข้อมูล cart ลงใน FormData

        fetch("process_checkout.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire("สำเร็จ!", "การสั่งซื้อของคุณถูกบันทึกเรียบร้อยแล้ว", "success");
                localStorage.removeItem("cart"); // ล้างตะกร้าสินค้า
                setTimeout(() => window.location.href = `order_summary.php?order_id=${data.order_id}`, 2000); // เปลี่ยนเส้นทางไปยัง order_summary.php
            } else {
                Swal.fire("ผิดพลาด!", data.message, "error");
            }
        })
        .catch(error => console.error("Error:", error));
    });
</script>

</body>
</html>
