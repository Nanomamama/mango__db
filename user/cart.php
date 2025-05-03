<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛒 ตะกร้าสินค้า</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-img-top {
            height: 250px; /* กำหนดความสูงของรูปภาพ */
            object-fit: cover; /* ครอบรูปภาพให้พอดีกับขนาด */
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container py-5">
    <br>
    <br>
    <br>
    <h1 class="text-center">🛒 ตะกร้าสินค้า</h1>
    <div id="cart-container" class="row g-4 mt-4">
        <!-- การ์ดสินค้าจะถูกเพิ่มที่นี่ -->
    </div>
    <h3 class="mt-4 text-end">ยอดรวม: <span id="total-price">฿0.00</span></h3>
    <div class="text-end">
        <button class="btn btn-primary mt-3" id="checkout-button">ดำเนินการชำระเงิน</button>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // โหลดสินค้าจาก Local Storage
    function loadCart() {
        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        let container = $("#cart-container");
        container.empty();
        let total = 0;

        if (cart.length === 0) {
            container.append(`<div class="text-center text-muted">🛒 ไม่มีสินค้าในตะกร้า</div>`);
        } else {
            cart.forEach((item, index) => {
                let price = parseFloat(item.price); // แปลงราคาเป็นตัวเลข
                let subtotal = price * item.quantity;
                total += subtotal;

                container.append(`
                <div class="col-lg-3 col-md-6">
                    <div class="card shadow-sm h-100">
                        <img src="${item.image}" class="card-img-top" alt="${item.name}">
                        <div class="card-body">
                            <h5 class="card-title">${item.name}</h5>
                            <p class="card-text">ราคา: ฿${price.toFixed(2)}</p>
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

        $("#total-price").text(total.toLocaleString("th-TH", { style: "currency", currency: "THB" }));
    }

    // อัปเดตจำนวนสินค้า
    function updateQuantity(index, change) {
        let cart = JSON.parse(localStorage.getItem("cart"));
        cart[index].quantity += change;
        if (cart[index].quantity <= 0) cart.splice(index, 1); // ลบสินค้าหากจำนวน <= 0
        localStorage.setItem("cart", JSON.stringify(cart));
        loadCart(); // โหลดตะกร้าใหม่
    }

    // ลบสินค้าออกจากตะกร้า
    function removeFromCart(index) {
        let cart = JSON.parse(localStorage.getItem("cart"));
        cart.splice(index, 1); // ลบสินค้าตามตำแหน่ง
        localStorage.setItem("cart", JSON.stringify(cart));
        loadCart(); // โหลดตะกร้าใหม่
    }

    // โหลดตะกร้าสินค้าเมื่อเปิดหน้า
    $(document).ready(loadCart);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
