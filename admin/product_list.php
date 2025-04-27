<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการเลือกซื้อสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .content {
            padding-left: 260px; /* เว้นช่องว่างจาก Sidebar */
            overflow-x: hidden; /* ป้องกันการซ้อนทับ */
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="container-fluid">
    <div class="row">
      

        <!-- Content -->
        <div class="col-md-10 content">
            <div class="container mt-4">
                <h1>📦 สินค้าผลิตภัณฑ์แปรรูป</h1>
                <input type="text" id="searchInput" class="form-control mb-3" placeholder="🔍 ค้นหาสินค้า...">

                <div class="row" id="product-list">
                    <!-- สินค้าตัวอย่าง -->
                    <?php
                    $products = [
                        ["id" => "001", "name" => "กล้วยทอดอบเนย", "price" => 50, "image" => "https://down-th.img.susercontent.com/file/th-11134207-7r98r-lo5m7m19khdc53"],
                        ["id" => "002", "name" => "มันฝรั่งทอด", "price" => 60, "image" => "https://inwfile.com/s-i/fdkajx.jpg"],
                        ["id" => "003", "name" => "ขนมข้าวโพดอบกรอบ", "price" => 40, "image" => "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRpDYUXjOtJiMOi06q2SOrzIcE64WvGnhTBlQ&s"],
                        ["id" => "004", "name" => "ข้าวเกรียบกุ้ง", "price" => 55, "image" => "https://image.makewebeasy.net/makeweb/m_1920x0/t2rR4pVxh/Snack20/121_01.jpg?v=202405291424"]
                    ];

                    foreach ($products as $product) {
                        echo '<div class="col-lg-3 col-md-4 col-sm-6 mb-4">';
                        echo '    <div class="card h-100 shadow-sm">';
                        echo '        <img src="' . $product["image"] . '" class="card-img-top" alt="' . $product["name"] . '">';
                        echo '        <div class="card-body text-center">';
                        echo '            <h5 class="card-title">' . $product["name"] . '</h5>';
                        echo '            <p class="card-text text-danger fw-bold">฿' . number_format($product["price"], 2) . '</p>';
                        echo '            <button class="btn btn-success add-to-cart" data-id="' . $product["id"] . '" data-name="' . $product["name"] . '" data-price="' . $product["price"] . '">🛒 เพิ่มลงตะกร้า</button>';
                        echo '        </div>';
                        echo '    </div>';
                        echo '</div>';
                    }
                    ?>
                </div>

                <a href="cart.php" class="btn btn-primary mt-3"> ไปที่ตะกร้าสินค้า</a>
            </div>
        </div>
    </div>
</div>

<script>
// ค้นหาสินค้า
$("#searchInput").on("keyup", function () {
    let filter = $(this).val().toLowerCase();
    $("#product-list .col-lg-3").each(function () {
        $(this).toggle($(this).find(".card-title").text().toLowerCase().indexOf(filter) > -1);
    });
});

// เพิ่มสินค้าลงตะกร้า
$(".add-to-cart").click(function () {
    let product = {
        id: $(this).data("id"),
        name: $(this).data("name"),
        price: $(this).data("price"),
        quantity: 1
    };

    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    let found = cart.find(item => item.id === product.id);
    if (found) {
        found.quantity++;
    } else {
        cart.push(product);
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    alert(product.name + " ถูกเพิ่มลงตะกร้าแล้ว!");
});
</script>

</body>
</html>
