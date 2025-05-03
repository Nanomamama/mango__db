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
            <label for="customer-address" class="form-label">ที่อยู่สำหรับจัดส่ง</label>
            <textarea class="form-control" id="customer-address" name="customer_address" rows="3" required></textarea>
        </div>
        <h2>รายการสินค้า</h2>
        <div id="cart-summary"></div>
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

    $(document).ready(loadCartSummary);

    document.getElementById("checkout-form").addEventListener("submit", function (e) {
        e.preventDefault();

        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        let formData = new FormData(this);
        formData.append("cart", JSON.stringify(cart));

        fetch("process_checkout.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire("สำเร็จ!", "การสั่งซื้อของคุณถูกบันทึกเรียบร้อยแล้ว", "success");
                localStorage.removeItem("cart"); // ล้างตะกร้าสินค้า
                setTimeout(() => window.location.href = "products.php", 2000);
            } else {
                Swal.fire("ผิดพลาด!", data.message, "error");
            }
        })
        .catch(error => console.error("Error:", error));
    });
</script>

</body>
</html>
