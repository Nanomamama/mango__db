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

        <a href="products.php" class="btn btn-warning">🔙 เลือกสินค้า</a>
        <button class="btn btn-danger" onclick="resetCart()">🗑️ ทิ้งตะกร้า</button>
        <button id="checkout-btn" class="btn btn-primary" onclick="window.location.href='checkout.php'" disabled>ดำเนินการชำระเงิน</button>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // โหลดสินค้าจาก Local Storage
    function loadCart() {
        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        let container = $("#cart-container");
        container.empty();
        let total = 0;

        let checkoutBtn = $('#checkout-btn');

        if (cart.length === 0) {
            container.append(`<div class="text-center text-muted">🛒 ไม่มีสินค้าในตะกร้า</div>`);
            checkoutBtn.prop('disabled', true);
        } else {
            checkoutBtn.prop('disabled', false);
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
                            <p class="card-text">ราคา: ฿${price.toFixed(2)} <span class="text-muted">(คลัง: ${item.stock})</span></p>
                            <p class="card-text">
                                จำนวน:
                                <button class="btn btn-sm btn-outline-danger me-1" onclick="updateQuantity(${index}, -1)">➖</button>
                                  <input type="number" class="form-control d-inline w-auto text-center" value="${item.quantity}" min="1" max="${item.stock}" onchange="updateQuantityDirect(${index}, this.value)">
                                <button class="btn btn-sm btn-outline-success ms-1" onclick="updateQuantity(${index}, 1)" ${item.quantity >= item.stock ? "disabled" : ""}>➕</button>
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
        let item = cart[index];

        // ตรวจสอบว่าจำนวนสินค้าไม่เกินสต็อก
        if (change > 0 && item.quantity >= item.stock) {
            alert(`สินค้า "${item.name}" มีจำนวนในสต็อกไม่เพียงพอ`);
            return;
        }

        item.quantity += change;

        // ลบสินค้าหากจำนวน <= 0
        if (item.quantity <= 0) cart.splice(index, 1);

        localStorage.setItem("cart", JSON.stringify(cart));
        loadCart(); // โหลดตะกร้าใหม่
    }

    // อัปเดตจำนวนสินค้าโดยตรง พิมพ์ในอินพุต
    function updateQuantityDirect(index, value) {
        let cart = JSON.parse(localStorage.getItem("cart"));
        let item = cart[index];
        let newQuantity = parseInt(value);

        // ตรวจสอบว่าจำนวนที่พิมพ์ไม่เกินสต็อกและไม่น้อยกว่า 1
        if (newQuantity > item.stock) {
            Swal.fire({
                icon: 'error',
                title: 'ขออภัยจำนวนสินค้าไม่เพียงพอ',
                text: `สินค้า "${item.name}" มีจำนวนในสต็อกเพียง ${item.stock} ชิ้น`,
                confirmButtonText: 'ตกลง'
            });
            newQuantity = item.stock; // ตั้งค่าเป็นจำนวนสูงสุดในสต็อก
        } else if (newQuantity < 1) {
            Swal.fire({
                icon: 'warning',
                title: 'จำนวนสินค้าไม่ถูกต้อง',
                text: 'จำนวนสินค้าต้องไม่น้อยกว่า 1',
                confirmButtonText: 'ตกลง'
            });
            newQuantity = 1; // ตั้งค่าเป็นอย่างน้อย 1
        }

        item.quantity = newQuantity;

        localStorage.setItem("cart", JSON.stringify(cart));
        loadCart(); // โหลดตะกร้าใหม่
    }
    function resetCart() {
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "การรีเซ็ตตะกร้าจะลบสินค้าทั้งหมด!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, ลบทั้งหมด!',
        cancelButtonText: 'ยกเลิก',
       
    }).then((result) => {
        if (result.isConfirmed) {
            localStorage.removeItem("cart"); // ลบข้อมูลตะกร้าใน localStorage
            loadCart(); // โหลดตะกร้าใหม่
            Swal.fire(
                'ลบสำเร็จ!',
                'ตะกร้าสินค้าของคุณถูกลบแล้ว.',
                'success'
            );
        }
    });
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
