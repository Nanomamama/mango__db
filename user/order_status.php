<?php
session_start();
require_once __DIR__ . '/../db/db.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$member_id = $_SESSION['member_id'] ?? null;
$orders = [];
$search_performed = false;
$error = '';
$success = '';

$keyword = trim($_GET['keyword'] ?? '');
/* ── Member: ดึงออเดอร์ตัวเองทั้งหมด (ยกเว้น completed) ── */
if ($member_id) {
    $stmt = $conn->prepare("
        SELECT
            o.*,
            COALESCE(SUM(oi.price * oi.quantity), 0) AS order_total
        FROM orders o
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        WHERE o.member_id = ?
          AND o.order_status != 'completed'
        GROUP BY o.order_id
        ORDER BY o.order_date DESC
    ");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/* ── Guest: ค้นหาด้วยเบอร์ หรือ ชื่อ (OR) ── */
if (
    !$member_id &&
    isset($_GET['keyword']) &&
    $keyword !== ''
) {
    $search_performed = true;

    /* ทำความสะอาดเบอร์โทร */
    $cleanPhone = preg_replace('/[^0-9]/', '', $keyword);

    $sql = "
        SELECT
            o.*,
            COALESCE(SUM(oi.price * oi.quantity), 0) AS order_total
        FROM orders o
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        WHERE 1
    ";

    $params = [];
    $types = '';

    $keywordPhone = preg_replace('/[^0-9]/', '', $keyword);

    /* ถ้ามีตัวเลข => ค้นหาเบอร์ */
    if ($keywordPhone !== '') {

        $sql .= " AND o.customer_phone LIKE ? ";
        $params[] = "%{$keywordPhone}%";
        $types .= 's';
    }

    /* ถ้ามีข้อความ => ค้นหาชื่อ */ else {

        $sql .= " AND o.customer_name LIKE ? ";
        $params[] = "%{$keyword}%";
        $types .= 's';
    }


    $sql .= "
        GROUP BY o.order_id
        ORDER BY o.order_date DESC
    ";

    $stmt = $conn->prepare($sql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (!empty($orders)) {
        $success = "พบคำสั่งซื้อ " . count($orders) . " รายการ";
    } else {
        $error = "ไม่พบคำสั่งซื้อ";
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <link rel="apple-touch-icon" sizes="180x180" href="../logo/logo_01.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../logo/logo_01.png">
    <title>สวนลุงเผือก</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --primary: #016A70;
            --primary-mid: #019a9f;
            --secondary: #2ad3bc;
            --pale: #e1f5f4;
            --pale2: #f0fbfb;
            --border: #c8e8e8;
            --border-mid: #8ecfcf;
            --white: #ffffff;
            --bg: #f2fafa;
            --text: #1a2e2f;
            --text-mid: #3d5f60;
            --text-muted: #6b8f90;
            --red: #b91c1c;
            --red-pale: #fee2e2;
            --green: #0f7a50;
            --green-pale: #dcfce7;
            --yellow: #92400e;
            --yellow-pale: #fef3c7;
            --blue: #1e40af;
            --blue-pale: #dbeafe;
            --pending: #ffe419;
            --approved: #21bf73;
            --rejected: #ff3251;
            --completed: #60a5fa;
            --radius: 14px;
            --radius-sm: 8px;
            --shadow: 0 2px 14px rgba(1, 106, 112, .07);
            --shadow-lg: 0 6px 28px rgba(1, 106, 112, .12);
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 15px;
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
        }

        .page-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 1rem 3rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .55rem 1.1rem;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 30px;
            color: var(--text-mid);
            text-decoration: none;
            font-size: .875rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            transition: .2s;
            box-shadow: var(--shadow);
        }

        .back-btn:hover {
            background: var(--pale);
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-1px);
        }

        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-mid) 55%, var(--secondary) 100%);
            border-radius: 20px;
            padding: 2.25rem 2rem;
            color: #fff;
            text-align: center;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .hero::before,
        .hero::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .06);
            pointer-events: none;
        }

        .hero::before {
            width: 260px;
            height: 260px;
            top: -110px;
            right: -70px;
        }

        .hero::after {
            width: 180px;
            height: 180px;
            bottom: -80px;
            left: -50px;
        }

        .hero-icon {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, .2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto .9rem;
            font-size: 1.75rem;
            backdrop-filter: blur(6px);
        }

        .hero h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: .3rem;
        }

        .hero p {
            font-size: .95rem;
            opacity: .88;
            margin: 0;
        }

        /* ── Search Box ── */
        .search-box {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            border-radius: 18px;
            padding: 12px 14px;
            border: 2px solid #e8f3f1;
            box-shadow: 0 8px 20px rgba(1, 106, 112, 0.08);
            transition: 0.3s ease;
            max-width: 650px;
            margin: 20px auto;
        }

        .search-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(1, 106, 112, 0.12);
        }

        .search-box:focus-within {
            border-color: #2ad3bc;
            box-shadow: 0 0 0 5px rgba(42, 211, 188, 0.15);
        }

        .search-icon {
            width: 48px;
            height: 48px;
            min-width: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #016A70, #2ad3bc);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .search-fields {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .search-fields input {
            width: 100%;
            border: none;
            outline: none;
            font-size: 16px;
            color: #333;
            background: transparent;
        }

        .search-fields input::placeholder {
            color: #9aa5b1;
        }

        .search-box button {
            border: none;
            outline: none;
            background: linear-gradient(135deg, #016A70, #2ad3bc);
            color: white;
            padding: 12px 22px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.25s ease;
            white-space: nowrap;
        }

        .search-box button:hover {
            transform: scale(1.04);
            box-shadow: 0 8px 18px rgba(1, 106, 112, 0.25);
        }

        /* ── Info Bar ── */
        .info-bar {
            background: linear-gradient(135deg, rgba(1, 106, 112, 0.08), rgba(42, 211, 188, 0.12));
            border: 1px solid rgba(42, 211, 188, 0.25);
            color: #016A70;
            padding: 14px 18px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 650px;
            margin: 0 auto 18px;
            font-size: 15px;
            font-weight: 500;
        }

        .info-bar i {
            font-size: 18px;
        }

        /* ── Count row ── */
        .count-row {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: 1rem;
            font-size: .875rem;
            color: var(--text-muted);
        }

        .count-pill {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: .2rem .8rem;
            font-weight: 700;
            color: var(--primary);
            font-size: .875rem;
        }

        /* ── Orders Grid ── */
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(390px, 1fr));
            gap: 1.35rem;
        }

        @media(max-width: 768px) {
            .orders-grid {
                grid-template-columns: 1fr;
            }
        }

        .order-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(1, 106, 112, .08);
            transition: .28s ease;
            display: flex;
            flex-direction: column;
            position: relative;
            animation: fadeUp .45s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 38px rgba(1, 106, 112, .14);
        }

        .status-line {
            height: 35px;
            width: 100%;
        }

        .line-pending {
            background: var(--pending);
        }

        .line-approved {
            background: var(--approved);
        }

        .line-rejected {
            background: var(--rejected);
        }

        .line-completed {
            background: var(--completed);
        }

        .card-top {
            padding: 1.2rem 1.2rem 1rem;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            border-bottom: 1px solid #edf7f7;
        }

        .left-top {
            min-width: 0;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem .95rem;
            border-radius: 999px;
            font-size: .76rem;
            font-weight: 700;
            margin-bottom: .8rem;
        }

        .badge-pending {
            background: var(--yellow-pale);
            color: var(--yellow);
        }

        .badge-approved {
            background: var(--green-pale);
            color: var(--green);
        }

        .badge-rejected {
            background: var(--red-pale);
            color: var(--red);
        }

        .badge-completed {
            background: var(--blue-pale);
            color: var(--blue);
        }

        .order-code {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: .2rem;
            word-break: break-word;
        }

        .order-date {
            font-size: .82rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: .45rem;
        }

        .total-box {
            background: linear-gradient(135deg, var(--primary), var(--primary-mid));
            color: #fff;
            border-radius: 18px;
            padding: .9rem 1rem;
            min-width: 120px;
            text-align: center;
            flex-shrink: 0;
            box-shadow: 0 6px 20px rgba(1, 106, 112, .18);
        }

        .total-box span {
            font-size: .72rem;
            opacity: .85;
            display: block;
            margin-bottom: .2rem;
        }

        .total-box strong {
            font-size: 1.45rem;
            line-height: 1;
            font-weight: 800;
        }

        .customer-grid {
            padding: 1rem 1.2rem;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .8rem;
            border-bottom: 1px solid #edf7f7;
        }

        .customer-item {
            background: var(--pale2);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: .75rem;
        }

        .customer-label {
            font-size: .72rem;
            color: var(--text-muted);
            margin-bottom: .35rem;
        }

        .customer-value {
            font-size: .88rem;
            font-weight: 700;
            color: var(--text);
            display: flex;
            align-items: flex-start;
            gap: .45rem;
            line-height: 1.45;
        }

        .products-wrap {
            padding: 1rem 1.2rem 1.2rem;
        }

        .products-title {
            display: flex;
            align-items: center;
            gap: .45rem;
            color: var(--primary);
            font-size: .82rem;
            font-weight: 800;
            margin-bottom: .9rem;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .products-list {
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .product-card {
            display: flex;
            gap: .8rem;
            padding: .7rem;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--pale2);
            transition: .2s;
        }

        .product-card:hover {
            border-color: var(--border-mid);
            background: var(--pale);
        }

        .product-image,
        .product-placeholder {
            width: 62px;
            height: 62px;
            border-radius: 14px;
            object-fit: cover;
            background: var(--pale);
            border: 1px solid var(--border);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        .product-info {
            flex: 1;
            min-width: 0;
        }

        .product-name {
            font-size: .9rem;
            font-weight: 700;
            color: var(--text);
            line-height: 1.4;
            margin-bottom: .45rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
        }

        .product-chip {
            background: #fff;
            border: 1px solid var(--border);
            color: var(--primary);
            border-radius: 999px;
            padding: .2rem .55rem;
            font-size: .72rem;
            font-weight: 700;
        }

        .admin-note {
            margin: 0 1.2rem 1.1rem;
            background: #fff1f2;
            border: 1px solid #fda4af;
            border-left: 4px solid #e11d48;
            border-radius: 14px;
            padding: .75rem .9rem;
            display: flex;
            gap: .6rem;
            color: #9f1239;
            font-size: .82rem;
            line-height: 1.5;
        }

        .card-footer {
            margin-top: auto;
            padding: 1rem 1.2rem 1.2rem;
            border-top: 1px solid #edf7f7;
        }

        .btn-detail {
            width: 100%;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-mid));
            color: #fff;
            text-decoration: none;
            font-size: .9rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            transition: .2s;
            border: none;
            cursor: pointer;
        }

        .btn-detail:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(1, 106, 112, .2);
        }

        .more-items {
            margin-top: .75rem;
            font-size: .8rem;
            color: var(--primary);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: .45rem;
        }

        /* ── Toast ── */
        .toast-wrap {
            position: fixed;
            top: 1.2rem;
            right: 1.2rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: .6rem;
            max-width: 360px;
        }

        .toast-item {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: .9rem 1rem;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: flex-start;
            gap: .7rem;
            animation: slideIn .3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(110%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast-error {
            border-left: 3px solid var(--red);
        }

        .toast-success {
            border-left: 3px solid var(--green);
        }

        .toast-ico {
            font-size: 1.1rem;
            margin-top: .1rem;
            flex-shrink: 0;
        }

        .toast-error .toast-ico {
            color: var(--red);
        }

        .toast-success .toast-ico {
            color: var(--green);
        }

        .toast-msg {
            font-size: .875rem;
            color: var(--text-mid);
            flex: 1;
            line-height: 1.5;
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
            flex-shrink: 0;
        }

        .toast-close:hover {
            color: var(--text);
        }

        /* ── Modal ── */
        .order-modal {
            border: none;
            border-radius: 24px;
            overflow: hidden;
        }

        .modal-header {
            padding: 1.3rem 1.4rem .8rem;
        }

        .modal-order-code {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--text);
        }

        .modal-order-date {
            font-size: .82rem;
            color: var(--text-muted);
            margin-top: .2rem;
        }

        .modal-body {
            padding: 1rem 1.4rem 1.4rem;
        }

        .modal-products {
            display: flex;
            flex-direction: column;
            gap: .9rem;
        }

        .modal-product-card {
            display: flex;
            gap: 1rem;
            border: 1px solid var(--border);
            background: var(--pale2);
            border-radius: 18px;
            padding: .9rem;
        }

        .modal-product-image,
        .modal-product-placeholder {
            width: 82px;
            height: 82px;
            border-radius: 16px;
            object-fit: cover;
            background: var(--pale);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            flex-shrink: 0;
        }

        .modal-product-info {
            flex: 1;
            min-width: 0;
        }

        .modal-product-name {
            font-size: .95rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: .45rem;
            line-height: 1.45;
        }

        .modal-product-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
        }

        .modal-product-meta span {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .3rem .7rem;
            font-size: .78rem;
            font-weight: 700;
            color: var(--primary);
        }

        .modal-footer {
            padding: 1rem 1.4rem 1.4rem;
        }

        .modal-total {
            width: 100%;
            text-align: left;
            font-size: 1rem;
            color: var(--text);
        }

        .modal-total strong {
            font-size: 1.5rem;
            color: var(--primary);
        }

        /* ── Empty state ── */
        .empty-state {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: var(--shadow);
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: var(--pale);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary);
        }

        .btn-empty {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            margin-top: 1rem;
            padding: .8rem 1.2rem;
            border-radius: 14px;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
        }

        /* ── Responsive ── */
        @media (max-width: 640px) {
            .search-box {
                flex-direction: column;
                align-items: stretch;
            }

            .search-fields {
                width: 100%;
            }

            .search-icon {
                width: 100%;
                height: 52px;
            }

            .search-box button {
                justify-content: center;
            }

            .card-top {
                flex-direction: column;
            }

            .total-box {
                width: 100%;
            }

            .customer-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 1.75rem 1rem;
            }

            .hero h1 {
                font-size: 1.3rem;
            }

            .page-wrap {
                padding: 1rem .75rem 2rem;
            }
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/navbar.php'; ?>
    <?php include __DIR__ . '/fb_chat_button.php'; ?>

    <!-- Toast alerts -->
    <div class="toast-wrap">
        <?php if ($error): ?>
            <div class="toast-item toast-error" id="toastErr">
                <i class="fas fa-exclamation-circle toast-ico"></i>
                <span class="toast-msg"><?= htmlspecialchars($error) ?></span>
                <button class="toast-close" onclick="this.closest('.toast-item').remove()">×</button>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="toast-item toast-success" id="toastOk">
                <i class="fas fa-check-circle toast-ico"></i>
                <span class="toast-msg"><?= htmlspecialchars($success) ?></span>
                <button class="toast-close" onclick="this.closest('.toast-item').remove()">×</button>
            </div>
        <?php endif; ?>
    </div>

    <div class="page-wrap">

        <!-- Back -->
        <a href="products.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> กลับหน้าหลัก
        </a>

        <!-- Hero -->
        <div class="hero">
            <div class="hero-icon"><i class="fas fa-search"></i></div>
            <h1>ติดตามสถานะคำสั่งซื้อ</h1>
            <p>ตรวจสอบสถานะและรายละเอียดคำสั่งซื้อของคุณ</p>
        </div>

        <!-- Info bar (guest only) -->
        <?php if (!$member_id): ?>
            <div class="info-bar">
                <i class="fas fa-shield-alt"></i>
                <span>ค้นหาคำสั่งซื้อด้วยเบอร์โทรศัพท์ หรือ ชื่อที่ใช้สั่งซื้อ</span>
            </div>
        <?php endif; ?>

        <!-- Search Form -->
        <?php if (!$member_id): ?>
            <form method="GET" class="search-box">

                <div class="search-icon">
                    <i class="fas fa-search"></i>
                </div>

                <div class="search-fields">

                    <input
                        type="text"
                        name="keyword"
                        placeholder="กรอกชื่อ หรือ เบอร์โทรศัพท์"
                        value="<?= htmlspecialchars($keyword ?? '') ?>">

                </div>

                <button type="submit">
                    <i class="fas fa-search"></i>
                    ค้นหา
                </button>

            </form>
        <?php endif; ?>

        <?php if (
            ($member_id && !empty($orders))
            || (!$member_id && $search_performed && !empty($orders))
        ): ?>

            <div class="count-row">
                พบคำสั่งซื้อทั้งหมด
                <span class="count-pill"><?= count($orders) ?> รายการ</span>
            </div>

            <div class="orders-grid">

                <?php foreach ($orders as $idx => $o):

                    /* ── ดึงรายการสินค้าในออเดอร์ ── */
                    $itemStmt = $conn->prepare("
                        SELECT oi.quantity, oi.price, p.product_name, p.product_image, p.unit
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.product_id
                        WHERE oi.order_id = ?
                    ");
                    $itemStmt->bind_param("i", $o['order_id']);
                    $itemStmt->execute();
                    $itemRows    = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $totalItems  = count($itemRows);
                    $previewItems = array_slice($itemRows, 0, 3);

                    /* ยอดรวม (ใช้จาก query หลักได้เลย แต่คำนวณซ้ำเผื่อกรณีพิเศษ) */
                    $orderTotal = $o['order_total'] ?? 0;

                    $statusLabel = match ($o['order_status']) {
                        'pending'   => 'รอยืนยัน',
                        'approved'  => 'ยืนยันแล้ว',
                        'rejected'  => 'ถูกปฏิเสธ',
                        'completed' => 'เสร็จสมบูรณ์',
                        default     => $o['order_status']
                    };
                    $statusIcon = match ($o['order_status']) {
                        'pending'   => 'fa-clock',
                        'approved'  => 'fa-check-circle',
                        'rejected'  => 'fa-times-circle',
                        'completed' => 'fa-flag-checkered',
                        default     => 'fa-circle'
                    };
                ?>

                    <!-- ORDER CARD -->
                    <div class="order-card" style="animation-delay: <?= $idx * 70 ?>ms">

                        <div class="status-line line-<?= $o['order_status'] ?>"></div>

                        <!-- TOP -->
                        <div class="card-top">
                            <div class="left-top">
                                <div class="status-badge badge-<?= $o['order_status'] ?>">
                                    <i class="fas <?= $statusIcon ?>"></i>
                                    <?= $statusLabel ?>
                                </div>
                                <div class="order-code">#<?= htmlspecialchars($o['order_code']) ?></div>
                                <div class="order-date">
                                    <i class="fas fa-clock"></i>
                                    <?= date('d/m/Y H:i', strtotime($o['order_date'])) ?>
                                </div>
                            </div>
                            <div class="total-box">
                                <span>ยอดรวม</span>
                                <strong>฿<?= number_format($orderTotal, 0) ?></strong>
                            </div>
                        </div>

                        <!-- CUSTOMER -->
                        <div class="customer-grid">
                            <div class="customer-item">
                                <div class="customer-label">ลูกค้า</div>
                                <div class="customer-value">
                                    <i class="fas fa-user-circle"></i>
                                    <?= htmlspecialchars($o['customer_name']) ?>
                                </div>
                            </div>
                            <div class="customer-item">
                                <div class="customer-label">เบอร์โทร</div>
                                <div class="customer-value">
                                    <i class="fas fa-phone"></i>
                                    <?= htmlspecialchars($o['customer_phone']) ?>
                                </div>
                            </div>
                            <div class="customer-item">
                                <div class="customer-label">การรับสินค้า</div>
                                <div class="customer-value">
                                    <?php if ($o['receive_type'] === 'pickup'): ?>
                                        <i class="fas fa-store"></i> รับที่สวน
                                    <?php else: ?>
                                        <i class="fas fa-truck"></i> จัดส่งถึงบ้าน
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- PRODUCTS PREVIEW -->
                        <div class="products-wrap">
                            <div class="products-title">
                                <i class="fas fa-basket-shopping"></i>
                                รายการสินค้า
                            </div>

                            <div class="products-list">

                                <?php if ($totalItems > 3): ?>
                                    <div class="more-items">
                                        <i class="fas fa-box-open"></i>
                                        และสินค้าอื่นอีก <?= $totalItems - 3 ?> รายการ
                                    </div>
                                <?php endif; ?>

                                <?php foreach ($previewItems as $item):
                                    $imgPath = !empty($item['product_image'])
                                        ? "../admin/uploads/products/" . htmlspecialchars($item['product_image'])
                                        : null;
                                ?>
                                    <div class="product-card">

                                        <?php if ($imgPath): ?>
                                            <img src="<?= $imgPath ?>"
                                                class="product-image"
                                                loading="lazy"
                                                alt="<?= htmlspecialchars($item['product_name']) ?>"
                                                onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                            <div class="product-placeholder" style="display:none">
                                                <i class="fas fa-seedling"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="product-placeholder">
                                                <i class="fas fa-seedling"></i>
                                            </div>
                                        <?php endif; ?>

                                        <div class="product-info">
                                            <div class="product-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                            <div class="product-meta">
                                                <span class="product-chip">×<?= (int)$item['quantity'] ?></span>
                                                <span class="product-chip"><?= htmlspecialchars($item['unit'] ?? 'ชิ้น') ?></span>
                                                <span class="product-chip">฿<?= number_format($item['price'], 0) ?></span>
                                            </div>
                                        </div>

                                    </div>
                                <?php endforeach; ?>

                            </div>
                        </div>

                        <!-- ADMIN NOTE -->
                        <?php if (!empty($o['admin_note'])): ?>
                            <div class="admin-note">
                                <i class="fas fa-circle-exclamation"></i>
                                <div><?= htmlspecialchars($o['admin_note']) ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- FOOTER -->
                        <div class="card-footer">
                            <button
                                class="btn-detail"
                                data-bs-toggle="modal"
                                data-bs-target="#orderModal<?= $o['order_id'] ?>">
                                <i class="fas fa-eye"></i>
                                ดูสินค้าทั้งหมด
                            </button>
                        </div>

                    </div>
                    <!-- END ORDER CARD -->

                    <!-- MODAL -->
                    <div class="modal fade"
                        id="orderModal<?= $o['order_id'] ?>"
                        tabindex="-1"
                        aria-hidden="true">

                        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                            <div class="modal-content order-modal">

                                <div class="modal-header border-0">
                                    <div>
                                        <div class="modal-order-code">#<?= htmlspecialchars($o['order_code']) ?></div>
                                        <div class="modal-order-date"><?= date('d/m/Y H:i', strtotime($o['order_date'])) ?></div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="modal-products">
                                        <?php foreach ($itemRows as $item):
                                            $imgPath = !empty($item['product_image'])
                                                ? "../admin/uploads/products/" . htmlspecialchars($item['product_image'])
                                                : null;
                                        ?>
                                            <div class="modal-product-card">

                                                <?php if ($imgPath): ?>
                                                    <img src="<?= $imgPath ?>"
                                                        class="modal-product-image"
                                                        alt="<?= htmlspecialchars($item['product_name']) ?>">
                                                <?php else: ?>
                                                    <div class="modal-product-placeholder">
                                                        <i class="fas fa-seedling"></i>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="modal-product-info">
                                                    <div class="modal-product-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                                    <div class="modal-product-meta">
                                                        <span>จำนวน: <?= (int)$item['quantity'] ?> <?= htmlspecialchars($item['unit'] ?? 'ชิ้น') ?></span>
                                                        <span>ราคา: ฿<?= number_format($item['price'], 0) ?></span>
                                                    </div>
                                                </div>

                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="modal-footer border-0">
                                    <div class="modal-total">
                                        ยอดรวม: <strong>฿<?= number_format($orderTotal, 0) ?></strong>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- END MODAL -->

                <?php endforeach; ?>

            </div><!-- .orders-grid -->

        <?php elseif ($search_performed): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-clipboard-list"></i></div>
                <h2>ไม่พบคำสั่งซื้อ</h2>
                <p>ไม่พบคำสั่งซื้อที่ตรงกับข้อมูลที่ค้นหา</p>
                <a href="products.php" class="btn-empty">
                    <i class="fas fa-arrow-left"></i> กลับไปสั่งซื้อสินค้า
                </a>
            </div>
        <?php endif; ?>

    </div><!-- .page-wrap -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss toasts after 5s
        setTimeout(() => {
            document.querySelectorAll('.toast-item').forEach(el => {
                el.style.transition = 'all .3s ease';
                el.style.transform = 'translateX(110%)';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 320);
            });
        }, 5000);
    </script>

</body>

</html>