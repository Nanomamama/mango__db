<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สินค้าผลิตภัณฑ์</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
  .cart-button {
    position: fixed;
    bottom: 2px;        /* ระยะจากขอบล่าง */
    left: 50%;           /* วางตรงกลางของหน้าจอ */
    transform: translateX(-50%);  /* ปรับปุ่มให้อยู่ตรงกลาง */
    z-index: 1050;       /* ให้อยู่หน้าสุด */
    border-radius: 50px; /* ทำให้ดูโค้งๆ */
    padding: 12px 20px;
    font-size: 16px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  }
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<br>
<br>
<div class="container text-cente">
    <div class="row">
      
        <!-- Content -->
        <div class="col ">
            <div class="container mt-4 text-center">

                <br>
                <h1>📦 สินค้าผลิตภัณฑ์แปรรูป</h1>
                <a href="order_status.php" class="btn btn-info mb-3">ติดตามสินค้า</a>
                <a href="cart.php" class="btn btn-primary mb-3"> ไปที่ตะกร้าสินค้า</a>
                <input type="text" id="searchInput" class="form-control mb-3" placeholder="🔍 ค้นหาสินค้า...">

                <div class="row" id="product-list">
                    <!-- สินค้าตัวอย่าง -->
                    <?php
$products = [
    ["id" => "001", "name" => "กล้วยทอดอบเนย", "price" => 50, "type" => "ทอด", "image" => "https://down-th.img.susercontent.com/file/th-11134207-7r98r-lo5m7m19khdc53", "description" => "กล้วยทอดอบเนยกรอบอร่อย", "weight" => "200g"],
    ["id" => "002", "name" => "มันฝรั่งทอด", "price" => 60, "type" => "ทอด", "image" => "https://inwfile.com/s-i/fdkajx.jpg", "description" => "มันฝรั่งทอดกรอบ รสชาติกลมกล่อม", "weight" => "250g"],
    ["id" => "003", "name" => "ขนมข้าวโพดอบกรอบ", "price" => 40, "type" => "อบ", "image" => "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRpDYUXjOtJiMOi06q2SOrzIcE64WvGnhTBlQ&s", "description" => "ขนมข้าวโพดอบกรอบรสชาติหวาน", "weight" => "150g"],
    ["id" => "004", "name" => "ข้าวเกรียบกุ้ง", "price" => 55, "type" => "ทอด", "image" => "https://image.makewebeasy.net/makeweb/m_1920x0/t2rR4pVxh/Snack20/121_01.jpg?v=202405291424", "description" => "ข้าวเกรียบกุ้งรสชาติอร่อย", "weight" => "300g"]
];

foreach ($products as $product) {
    echo '<div class="col-lg-3 col-md-4 col-sm-6 mb-4">';
    echo '    <div class="card h-100 shadow-sm">';
    echo '        <img src="' . $product["image"] . '" class="card-img-top" alt="' . $product["name"] . '">';
    echo '        <div class="card-body text-center">';
    echo '            <h5 class="card-title">' . $product["name"] . '</h5>';
    echo '            <p class="card-text text-danger fw-bold">฿' . number_format($product["price"], 2) . '</p>';
    echo '            <p class="card-text text-muted">' . $product["description"] . '</p>'; // คำอธิบายสินค้า
    echo '            <p class="card-text text-muted">ประเภท:' . $product["type"] . '</p>'; // ประเภทสินค้า เช่น 
    echo '            <p class="card-text text-muted">น้ำหนัก: ' . $product["weight"] . '</p>'; // น้ำหนักสินค้า
    echo '            <button class="btn btn-success add-to-cart" data-id="' . $product["id"] . '" data-name="' . $product["name"] . '" data-price="' . $product["price"] . '">🛒 เพิ่มลงตะกร้า</button>';
    echo '        </div>';
    echo '    </div>';
    echo '</div>';
}
?>

                </div>

               <a href="cart.php" class="btn btn-warning cart-button ">🛒 ไปที่ตะกร้าสินค้า</a>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
  image: $(this).closest(".card").find("img").attr("src"), // ✅ จุดสำคัญ
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>