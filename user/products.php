<?php
session_start();
require_once __DIR__ . '/../db/db.php';

// ตรวจสอบสถานะผู้ใช้ที่เข้าสู่ระบบ
if (isset($_SESSION['member_id'])) {
    $member_id_for_status_check = $_SESSION['member_id'];
    $stmt_status = $conn->prepare("SELECT status FROM members WHERE member_id = ?");
    if ($stmt_status) {
        $stmt_status->bind_param("i", $member_id_for_status_check);
        $stmt_status->execute();
        $result_status = $stmt_status->get_result();
        if ($row_status = $result_status->fetch_assoc()) {
            if ((int)$row_status['status'] === 0) {
                session_unset();
                session_destroy();
                header('Location: index.php?login_error=disabled');
                exit;
            }
        }
        $stmt_status->close();
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>สินค้าผลิตภัณฑ์ - สวนลุงเผือก</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
 
    <style>

        :root
        {
            --primary-color:    #25a2b6;
            --secondary-color: #44e3ff;
            --text-color: #333;
            --bg-color: #f5f5f5;
        }


        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        /* Shopee Style Header */
        .shopee-header {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        /* Hero Banner - Shopee Style */
        .hero-banner {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            margin: 0 0 20px 0;
            padding: 40px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
        }

        .hero-banner::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
        }

        .hero-content-shopee {
            position: relative;
            z-index: 2;
        }

        .hero-title-shopee {
            font-size: 2.2rem;
            font-weight: 800;
            color: white;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .hero-subtitle-shopee {
            font-size: 1rem;
            color: rgba(255,255,255,0.95);
            font-weight: 500;
        }

        /* Category Pills - Shopee Style */
        .category-pills {
            background: white;
            padding: 15px 0;
            margin-bottom: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            position: sticky;
            top: 60px;
            z-index: 999;
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: none;
        }

        .category-pills::-webkit-scrollbar {
            display: none;
        }

        .pill-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            margin: 0 5px;
            border: 1px solid #e5e5e5;
            background: white;
            border-radius: 30px;
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .pill-btn i {
            font-size: 1rem;
        }

        .pill-btn:hover, .pill-btn.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255,107,53,0.3);
        }

        /* Product Card - Shopee Style */
        .product-card-shopee {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            height: 100%;
            position: relative;
            cursor: pointer;
        }

        .product-card-shopee:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .product-image-wrapper-shopee {
            position: relative;
            overflow: hidden;
            background: #fafafa;
            padding-top: 100%;
        }

        .product-image-shopee {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card-shopee:hover .product-image-shopee {
            transform: scale(1.05);
        }

        /* Badges */
        .badge-seasonal {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 2;
            box-shadow: 0 2px 8px rgba(255,107,53,0.3);
        }

        .badge-stock {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            z-index: 2;
        }

        /* Product Info */
        .product-info-shopee {
            padding: 12px;
        }

        .product-name-shopee {
            font-size: 0.9rem;
            font-weight: 600;
            color: #222;
            margin-bottom: 6px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
            min-height: 2.8em;
        }

        .product-price-shopee {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 4px;
        }

        .product-price-shopee small {
            font-size: 0.7rem;
            font-weight: 500;
            color: #999;
        }

        .product-sold-shopee {
            font-size: 0.7rem;
            color: #999;
            margin-bottom: 8px;
        }

        /* Quantity Control - Shopee Style */
        .quantity-control-shopee {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #f0f0f0;
        }

        .qty-btn-shopee {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid #e5e5e5;
            background: white;
            color: var(--primary-color);
            font-weight: 600;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .qty-btn-shopee:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .qty-input-shopee {
            width: 50px;
            text-align: center;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 6px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .btn-add-cart-shopee {
            background: var(--primary-color);
            border: none;
            color: white;
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            margin-top: 8px;
        }

        .btn-add-cart-shopee:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255,107,53,0.3);
        }

        /* Info Notice - Shopee Style */
        .info-notice-shopee {
            background: #fff7e6;
            border-left: 4px solid var(--primary-color);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .info-notice-shopee h6 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 8px;
        }

        .info-notice-shopee p {
            margin-bottom: 5px;
            font-size: 0.85rem;
            color: #666;
        }

        /* Floating Cart - Shopee Style */
        .cart-floating {
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 4px 16px rgba(255,107,53,0.4);
            z-index: 1000;
            transition: all 0.3s;
            text-decoration: none;
        }

        .cart-floating:hover {
            transform: scale(1.1);
            background: var(--secondary-color);
            color: white;
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--accent-color);
            color: white;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            border: 2px solid white;
        }

        .status-floating {
            position: fixed;
            bottom: 155px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: all 0.3s;
            text-decoration: none;
        }

        .status-floating:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        /* Toast Notification */
        .toast-shopee {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: white;
            color: #333;
            padding: 12px 24px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            z-index: 10000;
            opacity: 0;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .toast-shopee.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        .toast-shopee i {
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title-shopee {
                font-size: 1.5rem;
            }
            
            .category-pills {
                top: 55px;
            }
            
            .cart-floating, .status-floating {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
            
            .status-floating {
                bottom: 140px;
            }
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/navbar.php'; ?>
    <?php include __DIR__ . '/fb_chat_button.php'; ?>

    <!-- Hero Banner Shopee Style -->
    <div class="hero-banner">
        <div class="container">
            <div class="hero-content-shopee text-center">
                <h1 class="hero-title-shopee">🛒 สวนลุงเผือก</h1>
                <p class="hero-subtitle-shopee">สินค้าเกษตรปลอดสาร สดใหม่ ส่งตรงจากสวน สั่งง่าย จ่ายคล่อง ได้ของไว</p>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Info Notice -->
        <div class="info-notice-shopee animate__animated animate__fadeInUp">
            <h6><i class="fas fa-info-circle"></i> หมายเหตุการสั่งซื้อ</h6>
            <div class="row">
                <div class="col-md-4">
                    <p><i class="fas fa-truck"></i> จัดส่งฟรี! ขั้นต่ำ 500 บาท</p>
                </div>
                <div class="col-md-4">
                    <p><i class="fas fa-store"></i> รับสินค้าที่สวนได้</p>
                </div>
                <div class="col-md-4">
                    <p><i class="fas fa-phone-alt"></i> ติดต่อกลับหากสินค้าไม่พอ</p>
                </div>
            </div>
        </div>

        <!-- Category Pills -->
        <div class="category-pills">
            <div class="container">
                <button class="pill-btn active" onclick="filterProducts('all')">
                    <i class="fas fa-th-large"></i> ทั้งหมด
                </button>
                <button class="pill-btn" onclick="filterProducts('seasonal')">
                    <i class="fas fa-star"></i> ตามฤดูกาล
                </button>
                <button class="pill-btn" onclick="filterProducts('normal')">
                    <i class="fas fa-leaf"></i> สินค้าทั่วไป
                </button>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="row g-3" id="product-list">
            <?php
            $sql = "SELECT * FROM products WHERE status = 'active' ORDER BY product_id DESC";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0):
                while ($p = $result->fetch_assoc()):
                    $image = $p['product_image'] ? "../admin/uploads/products/" . $p['product_image'] : "../assets/no-image.png";
            ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 product-item animate__animated animate__fadeInUp"
                        data-seasonal="<?= $p['seasonal'] ?>">
                        
                        <div class="product-card-shopee">
                            <div class="product-image-wrapper-shopee">
                                <?php if ($p['seasonal'] == 1): ?>
                                    <span class="badge-seasonal">
                                        <i class="fas fa-star"></i> ตามฤดูกาล
                                    </span>
                                <?php endif; ?>
                                <span class="badge-stock">
                                    <i class="fas fa-check-circle"></i> พร้อมส่ง
                                </span>
                                <img src="<?= htmlspecialchars($image) ?>"
                                    alt="<?= htmlspecialchars($p['product_name']) ?>"
                                    class="product-image-shopee"
                                    loading="lazy">
                            </div>
                            
                            <div class="product-info-shopee">
                                <div class="product-name-shopee">
                                    <?= htmlspecialchars($p['product_name']) ?>
                                </div>
                                <div class="product-price-shopee">
                                    ฿<?= number_format($p['price'], 2) ?>
                                    <small>/ <?= htmlspecialchars($p['unit']) ?></small>
                                </div>
                                <div class="product-sold-shopee">
                                    <i class="fas fa-chart-line"></i> ขายแล้ว 1.2k ชิ้น
                                </div>
                                
                                <div class="quantity-control-shopee">
                                    <button class="qty-btn-shopee" onclick="changeQty(<?= $p['product_id'] ?>,-1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number"
                                        id="qty<?= $p['product_id'] ?>"
                                        class="qty-input-shopee"
                                        value="1"
                                        min="1">
                                    <button class="qty-btn-shopee" onclick="changeQty(<?= $p['product_id'] ?>,1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <button class="btn-add-cart-shopee"
                                    onclick="addToCart(<?= $p['product_id'] ?>, '<?= htmlspecialchars($p['product_name']) ?>', <?= $p['price'] ?>, '<?= htmlspecialchars($image) ?>')">
                                    <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                                </button>
                            </div>
                        </div>
                    </div>
                <?php
                endwhile;
            else:
                ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-box-open" style="font-size: 60px; color: #ccc;"></i>
                    <h4 class="mt-3 text-muted">ไม่มีสินค้าในขณะนี้</h4>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Floating Buttons -->
    <a href="order_status.php" class="status-floating" id="statusButton">
        <i class="fas fa-clipboard-list"></i>
    </a>
    
    <a href="order.php" class="cart-floating" id="cartButton" style="display: none;">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-badge" id="cartCount">0</span>
    </a>

    <!-- Toast Notification -->
    <div id="toastNotification" class="toast-shopee">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">เพิ่มสินค้าลงตะกร้าเรียบร้อย!</span>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function changeQty(id, delta) {
            let input = document.getElementById('qty' + id);
            let val = parseInt(input.value) + delta;
            if (val < 1) val = 1;
            input.value = val;
            
            // Animation
            input.style.transform = 'scale(1.05)';
            setTimeout(() => input.style.transform = 'scale(1)', 150);
        }

        function showToast(message) {
            const toast = document.getElementById('toastNotification');
            const toastMessage = document.getElementById('toastMessage');
            toastMessage.textContent = message;
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2000);
        }

        function addToCart(id, name, price, image) {
            let qty = parseInt(document.getElementById('qty' + id).value);
            let cart = JSON.parse(localStorage.getItem("cart")) || [];
            let found = cart.find(i => i.product_id === id);
            
            if (found) {
                found.quantity += qty;
                showToast(`📦 เพิ่ม ${name} อีก ${qty} ชิ้น ลงตะกร้าแล้ว`);
            } else {
                cart.push({
                    product_id: id,
                    name: name,
                    price: price,
                    image: image,
                    quantity: qty
                });
                showToast(`✅ เพิ่ม ${name} ${qty} ชิ้น ลงตะกร้าเรียบร้อย`);
            }
            
            localStorage.setItem("cart", JSON.stringify(cart));
            updateCartCount();
            pulseCartButton();
            
            // Reset quantity
            document.getElementById('qty' + id).value = 1;
        }

        function updateCartCount() {
            let cart = JSON.parse(localStorage.getItem("cart")) || [];
            let total = cart.reduce((sum, i) => sum + i.quantity, 0);
            document.getElementById("cartCount").innerText = total;
            document.getElementById("cartButton").style.display = total > 0 ? "flex" : "none";
        }

        function pulseCartButton() {
            let btn = document.getElementById("cartButton");
            btn.style.transform = 'scale(1.2)';
            setTimeout(() => btn.style.transform = 'scale(1)', 200);
        }

        function filterProducts(type) {
            const items = document.querySelectorAll('.product-item');
            const buttons = document.querySelectorAll('.pill-btn');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.currentTarget.classList.add('active');
            
            items.forEach(item => {
                const seasonal = item.getAttribute('data-seasonal');
                if (type === 'all') {
                    item.style.display = '';
                } else if (type === 'seasonal' && seasonal === '1') {
                    item.style.display = '';
                } else if (type === 'normal' && seasonal === '0') {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        document.addEventListener("DOMContentLoaded", () => {
            updateCartCount();
            
            // Add ripple effect to buttons
            document.querySelectorAll('.btn-add-cart-shopee, .qty-btn-shopee').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    let ripple = document.createElement('span');
                    ripple.classList.add('ripple');
                    this.appendChild(ripple);
                    setTimeout(() => ripple.remove(), 500);
                });
            });
        });
        
        // Ripple effect styles
        const style = document.createElement('style');
        style.textContent = `
            .btn-add-cart-shopee, .qty-btn-shopee {
                position: relative;
                overflow: hidden;
            }
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255,255,255,0.5);
                transform: scale(0);
                animation: ripple-animation 0.5s linear;
                pointer-events: none;
            }
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>