<?php
require_once 'sidebar.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$adminName = $_SESSION['admin_name'] ?? '';

function safeQuery(mysqli $conn, string $sql): mysqli_result|false
{
    try {
        $result = $conn->query($sql);
        if (!$result) {
            error_log('DB error: ' . $conn->error . ' | SQL: ' . $sql);
        }
        return $result;
    } catch (mysqli_sql_exception $e) {
        error_log('DB exception: ' . $e->getMessage() . ' | SQL: ' . $sql);
        return false;
    }
}

function formatCurrency(float $amount): string
{
    return number_format($amount, 2);
}

function formatNumber(int|float $number): string
{
    return number_format($number);
}

function formatThaiDateTime(?string $dateTime): string
{
    if (empty($dateTime) || $dateTime === '0000-00-00 00:00:00') {
        return '-';
    }

    $timestamp = strtotime($dateTime);
    if ($timestamp === false) {
        return '-';
    }

    return date('d/m/Y H:i', $timestamp);
}

function formatThaiDate(?string $date): string
{
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }

    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '-';
    }

    return date('d/m/Y', $timestamp);
}

function renderChangeText(float $value): string
{
    $prefix = $value >= 0 ? '+' : '';
    return $prefix . number_format($value, 1) . '% จากเดือนก่อน';
}

function orderStatusBadge(string $status): array
{
    return match ($status) {
        'pending' => ['label' => 'รอดำเนินการ', 'class' => 'badge-pending'],
        'approved' => ['label' => 'อนุมัติแล้ว', 'class' => 'badge-approved'],
        'completed' => ['label' => 'สำเร็จแล้ว', 'class' => 'badge-completed'],
        'rejected' => ['label' => 'ปฏิเสธ', 'class' => 'badge-rejected'],
        default => ['label' => $status, 'class' => 'badge-muted'],
    };
}

function bookingStatusBadge(string $status): array
{
    return match ($status) {
        'pending' => ['label' => 'รอยืนยัน', 'class' => 'badge-pending'],
        'awaiting_payment' => ['label' => 'รอชำระเงิน', 'class' => 'badge-info'],
        'confirmed' => ['label' => 'ยืนยันแล้ว', 'class' => 'badge-approved'],
        'cancelled' => ['label' => 'ยกเลิก', 'class' => 'badge-rejected'],
        default => ['label' => $status, 'class' => 'badge-muted'],
    };
}

$thaiMonths = [
    '',
    'ม.ค.',
    'ก.พ.',
    'มี.ค.',
    'เม.ย.',
    'พ.ค.',
    'มิ.ย.',
    'ก.ค.',
    'ส.ค.',
    'ก.ย.',
    'ต.ค.',
    'พ.ย.',
    'ธ.ค.',
];

$dashboardStats = [
    'sales_this_month' => 0.0,
    'sales_last_month' => 0.0,
    'bookings_this_month' => 0,
    'bookings_last_month' => 0,
    'pending_balance' => 0.0,
    'pending_booking_count' => 0,
    'active_products' => 0,
    'total_members' => 0,
    'completed_orders' => 0,
    'orders_pending' => 0,
    'orders_approved' => 0,
    'orders_rejected' => 0,
    'total_booking_value' => 0.0,
    'deposit_total' => 0.0,
    'balance_total' => 0.0,
    'confirmed_bookings' => 0,
];

$salesChart = [
    'day' => ['labels' => [], 'data' => []],
    'week' => ['labels' => [], 'data' => []],
    'month' => ['labels' => [], 'data' => []],
    'year' => ['labels' => [], 'data' => []],
];

$bookingStatusLabels = [];
$bookingStatusDatasets = [];
$recentOrders = [];
$recentBookings = [];

$r = safeQuery(
    $conn,
    "SELECT
        COALESCE(SUM(CASE
            WHEN order_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
             AND order_status <> 'rejected'
            THEN total_amount ELSE 0 END), 0) AS sales_this_month,
        COALESCE(SUM(CASE
            WHEN order_date >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
             AND order_date < DATE_FORMAT(CURDATE(), '%Y-%m-01')
             AND order_status <> 'rejected'
            THEN total_amount ELSE 0 END), 0) AS sales_last_month,
        SUM(order_status = 'completed') AS completed_orders,
        SUM(order_status = 'pending') AS orders_pending,
        SUM(order_status = 'approved') AS orders_approved,
        SUM(order_status = 'rejected') AS orders_rejected
     FROM orders"
);
if ($r && $row = $r->fetch_assoc()) {
    $dashboardStats['sales_this_month'] = (float) ($row['sales_this_month'] ?? 0);
    $dashboardStats['sales_last_month'] = (float) ($row['sales_last_month'] ?? 0);
    $dashboardStats['completed_orders'] = (int) ($row['completed_orders'] ?? 0);
    $dashboardStats['orders_pending'] = (int) ($row['orders_pending'] ?? 0);
    $dashboardStats['orders_approved'] = (int) ($row['orders_approved'] ?? 0);
    $dashboardStats['orders_rejected'] = (int) ($row['orders_rejected'] ?? 0);
}

$r = safeQuery(
    $conn,
    "SELECT
        SUM(CASE
            WHEN created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
            THEN 1 ELSE 0 END) AS bookings_this_month,
        SUM(CASE
            WHEN created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
             AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01')
            THEN 1 ELSE 0 END) AS bookings_last_month,
        COALESCE(SUM(CASE
            WHEN status IN ('pending', 'awaiting_payment')
            THEN balance_amount ELSE 0 END), 0) AS pending_balance,
        SUM(status IN ('pending', 'awaiting_payment')) AS pending_booking_count,
        COALESCE(SUM(CASE WHEN status = 'confirmed' THEN price_total ELSE 0 END), 0) AS total_booking_value,
        COALESCE(SUM(CASE WHEN status = 'confirmed' THEN deposit_amount ELSE 0 END), 0) AS deposit_total,
        COALESCE(SUM(CASE WHEN status = 'confirmed' THEN balance_amount ELSE 0 END), 0) AS balance_total,
        SUM(status = 'confirmed') AS confirmed_bookings,
        SUM(status = 'pending') AS booking_pending,
        SUM(status = 'awaiting_payment') AS booking_awaiting_payment,
        SUM(status = 'cancelled') AS booking_cancelled
     FROM bookings"
);
if ($r && $row = $r->fetch_assoc()) {
    $dashboardStats['bookings_this_month'] = (int) ($row['bookings_this_month'] ?? 0);
    $dashboardStats['bookings_last_month'] = (int) ($row['bookings_last_month'] ?? 0);
    $dashboardStats['pending_balance'] = (float) ($row['pending_balance'] ?? 0);
    $dashboardStats['pending_booking_count'] = (int) ($row['pending_booking_count'] ?? 0);
    $dashboardStats['total_booking_value'] = (float) ($row['total_booking_value'] ?? 0);
    $dashboardStats['deposit_total'] = (float) ($row['deposit_total'] ?? 0);
    $dashboardStats['balance_total'] = (float) ($row['balance_total'] ?? 0);
    $dashboardStats['confirmed_bookings'] = (int) ($row['confirmed_bookings'] ?? 0);
    $bookingStatusTotals = [
        (int) ($row['booking_pending'] ?? 0),
        (int) ($row['booking_awaiting_payment'] ?? 0),
        (int) ($row['confirmed_bookings'] ?? 0),
        (int) ($row['booking_cancelled'] ?? 0),
    ];
} else {
    $bookingStatusTotals = [0, 0, 0, 0];
}

$r = safeQuery($conn, "SELECT COUNT(*) AS cnt FROM products WHERE status = 'active'");
if ($r && $row = $r->fetch_assoc()) {
    $dashboardStats['active_products'] = (int) ($row['cnt'] ?? 0);
}

$r = safeQuery($conn, "SELECT COUNT(*) AS cnt FROM members WHERE status = 1");
if ($r && $row = $r->fetch_assoc()) {
    $dashboardStats['total_members'] = (int) ($row['cnt'] ?? 0);
}

$salesPeriods = [
    'day' => [
        'sql' => "SELECT DATE_FORMAT(order_date, '%d/%m') AS label,
                         COALESCE(SUM(total_amount), 0) AS total
                  FROM orders
                  WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                    AND order_status <> 'rejected'
                  GROUP BY DATE(order_date)
                  ORDER BY DATE(order_date)",
    ],
    'week' => [
        'sql' => "SELECT CONCAT('สัปดาห์ ', WEEK(order_date, 3)) AS label,
                         COALESCE(SUM(total_amount), 0) AS total
                  FROM orders
                  WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 7 WEEK)
                    AND order_status <> 'rejected'
                  GROUP BY YEARWEEK(order_date, 3)
                  ORDER BY YEARWEEK(order_date, 3)",
    ],
    'month' => [
        'sql' => "SELECT YEAR(order_date) AS order_year,
                         MONTH(order_date) AS order_month,
                         COALESCE(SUM(total_amount), 0) AS total
                  FROM orders
                  WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
                    AND order_status <> 'rejected'
                  GROUP BY YEAR(order_date), MONTH(order_date)
                  ORDER BY YEAR(order_date), MONTH(order_date)",
    ],
    'year' => [
        'sql' => "SELECT YEAR(order_date) AS label,
                         COALESCE(SUM(total_amount), 0) AS total
                  FROM orders
                  WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 4 YEAR)
                    AND order_status <> 'rejected'
                  GROUP BY YEAR(order_date)
                  ORDER BY YEAR(order_date)",
    ],
];

foreach ($salesPeriods as $period => $config) {
    $r = safeQuery($conn, $config['sql']);
    if (!$r) {
        continue;
    }

    while ($row = $r->fetch_assoc()) {
        if ($period === 'month') {
            $monthNumber = (int) ($row['order_month'] ?? 0);
            $salesChart[$period]['labels'][] = $thaiMonths[$monthNumber] ?? (string) $monthNumber;
        } else {
            $salesChart[$period]['labels'][] = (string) ($row['label'] ?? '-');
        }
        $salesChart[$period]['data'][] = (float) ($row['total'] ?? 0);
    }
}

$bookingStatusMap = [
    'pending' => ['label' => 'รอยืนยัน', 'color' => '#f59e0b'],
    'awaiting_payment' => ['label' => 'รอชำระเงิน', 'color' => '#3b82f6'],
    'confirmed' => ['label' => 'ยืนยันแล้ว', 'color' => '#10b981'],
    'cancelled' => ['label' => 'ยกเลิก', 'color' => '#ef4444'],
];

$bookingRaw = [];
$r = safeQuery(
    $conn,
    "SELECT YEAR(created_at) AS created_year,
            MONTH(created_at) AS created_month,
            status,
            COUNT(*) AS total
     FROM bookings
     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
     GROUP BY YEAR(created_at), MONTH(created_at), status
     ORDER BY YEAR(created_at), MONTH(created_at)"
);
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $monthIndex = (int) ($row['created_month'] ?? 0);
        $label = $thaiMonths[$monthIndex] ?? '-';
        $status = (string) ($row['status'] ?? 'pending');
        if (!isset($bookingRaw[$label])) {
            $bookingRaw[$label] = [];
        }
        $bookingRaw[$label][$status] = (int) ($row['total'] ?? 0);
    }
}

$bookingStatusLabels = array_keys($bookingRaw);
foreach ($bookingStatusMap as $status => $meta) {
    $dataset = [];
    foreach ($bookingStatusLabels as $label) {
        $dataset[] = $bookingRaw[$label][$status] ?? 0;
    }
    $bookingStatusDatasets[] = [
        'label' => $meta['label'],
        'data' => $dataset,
        'backgroundColor' => $meta['color'],
        'borderRadius' => 10,
        'maxBarThickness' => 24,
    ];
}

$r = safeQuery(
    $conn,
    "SELECT order_id, order_code, customer_name, order_date, total_amount, order_status
     FROM orders
     ORDER BY order_date DESC
     LIMIT 6"
);
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $recentOrders[] = $row;
    }
}

$r = safeQuery(
    $conn,
    "SELECT bookings_id, booking_code, guest_name, created_at, booking_date, booking_time, status
     FROM bookings
     ORDER BY created_at DESC
     LIMIT 6"
);
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $recentBookings[] = $row;
    }
}

$salesChange = 0.0;
if ($dashboardStats['sales_last_month'] > 0) {
    $salesChange = (($dashboardStats['sales_this_month'] - $dashboardStats['sales_last_month']) / $dashboardStats['sales_last_month']) * 100;
}

$bookingChange = 0.0;
if ($dashboardStats['bookings_last_month'] > 0) {
    $bookingChange = (($dashboardStats['bookings_this_month'] - $dashboardStats['bookings_last_month']) / $dashboardStats['bookings_last_month']) * 100;
}

$bookingLabels = array_map(
    static fn(array $item): string => $item['label'],
    array_values($bookingStatusMap)
);

adminPageStart('Dashboard');
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<style>
    :root {
        --dashboard-primary: var(--green);
        --dashboard-primary-dark: var(--green-dark);
        --dashboard-primary-light: var(--green-light);
        --dashboard-surface: #ffffff;
        --dashboard-surface-soft: #f8fafc;
        --dashboard-border: var(--border);
        --dashboard-text: var(--text);
        --dashboard-text-soft: var(--text-soft);
        --dashboard-success: #16a34a;
        --dashboard-warning: #f59e0b;
        --dashboard-danger: #ef4444;
        --dashboard-info: #3b82f6;
    }

    .page-content.dashboard-page {
        background:
            radial-gradient(circle at top right, rgba(13, 138, 146, 0.12), transparent 28%),
            linear-gradient(180deg, #f8fbfc 0%, #f3f7f8 100%);
    }

    .dashboard-shell {
        display: grid;
        gap: 24px;
    }

    .dashboard-hero {
        position: relative;
        overflow: hidden;
        padding: 28px;
        border: 1px solid rgba(1, 106, 112, 0.14);
        border-radius: 28px;
        background: linear-gradient(135deg, #ffffff 0%, #f2fbfb 52%, #e7f7f7 100%);
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    }

    .dashboard-hero::after {
        content: "";
        position: absolute;
        inset: auto -60px -80px auto;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(13, 138, 146, 0.18), rgba(13, 138, 146, 0));
        pointer-events: none;
    }

    .hero-topline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(1, 106, 112, 0.08);
        color: var(--dashboard-primary);
        font-size: 0.9rem;
        font-weight: 600;
    }

    .hero-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(320px, 1fr);
        gap: 24px;
        align-items: end;
        margin-top: 18px;
    }

    .hero-title {
        max-width: 10ch;
        margin: 0;
        font-size: clamp(2rem, 4vw, 3.35rem);
        line-height: 1.05;
        color: var(--dashboard-text);
    }

    .hero-copy {
        max-width: 58ch;
        margin: 12px 0 0;
        color: var(--dashboard-text-soft);
        line-height: 1.7;
    }

    .hero-meta {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .hero-stat {
        padding: 18px;
        border: 1px solid rgba(1, 106, 112, 0.1);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.78);
        backdrop-filter: blur(6px);
    }

    .hero-stat-label {
        color: var(--dashboard-text-soft);
        font-size: 0.92rem;
    }

    .hero-stat-value {
        margin-top: 8px;
        font-size: 1.55rem;
        font-weight: 700;
        color: var(--dashboard-text);
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .metric-card,
    .panel {
        background: var(--dashboard-surface);
        border: 1px solid var(--dashboard-border);
        border-radius: 24px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
    }

    .metric-card {
        padding: 20px;
    }

    .metric-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .metric-label {
        color: var(--dashboard-text-soft);
        font-size: 0.95rem;
    }

    .metric-value {
        margin-top: 8px;
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1.05;
        color: var(--dashboard-text);
    }

    .metric-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: rgba(1, 106, 112, 0.1);
        color: var(--dashboard-primary);
        font-size: 1.4rem;
    }

    .metric-trend {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 16px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 0.86rem;
        font-weight: 600;
    }

    .metric-trend.positive {
        background: rgba(22, 163, 74, 0.12);
        color: #166534;
    }

    .metric-trend.negative {
        background: rgba(239, 68, 68, 0.12);
        color: #991b1b;
    }

    .metric-trend.neutral {
        background: rgba(100, 116, 139, 0.14);
        color: #475569;
    }

    .dashboard-columns {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(320px, 0.95fr);
        gap: 24px;
    }

    .panel {
        padding: 22px;
    }

    .panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .panel-title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--dashboard-text);
    }

    .panel-desc {
        margin: 6px 0 0;
        color: var(--dashboard-text-soft);
        font-size: 0.95rem;
    }

    .chart-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .chart-btn {
        border: 1px solid var(--dashboard-border);
        background: #f8fafc;
        color: var(--dashboard-primary);
        padding: 9px 14px;
        border-radius: 999px;
        font: inherit;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .chart-btn.active,
    .chart-btn:hover {
        background: var(--dashboard-primary);
        border-color: var(--dashboard-primary);
        color: #ffffff;
    }

    .panel-action-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 0 14px;
        border-radius: 12px;
        background: var(--dashboard-primary);
        color: #ffffff;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 700;
        white-space: nowrap;
        transition: 0.2s ease;
    }

    .panel-action-link:hover {
        background: var(--dashboard-primary-dark);
        color: #ffffff;
        transform: translateY(-1px);
    }

    .chart-box {
        height: 340px;
    }

    .booking-summary {
        display: grid;
        gap: 14px;
    }

    .summary-row {
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: center;
        gap: 12px;
    }

    .summary-dot {
        width: 12px;
        height: 12px;
        border-radius: 999px;
    }

    .summary-label {
        color: var(--dashboard-text);
        font-weight: 500;
    }

    .summary-value {
        color: var(--dashboard-text-soft);
        font-weight: 600;
    }

    .summary-bar {
        grid-column: 2 / 4;
        width: 100%;
        height: 9px;
        border-radius: 999px;
        background: #eaf0f4;
        overflow: hidden;
    }

    .summary-bar > span {
        display: block;
        height: 100%;
        border-radius: inherit;
    }

    .table-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
    }

    .table-wrap {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 540px;
    }

    .data-table thead th {
        padding: 0 0 14px;
        border-bottom: 1px solid var(--dashboard-border);
        text-align: left;
        color: var(--dashboard-text-soft);
        font-size: 0.85rem;
        font-weight: 600;
    }

    .data-table tbody td {
        padding: 16px 0;
        border-bottom: 1px solid #edf2f7;
        color: var(--dashboard-text);
        vertical-align: middle;
    }

    .data-table tbody tr:last-child td {
        border-bottom: none;
    }

    .code-pill {
        display: inline-flex;
        padding: 7px 12px;
        border-radius: 999px;
        background: #f8fafc;
        color: var(--dashboard-primary);
        font-weight: 600;
        font-size: 0.9rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge-pending {
        background: rgba(245, 158, 11, 0.14);
        color: #92400e;
    }

    .badge-approved,
    .badge-completed {
        background: rgba(22, 163, 74, 0.12);
        color: #166534;
    }

    .badge-rejected {
        background: rgba(239, 68, 68, 0.12);
        color: #991b1b;
    }

    .badge-info {
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
    }

    .badge-muted {
        background: rgba(100, 116, 139, 0.14);
        color: #475569;
    }

    .empty-state {
        padding: 18px 0 6px;
        color: var(--dashboard-text-soft);
    }

    @media (max-width: 1280px) {
        .metrics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-columns,
        .table-grid,
        .hero-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .page-content.dashboard-page {
            padding: 18px;
        }

        .dashboard-hero,
        .panel,
        .metric-card {
            border-radius: 20px;
        }

        .dashboard-hero {
            padding: 22px;
        }

        .hero-meta,
        .metrics-grid {
            grid-template-columns: 1fr;
        }

        .panel-header {
            flex-direction: column;
        }

        .chart-box {
            height: 300px;
        }
    }
</style>

<div class="dashboard-shell">
    <section class="metrics-grid">
        <article class="metric-card">
            <div class="metric-top">
                <div>
                    <div class="metric-label">ยอดขายเดือนนี้</div>
                    <div class="metric-value">฿<?= formatCurrency($dashboardStats['sales_this_month']) ?></div>
                </div>
                <div class="metric-icon">
                    <i class='bx bx-line-chart'></i>
                </div>
            </div>
            <div class="metric-trend <?= $salesChange > 0 ? 'positive' : ($salesChange < 0 ? 'negative' : 'neutral') ?>">
                <i class='bx <?= $salesChange >= 0 ? 'bx-trending-up' : 'bx-trending-down' ?>'></i>
                <?= h(renderChangeText($salesChange)) ?>
            </div>
        </article>

        <article class="metric-card">
            <div class="metric-top">
                <div>
                    <div class="metric-label">การจองเดือนนี้</div>
                    <div class="metric-value"><?= formatNumber($dashboardStats['bookings_this_month']) ?></div>
                </div>
                <div class="metric-icon">
                    <i class='bx bx-calendar-check'></i>
                </div>
            </div>
            <div class="metric-trend <?= $bookingChange > 0 ? 'positive' : ($bookingChange < 0 ? 'negative' : 'neutral') ?>">
                <i class='bx <?= $bookingChange >= 0 ? 'bx-trending-up' : 'bx-trending-down' ?>'></i>
                <?= h(renderChangeText($bookingChange)) ?>
            </div>
        </article>

        <article class="metric-card">
            <div class="metric-top">
                <div>
                    <div class="metric-label">ค้างชำระ / รอดำเนินการ</div>
                    <div class="metric-value">฿<?= formatCurrency($dashboardStats['pending_balance']) ?></div>
                </div>
                <div class="metric-icon">
                    <i class='bx bx-wallet'></i>
                </div>
            </div>
            <div class="metric-trend neutral">
                <i class='bx bx-time-five'></i>
                <?= formatNumber($dashboardStats['pending_booking_count']) ?> รายการรอตรวจสอบ
            </div>
        </article>

        <article class="metric-card">
            <div class="metric-top">
                <div>
                    <div class="metric-label">สินค้าเปิดใช้งาน</div>
                    <div class="metric-value"><?= formatNumber($dashboardStats['active_products']) ?></div>
                </div>
                <div class="metric-icon">
                    <i class='bx bx-package'></i>
                </div>
            </div>
        </article>
    </section>

    <section class="dashboard-columns">
        <article class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">แนวโน้มยอดขาย</h2>
                    <p class="panel-desc">สลับดูข้อมูลรายวัน รายสัปดาห์ รายเดือน และรายปีได้ทันที</p>
                </div>

                <div class="chart-tabs">
                    <button class="chart-btn active" type="button" data-period="day">7 วัน</button>
                    <button class="chart-btn" type="button" data-period="week">8 สัปดาห์</button>
                    <button class="chart-btn" type="button" data-period="month">12 เดือน</button>
                    <button class="chart-btn" type="button" data-period="year">5 ปี</button>
                    <a class="panel-action-link" href="sales_report.php">
                        <i class='bx bx-bar-chart-alt-2'></i>
                        รายงานการขาย
                    </a>
                </div>
            </div>

            <div class="chart-box">
                <canvas id="salesChart"></canvas>
            </div>
        </article>

        <article class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">สถานะการจอง</h2>
                    <p class="panel-desc">ภาพรวมรายการจองล่าสุด แยกตามสถานะสำคัญของระบบ</p>
                </div>
            </div>

            <div class="chart-box" style="height: 220px; margin-bottom: 20px;">
                <canvas id="bookingChart"></canvas>
            </div>

            <div class="booking-summary">
                <?php
                $maxBookingStatus = max($bookingStatusTotals ?: [0]);
                foreach (array_values($bookingStatusMap) as $index => $meta):
                    $total = $bookingStatusTotals[$index] ?? 0;
                    $width = $maxBookingStatus > 0 ? ($total / $maxBookingStatus) * 100 : 0;
                ?>
                    <div class="summary-row">
                        <span class="summary-dot" style="background: <?= h($meta['color']) ?>;"></span>
                        <span class="summary-label"><?= h($meta['label']) ?></span>
                        <span class="summary-value"><?= formatNumber($total) ?></span>
                        <div class="summary-bar">
                            <span style="width: <?= number_format($width, 2, '.', '') ?>%; background: <?= h($meta['color']) ?>;"></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>

    <section class="table-grid">
        <article class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">ออเดอร์ล่าสุด</h2>
                    <p class="panel-desc">ติดตามคำสั่งซื้อที่เข้ามาล่าสุดและสถานะการดำเนินการ</p>
                </div>
            </div>

            <div class="table-wrap">
                <?php if ($recentOrders): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>รหัส</th>
                                <th>ลูกค้า</th>
                                <th>วันที่</th>
                                <th>ยอดรวม</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <?php $badge = orderStatusBadge((string) ($order['order_status'] ?? '')); ?>
                                <tr>
                                    <td><span class="code-pill"><?= h((string) ($order['order_code'] ?? '-')) ?></span></td>
                                    <td><?= h((string) ($order['customer_name'] ?? '-')) ?></td>
                                    <td><?= h(formatThaiDate((string) ($order['order_date'] ?? ''))) ?></td>
                                    <td>฿<?= formatCurrency((float) ($order['total_amount'] ?? 0)) ?></td>
                                    <td><span class="status-badge <?= h($badge['class']) ?>"><?= h($badge['label']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">ยังไม่มีข้อมูลออเดอร์ล่าสุด</div>
                <?php endif; ?>
            </div>
        </article>

        <article class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">การจองล่าสุด</h2>
                    <p class="panel-desc">ตรวจสอบรายการจองใหม่พร้อมวันใช้งานและสถานะล่าสุด</p>
                </div>
            </div>

            <div class="table-wrap">
                <?php if ($recentBookings): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>รหัส</th>
                                <th>ผู้จอง</th>
                                <th>วันที่จอง</th>
                                <th>วันใช้งาน</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $booking): ?>
                                <?php $badge = bookingStatusBadge((string) ($booking['status'] ?? '')); ?>
                                <tr>
                                    <td><span class="code-pill"><?= h((string) ($booking['booking_code'] ?? '-')) ?></span></td>
                                    <td><?= h((string) ($booking['guest_name'] ?? '-')) ?></td>
                                    <td><?= h(formatThaiDateTime((string) ($booking['created_at'] ?? ''))) ?></td>
                                    <td>
                                        <?= h(formatThaiDate((string) ($booking['booking_date'] ?? ''))) ?>
                                        <?= !empty($booking['booking_time']) ? ' ' . h((string) $booking['booking_time']) : '' ?>
                                    </td>
                                    <td><span class="status-badge <?= h($badge['class']) ?>"><?= h($badge['label']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">ยังไม่มีข้อมูลการจองล่าสุด</div>
                <?php endif; ?>
            </div>
        </article>
    </section>
</div>

<script>
    document.querySelector('.page-content')?.classList.add('dashboard-page');

    const salesChartData = <?= json_encode($salesChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const bookingLabels = <?= json_encode($bookingLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const bookingTotals = <?= json_encode($bookingStatusTotals, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const bookingStatusDatasets = <?= json_encode($bookingStatusDatasets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const bookingStatusLabels = <?= json_encode($bookingStatusLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    const salesCtx = document.getElementById('salesChart');
    const bookingCtx = document.getElementById('bookingChart');
    const chartButtons = document.querySelectorAll('.chart-btn');

    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesChartData.day.labels,
            datasets: [{
                label: 'ยอดขาย',
                data: salesChartData.day.data,
                borderColor: '#016A70',
                backgroundColor: 'rgba(1, 106, 112, 0.14)',
                fill: true,
                tension: 0.34,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.18)'
                    },
                    ticks: {
                        callback(value) {
                            return '฿' + Number(value).toLocaleString();
                        }
                    }
                }
            }
        }
    });

    chartButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const period = button.dataset.period;
            if (!period || !salesChartData[period]) {
                return;
            }

            chartButtons.forEach((item) => item.classList.remove('active'));
            button.classList.add('active');

            salesChart.data.labels = salesChartData[period].labels;
            salesChart.data.datasets[0].data = salesChartData[period].data;
            salesChart.update();
        });
    });

    new Chart(bookingCtx, {
        type: 'doughnut',
        data: {
            labels: bookingLabels,
            datasets: [{
                data: bookingTotals,
                backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>

<?php adminPageEnd(); ?>
