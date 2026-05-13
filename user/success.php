<?php
session_start();
require_once __DIR__ . '/../db/db.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_GET['code'])) {
    header("Location: products.php");
    exit;
}

$code = trim((string) $_GET['code']);

if ($code === '' || !preg_match('/^ORD\d{6}[A-F0-9]{4}$/', $code)) {
    header("Location: products.php");
    exit;
}

$sessionMemberId = isset($_SESSION['member_id']) ? (int) $_SESSION['member_id'] : null;
$guestOrderCodes = $_SESSION['guest_order_codes'] ?? [];

if (!is_array($guestOrderCodes)) {
    $guestOrderCodes = [];
}

$stmt = $conn->prepare("
    SELECT 
        order_id,
        order_code,
        member_id,
        customer_name,
        customer_phone,
        customer_address,
        receive_type,
        receive_datetime,
        order_status
    FROM orders
    WHERE order_code = ?
");
$stmt->bind_param("s", $code);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: products.php");
    exit;
}

$orderMemberId = isset($order['member_id']) ? (int) $order['member_id'] : 0;
$canView = false;

if ($orderMemberId > 0) {
    $canView = $sessionMemberId !== null && $sessionMemberId === $orderMemberId;
} else {
    $canView = isset($guestOrderCodes[$code]);
}

if (!$canView) {
    http_response_code(403);
    echo "คุณไม่มีสิทธิ์เข้าถึงคำสั่งซื้อนี้";
    exit;
}

$item = $conn->prepare("
    SELECT
        oi.*,
        p.unit,
        p.product_image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$item->bind_param("i", $order['order_id']);
$item->execute();
$items = $item->get_result();
$item->close();

$statusMap = [
    'pending'   => ['label' => 'รอยืนยัน',      'icon' => 'fa-clock',        'class' => 'pending'],
    'approved'  => ['label' => 'ยืนยันแล้ว',     'icon' => 'fa-check-circle', 'class' => 'approved'],
    'rejected'  => ['label' => 'ถูกปฏิเสธ',      'icon' => 'fa-times-circle', 'class' => 'rejected'],
    'completed' => ['label' => 'เสร็จสมบูรณ์',   'icon' => 'fa-flag-checkered', 'class' => 'completed'],
];
$statusInfo = $statusMap[$order['order_status']] ?? ['label' => $order['order_status'], 'icon' => 'fa-info-circle', 'class' => 'pending'];

function baht(int|float|string $num): string
{
    return '฿' . number_format((float)$num);
}

// Pre-fetch items into array so we can use twice (count + display)
$itemRows = [];
$total    = 0;
while ($r = $items->fetch_assoc()) {
    $r['_sum'] = $r['price'] * $r['quantity'];
    $total    += $r['_sum'];
    $itemRows[] = $r;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>สั่งซื้อสำเร็จ – สวนลุงเผือก</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ── Reset & Base ─────────────────────────────────────── */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        :root {
            --sage: #016A70;
            --sage-mid: #019a9f;
            --sage-light: #2ad3bc;
            --sage-pale: #e1f5f4;
            --sage-pale2: #f0fbfb;
            --white: #ffffff;
            --bg: #f2fafa;
            --border: #c8e8e8;
            --border-mid: #99d4d4;
            --text: #1a2e2f;
            --text-mid: #3d5f60;
            --text-muted: #6b8f90;
            --red: #c0392b;
            --green-acc: #0f7a50;
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --shadow: 0 2px 16px rgba(1, 106, 112, .08);
            --shadow-lg: 0 6px 28px rgba(1, 106, 112, .12);
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            font-size: 15px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* ── Page wrapper ─────────────────────────────────────── */
        .page-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 1rem 3rem;
        }

        /* ── Banner ───────────────────────────────────────────── */
        .banner {
            background: linear-gradient(135deg, var(--sage) 0%, var(--sage-mid) 55%, var(--sage-light) 100%);
            border-radius: var(--radius-lg);
            padding: 2.25rem 1.5rem 2rem;
            color: #fff;
            text-align: center;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        /* Decorative circles */
        .banner::before,
        .banner::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .06);
            pointer-events: none;
        }

        .banner::before {
            width: 280px;
            height: 280px;
            top: -120px;
            right: -80px;
        }

        .banner::after {
            width: 200px;
            height: 200px;
            bottom: -90px;
            left: -60px;
        }

        .banner-icon {
            font-size: 3rem;
            margin-bottom: .6rem;
            animation: pop .6s cubic-bezier(.34, 1.56, .64, 1) both;
        }

        @keyframes pop {
            from {
                transform: scale(.3);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .banner h1 {
            font-size: 1.65rem;
            font-weight: 700;
            margin-bottom: .25rem;
            letter-spacing: .3px;
        }

        .banner .subtitle {
            font-size: .95rem;
            opacity: .88;
            margin-bottom: 1.4rem;
        }

        .code-pill {
            display: inline-block;
            background: rgba(255, 255, 255, .18);
            border: none;
            border-radius: 50px;
            padding: .55rem 1.6rem;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 2px;
            backdrop-filter: blur(6px);
            margin-bottom: 1rem;
            word-break: break-all;
        }

        /* Status pill */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem 1.2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: .88rem;
        }

        .status-pill.pending {
            background: #fef9c3;
            color: #713f12;
        }

        .status-pill.approved {
            background: #dcfce7;
            color: #14532d;
        }

        .status-pill.completed {
            background: #dbeafe;
            color: #1e3a5f;
        }

        .status-pill.rejected {
            background: #fee2e2;
            color: #7f1d1d;
        }

        /* ── 2-column layout ──────────────────────────────────── */
        .layout {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 1.25rem;
            align-items: start;
        }

        /* ── Cards ────────────────────────────────────────────── */
        .card {
            background: var(--white);
            border: none;
            /* border-radius: var(--radius-lg); */
            box-shadow: none;
            overflow: hidden;
        }

        .card:hover {
            box-shadow: none;
        }

        .card+.card {
            margin-top: 1.25rem;
        }

        .card-head {
            background: var(--sage-pale);
            border-bottom: none;
            padding: .8rem 1.2rem;
            display: flex;
            align-items: center;
            gap: .7rem;
        }

        .card-head-icon {
            width: 32px;
            height: 32px;
            background: var(--sage);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: .8rem;
            flex-shrink: 0;
        }

        .card-head h2 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--sage);
            margin: 0;
        }

        .card-body {
            padding: 1.25rem;
        }

        /* ── Info grid ────────────────────────────────────────── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
        }

        .info-item {
            background: var(--sage-pale2);
            border: none;
            border-radius: var(--radius-sm);
            padding: .7rem .95rem;
        }

        .info-item .lbl {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--sage);
            margin-bottom: .2rem;
        }

        .info-item .val {
            font-size: .93rem;
            color: var(--text);
            font-weight: 500;
            line-height: 1.45;
        }

        /* ── Order items list ─────────────────────────────────── */
        .items-list {
            max-height: 380px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .items-list::-webkit-scrollbar {
            width: 5px;
        }

        .items-list::-webkit-scrollbar-track {
            background: var(--sage-pale2);
            border-radius: 10px;
        }

        .items-list::-webkit-scrollbar-thumb {
            background: var(--border-mid);
            border-radius: 10px;
        }

        .items-list::-webkit-scrollbar-thumb:hover {
            background: var(--sage);
        }

        .order-item {
            display: grid;
            grid-template-columns: 72px 1fr auto;
            gap: .9rem;
            align-items: center;
            padding: .9rem 0;
            border-bottom: none;
            animation: fadeUp .35s ease both;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .item-img {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: var(--radius-md);
            border: none;
            flex-shrink: 0;
        }

        .item-img-placeholder {
            width: 72px;
            height: 72px;
            border-radius: var(--radius-md);
            background: var(--sage-pale);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--sage);
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .item-name {
            font-weight: 600;
            font-size: .92rem;
            color: var(--text);
            margin-bottom: .35rem;
            line-height: 1.35;
        }

        .item-tags {
            display: flex;
            flex-wrap: wrap;
            gap: .3rem;
        }

        .tag {
            background: var(--sage-pale);
            color: var(--sage);
            font-size: .72rem;
            font-weight: 600;
            padding: .15rem .55rem;
            border-radius: 20px;
            border: none;
        }

        .item-price {
            font-size: 1rem;
            font-weight: 700;
            color: var(--red);
            white-space: nowrap;
            text-align: right;
        }

        .items-count {
            text-align: right;
            font-size: .78rem;
            color: var(--text-muted);
            margin-top: .65rem;
            padding-top: .5rem;
            border-top: none;
        }

        /* ── Summary ──────────────────────────────────────────── */
        .sum-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .65rem 0;
            border-bottom: none;
            font-size: .93rem;
            gap: .5rem;
        }

        .sum-row:last-child {
            border: none;
        }

        .sum-row .lbl-s {
            color: var(--text-mid);
        }

        .sum-row .val-s {
            font-weight: 500;
            color: var(--text);
        }

        .sum-row.total-row {
            padding-top: .9rem;
            margin-top: .2rem;
            border-top: none;
            border-bottom: none;
        }

        .sum-row.total-row .lbl-s {
            font-weight: 700;
            font-size: 1rem;
            color: var(--text);
        }

        .sum-row.total-row .val-s {
            font-weight: 700;
            font-size: 1.35rem;
            color: var(--red);
        }

        .free-ship {
            color: var(--green-acc);
            font-weight: 700;
        }

        .pay-note {
            background: var(--sage-pale2);
            border: none;
            border-radius: var(--radius-sm);
            padding: .7rem .9rem;
            margin-top: 1rem;
            font-size: .8rem;
            color: var(--text-muted);
            display: flex;
            gap: .5rem;
            align-items: flex-start;
            line-height: 1.5;
        }

        .pay-note i {
            color: var(--sage);
            margin-top: .1rem;
            flex-shrink: 0;
        }

        /* ── Action buttons ───────────────────────────────────── */
        .action-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .7rem;
        }

        .btn-act {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            padding: .72rem .5rem;
            border-radius: 30px;
            font-family: 'Sarabun', sans-serif;
            font-size: .875rem;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all .2s ease;
            text-align: center;
            white-space: nowrap;
        }

        .btn-outline {
            background: var(--white);
            border: none;
            color: var(--text-mid);
        }

        .btn-outline:hover {
            background: var(--sage-pale2);
            color: var(--sage);
            transform: translateY(-1px);
        }

        .btn-primary {
            background: var(--sage);
            color: #fff;
            border: none;
        }

        .btn-primary:hover {
            background: var(--sage-mid);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(1, 106, 112, .3);
        }

        /* Sidebar sticky */
        .sidebar {
            position: sticky;
            top: 1rem;
        }

        /* ── Responsive ───────────────────────────────────────── */
        @media (max-width: 900px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .banner {
                padding: 1.75rem 1rem 1.5rem;
            }

            .banner h1 {
                font-size: 1.35rem;
            }

            .code-pill {
                font-size: 1rem;
                padding: .45rem 1rem;
                letter-spacing: 1px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .order-item {
                grid-template-columns: 64px 1fr;
                grid-template-rows: auto auto;
            }

            .item-img,
            .item-img-placeholder {
                width: 64px;
                height: 64px;
            }

            .item-price {
                grid-column: 2;
                text-align: left;
                font-size: .92rem;
            }

            .action-grid {
                grid-template-columns: 1fr;
            }

            .btn-act {
                padding: .78rem 1rem;
                font-size: .9rem;
            }

            .sum-row.total-row .val-s {
                font-size: 1.2rem;
            }

            .card-body {
                padding: 1rem;
            }

            .page-wrap {
                padding: 1rem .75rem 2.5rem;
            }
        }

        @media (max-width: 380px) {
            .banner h1 {
                font-size: 1.2rem;
            }

            .code-pill {
                font-size: .85rem;
            }

            .banner-icon {
                font-size: 2.4rem;
            }
        }

        /* ── Print ────────────────────────────────────────────── */
        @media print {
            body {
                background: #fff;
            }

            .banner {
                background: var(--sage) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .card {
                box-shadow: none;
                border: none;
            }

            .action-grid,
            .sidebar .card:last-child {
                display: none;
            }

            .sidebar {
                position: static;
            }

            .layout {
                grid-template-columns: 1fr;
            }

            .items-list {
                max-height: none;
                overflow: visible;
            }

            .page-wrap {
                padding: 0;
            }
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/navbar.php'; ?>
    <?php include __DIR__ . '/fb_chat_button.php'; ?>

    <div class="page-wrap">

        <!-- ── Banner ── -->
        <div class="banner">
            <div class="banner-icon"><i class="fas fa-check-circle"></i></div>
            <h1>สั่งซื้อสำเร็จ!</h1>
            <p class="subtitle">ขอบคุณสำหรับการสั่งซื้อสินค้ากับสวนลุงเผือก</p>
            <div class="code-pill"><?= htmlspecialchars($order['order_code']) ?></div><br>
            <span class="status-pill <?= $statusInfo['class'] ?>">
                <i class="fas <?= $statusInfo['icon'] ?>"></i>
                สถานะ: <?= $statusInfo['label'] ?>
            </span>
        </div>

        <!-- ── Main layout ── -->
        <div class="layout">

            <!-- ── Left column ── -->
            <div>

                <!-- Order items -->
                <div class="card">
                    <div class="card-head">
                        <div class="card-head-icon"><i class="fas fa-shopping-basket"></i></div>
                        <h2>รายการสินค้า</h2>
                    </div>
                    <div class="card-body">
                        <div class="items-list">
                            <?php if (count($itemRows) > 0): ?>
                                <?php foreach ($itemRows as $i): ?>
                                    <?php
                                    $imgPath = !empty($i['product_image'])
                                        ? "../admin/uploads/products/" . htmlspecialchars($i['product_image'])
                                        : null;
                                    ?>
                                    <div class="order-item">
                                        <?php if ($imgPath): ?>
                                            <img src="<?= $imgPath ?>"
                                                class="item-img"
                                                alt="<?= htmlspecialchars($i['product_name']) ?>"
                                                loading="lazy"
                                                onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                            <div class="item-img-placeholder" style="display:none">
                                                <i class="fas fa-seedling"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="item-img-placeholder">
                                                <i class="fas fa-seedling"></i>
                                            </div>
                                        <?php endif; ?>

                                        <div>
                                            <div class="item-name"><?= htmlspecialchars($i['product_name']) ?></div>
                                            <div class="item-tags">
                                                <span class="tag">ราคา <?= baht($i['price']) ?></span>
                                                <span class="tag">×<?= (int)$i['quantity'] ?></span>
                                                <?php if (!empty($i['unit'])): ?>
                                                    <span class="tag"><?= htmlspecialchars($i['unit']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="item-price"><?= baht($i['_sum']) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-box-open fa-2x mb-2 d-block" style="color:var(--border-mid)"></i>
                                    ไม่พบรายการสินค้า
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="items-count">จำนวนรายการ: <?= count($itemRows) ?> รายการ</div>
                    </div>
                </div>

                <!-- Customer info -->
                <div class="card">
                    <div class="card-head">
                        <div class="card-head-icon"><i class="fas fa-user"></i></div>
                        <h2>ข้อมูลผู้สั่งซื้อ</h2>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="lbl">ชื่อ-นามสกุล</div>
                                <div class="val"><?= htmlspecialchars($order['customer_name']) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="lbl">เบอร์โทรศัพท์</div>
                                <div class="val"><?= htmlspecialchars($order['customer_phone']) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="lbl">ที่อยู่จัดส่ง</div>
                                <div class="val"><?= nl2br(htmlspecialchars($order['customer_address'])) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="lbl">วิธีการรับสินค้า</div>
                                <div class="val">
                                    <?php if ($order['receive_type'] === 'pickup'): ?>
                                        <i class="fas fa-map-marker-alt me-1" style="color:var(--sage)"></i>รับที่สวน
                                    <?php else: ?>
                                        <i class="fas fa-truck me-1" style="color:var(--sage)"></i>จัดส่งถึงบ้าน
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($order['receive_datetime']) && $order['receive_datetime'] !== '0000-00-00 00:00:00'): ?>
                                <div class="info-item">
                                    <div class="lbl">วันเวลาที่นัดรับ</div>
                                    <div class="val">
                                        <i class="fas fa-calendar-alt me-1" style="color:var(--sage)"></i>
                                        <?= date('d/m/Y', strtotime($order['receive_datetime'])) ?>
                                        เวลา <?= date('H:i', strtotime($order['receive_datetime'])) ?> น.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div><!-- /left column -->

            <!-- ── Right column (sidebar) ── -->
            <div class="sidebar">

                <!-- Order summary -->
                <div class="card">
                    <div class="card-head">
                        <div class="card-head-icon"><i class="fas fa-file-invoice"></i></div>
                        <h2>สรุปยอดสั่งซื้อ</h2>
                    </div>
                    <div class="card-body">
                        <div class="sum-row">
                            <span class="lbl-s">ยอดรวมสินค้า (<?= count($itemRows) ?> รายการ)</span>
                            <span class="val-s"><?= baht($total) ?></span>
                        </div>

                        <?php if ($order['receive_type'] !== 'pickup'): ?>
                            <div class="sum-row">
                                <span class="lbl-s">ค่าจัดส่ง</span>
                                <span class="val-s free-ship"><i class="fas fa-gift me-1"></i>ฟรี</span>
                            </div>
                        <?php endif; ?>

                        <div class="sum-row total-row">
                            <span class="lbl-s">รวมทั้งสิ้น</span>
                            <span class="val-s"><?= baht($total) ?></span>
                        </div>
                        <div class="pay-note">
                            <i class="fas fa-info-circle"></i>
                            <span>โอนเงินผ่านธนาคาร หรือ ชำระเงินสด ณ วันนัดรับสินค้า</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-head">
                        <div class="card-head-icon"><i class="fas fa-bolt"></i></div>
                        <h2>ดำเนินการต่อ</h2>
                    </div>
                    <div class="card-body">
                        <div class="action-grid">
                            <a href="products.php" class="btn-act btn-outline">
                                <i class="fas fa-arrow-left"></i> ซื้อสินค้าเพิ่ม
                            </a>
                            <a href="order_status.php" class="btn-act btn-primary">
                                <i class="fas fa-search"></i> ติดตามสถานะ
                            </a>
                        </div>
                    </div>
                </div>

            </div><!-- /sidebar -->

        </div><!-- /layout -->

    </div><!-- /page-wrap -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Clear cart
        localStorage.removeItem('cart');
        if (typeof updateCartCount === 'function') updateCartCount();

        // Stagger item animation
        document.querySelectorAll('.order-item').forEach((el, i) => {
            el.style.animationDelay = (i * 60) + 'ms';
        });
    </script>

</body>

</html>