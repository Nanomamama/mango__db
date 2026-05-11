<?php
session_start();
require_once __DIR__ . '/../db/db.php';

$member_id = $_SESSION['member_id'] ?? null;
$member_name = '';
$member_phone = '';

if ($member_id) {
    $m = $conn->prepare("SELECT fullname, phone FROM members WHERE member_id=?");
    $m->bind_param("i", $member_id);
    $m->execute();
    $mem = $m->get_result()->fetch_assoc();
    $member_name = $mem['fullname'] ?? '';
    $member_phone = $mem['phone'] ?? '';
}

$order_error = $_SESSION['order_error'] ?? '';
unset($_SESSION['order_error']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าสินค้า — สวนลุงเผือก</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Mitr:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ===== RESET & ROOT ===== */
        :root {
            --sage: #016A70;
            --sage-light: #2ad3bc;
            --sage-pale: #e8f4f3;
            --sage-mist: #dcfffc;
            --cream: #f4fdfa;
            --earth: #4e808b;
            --earth-pale: #e2f5f3;
            --text: #373833;
            --text-muted: #4a4b48;
            --white: #ffffff;
            --red: #f82424;

            --shadow-sm: 0 2px 12px rgba(60, 80, 30, .07);
            --shadow-md: 0 6px 28px rgba(60, 80, 30, .10);
            --shadow-lg: 0 16px 48px rgba(60, 80, 30, .13);
            --radius-sm: 10px;
            --radius-md: 16px;
            --radius-lg: 24px;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--cream);
            color: var(--text);

            font-size: 15px;
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ===== AMBIENT BACKGROUND ===== */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 900px 600px at 80% -10%, rgba(127, 166, 96, .10) 0%, transparent 70%),
                radial-gradient(ellipse 600px 400px at -5% 80%, rgba(90, 122, 74, .08) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        /* ===== PAGE HEADER ===== */
        .pg-header {
            background: linear-gradient(135deg, var(--sage) 0%, var(--sage-light) 100%);
            color: white;
            padding: 32px 0 40px;
            position: relative;
            overflow: hidden;
        }

        .pg-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 28px;
            background: var(--cream);
            border-radius: 50% 50% 0 0 / 28px 28px 0 0;
        }

        .pg-header .breadcrumb {
            opacity: .75;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .pg-header .breadcrumb a {
            color: white;
            text-decoration: none;
        }

        .pg-header .breadcrumb-item.active {
            color: var(--white);
        }

        .pg-header .breadcrumb-item+.breadcrumb-item::before {
            color:var(--white);
        }

    
        .pg-title {

            font-size: 2rem;
            font-weight: 500;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pg-title-icon {
            width: 48px;
            height: 48px;
            background: var(--earth);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* ===== MAIN WRAPPER ===== */
        .main-wrap {
            position: relative;
            z-index: 1;
            padding: 32px 0 80px;
        }

        /* ===== SECTION LABEL ===== */
        .section-label {

            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--sage);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ===== CART HEADER ROW ===== */
        .cart-col-header {
            display: grid;
            grid-template-columns: 2.6fr 1fr 1.2fr 1fr 48px;
            gap: 16px;
            padding: 0 24px 14px;
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .cart-col-header>* {
            text-align: center;
        }

        .cart-col-header>*:first-child {
            text-align: left;
        }

        /* ===== CART CARD ===== */
        .cart-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        /* ===== CART ITEM ===== */
        .cart-item {
            display: grid;
            grid-template-columns: 2.6fr 1fr 1.2fr 1fr 48px;
            gap: 16px;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #f0f5ea;
            transition: background .2s;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item:hover {
            background: var(--sage-mist);
        }

        /* product cell */
        .item-product {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .item-img-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
        }

        .item-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: var(--sage);
            color: white;
            font-size: .65rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
        }

        .item-name {
            font-weight: 600;
            color: var(--text);
            font-size: .97rem;
            line-height: 1.4;
        }

        .item-tag {
            display: inline-block;
            background: var(--sage-pale);
            color: var(--sage);
            font-size: .72rem;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 20px;
            margin-top: 6px;
        }

        /* price cell */
        .item-price {
            text-align: center;
            font-weight: 600;
            color: var(--text-muted);
            font-size: .93rem;
        }

        /* qty cell */
        .item-qty {
            text-align: center;
        }

        .item-actions {
            display: contents;
        }

        .qty-wrap {
            display: inline-flex;
            align-items: center;
            background: var(--sage-pale);
            border-radius: 50px;
            padding: 4px;
            gap: 4px;
        }

        .qty-btn {
            width: 30px;
            height: 30px;
            border: none;
            background: white;
            border-radius: 50%;
            color: var(--sage);
            font-size: .85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: .18s;
            box-shadow: 0 1px 4px rgba(90, 122, 74, .15);
        }

        .qty-btn:hover {
            background: var(--sage);
            color: white;
        }

        .qty-num {
            width: 36px;
            text-align: center;
            border: none;
            background: transparent;

            font-weight: 500;
            font-size: 1rem;
            color: var(--text);
        }

        /* total cell */
        .item-total {
            text-align: center;
            ;
            font-weight: 500;
            font-size: 1rem;
            color: var(--sage);
        }

        /* remove btn */
        .btn-remove {
            width: 36px;
            height: 36px;
            border: 1px solid #f0ddd5;
            background: #fef4ef;
            color: var(--red);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: .18s;
            font-size: .85rem;
        }

        .btn-remove:hover {
            background: var(--red);
            color: white;
            border-color: var(--red);
        }

        /* ===== CART FOOTER ===== */
        .cart-footer {
            padding: 20px 24px;
            background: var(--sage-mist);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            border-top: 1px solid var(--border);
        }

        .btn-clear {
            border: 1px solid #f0ddd5;
            background: white;
            color: var(--red);
            padding: 10px 18px;
            border-radius: var(--red);
            /* fon; */
            font-size: .88rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: .2s;
        }

        .btn-clear:hover {
            background: #e05a38;
            color: white;
            border-color: #e05a38;
        }

        .footer-total-wrap {
            text-align: right;
        }

        .footer-total-label {
            font-size: .8rem;
            color: var(--text-muted);
            margin-bottom: 2px;
        }

        .footer-total-amount {

            font-size: 1.5rem;
            font-weight: 500;
            color: var(--sage);
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            padding: 72px 24px;
            text-align: center;
        }

        .empty-icon {
            width: 100px;
            height: 100px;
            background: var(--sage-pale);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.6rem;
            color: var(--sage-light);
            margin: 0 auto 20px;
        }

        .empty-title {

            font-size: 1.3rem;
            font-weight: 500;
            color: var(--text);
            margin-bottom: 8px;
        }

        .empty-sub {
            color: var(--text-muted);
            font-size: .9rem;
            margin-bottom: 24px;
        }

        .btn-shop-now {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--sage);
            color: white;
            padding: 12px 24px;
            border-radius: var(--radius-md);
            text-decoration: none;
            font-weight: 600;
            transition: .2s;
        }

        .btn-shop-now:hover {
            background: var(--sage-light);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* ===== CHECKOUT PANEL ===== */
        .checkout-panel {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            position: sticky;
            top: 24px;
        }

        .panel-head {
            background: linear-gradient(135deg, var(--sage) 0%, var(--sage-light) 100%);
            padding: 20px 24px;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-head-icon {
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, .2);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .panel-head-title {

            font-size: 1.1rem;
            font-weight: 500;
        }

        .panel-body {
            padding: 24px;
        }

        /* summary box */
        .summary-box {
            background: var(--sage-mist);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 16px 18px;
            margin-bottom: 24px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            font-size: .92rem;
            color: var(--text-muted);
        }

        .summary-row:not(:last-child) {
            border-bottom: 1px dashed var(--border);
        }

        .summary-row.total-row {
            color: var(--text);
            font-weight: 700;
            padding-top: 14px;
            margin-top: 4px;
        }

        .summary-row.total-row span:last-child {

            font-size: 1.3rem;
            color: var(--sage);
        }

        .free-tag {
            background: #e8f8e0;
            color: #4a8c30;
            font-size: .72rem;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 20px;
        }

        /* form */
        .form-label {
            font-weight: 600;
            font-size: .87rem;
            color: var(--text);
            display: block;
            margin-bottom: 8px;
        }

        .form-control,
        .form-input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--sage-light);
            font-size: .93rem;
            color: var(--text);
            transition: .2s;
            outline: none;
        }

        .form-control:focus,
        .form-input:focus {
            border-color: var(--sage);
            background: white;
            box-shadow: 0 0 0 4px rgba(90, 122, 74, .10);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .mb-field {
            margin-bottom: 18px;
        }

        /* receive cards */
        .receive-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 18px;
        }

        .receive-card {
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            padding: 14px;
            cursor: pointer;
            transition: .2s;
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--sage-mist);
        }

        .receive-card input[type="radio"] {
            display: none;
        }

        .receive-card:hover {
            border-color: var(--sage);
            background: var(--sage-light);
        }

        .receive-card.active {
            border-color: var(--sage);
            background: var(--sage-light);
            box-shadow: 0 0 0 3px rgba(90, 122, 74, .12);
        }

        .receive-icon {
            width: 42px;
            height: 42px;
            border-radius: var(--radius-sm);
            background: white;
            border: 1.5px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--sage);
            font-size: 1.1rem;
            flex-shrink: 0;
            transition: .2s;
        }

        .receive-card.active .receive-icon {
            background: var(--sage);
            color: white;
            border-color: var(--sage);
        }

        .receive-text strong {
            display: block;
            font-size: .88rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 2px;
        }

        .receive-text small {
            font-size: .76rem;
            color: var(--text-muted);
        }

        /* date trigger */
        .date-btn {
            width: 100%;
            border: 1.5px dashed var(--sage-light);
            background: var(--sage-mist);
            border-radius: var(--radius-md);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: .2s;
            color: var(--text);

            font-size: .93rem;
            text-align: left;
        }

        .date-btn:hover {
            border-color: var(--sage);
            background: var(--sage-pale);
        }

        .date-btn-icon {
            width: 40px;
            height: 40px;
            background: var(--sage);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            flex-shrink: 0;
        }

        #triggerText {
            font-weight: 600;
            color: var(--sage);
        }

        /* info alert */
        .info-alert {
            background: var(--sage-pale);
            border: 1px solid #61d1f3;
            border-left: 4px solid var(--sage);
            border-radius: var(--radius-sm);
            padding: 12px 14px;
            font-size: .85rem;
            color: var(--text);
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        /* submit */
        .btn-checkout {
            width: 100%;
            border: none;
            background: linear-gradient(135deg, var(--sage) 0%, var(--sage-light) 100%);
            color: white;
            height: 52px;
            border-radius: var(--radius-md);
            ;
            font-weight: 400;
            font-size: 1.05rem;
            cursor: pointer;
            transition: .25s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 6px 20px rgba(90, 122, 74, .25);
        }

        .btn-checkout:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(90, 122, 74, .32);
        }

        .btn-checkout:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .btn-continue-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 12px;
            height: 44px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: .88rem;
            transition: .2s;
            background: white;
        }

        .btn-continue-link:hover {
            border-color: var(--sage);
            color: var(--sage);
        }

        /* ===== MODAL ===== */
        #dateModal .modal-content {
            border: none;
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        .modal-head {
            background: linear-gradient(135deg, var(--sage) 0%, var(--sage-light) 100%);
            padding: 22px 24px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-head h5 {
            font-size: 1.1rem;
            font-weight: 400;
            margin: 0;
        }

        .modal-head .btn-close {
            filter: brightness(0) invert(1);
            opacity: .8;
        }

        .modal-body {
            padding: 24px;
        }

        .quick-date-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }

        .quick-btn {
            border: 1.5px solid var(--border);
            background: var(--cream);
            border-radius: var(--radius-md);
            padding: 14px;
            font-weight: 600;
            font-size: .9rem;
            color: var(--text);
            cursor: pointer;
            transition: .2s;
        }

        .quick-btn:hover {
            border-color: var(--sage-light);
            background: var(--sage-pale);
        }

        .quick-btn.active {
            border-color: var(--sage);
            background: var(--sage-pale);
            color: var(--sage);
        }

        .modal-label {
            font-weight: 700;
            font-size: .85rem;
            color: var(--text);
            display: block;
            margin-bottom: 10px;
        }

        .time-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 10px;
        }

        .time-pill {
            border: 1.5px solid var(--border);
            background: var(--cream);
            border-radius: 50px;
            padding: 10px;
            text-align: center;
            font-weight: 700;
            font-size: .88rem;
            color: var(--text);
            cursor: pointer;
            transition: .18s;
        }

        .time-pill:hover {
            border-color: var(--sage-light);
            background: var(--sage-pale);
        }

        .time-pill.active {
            border-color: var(--sage);
            background: var(--sage);
            color: white;
        }

        .btn-confirm {
            width: 100%;
            border: none;
            background: linear-gradient(135deg, var(--sage) 0%, var(--sage-light) 100%);
            color: white;
            height: 50px;
            border-radius: var(--radius-md);
            ;
            font-size: 1rem;
            cursor: pointer;
            transition: .2s;
            margin-top: 24px;
        }

        .btn-confirm:hover {
            box-shadow: 0 8px 24px rgba(90, 122, 74, .28);
            transform: translateY(-1px);
        }

        /* ===== LOADING ===== */
        .loading-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(253, 250, 244, .92);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 16px;
        }

        .spinner-leaf {
            width: 52px;
            height: 52px;
            border: 4px solid var(--border);
            border-top-color: var(--sage);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991px) {

            .cart-col-header {
                display: none;
            }

            .cart-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                padding: 12px;
            }

            /* ฝั่งซ้าย */
            .item-product {
                flex: 1;
                min-width: 0;
                gap: 10px;
            }

            .item-img {
                width: 62px;
                height: 62px;
            }

            .item-name {
                font-size: .88rem;
                line-height: 1.3;
            }

            .item-tag {
                font-size: .65rem;
                padding: 2px 8px;
            }

            /* ซ่อนราคาเดี่ยว */
            .item-price {
                display: none;
            }

            /* ฝั่งขวา */
            .item-actions {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 8px;
                flex-shrink: 0;
            }


        }

        .qty-wrap {
            gap: 2px;
            padding: 3px;
        }

        .qty-btn {
            width: 28px;
            height: 28px;
            font-size: .75rem;
        }

        .qty-num {
            width: 28px;
            font-size: .9rem;
        }

        .item-total {
            font-size: .9rem;
            min-width: 62px;
            text-align: right;
        }

        .btn-remove {
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            border-radius: 10px;
            padding: 0;
        }

        .checkout-panel {
            position: static;
            margin-top: 24px;
        }

        .receive-grid {
            grid-template-columns: 1fr;
        }


        @media (max-width: 768px) {
            .pg-title {
                font-size: 1.5rem;
            }

            .footer-total-amount {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-leaf"></div>
        <p style="color:var(--sage);font-weight:600;">กำลังดำเนินการ...</p>
    </div>

    <!-- PAGE HEADER -->
    <div class="pg-header">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="products.php"><i class="fas fa-home"></i> หน้าแรก</a></li>
                    <li class="breadcrumb-item active">ตะกร้าสินค้า</li>
                </ol>
            </nav>
            <div class="pg-title">
                <div class="pg-title-icon"><i class="fas fa-shopping-basket"></i></div>
                ตะกร้าสินค้า
            </div>
        </div>
    </div>

    <div class="main-wrap">
        <div class="container">
            <form action="save_order.php" method="post" onsubmit="return submitOrder()">
                <div class="row g-4 align-items-start">

                    <!-- LEFT: CART -->
                    <div class="col-lg-8">

                        <div class="section-label">
                            <i class="fas fa-leaf"></i>
                            รายการสินค้า
                        </div>

                        <!-- Column headers (desktop) -->
                        <div class="cart-col-header d-none d-lg-grid">
                            <span>สินค้า</span>
                            <span>ราคา/ชิ้น</span>
                            <span>จำนวน</span>
                            <span>รวม</span>
                            <span></span>
                        </div>

                        <!-- Cart card -->
                        <div class="cart-card">
                            <div id="cartItems"></div>

                            <!-- Empty state -->
                            <div id="emptyCart" class="empty-state" style="display:none;">
                                <div class="empty-icon"><i class="fas fa-shopping-basket"></i></div>
                                <div class="empty-title">ตะกร้าว่างเปล่า</div>
                                <p class="empty-sub">ยังไม่มีสินค้าที่เลือก ไปเลือกผลผลิตสดจากสวนกันเลย!</p>
                                <a href="products.php" class="btn-shop-now">
                                    <i class="fas fa-seedling"></i> เลือกซื้อสินค้า
                                </a>
                            </div>

                            <!-- Cart footer -->
                            <div class="cart-footer" id="cartFooter" style="display:none;">
                                <button type="button" class="btn-clear" onclick="clearCart()">
                                    <i class="fas fa-trash-alt"></i> ล้างตะกร้า
                                </button>
                                <div class="footer-total-wrap">
                                    <div class="footer-total-label">ยอดรวมสินค้า</div>
                                    <div class="footer-total-amount" id="footerTotal" style="color: red;">฿0.00</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT: CHECKOUT -->
                    <div class="col-lg-4">

                        <div class="section-label">
                            <i class="fas fa-receipt"></i>
                            ชำระเงิน
                        </div>

                        <div class="checkout-panel">
                            <div class="panel-head">
                                <div class="panel-head-icon">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <div class="panel-head-title">สรุปคำสั่งซื้อ</div>
                            </div>

                            <div class="panel-body">
                                <input type="hidden" name="member_id" value="<?= $member_id ?>">

                                <!-- SUMMARY -->
                                <div class="summary-box">
                                    <div class="summary-row">
                                        <span>ยอดสินค้า</span>
                                        <span id="subtotal">฿0.00</span>
                                    </div>
                                    <div class="summary-row">
                                        <span>ค่าจัดส่ง</span>
                                        <span id="shippingText">-</span>
                                    </div>
                                    <div class="summary-row total-row ">
                                        <span>ยอดรวมทั้งหมด</span>
                                        <span id="totalPrice" style="color: red;">฿0.00</span>
                                    </div>
                                </div>

                                <!-- NAME -->
                                <div class="mb-field">
                                    <label class="form-label"><i class="fas fa-user" style="color:var(--sage);margin-right:6px"></i>ชื่อผู้สั่งซื้อ</label>
                                    <input type="text" name="customer_name" class="form-control"
                                        value="<?= htmlspecialchars($member_name) ?>"
                                        placeholder="กรอกชื่อ-นามสกุล">
                                </div>

                                <!-- PHONE -->
                                <div class="mb-field">
                                    <label class="form-label"><i class="fas fa-phone" style="color:var(--sage);margin-right:6px"></i>เบอร์โทรศัพท์</label>
                                    <input type="tel" name="customer_phone" class="form-control"
                                        value="<?= htmlspecialchars($member_phone) ?>"
                                        placeholder="เช่น 08X-XXX-XXXX">
                                </div>

                                <!-- RECEIVE TYPE -->
                                <div class="mb-field">
                                    <label class="form-label"><i class="fas fa-box" style="color:var(--sage);margin-right:6px"></i>วิธีรับสินค้า</label>
                                    <div class="receive-grid">
                                        <label class="receive-card active" onclick="selectReceiveType('pickup')">
                                            <input type="radio" name="receive_type" value="pickup" checked>
                                            <div class="receive-icon"><i class="fas fa-store"></i></div>
                                            <div class="receive-text">
                                                <strong>รับที่สวน</strong>
                                                <small>รับเองที่สวนลุงเผือก</small>
                                            </div>
                                        </label>
                                        <label class="receive-card" onclick="selectReceiveType('delivery')">
                                            <input type="radio" name="receive_type" value="delivery">
                                            <div class="receive-icon"><i class="fas fa-truck"></i></div>
                                            <div class="receive-text">
                                                <strong>จัดส่ง</strong>
                                                <small>ขั้นต่ำ 500 บาท ส่งฟรี</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- ADDRESS / NOTE -->
                                <div class="mb-field">
                                    <label class="form-label" id="address_label">
                                        <i class="fas fa-sticky-note" style="color:var(--sage);margin-right:6px"></i>หมายเหตุ
                                    </label>
                                    <textarea class="form-control" name="customer_address" id="customer_address"
                                        placeholder="หมายเหตุเพิ่มเติม" rows="3"></textarea>
                                </div>

                                <!-- DATE -->
                                <div class="mb-field">
                                    <label class="form-label"><i class="fas fa-calendar-alt" style="color:var(--sage);margin-right:6px"></i>วันเวลารับสินค้า</label>
                                    <button type="button" class="date-btn" onclick="openDateModal()">
                                        <div class="date-btn-icon"><i class="fas fa-calendar-alt"></i></div>
                                        <span id="triggerText">เลือกวันและเวลา</span>
                                    </button>
                                    <input type="hidden" id="receive_datetime" name="receive_datetime">
                                </div>

                                <!-- INFO ALERT -->
                                <div class="info-alert">
                                    <i class="fas fa-circle-exclamation" style="margin-top:2px;flex-shrink:0"></i>
                                    <span>สินค้าอาจมีจำนวนจำกัด ทางสวนจะติดต่อกลับหากสินค้าไม่พอ</span>
                                </div>

                                <input type="hidden" name="cart_data" id="cartData">

                                <!-- SUBMIT -->
                                <button type="submit" class="btn-checkout" id="submitBtn" disabled>
                                    <i class="fas fa-check-circle"></i> ยืนยันการสั่งซื้อ
                                </button>

                                <a href="products.php" class="btn-continue-link">
                                    <i class="fas fa-arrow-left"></i> เลือกซื้อเพิ่ม
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DATE MODAL -->
    <div class="modal fade" id="dateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-head">
                    <h5><i class="fas fa-calendar-check me-2"></i>เลือกวันรับสินค้า</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <label class="modal-label">วันที่รับสินค้า</label>

                    <div class="quick-date-grid">
                        <button type="button" class="quick-btn" onclick="setQuickDate(0,this)">
                            <label for="today">วันนี้</label>
                        </button>
                        <button type="button" class="quick-btn" onclick="setQuickDate(1,this)">
                            <label for="tomorrow">พรุ่งนี้</label>
                        </button>
                    </div>

                    <div style="margin-bottom:20px;">
                        <input type="date" id="customDate" class="form-control" style="background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:12px 14px;">
                    </div>

                    <label class="modal-label">ช่วงเวลา</label>
                    <input type="time" id="customTime" class="form-control"
                        style="background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:12px 14px;margin-bottom:14px;"
                        min="08:00" max="18:00" step="1800">

                    <div class="time-grid">
                        <button type="button" class="time-pill" onclick="setTime('08:30',this)">08:30</button>
                        <button type="button" class="time-pill" onclick="setTime('10:00',this)">10:00</button>
                        <button type="button" class="time-pill" onclick="setTime('13:00',this)">13:00</button>
                        <button type="button" class="time-pill" onclick="setTime('15:00',this)">15:00</button>
                        <button type="button" class="time-pill" onclick="setTime('17:00',this)">17:00</button>
                        <button type="button" class="time-pill" onclick="setTime('18:00',this)">18:00</button>
                    </div>

                    <button class="btn-confirm" onclick="confirmDate()">
                        <i class="fas fa-check-circle me-2"></i>ยืนยันวันรับสินค้า
                    </button>

                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let cart = JSON.parse(localStorage.getItem("cart")) || [];


        // ฟอร์แมตราคา
        function formatPrice(price) {
            return '฿' + Number(price).toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function renderCart() {
            const cartItems = document.getElementById("cartItems");
            const emptyCart = document.getElementById("emptyCart");
            const submitBtn = document.getElementById("submitBtn");
            const cartFooter = document.getElementById("cartFooter");
            const shippingText = document.getElementById("shippingText");

            if (!Array.isArray(cart)) cart = [];

            if (cart.length === 0) {
                cartItems.innerHTML = "";
                emptyCart.style.display = "block";
                submitBtn.disabled = true;
                cartFooter.style.display = "none";
                document.getElementById("subtotal").textContent = "฿0.00";
                document.getElementById("totalPrice").textContent = "฿0.00";
                return;
            }

            emptyCart.style.display = "none";
            submitBtn.disabled = false;
            cartFooter.style.display = "flex";

            let total = 0;
            let html = "";

            const receiveType =
                document.querySelector('input[name="receive_type"]:checked')?.value || 'pickup';

            cart.forEach((item, idx) => {
                item.quantity = parseInt(item.quantity) || 1;
                item.price = parseFloat(item.price) || 0;
                const sub = item.price * item.quantity;
                total += sub;

                html += `
                <div class="cart-item">
                    <div class="item-product">
                        <div class="item-img-wrap">
                            <img src="${item.image || 'assets/no-image.png'}"
                                class="item-img"
                                onerror="this.src='assets/no-image.png'">
                            ${item.quantity > 1 ? `<span class="item-badge">${item.quantity}</span>` : ''}
                        </div>
                        <div>
                            <div class="item-name">${item.name || 'ไม่พบชื่อสินค้า'}</div>
                            <span class="item-tag">แนะนำ</span>
                        </div>
                    </div>

                   <div class="item-price">${formatPrice(item.price)}</div>

              <div class="item-actions">

                <div class="item-qty">
                    <div class="qty-wrap">
                        <button type="button" class="qty-btn" onclick="decreaseQty(${idx})">
                            <i class="fas fa-minus"></i>
                        </button>

                        <input type="number" class="qty-num" value="${item.quantity}"
                            min="1" onchange="updateQty(${idx}, this.value)">

                        <button type="button" class="qty-btn" onclick="increaseQty(${idx})">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="item-total">${formatPrice(sub)}</div>

                <button type="button" class="btn-remove" onclick="removeItem(${idx})">
                    <i class="fas fa-trash"></i>
                </button>

            </div>
                </div>`;
            });

            cartItems.innerHTML = html;

            if (receiveType === 'delivery') {
                if (total >= 500) {
                    shippingText.innerHTML = '<span class="free-tag">ฟรี</span>';
                } else {
                    shippingText.innerHTML = '<span style="color:red;">ขั้นต่ำ 500 บาท</span>';
                }
            } else {
                shippingText.innerHTML = '<span style="color:var(--sage);">รับที่สวน</span>';
            }
            document.getElementById("subtotal").textContent = formatPrice(total);
            document.getElementById("totalPrice").textContent = formatPrice(total);
            document.getElementById("footerTotal").textContent = formatPrice(total);
        }

        function clearCart() {
            Swal.fire({
                title: 'ล้างตะกร้า?',
                text: 'ต้องการลบสินค้าทั้งหมดออกจากตะกร้า?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e05a38',
                cancelButtonColor: '#888',
                confirmButtonText: 'ลบทั้งหมด',
                cancelButtonText: 'ยกเลิก'
            }).then(r => {
                if (r.isConfirmed) {
                    cart = [];
                    saveCart();
                    renderCart();
                    Swal.fire({
                        icon: 'success',
                        title: 'ล้างตะกร้าแล้ว',
                        timer: 1400,
                        showConfirmButton: false
                    });
                }
            });
        }

        function increaseQty(i) {
            cart[i].quantity++;
            saveCart();
            renderCart();
        }

        function decreaseQty(i) {
            if (cart[i].quantity > 1) {
                cart[i].quantity--;
                saveCart();
                renderCart();
            }
        }

        function updateQty(i, v) {
            const q = parseInt(v);
            if (q > 0) {
                cart[i].quantity = q;
                saveCart();
                renderCart();
            }
        }

        function removeItem(i) {
            Swal.fire({
                title: 'ลบสินค้า?',
                text: 'ต้องการลบสินค้านี้ออกจากตะกร้า?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e05a38',
                cancelButtonColor: '#888',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก'
            }).then(r => {
                if (r.isConfirmed) {
                    cart.splice(i, 1);
                    saveCart();
                    renderCart();
                    Swal.fire({
                        icon: 'success',
                        title: 'ลบสินค้าแล้ว',
                        timer: 1400,
                        showConfirmButton: false
                    });
                }
            });
        }

        function saveCart() {
            localStorage.setItem("cart", JSON.stringify(cart));
        }

        function selectReceiveType(type) {
            document.querySelectorAll('.receive-card').forEach(c => c.classList.remove('active'));
            const inp = document.querySelector(`input[value="${type}"]`);
            inp.closest('.receive-card').classList.add('active');
            inp.checked = true;
            toggleAddressField(type);
            renderCart();
        }

        function toggleAddressField(type) {
            const lbl = document.getElementById('address_label');
            const ta = document.getElementById('customer_address');
            if (type === 'pickup') {
                lbl.innerHTML = '<i class="fas fa-sticky-note" style="color:var(--sage);margin-right:6px"></i>หมายเหตุ';
                ta.placeholder = 'หมายเหตุเพิ่มเติม เช่น จะไปรับช่วงบ่าย 3 โมง';
                ta.removeAttribute('required');
            } else {
                lbl.innerHTML = '<i class="fas fa-map-marker-alt" style="color:var(--sage);margin-right:6px"></i>ที่อยู่จัดส่ง';
                ta.placeholder = 'กรอกที่อยู่สำหรับจัดส่ง เช่น ซอย หมู่บ้านที่เจาะจงเพื่อการจัดส่งที่ถูกต้อง';
                ta.setAttribute('required', 'required');
            }
        }

        function submitOrder() {
            const dt = document.getElementById("receive_datetime").value.trim();
            const type = document.querySelector('input[name="receive_type"]:checked')?.value || '';
            const name = document.querySelector('input[name="customer_name"]').value.trim();
            const phone = document.querySelector('input[name="customer_phone"]').value.trim();
            const address = document.getElementById("customer_address").value.trim();
            const total = cart.reduce((s, i) => s + (Number(i.price) * Number(i.quantity)), 0);

            if (cart.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'ตะกร้าว่าง',
                    text: 'กรุณาเลือกสินค้าก่อนสั่งซื้อ'
                });
                return false;
            }
            if (!name || !phone) {
                Swal.fire({
                    icon: 'error',
                    title: 'ข้อมูลไม่ครบ',
                    text: 'กรุณากรอกชื่อและเบอร์โทรศัพท์'
                });
                return false;
            }
            if (!dt) {
                Swal.fire({
                    icon: 'error',
                    title: 'ยังไม่เลือกวัน-เวลา',
                    text: 'กรุณาเลือกวันและเวลารับสินค้า'
                });
                return false;
            }
            if (type === 'delivery' && !address) {
                Swal.fire({
                    icon: 'error',
                    title: 'ระบุที่อยู่',
                    text: 'กรุณากรอกที่อยู่สำหรับจัดส่ง'
                });
                return false;
            }
            if (type === 'delivery' && total < 500) {
                Swal.fire({
                    icon: 'error',
                    title: 'ยอดไม่ถึงขั้นต่ำ',
                    text: 'การจัดส่งต้องมียอดขั้นต่ำ 500 บาท'
                });
                return false;
            }

            document.getElementById("cartData").value = JSON.stringify(cart);
            document.getElementById("loadingOverlay").style.display = "flex";
            document.getElementById("submitBtn").disabled = true;
            return true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            renderCart();
            toggleAddressField('pickup');

            <?php if ($order_error !== ''): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'ไม่สามารถสั่งซื้อได้',
                    text: <?= json_encode($order_error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
                });
            <?php endif; ?>
        });

        let selectedDate = "",
            selectedTime = "";

        function openDateModal() {
            new bootstrap.Modal(document.getElementById('dateModal')).show();
        }

        function setQuickDate(offset, el) {
            const d = new Date();
            d.setDate(d.getDate() + offset);
            selectedDate = d.toISOString().split('T')[0];
            document.getElementById('customDate').value = selectedDate;
            document.querySelectorAll('.quick-btn').forEach(b => b.classList.remove('active'));
            el.classList.add('active');
        }

        function setTime(t, el) {
            selectedTime = t;
            document.getElementById('customTime').value = t;
            document.querySelectorAll('.time-pill').forEach(b => b.classList.remove('active'));
            el.classList.add('active');
        }

        function confirmDate() {
            const cd = document.getElementById("customDate").value;
            const ct = document.getElementById("customTime").value;
            if (cd) selectedDate = cd;
            if (ct) selectedTime = ct;

            if (!selectedDate || !selectedTime) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเลือกวัน-เวลา',
                    text: 'โปรดระบุวันที่และเวลารับสินค้า'
                });
                return;
            }

            document.getElementById("receive_datetime").value = `${selectedDate} ${selectedTime}`;
            document.getElementById("triggerText").textContent = `${selectedDate}  เวลา ${selectedTime} น.`;
            bootstrap.Modal.getInstance(document.getElementById('dateModal')).hide();
        }
    </script>
</body>

</html>