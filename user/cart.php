<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🛒 ตะกร้าสินค้า</title>
  <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>

<br>
<div class="container ">
  <h1 >🛒 ตะกร้าสินค้า</h1>
  <div id="cart-container" class="row g-4 mt-3"></div>

  <h4 class="text-end mt-4">ยอดรวม: ฿<span id="total-price">0.00</span></h4>

  <div class="d-flex justify-content-between mt-4">
    <a href="products.php" class="btn btn-secondary">🔙 เลือกซื้อสินค้าเพิ่ม</a>
    <a href="checkout.php" class="btn btn-info">🧾 ดำเนินการสั่งซื้อ</a>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// โหลดสินค้าจาก localStorage
function loadCart() {
  let cart = JSON.parse(localStorage.getItem("cart")) || [];
  let container = $("#cart-container");
  container.empty();
  let total = 0;

  if (cart.length === 0) {
    container.append(`<div class="text-center text-muted">🛒 ไม่มีสินค้าในตะกร้า</div>`);
  } else {
    cart.forEach((item, index) => {
      let subtotal = item.price * item.quantity;
      total += subtotal;

    container.append(`
    <div class="col-md-6 col-lg-4 ce">
        <div class="card shadow-sm h-100">
        <img src="${item.image}" class="card-img-top" alt="${item.name}">
        <div class="card-body">
            <h5 class="card-title">${item.name}</h5>
            <p class="card-text">ราคา: ฿${item.price.toFixed(2)}</p>
            <p class="card-text">
            จำนวน:
            <button class="btn btn-sm btn-outline-danger me-1" onclick="updateQuantity(${index}, -1)">➖</button>
            ${item.quantity}
            <button class="btn btn-sm btn-outline-success ms-1" onclick="updateQuantity(${index}, 1)">➕</button>
            </p>
            <p class="card-text">รวม: ฿${subtotal.toFixed(2)}</p>
            <button class="btn btn-sm btn-danger" onclick="removeFromCart(${index})">❌ ลบสินค้า</button>
        </div>
        </div>
    </div>
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

// โหลดตะกร้าเมื่อเปิดหน้า
$(document).ready(loadCart);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
