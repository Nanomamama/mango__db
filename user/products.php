<?php
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

if (!isset($_SESSION['regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = true;
}

require_once __DIR__ . '/../db/db.php';
// CSP Security Header
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=()");

if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    exit('Database connection failed');
}

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
    <link rel="apple-touch-icon" sizes="180x180" href="/mango/logo/logo_01.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/mango/logo/logo_01.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title> สวนลุงเผือก</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <style>
        :root {
            --primary-color: #016A70;
            --secondary-color: #2ad3bc;
            --text-color: #686767;
            --bg-color: #f5f5f5;
            --red: #ff4d4f;
            --shiping: #05d135;
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
            padding: 5px 0;
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
            font-size: 3rem;
            font-weight: 900;
            margin-top: 1rem;
            line-height: 1.15;
            letter-spacing: -1px;

            margin-bottom: 12px;

            position: relative;
            z-index: 2;

            color: #ffffff;

            
            
        }

        /* Shine Effect */
        .hero-title::after {
            content: '';
            position: absolute;

            top: 0;
            left: -120%;

            width: 60%;
            height: 100%;

            background: linear-gradient(120deg,
                    transparent,
                    rgba(255, 255, 255, .5),
                    transparent);

            transform: skewX(-20deg);

            animation: shine 4s infinite;
        }

        /* Glow Animation */
        @keyframes glowText {
            0% {
                text-shadow:
                    0 2px 4px rgba(0, 0, 0, .25),
                    0 6px 18px rgba(0, 0, 0, .35),
                    0 0 10px rgba(255, 255, 255, .15);
            }

            50% {
                text-shadow:
                    0 2px 6px rgba(0, 0, 0, .3),
                    0 8px 22px rgba(0, 0, 0, .45),
                    0 0 25px rgba(255, 255, 255, .4);
            }

            100% {
                text-shadow:
                    0 2px 4px rgba(0, 0, 0, .25),
                    0 6px 18px rgba(0, 0, 0, .35),
                    0 0 10px rgba(255, 255, 255, .15);
            }
        }

        /* Shine Move */
        @keyframes shine {
            0% {
                left: -120%;
            }

            100% {
                left: 130%;
            }
        }

        /* Mobile */
        @media (max-width: 768px) {

            .hero-title {
                font-size: 2rem;
            }

        }

        @media (max-width: 768px) {

            .hero-title {
                font-size: 1.8rem;
            }

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
            box-shadow: 0 4px 12px rgba(53, 238, 255, 0.3);
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
            min-height: 520px;
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
            box-shadow: 0 2px 8px rgba(53, 198, 255, 0.3);
        }

        .badge-stock {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: var(--shiping);
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
            max-height: 2.8em;
        }

        /* Product Description */
        .product-description {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-color);

        }

        .product-description-wrapper {
            margin-bottom: 10px;
            min-height: 3em;
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
            min-height: 3em;

        }

        .product-description-shopee.expanded {
            line-clamp: unset;
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
            min-width: 0;
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
            min-height: 52px;
            margin-top: auto;
        }

        .product-sold {
            font-size: 0.75rem;
            color: #999;
            white-space: nowrap;
            margin-bottom: 0;
            flex-shrink: 0;
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

        .product-card:focus-visible {
            outline: 3px solid rgba(1, 106, 112, 0.35);
            outline-offset: 3px;
        }

        .modal-product-image {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border-radius: 8px;
            background: #f4f4f4;
        }

        .modal-product-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--text-color);
            line-height: 1.35;
            margin-bottom: 8px;
        }

        .modal-product-price {
            color: var(--red);
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .modal-product-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }

        .modal-product-meta span {
            background: #eefafa;
            color: var(--primary-color);
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .modal-product-description {
            color: #666;
            line-height: 1.65;
            white-space: pre-line;
            max-height: 220px;
            overflow: auto;
            padding-right: 4px;
        }

        .modal-quantity-control {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px;
            border-radius: 10px;
            background: #f6fafa;
            border: 1px solid #e6eeee;
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
            bottom: 170px;
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
            box-shadow: 0 4px 16px rgba(53, 242, 255, 0.4);
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
            bottom: 95px;
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

        /* ---------------------------------Responsive --------------------------- */
        @media (max-width: 1199px) {
            .product-card {
                min-height: 500px;
            }

            .product-name {
                font-size: 1.05rem;
            }

            .product-description-shopee {
                font-size: 0.94rem;
            }
        }

        @media (max-width: 991px) {
            .product-card {
                min-height: 480px;
            }

            .product-price,
            .product-price small {
                font-size: 1.08rem;
            }
        }

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
                bottom: 95px;
            }

            .cart-floating {
                bottom: 155px;
            }

            .product-card {
                min-height: 455px;
            }

            .product-name {
                font-size: 0.95rem;
            }

            .product-description-shopee {
                font-size: 0.9rem;
            }

            .modal-product-title {
                font-size: 1.2rem;
            }

            .modal-product-price {
                font-size: 1.25rem;
            }

            .modal-product-description {
                max-height: none;
            }

            .modal-quantity-control {
                width: 100%;
                justify-content: center;
            }

            #modalAddCartBtn {
                width: 100%;
                min-height: 44px;
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
                max-height: 2.5em;
            }

            .product-description-wrapper,
            .product-description-shopee {
                min-height: 2.8em;
            }

            .product-description-shopee {
                font-size: 0.78rem;
                line-height: 1.4;
            }

            .product-card {
                min-height: 395px;
                border-radius: 10px;
            }

            .product-price {
                font-size: 0.92rem;
                line-height: 1.2;
            }

            .product-price small {
                display: block;
                font-size: 0.76rem;
                margin-top: 2px;
            }

            .price-sold-row {
                align-items: flex-start;
                min-height: 54px;
            }

            .product-sold {
                font-size: 0.68rem;
            }

            .btn-add-cart {
                font-size: 0.72rem;
                padding: 8px;
                min-height: 38px;
            }

            .qty-btn {
                width: 28px;
                height: 28px;
            }

            .qty-input {
                width: 40px;
                font-size: 0.8rem;
            }

            .modal-dialog {
                margin: 12px;
            }

            .modal-content {
                max-height: calc(100vh - 24px);
                border-radius: 12px;
                overflow: hidden;
            }

            .modal-body {
                padding: 0 16px 20px;
                overflow-y: auto;
            }

            .modal-product-image {
                max-height: 46vh;
            }

            .modal-product-meta span {
                font-size: 0.74rem;
                padding: 4px 9px;
            }
        }

        @media (max-width: 390px) {
            #product-list {
                --bs-gutter-x: 8px;
                --bs-gutter-y: 8px;
            }

            .product-card {
                min-height: 370px;
            }

            .product-info {
                padding: 8px;
            }

            .product-name {
                font-size: 0.76rem;
            }

            .product-description-wrapper,
            .product-description-shopee {
                min-height: 2.6em;
            }

            .quantity-control {
                gap: 4px;
            }

            .qty-btn {
                width: 26px;
                height: 26px;
            }

            .qty-input {
                width: 36px;
                padding: 4px;
            }
        }



        /* Shopee Banner Slider */
        .banner-slider {
            /* border-radius: 20px; */
            overflow: hidden;
        }

        .banner-image {
            width: 100%;
            height: 350px;
            object-fit: cover;
        }

        .banner-overlay {
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;

            padding: 40px 20px 20px;

            background: linear-gradient(to top,
                    rgba(0, 0, 0, .6),
                    rgba(0, 0, 0, 0));

            color: white;
        }

        .banner-overlay h2 {
            font-size: 2rem;
            font-weight: 700;
        }

        .banner-overlay p {
            margin: 0;
            font-size: 1rem;
        }

        .side-banner {
            width: 100%;
            height: calc(175px - 6px);
            object-fit: cover;
            /* border-radius: 10px; */
        }

        @media (max-width: 768px) {

            .banner-image {
                height: 220px;
            }

            .side-banner {
                height: 120px;
            }

            .banner-overlay h2 {
                font-size: 1.2rem;
            }

             .hero-title{
        animation:none;
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

        <?php

        $banner_sql = "
SELECT * FROM products
WHERE status = 'active'
AND product_image IS NOT NULL
AND product_image != ''
ORDER BY product_id DESC
LIMIT 10
";
        $banner_result = $conn->query($banner_sql);
        ?>
        <!-- Shopee Banner Slider -->
        <div class="container mb-4">

            <div class="row g-3">

                <!-- Main Slider -->
                <div class="col-lg-8">

                    <div id="productBanner"
                        class="carousel slide banner-slider"
                        data-bs-ride="carousel"
                        data-bs-interval="3000"
                        data-bs-pause="false"
                        data-bs-wrap="true">

                        <!-- indicators -->
                         
                        <div class="carousel-indicators">

                            <?php
                            $i = 0;

                            while ($row = $banner_result->fetch_assoc()):
                            ?>

                                <button type="button"
                                    data-bs-target="#productBanner"
                                    data-bs-slide-to="<?= $i ?>"
                                    class="<?= $i == 0 ? 'active' : '' ?>">
                                </button>

                            <?php
                                $i++;
                            endwhile;

                            if ($banner_result && $banner_result->num_rows > 0) {
                                $banner_result->data_seek(0);
                            }
                            ?>

                        </div>

                        <!-- slides -->
                        <div class="carousel-inner ">

                            <?php
                            $active = true;

                            while ($banner = $banner_result->fetch_assoc()):

                                $bannerImage = basename((string) $banner['product_image']);
                                $image = "../admin/uploads/products/" . $bannerImage;
                            ?>

                                <div class="carousel-item <?= $active ? 'active' : '' ?>">

                                    <img src="<?= htmlspecialchars($image) ?>"
                                        class="d-block w-100 banner-image"
                                        loading="lazy"
                                        alt="<?= htmlspecialchars($banner['product_name']) ?>">

                                    <!-- overlay -->
                                    <div class="banner-overlay">

                                        <h2>
                                            <?= htmlspecialchars($banner['product_name']) ?>
                                        </h2>

                                        <p>
                                            ฿<?= number_format($banner['price']) ?>
                                            / <?= htmlspecialchars($banner['unit']) ?>
                                        </p>

                                    </div>

                                </div>

                            <?php
                                $active = false;
                            endwhile;
                            ?>

                        </div>
                        <button class="carousel-control-prev"
                            type="button"
                            data-bs-target="#productBanner"
                            data-bs-slide="prev">

                            <span class="carousel-control-prev-icon"></span>
                        </button>

                        <button class="carousel-control-next"
                            type="button"
                            data-bs-target="#productBanner"
                            data-bs-slide="next">

                            <span class="carousel-control-next-icon"></span>
                        </button>

                    </div>

                </div>

                <!-- Main Side Banners -->
                <div class="col-lg-4">

                    <div class="d-flex flex-column gap-3 h-100">

                        <img src="./image/poster/poster500Free.png"
                            class="side-banner">

                        <img src="./image/poster/posterproduct.png "
                            class="side-banner">

                    </div>

                </div>

            </div>

        </div>
    </div>

    <div class="container">
        <!-- Info Notice -->
        <div class="info-notice animate__animated animate__fadeInUp">
            <h6><i class="fas fa-info-circle"></i> หมายเหตุการสั่งซื้อ สั่งซื้อได้เลยโดยไม่ต้องสมัครสมาชิก</h6>
            <div class="row">
                <div class="col-md-4">
                    <p><i class="fas fa-truck"></i> จัดส่งฟรี! ขั้นต่ำ 500 บาท</p>
                    <p>ในพื้นที่ บ้านน้อย เชียงคาน แก่งคุดคู้ หรือพื้นที่ใกล้เคียง</p>
                </div>
                <div class="col-md-4">
                    <p><i class="fas fa-store"></i> รับสินค้าที่สวนได้</p>
                    <p><i class="fas fa-phone-alt"></i> ติดต่อกลับหากสินค้าไม่พอ</p>
                </div>
                <div class="col-md-4">
                   <p><i class="fa-solid fa-check"></i> ติดตามสถานะการสั่งซื้อได้ทันที</p>
                   <p><i class="fa-regular fa-circle-question"></i> สอบถามเพิ่มเติมทางข้อความ หรือโทร 089-8980821</p>
                </div>
            </div>
        </div>

        <!-- Category Pills -->
        <div class="category-pills">
            <div class="container">
                <button class="pill-btn active" onclick="filterProducts(event,'all')">
                    <i class="fas fa-th-large"></i> ทั้งหมด
                </button>
                <button class="pill-btn" onclick="filterProducts(event,'seasonal')">
                    <i class="fas fa-star"></i> ตามฤดูกาล
                </button>
                <button class="pill-btn" onclick="filterProducts(event,'normal')">
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

            // เช็ก Query Error ก่อนใช้งาน
            $result = $conn->query($sql);

            if (!$result) {
                error_log($conn->error);
                $result = false;
            }

            if ($result && $result->num_rows > 0):
                while ($p = $result->fetch_assoc()):
                    $productImage = basename((string) $p['product_image']);
                    $image = $productImage
                        ? "../admin/uploads/products/" . $productImage
                        : "../assets/no-image.png";
                    $productPayload = [
                        'id' => (int) $p['product_id'],
                        'name' => (string) $p['product_name'],
                        'description' => (string) $p['product_description'],
                        'category' => (string) ($p['category'] ?? ''),
                        'price' => (float) $p['price'],
                        'unit' => (string) $p['unit'],
                        'image' => $image,
                        'sold_count' => (int) $p['sold_count'],
                        'seasonal' => (int) $p['seasonal'],
                    ];
            ?>
                    <div class="col-6 col-sm-6 col-md-4 col-lg-3 product-item animate__animated animate__fadeInUp"
                        data-seasonal="<?= $p['seasonal'] ?>">

                        <div class="product-card"
                            role="button"
                            tabindex="0"
                            onclick='openProductModal(<?= json_encode($productPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>)'
                            onkeydown="handleProductCardKey(event, this)">
                            <div class="product-image-wrapper">
                                <?php if ($p['seasonal'] == 1): ?>
                                    <!-- <span class="badge-seasonal">
                                        <i class="fas fa-star"></i> ตามฤดูกาล
                                    </span> -->
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
                                    <button class="qty-btn" onclick="event.stopPropagation(); changeQty(<?= $p['product_id'] ?>,-1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number"
                                        id="qty<?= $p['product_id'] ?>"
                                        class="qty-input"
                                        value="1"
                                        min="1"
                                        max="99"
                                        onclick="event.stopPropagation()">
                                    <button class="qty-btn" onclick="event.stopPropagation(); changeQty(<?= $p['product_id'] ?>,1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>

                                <button class="btn-add-cart"
                                    onclick='event.stopPropagation(); addToCart(
<?= (int)$p["product_id"] ?>,
<?= json_encode($p["product_name"], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
<?= (float)$p["price"] ?>,
<?= json_encode($image, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
)'>
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

    <div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title visually-hidden" id="productDetailTitle">รายละเอียดสินค้า</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="row g-4 align-items-start">
                        <div class="col-md-5">
                            <img src="../assets/no-image.png" alt="" class="modal-product-image" id="modalProductImage">
                        </div>
                        <div class="col-md-7">
                            <div class="modal-product-title" id="modalProductName"></div>
                            <div class="modal-product-price" id="modalProductPrice"></div>
                            <div class="modal-product-meta">
                                <span id="modalProductUnit"></span>
                                <span id="modalProductCategory"></span>
                                <span id="modalProductSold"></span>
                                <span id="modalProductSeasonal"></span>
                            </div>
                            <div class="modal-product-description" id="modalProductDescription"></div>

                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4">
                                <div class="modal-quantity-control">
                                    <button type="button" class="qty-btn" onclick="changeModalQty(-1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" id="modalQty" class="qty-input" value="1" min="1" max="99">
                                    <button type="button" class="qty-btn" onclick="changeModalQty(1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>

                                <button type="button" class="btn-add-cart flex-grow-1" id="modalAddCartBtn">
                                    <i class="fas fa-shopping-basket"></i> เพิ่มลงตะกร้า
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

        let currentModalProduct = null;

        function formatProductPrice(price) {
            return '฿' + Number(price).toLocaleString('th-TH');
        }

        function handleProductCardKey(event, card) {
            if (event.target.closest('button, input')) {
                return;
            }

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                card.click();
            }
        }

        function openProductModal(product) {
            currentModalProduct = product;

            document.getElementById('modalProductImage').src = product.image || '../assets/no-image.png';
            document.getElementById('modalProductImage').alt = product.name || '';
            document.getElementById('modalProductName').textContent = product.name || '';
            document.getElementById('modalProductPrice').textContent = `${formatProductPrice(product.price)} / ${product.unit || ''}`;
            document.getElementById('modalProductUnit').textContent = product.unit ? `หน่วย: ${product.unit}` : 'หน่วย: -';
            document.getElementById('modalProductCategory').textContent = product.category ? `หมวดหมู่: ${product.category}` : 'หมวดหมู่: -';
            document.getElementById('modalProductSold').textContent = `ขายแล้ว ${Number(product.sold_count || 0).toLocaleString('th-TH')}`;
            document.getElementById('modalProductSeasonal').textContent = Number(product.seasonal) === 1 ? 'ตามฤดูกาล' : 'สินค้าทั่วไป';
            document.getElementById('modalProductDescription').textContent = product.description || 'ไม่มีรายละเอียดสินค้า';
            document.getElementById('modalQty').value = 1;

            const addBtn = document.getElementById('modalAddCartBtn');
            addBtn.onclick = () => {
                const qty = parseInt(document.getElementById('modalQty').value) || 1;
                addToCart(product.id, product.name, product.price, product.image, qty);
                const modal = bootstrap.Modal.getInstance(document.getElementById('productDetailModal'));
                if (modal) modal.hide();
            };

            new bootstrap.Modal(document.getElementById('productDetailModal')).show();
        }

        function changeModalQty(delta) {
            const input = document.getElementById('modalQty');
            let val = parseInt(input.value) || 1;
            val += delta;
            val = Math.max(1, Math.min(99, val));
            input.value = val;
        }

        function addToCart(id, name, price, image, quantity = null) {
            const qtyInput = document.getElementById('qty' + id);
            let qty = quantity !== null ? parseInt(quantity) : parseInt(qtyInput?.value);

            if (isNaN(qty) || qty < 1) {
                qty = 1;
            }
            qty = Math.max(1, Math.min(99, qty));
            let cart = [];

            try {
                cart = JSON.parse(localStorage.getItem("cart")) || [];
            } catch (e) {
                cart = [];
            }
            let found = cart.find(i => i.product_id === id);

            if (found) {
                found.quantity = Math.min(99, (parseInt(found.quantity) || 0) + qty);
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
            if (qtyInput) {
                qtyInput.value = 1;
            }
        }

        function updateCartCount() {

            let cart = [];

            try {
                cart = JSON.parse(localStorage.getItem("cart")) || [];
            } catch (e) {
                cart = [];
            }

            let total = cart.reduce((sum, i) => sum + i.quantity, 0);

            document.getElementById("cartCount").innerText = total;

            document.getElementById("cartButton").style.display =
                total > 0 ? "flex" : "none";
        }

        function pulseCartButton() {
            let btn = document.getElementById("cartButton");
            btn.style.transform = 'scale(1.2)';
            setTimeout(() => btn.style.transform = 'scale(1)', 200);
        }

        function filterProducts(event, type) {
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
