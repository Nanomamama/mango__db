<?php
session_start();
require_once '../admin/db.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>สินค้าผลิตภัณฑ์</title>

<link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.product-image{
    width:100%;
    height:220px;
    object-fit:cover;
    border-radius:5px;
}
.cart-button {
    position: fixed;        /* ทำให้ลอย */
    bottom: 20px;           /* ห่างจากล่าง */
    right: 20px;            /* ห่างจากขวา */
    z-index: 999;           /* อยู่บนสุด */
    display: none;          /* ซ่อนไว้ก่อน */
}

</style>
</head>

<body>
<?php include 'navbar.php'; ?>
<br>
<br>
<div class="container mt-5">
    <h1 class="text-center mb-3">สินค้าผลิตภัณฑ์</h1>

    <div class="text-center mb-3">
        <a href="order_status.php" class="btn btn-info">ติดตามสินค้า</a>
        <a href="cart.php" class="btn btn-primary">ไปที่ตะกร้า</a>
    </div>

    <input type="text" id="searchInput" class="form-control mb-4" placeholder="ค้นหาสินค้า...">

    <div class="row" id="product-list">

<?php
$sql = "SELECT * FROM products WHERE stock > 0 ORDER BY product_id DESC";
$result = $conn->query($sql);

while ($p = $result->fetch_assoc()):
    $image = $p['image'] ? "../admin/uploads/products/".$p['image'] : "../assets/no-image.png";
?>

<div class="col-lg-3 col-md-4 col-sm-6 mb-4 product-item">
    <div class="card h-100 shadow-sm">

        <img src="<?= htmlspecialchars($image) ?>" class="product-image">

        <div class="card-body text-center">
            <h5 class="card-title"><?= htmlspecialchars($p['product_name']) ?></h5>

            <p class="text-danger fw-bold">
                ฿<?= number_format($p['price'],2) ?>
            </p>

            <p class="small">
                คงเหลือ <?= $p['stock'] ?> ชิ้น
            </p>

            <?php if ($p['stock'] <= $p['min_stock']): ?>
                <span class="badge bg-danger mb-2">ใกล้หมด</span>
            <?php endif; ?>

            <div class="d-grid gap-2 mt-2">
                <button class="btn btn-info"
                        data-bs-toggle="modal"
                        data-bs-target="#detail<?= $p['product_id'] ?>">
                    ดูรายละเอียด
                </button>

                <button class="btn btn-success add-to-cart"
                        data-id="<?= $p['product_id'] ?>"
                        data-name="<?= htmlspecialchars($p['product_name']) ?>"
                        data-price="<?= $p['price'] ?>"
                        data-stock="<?= $p['stock'] ?>"
                        data-image="<?= $image ?>">
                    🛒 เพิ่มลงตะกร้า
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal รายละเอียด -->
<div class="modal fade" id="detail<?= $p['product_id'] ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= htmlspecialchars($p['product_name']) ?></h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <img src="<?= htmlspecialchars($image) ?>" class="img-fluid mb-3">
        <p><?= nl2br(htmlspecialchars($p['description'])) ?></p>
        <p><strong>น้ำหนัก:</strong> <?= $p['weight'] ?> กก.</p>
      </div>
    </div>
  </div>
</div>

<?php endwhile; ?>

    </div>
</div>

<a href="cart.php" class="btn btn-warning cart-button">🛒 ไปที่ตะกร้า</a>

<?php include 'footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

//ปุ่มไปที่ตระกร้า
const cartButton = document.querySelector('.cart-button');

// เมื่อมีการ scroll หน้าจอ
    window.addEventListener("scroll", function () {

        // เช็คว่าเลื่อนลงมากกว่า 200px หรือยัง
        if (window.scrollY > 100) {
            cartButton.style.display = "block"; // แสดงปุ่ม
        } else {
            cartButton.style.display = "none";  // ซ่อนปุ่ม
        }

    });


// ค้นหา
$("#searchInput").on("keyup", function(){
    let v = $(this).val().toLowerCase();
    $(".product-item").each(function(){
        $(this).toggle($(this).text().toLowerCase().includes(v));
    });
});

// เพิ่มลงตะกร้า
$(".add-to-cart").click(function(){
    let product = {
        product_id: $(this).data("id"), // ✅ สำคัญมาก
        name: $(this).data("name"),
        price: $(this).data("price"),
        image: $(this).data("image"),
        quantity: 1,
        stock: $(this).data("stock")
    };

    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    let found = cart.find(i => i.product_id === product.product_id);

    if (found) {
        if (found.quantity < product.stock) {
            found.quantity++;
        }
    } else {
        cart.push(product);
    }

    localStorage.setItem("cart", JSON.stringify(cart));

    Swal.fire({
        icon: "success",
        title: "เพิ่มสินค้าแล้ว",
        timer: 1200,
        showConfirmButton: false
    });
});
localStorage.removeItem("cart");

</script>

</body>
</html>
