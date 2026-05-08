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
        :root {
            --primary-color: #25a2b6;
            --secondary-color: #44e3ff;
            --text-color: #686767;
            --bg-color: #f5f5f5;
            --red: #ff4d4f;
            --green: #7ad04f;
            --yellow: #ffcc00;
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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
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
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
        }

        .hero-banner::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: white;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .hero-subtitle {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 500;
        }

        /* Category Pills - Shopee Style */
        .category-pills {
            background: white;
            padding: 15px 0;
            margin-bottom: 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
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

        .pill-btn:hover,
        .pill-btn.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        /* Product Card - Shopee Style */
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .product-image-wrapper {
            position: relative;
            overflow: hidden;
            background: #fafafa;
            padding-top: 100%;
        }

        .product-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        /* Badges */
        .badge-seasonal {
            position: absolute;
            top: 10px;
            right: 10px;
            /* background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); */
            background: var(--yellow);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 2;
            box-shadow: 0 2px 8px rgba(255, 107, 53, 0.3);
        }

        .badge-stock {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--green);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            z-index: 2;
        }

        /* Product Info */
        .product-info {
            padding: 12px;

            display: flex;
            flex-direction: column;

            flex: 1;
        }

        .product-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-color);
            /* margin-bottom: 6px; */
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
            min-height: 2.8em;
        }

        /* Product Description */
        .product-description {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-color);

        }

        .product-description-wrapper {
            margin-bottom: 10px;
        }

        .product-description-shopee {
            font-size: 1rem;
            color: #777;
            line-height: 1.5;

            display: -webkit-box;
              line-clamp: 2;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;

            overflow: hidden;
            transition: all .3s ease;
            
        }

        .product-description-shopee.expanded {
            -webkit-line-clamp: unset;
            overflow: visible;
        }

        .read-more-btn {
            border: none;
            background: none;
            color: var(--primary-color);
            font-size: 0.75rem;
            padding: 0;
            margin-top: 4px;
            font-weight: 600;
            cursor: pointer;
        }

        .read-more-btn:hover {
            text-decoration: underline;
        }

        /* end Product Price */
        .product-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--red);
            margin-bottom: 4px;
        }

        .product-price small {
            font-size: 1.2rem;
            font-weight: 500;
            color: #999;
        }

        .price-sold-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .product-sold {
            font-size: 0.75rem;
            color: #999;
            white-space: nowrap;
            margin-bottom: 0;
        }

        /* Quantity Control - Shopee Style */
        .quantity-control {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #f0f0f0;
        }

        .qty-btn {
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

        .qty-btn:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .qty-input {
            width: 50px;
            text-align: center;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 6px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .btn-add-cart {
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
            margin-top: auto;
        }

        .btn-add-cart:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        /* Info Notice - Shopee Style */
        .info-notice {
            background: #fff7e6;
            border-left: 4px solid var(--primary-color);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .info-notice h6 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 8px;
        }

        .info-notice p {
            margin-bottom: 5px;
            font-size: 0.85rem;
            color: #666;
        }

        /* Floating Cart - Shopee Style */
        .cart-floating {
            position: fixed;
            bottom: 95px;
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
            box-shadow: 0 4px 16px rgba(255, 107, 53, 0.4);
            z-index: 1000;
            transition: all 0.3s;
            text-decoration: none;
        }

        .cart-floating:hover {
            transform: scale(1.1);
            background: var(--secondary-color);
            color: black;
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--red);
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
            bottom: 170px;
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
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.3s;
            text-decoration: none;
        }

        .status-floating:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        /* Toast Notification */
        .toast {
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
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            z-index: 10000;
            opacity: 0;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        .toast i {
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 1.5rem;
            }

            .category-pills {
                top: 55px;
            }

            .cart-floating,
            .status-floating {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .status-floating {
                bottom: 140px;
            }
        }

        @media (max-width: 576px) {

            #product-list {
                --bs-gutter-x: 10px;
                --bs-gutter-y: 10px;
            }

            .product-info {
                padding: 10px;
            }

            .product-name {
                font-size: 0.8rem;
                min-height: 2.5em;
            }

            .product-price {
                font-size: 1rem;
            }

            .btn-add-cart {
                font-size: 0.75rem;
                padding: 8px;
            }

            .qty-btn {
                width: 28px;
                height: 28px;
            }

            .qty-input {
                width: 40px;
                font-size: 0.8rem;
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
            <div class="hero-content text-center">
                <h1 class="hero-title">ร้านค้าสวนลุงเผือก</h1>
                <p class="hero-subtitle"></p>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Info Notice -->
        <div class="info-notice animate__animated animate__fadeInUp">
            <h6><i class="fas fa-info-circle"></i> หมายเหตุการสั่งซื้อ</h6>
            <div class="row">
                <div class="col-md-4">
                    <p><i class="fas fa-truck"></i> จัดส่งฟรี! ขั้นต่ำ 500 บาท</p>
                    <p>ในพื้นที่ที่กำหนด บ้านน้อย เชียงคาน แก่งคุดคู้</p>
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

            $sql = "
            SELECT  p.*,
                COALESCE(SUM(
                CASE 
                    WHEN o.order_status = 'completed' 
                    THEN oi.quantity 
                    ELSE 0 
                END
            ),0) AS sold_count

                FROM products p

                LEFT JOIN order_items oi 
                    ON p.product_id = oi.product_id

                LEFT JOIN orders o 
                    ON oi.order_id = o.order_id

            WHERE p.status = 'active'

            GROUP BY p.product_id

            ORDER BY p.product_id DESC ";

            $result = $conn->query($sql);

            if ($result->num_rows > 0):
                while ($p = $result->fetch_assoc()):
                    $image = $p['product_image'] ? "../admin/uploads/products/" . $p['product_image'] : "../assets/no-image.png";
            ?>
                    <div class="col-6 col-sm-6 col-md-4 col-lg-3 product-item animate__animated animate__fadeInUp"
                        data-seasonal="<?= $p['seasonal'] ?>">

                        <div class="product-card">
                            <div class="product-image-wrapper">
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
                                    class="product-image"
                                    loading="lazy">
                            </div>

                            <div class="product-info">
                                <div class="product-name">
                                    <?= htmlspecialchars($p['product_name']) ?>
                                </div>

                                <div class="product-description-wrapper">

                                    <div class="product-description-shopee"
                                        id="desc<?= $p['product_id'] ?>">

                                        <?= nl2br(htmlspecialchars($p['product_description'])) ?>

                                    </div>

                                    <?php if (mb_strlen($p['product_description']) > 80): ?>
                                        <button class="read-more-btn"
                                            onclick="toggleDescription(<?= $p['product_id'] ?>, this)">
                                            อ่านเพิ่มเติม
                                        </button>
                                    <?php endif; ?>

                                </div>

                                <div class="price-sold-row">

                                    <div class="product-price">
                                        ฿<?= number_format($p['price']) ?>
                                        <small>/ <?= htmlspecialchars($p['unit']) ?></small>
                                    </div>

                                    <div class="product-sold">
                                        ขายแล้ว <?= number_format($p['sold_count']) ?>
                                    </div>

                                </div>

                                <div class="quantity-control">
                                    <button class="qty-btn" onclick="changeQty(<?= $p['product_id'] ?>,-1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number"
                                        id="qty<?= $p['product_id'] ?>"
                                        class="qty-input"
                                        value="1"
                                        min="1">
                                    <button class="qty-btn" onclick="changeQty(<?= $p['product_id'] ?>,1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>

                                <button class="btn-add-cart"
                                    onclick="addToCart(<?= $p['product_id'] ?>, '<?= htmlspecialchars($p['product_name']) ?>', <?= $p['price'] ?>, '<?= htmlspecialchars($image) ?>')">
                                    <i class="fas fa-shopping-basket"></i> เพิ่มลงตะกร้า
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
        <i class="fas fa-shopping-basket"></i>
        <span class="cart-badge" id="cartCount">0</span>
    </a>

    <!-- Toast Notification -->
    <div id="toastNotification" class="toast">
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
            }, 3000);
        }

        function addToCart(id, name, price, image) {
            let qty = parseInt(document.getElementById('qty' + id).value);
            let cart = JSON.parse(localStorage.getItem("cart")) || [];
            let found = cart.find(i => i.product_id === id);

            if (found) {
                found.quantity += qty;
                showToast(` เพิ่ม ${name} อีก ${qty} ชิ้น ลงตะกร้าแล้ว`);
            } else {
                cart.push({
                    product_id: id,
                    name: name,
                    price: price,
                    image: image,
                    quantity: qty
                });
                showToast(` เพิ่ม ${name} ${qty} ชิ้น ลงตะกร้าเรียบร้อย`);
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

        // Toggle product description
        function toggleDescription(id, btn) {

            const desc = document.getElementById('desc' + id);

            desc.classList.toggle('expanded');

            if (desc.classList.contains('expanded')) {
                btn.innerText = 'ย่อข้อความ';
            } else {
                btn.innerText = 'อ่านเพิ่มเติม';
            }
        }
        // -------------------------------------------------------------

        document.addEventListener("DOMContentLoaded", () => {
            updateCartCount();

            // Add ripple effect to buttons
            document.querySelectorAll('.btn-add-cart, .qty-btn').forEach(btn => {
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
            .btn-add-cart, .qty-btn {
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