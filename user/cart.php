<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛒 ตะกร้าสินค้า</title>
   
</head>
<body>
<?php include 'navbar.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<div class="container mt-4">
    <h1>🛒 ตะกร้าสินค้า</h1>
    
    <table class="table table-bordered text-center">
        <thead>
            <tr>
                <th>สินค้า</th>
                <th>ราคา</th>
                <th>จำนวน</th>
                <th>รวม</th>
                <th>ลบ</th>
            </tr>
        </thead>
        <tbody id="cart-body">
            <!-- แสดงรายการสินค้า -->
        </tbody>
    </table>

    <h4 class="text-end">ยอดรวม: ฿<span id="total-price">0.00</span></h4>

    <div class="d-flex justify-content-between mt-4">
        <a href="products.php" class="btn btn-secondary">🔙 เลือกซื้อสินค้าเพิ่ม</a>
        <a href="checkout.php" class="btn btn-info mt-3"> ดำเนินการสั่งซื้อ</a>
    </div>
</div>

<script>
// โหลดสินค้าจาก localStorage
function loadCart() {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    let cartBody = $("#cart-body");
    cartBody.empty();
    let total = 0;

    if (cart.length === 0) {
        cartBody.append('<tr><td colspan="5">🛒 ไม่มีสินค้าในตะกร้า</td></tr>');
    } else {
        cart.forEach((item, index) => {
            let subtotal = item.price * item.quantity;
            total += subtotal;

            cartBody.append(`
                <tr>
                    <td>${item.name}</td>
                    <td>฿${item.price.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger" onclick="updateQuantity(${index}, -1)">➖</button>
                        ${item.quantity}
                        <button class="btn btn-sm btn-outline-success" onclick="updateQuantity(${index}, 1)">➕</button>
                    </td>
                    <td>฿${subtotal.toFixed(2)}</td>
                    <td><button class="btn btn-danger btn-sm" onclick="removeFromCart(${index})">❌</button></td>
                </tr>
            `);
        });
    }
    $("#total-price").text(total.toFixed(2));
}

// อัปเดตจำนวนสินค้า
function updateQuantity(index, change) {
    let cart = JSON.parse(localStorage.getItem("cart"));
    cart[index].quantity += change;
    if (cart[index].quantity <= 0) cart.splice(index, 1);
    localStorage.setItem("cart", JSON.stringify(cart));
    loadCart();
}

// ลบสินค้าออกจากตะกร้า
function removeFromCart(index) {
    let cart = JSON.parse(localStorage.getItem("cart"));
    cart.splice(index, 1);
    localStorage.setItem("cart", JSON.stringify(cart));
    loadCart();
}

// ดำเนินการสั่งซื้อ
$("#checkoutBtn").click(function () {
    let cart = JSON.parse(localStorage.getItem("cart"));
    if (cart.length === 0) {
        alert("❌ ตะกร้าสินค้าว่างอยู่!");
        return;
    }
    alert("✅ คำสั่งซื้อของคุณถูกส่งเรียบร้อย!");
    localStorage.removeItem("cart"); // เคลียร์ตะกร้า
    loadCart();
});

// โหลดตะกร้าเมื่อเปิดหน้า
$(document).ready(loadCart);
</script>

</body>
</html>
