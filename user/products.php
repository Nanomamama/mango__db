<?php
session_start();
require_once __DIR__ . '/../db/db.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>สินค้าผลิตภัณฑ์ - สวนลุงเผือก</title>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

<style>
* {
    font-family: 'Prompt', sans-serif;
}

body {
    background: #ffffff;
}

/* Hero Section */
.hero-section {
    position: relative;
    background: #ffffff;
    color: #333;
    padding: 40px 0 20px;
    margin-bottom: 30px;
    border-bottom: 1px solid #e5e5e5;
}

.hero-content {
    text-align: center;
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 15px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.hero-subtitle {
    font-size: 1.2rem;
    opacity: 0.95;
}

/* Carousel Enhancement */
#heroCarousel {
    margin-bottom: 30px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.carousel-item img {
    height: 400px;
    object-fit: cover;
}

.carousel-caption {
    background: rgba(0,0,0,0.6);
    padding: 20px;
    border-radius: 10px;
}

/* Info Alert */
.info-box {
    background: #ffffff;
    color: #333;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 1px solid #e5e5e5;
}

.info-box h5 {
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
    font-size: 1.1rem;
}

.info-box p {
    margin-bottom: 8px;
    line-height: 1.6;
    color: #666;
    font-size: 0.95rem;
}

/* Category Filter */
.category-filter {
    background: #ffffff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #e5e5e5;
}

.filter-btn {
    margin: 5px;
    border-radius: 6px;
    padding: 8px 18px;
    border: 1px solid #ddd;
    color: #666;
    background: white;
    transition: all 0.3s;
    font-size: 0.9rem;
}

.filter-btn:hover,
.filter-btn.active {
    background: #333;
    color: white;
    border-color: #333;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

/* Product Card */
.product-card {
    background: #ffffff;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    height: 100%;
    position: relative;
    border: 1px solid #e5e5e5;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    border-color: #ddd;
}

.product-image-wrapper {
    position: relative;
    overflow: hidden;
    height: 250px;
    background: #fafafa;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}

.product-card:hover .product-image {
    transform: scale(1.1);
}

.product-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #dc3545;
    color: white;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(220, 53, 69, 0.3);
    z-index: 2;
}

.stock-status {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #28a745;
    color: white;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    z-index: 2;
}

.product-body {
    padding: 20px;
}

.product-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
    min-height: 50px;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #dc3545;
    margin-bottom: 5px;
}

.product-unit {
    color: #7f8c8d;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.product-actions {
    display: grid;
    gap: 10px;
}

.btn-detail {
    background: white;
    border: 1px solid #ddd;
    color: #666;
    border-radius: 6px;
    padding: 10px;
    font-weight: 500;
    transition: all 0.3s;
    font-size: 0.9rem;
}

.btn-detail:hover {
    background: #f8f9fa;
    color: #333;
    border-color: #ccc;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.btn-add-cart {
    background: #333;
    border: none;
    color: white;
    border-radius: 6px;
    padding: 12px;
    font-weight: 600;
    transition: all 0.3s;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    font-size: 0.9rem;
}

.btn-add-cart:hover {
    background: #000;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

/* Cart Button */
.cart-button {
    position: fixed;
    bottom: 100px;
    right: 30px;
    width: 65px;
    height: 65px;
    background: #fdc304;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    text-decoration: none;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    z-index: 9999;
    transition: all 0.3s;
}

.cart-button:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    background: #ff6309;
    color: white;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.02);
    }
}

.cart-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    border: 2px solid white;
    box-shadow: 0 2px 6px rgba(220, 53, 69, 0.4);
}

/* Action Buttons */
.action-buttons {
    text-align: center;
    margin-bottom: 40px;
}

.btn-track {
    background: #333;
    border: none;
    color: white;
    padding: 12px 30px;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.btn-track:hover {
    background: #000;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

/* Modal Enhancement */
.modal-content {
    border-radius: 20px;
    border: none;
    overflow: hidden;
}

.modal-header {
    background: #333;
    color: white;
    border: none;
    padding: 20px 30px;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
}

.modal-body {
    padding: 30px;
}

.modal-body img {
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

/* Loading Animation */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.95);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #333;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 1.8rem;
    }
    
    .carousel-item img {
        height: 250px;
    }
    
    .product-image-wrapper {
        height: 200px;
    }
    
    .cart-button {
        width: 60px;
        height: 60px;
        bottom: 80px;
        right: 20px;
        font-size: 24px;
    }
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 80px;
    color: #ddd;
    margin-bottom: 20px;
}

</style>
</head>

<body>
<?php include __DIR__ . '/navbar.php'; ?>
<?php include __DIR__ . '/fb_chat_button.php'; ?>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

<!-- Hero Carousel -->
 <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
  </div>
  
  <div class="carousel-inner">
    Slide 1 
    <div class="carousel-item active">
      <div class="row g-0">
        <div class="col-md-6">
          <img src="../admin/uploads/products/กล้วยทอด.jpg" class="d-block w-100">
        </div>
        <div class="col-md-6">
          <img src="../admin/uploads/products/มะม่วง.jpg" class="d-block w-100">
        </div>
      </div>
      <div class="carousel-caption">
        <h3 class="animate__animated animate__fadeInDown">สวนลุงเผือก</h3>
        <p class="animate__animated animate__fadeInUp">ผักสด ปลอดสาร ส่งตรงจากสวน</p>
      </div>
    </div>

    Slide 2 
    <div class="carousel-item">
      <div class="row g-0">
        <div class="col-md-6">
          <img src="../admin/uploads/products/มะขามแช่อิ่ม.jpg" class="d-block w-100">
        </div>
        <div class="col-md-6">
          <img src="../admin/uploads/products/ไข่ไก่.jpg" class="d-block w-100">
        </div>
      </div>
    </div>
  </div>

  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div> 

<div class="container">
   <!-- Hero Content -->
    
   <div class="hero-content mb-4 animate__animated animate__fadeIn">
        <h1 class="hero-title"> ร้านค้าออนไลน์สวนลุงเผือก</h1>
        <p class="hero-subtitle">สินค้าเกษตรปลอดสาร สดใหม่ ส่งตรงจากสวน</p>
   </div>

   <!-- Info Box -->
   <div class="info-box animate__animated animate__fadeInUp">
      <h5><i class="fas fa-info-circle"></i> หมายเหตุการสั่งซื้อสินค้า</h5>
      <p><i class="fas fa-map-marker-alt"></i> ให้บริการจัดส่งเฉพาะในพื้นที่บ้านบุฮม และสามารถรับสินค้าที่สวนได้โดยตรง</p>
      <p><i class="fas fa-calendar-check"></i> สามารถสั่งจองล่วงหน้าได้ ทางสวนจะยืนยันคำสั่งซื้ออีกครั้ง</p>
      <p><i class="fas fa-phone-alt"></i> หากสินค้าไม่เพียงพอ ทางสวนจะติดต่อแจ้งทางโทรศัพท์</p>
      <p><i class="fas fa-search"></i> ตรวจสอบสถานะการสั่งซื้อได้ทันที ผ่านหมายเลขโทรศัพท์</p>
      <p class="mb-0"><strong> ขอบคุณทุกท่านที่อุดหนุนสวนลุงเผือก</strong></p>
   </div>

   <!-- Action Buttons -->
   <div class="action-buttons animate__animated animate__fadeInUp">
       <a href="order_status.php" class="btn btn-track">
           <i class="fas fa-truck"></i> ติดตามสถานะสินค้า
       </a>
   </div>

   <!-- Category Filter (Optional - if you have categories) -->
   <div class="category-filter animate__animated animate__fadeInUp">
       <div class="text-center">
           <button class="filter-btn active" data-filter="all">
               <i class="fas fa-th"></i> ทั้งหมด
           </button>
           <button class="filter-btn" data-filter="seasonal">
               <i class="fas fa-leaf"></i> สินค้าตามฤดูกาล
           </button>
           <button class="filter-btn" data-filter="regular">
               <i class="fas fa-box"></i> สินค้าปกติ
           </button>
       </div>
   </div>

   <!-- Product Grid -->
   <div class="row g-4" id="product-list">

<?php
$sql = "SELECT * FROM products 
        WHERE status = 'active'
        ORDER BY product_id DESC";

$result = $conn->query($sql);
$count = 0;

if ($result->num_rows > 0):
    while ($p = $result->fetch_assoc()):
        $count++;
        $image = $p['product_image'] 
            ? "../admin/uploads/products/".$p['product_image'] 
            : "../assets/no-image.png";
        
        $delay = ($count % 4) * 100;
?>

<div class="col-lg-3 col-md-4 col-sm-6 product-item animate__animated animate__fadeInUp" 
     data-seasonal="<?= $p['seasonal'] ?>" 
     style="animation-delay: <?= $delay ?>ms">
    <div class="product-card">
        <div class="product-image-wrapper">
            <?php if ($p['seasonal'] == 1): ?>
                <span class="product-badge">
                    <i class="fas fa-star"></i> ตามฤดูกาล
                </span>
            <?php endif; ?>
            
            <span class="stock-status">
                <i class="fas fa-check-circle"></i> พร้อมจำหน่าย
            </span>
            
            <img src="<?= htmlspecialchars($image) ?>" 
                 alt="<?= htmlspecialchars($p['product_name']) ?>"
                 class="product-image">
        </div>

        <div class="product-body">
            <h5 class="product-name"><?= htmlspecialchars($p['product_name']) ?></h5>

            <div class="product-price">
                ฿<?= number_format($p['price'], 2) ?>
            </div>
            <div class="product-unit">
                <i class="fas fa-box"></i> หน่วย: <?= htmlspecialchars($p['unit']) ?>
            </div>

            <div class="product-actions">
                <button class="btn btn-detail"
                        data-bs-toggle="modal"
                        data-bs-target="#detail<?= $p['product_id'] ?>">
                    <i class="fas fa-info-circle"></i> ดูรายละเอียด
                </button>

                <button class="btn btn-add-cart add-to-cart"
                        data-id="<?= $p['product_id'] ?>"
                        data-name="<?= htmlspecialchars($p['product_name']) ?>"
                        data-price="<?= $p['price'] ?>"
                        data-image="<?= htmlspecialchars($image) ?>">
                    <i class="fas fa-shopping-cart"></i> เพิ่มลงตะกร้า
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal รายละเอียด -->
<div class="modal fade" id="detail<?= $p['product_id'] ?>">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-leaf"></i> <?= htmlspecialchars($p['product_name']) ?>
        </h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <img src="<?= htmlspecialchars($image) ?>" 
             alt="<?= htmlspecialchars($p['product_name']) ?>"
             class="img-fluid">
        
        <div class="mt-3">
            <h6><i class="fas fa-align-left"></i> รายละเอียดสินค้า</h6>
            <p><?= nl2br(htmlspecialchars($p['product_description'])) ?></p>
        </div>
        
        <div class="row mt-3">
            <div class="col-6">
                <strong><i class="fas fa-tag"></i> ราคา:</strong><br>
                <span class="text-primary fs-4">฿<?= number_format($p['price'], 2) ?></span>
            </div>
            <div class="col-6">
                <strong><i class="fas fa-box"></i> หน่วย:</strong><br>
                <span class="fs-5"><?= htmlspecialchars($p['unit']) ?></span>
            </div>
        </div>
        
        <?php if ($p['seasonal'] == 1): ?>
        <div class="alert alert-warning mt-3">
            <i class="fas fa-exclamation-triangle"></i> 
            สินค้าตามฤดูกาล - อาจมีจำนวนจำกัด
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php 
    endwhile;
else:
?>
    <div class="col-12">
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h4>ไม่มีสินค้าในขณะนี้</h4>
            <p class="text-muted">กรุณาลองใหม่อีกครั้งภายหลัง</p>
        </div>
    </div>
<?php endif; ?>

   </div>
</div>

<!-- Floating Cart Button -->
<a href="order.php" class="cart-button" id="cartButton" style="display: none;">
  <i class="fas fa-shopping-basket"></i>
  <span class="cart-count" id="cartCount">0</span>
</a>

<?php include 'footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Update Cart Count
function updateCartCount() {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    let total = cart.reduce((sum, item) => sum + item.quantity, 0);
    $("#cartCount").text(total);

    if (total > 0) {
        $("#cartButton").fadeIn();
    } else {
        $("#cartButton").fadeOut();
    }
}

// Initialize
$(document).ready(function() {
    updateCartCount();
    
    // Auto hide carousel after 5 seconds
    setTimeout(function() {
        $('#heroCarousel').carousel('pause');
    }, 10000);
});

// Add to Cart
$(".add-to-cart").click(function(){
    let $btn = $(this);
    $btn.prop('disabled', true);
    
    let product = {
        product_id: $btn.data("id"),
        name: $btn.data("name"),
        price: $btn.data("price"),
        image: $btn.data("image"),
        quantity: 1
    };

    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    let found = cart.find(i => i.product_id === product.product_id);

    if (found) {
        found.quantity++;
    } else {
        cart.push(product);
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCount();

    // Success Animation
    Swal.fire({
        icon: "success",
        title: "เพิ่มสินค้าแล้ว!",
        text: product.name,
        timer: 1500,
        showConfirmButton: false,
        customClass: {
            popup: 'animate__animated animate__bounceIn'
        }
    });
    
    setTimeout(() => $btn.prop('disabled', false), 1500);
});

// Category Filter
$(".filter-btn").click(function(){
    $(".filter-btn").removeClass("active");
    $(this).addClass("active");
    
    let filter = $(this).data("filter");
    
    if (filter === "all") {
        $(".product-item").fadeIn();
    } else if (filter === "seasonal") {
        $(".product-item").hide();
        $(".product-item[data-seasonal='1']").fadeIn();
    } else if (filter === "regular") {
        $(".product-item").hide();
        $(".product-item[data-seasonal='0']").fadeIn();
    }
});

// Smooth Scroll
$('a[href^="#"]').on('click', function(e) {
    e.preventDefault();
    let target = this.hash;
    $('html, body').animate({
        scrollTop: $(target).offset().top - 80
    }, 800);
});

// Cart Button Pulse on Add
function pulseCartButton() {
    $("#cartButton").addClass('animate__animated animate__pulse');
    setTimeout(() => {
        $("#cartButton").removeClass('animate__animated animate__pulse');
    }, 1000);
}

</script>

</body>
</html>