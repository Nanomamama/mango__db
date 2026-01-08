<?php
session_start();
require_once '../admin/db.php';

/* ---------- เช็ค Login ---------- */
if (!isset($_SESSION['member_id'])) {
    header("Location: member_login.php");
    exit;
}

/* ---------- ดึงข้อมูลผู้ใช้ ---------- */
$member_id = $_SESSION['member_id'];
$stmt = $conn->prepare("
    SELECT fullname, phone, address 
    FROM members 
    WHERE member_id = ?
");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ดำเนินการสั่งซื้อ</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
<?php include 'navbar.php'; ?>

<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-6">

<form id="checkout-form" enctype="multipart/form-data">
<h3 class="text-center mb-4">ดำเนินการสั่งซื้อ</h3>

<h5>ข้อมูลลูกค้า</h5>
<div class="mb-3">
<input type="text" class="form-control" name="customer_name"
value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" required>
</div>

<div class="mb-3">
<input type="text" class="form-control" name="customer_phone"
value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
</div>

<h5>ที่อยู่จัดส่ง</h5>
<div class="mb-3">
<input type="text" class="form-control" name="address_number"
value="<?= htmlspecialchars($user['address'] ?? '') ?>" required>
</div>

<h5>วิธีชำระเงิน</h5>
<div class="form-check">
<input class="form-check-input" type="radio" name="payment_method" value="cod" required>
<label class="form-check-label">เก็บเงินปลายทาง</label>
</div>

<div class="form-check mb-3">
<input class="form-check-input" type="radio" name="payment_method" value="promptpay">
<label class="form-check-label">PromptPay</label>
</div>

<h5>รายการสินค้า</h5>
<div id="cart-summary" class="mb-3"></div>

<div class="d-flex justify-content-between">
<a href="cart.php" class="btn btn-warning">ย้อนกลับ</a>
<button type="submit" class="btn btn-primary">ยืนยันสั่งซื้อ</button>
</div>

<input type="hidden" name="cart" id="cart-data">
</form>

</div>
</div>
</div>

<?php include 'footer.php'; ?>

<script>
/* ---------- ค่าจัดส่ง ---------- */
function calculateShipping(weight) {
    if (weight <= 1) return 40;
    if (weight <= 3) return 60;
    return 80;
}

/* ---------- โหลดตะกร้า ---------- */
function loadCartSummary() {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    if (cart.length === 0) {
        $("#cart-summary").html("<p class='text-danger'>ไม่มีสินค้าในตะกร้า</p>");
        return;
    }

    let total = 0;
    let totalWeight = 0;

    cart.forEach(i => {
        total += i.price * i.quantity;
        totalWeight += (i.weight || 0) * i.quantity;
    });

    const shipping = calculateShipping(totalWeight);
    const grandTotal = total + shipping;

    let html = cart.map(i => `
        <div class="d-flex justify-content-between">
            <span>${i.name} x ${i.quantity}</span>
            <span>฿${(i.price * i.quantity).toFixed(2)}</span>
        </div>
    `).join("");

    html += `
        <hr>
        <div class="d-flex justify-content-between">
            <span>ค่าจัดส่ง</span>
            <span>฿${shipping.toFixed(2)}</span>
        </div>
        <div class="text-end text-danger mt-2">
            <strong>ยอดสุทธิ: ฿${grandTotal.toFixed(2)}</strong>
        </div>
    `;

    $("#cart-summary").html(html);

    // ส่งให้ backend
    $("#cart-data").val(JSON.stringify({
        items: cart.map(i => ({
            product_id: i.product_id,
            quantity: i.quantity
        })),
        shipping_cost: shipping
    }));
}

$(document).ready(function () {
    loadCartSummary();
});

/* ---------- Submit ---------- */
$("#checkout-form").on("submit", function(e) {
    e.preventDefault();

    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    if (cart.length === 0) {
        Swal.fire("ผิดพลาด", "ตะกร้าว่าง", "error");
        return;
    }

    let formData = new FormData(this);

    fetch("process_checkout.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            localStorage.removeItem("cart");

            
            location.href = "order_success.php?order_id=" + data.order_id;
        } else {
            Swal.fire("ผิดพลาด", data.message, "error");
        }
    });
});




const btn = $(this).find("button[type=submit]");
    btn.prop("disabled", true).text("กำลังดำเนินการ...");

</script>

</body>
</html>
