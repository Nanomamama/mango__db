<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

$admin_name = $_SESSION['admin_name'] ?? '';
$admin_email = $_SESSION['admin_email'] ?? '';

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

function formatCompactNumber(int|float $number): string
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
    'ธ.ค.'
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
$bookingDonutLabels = ['รอยืนยัน', 'รอชำระเงิน', 'ยืนยันแล้ว', 'ยกเลิก'];
$bookingDonutData = [0, 0, 0, 0];
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
    $bookingDonutData = [
        (int) ($row['booking_pending'] ?? 0),
        (int) ($row['booking_awaiting_payment'] ?? 0),
        (int) ($row['confirmed_bookings'] ?? 0),
        (int) ($row['booking_cancelled'] ?? 0),
    ];
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
        'borderRadius' => 12,
        'maxBarThickness' => 28,
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
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Mango Paradise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="./css/sidebar.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        :root {
            --bg: #f3f7f2;
            --surface: rgba(255, 255, 255, 0.88);
            --surface-strong: #ffffff;
            --text: #183227;
            --muted: #6e7f75;
            --primary: #2f7d32;
            --primary-deep: #1f5d28;
            --secondary: #f59e0b;
            --info: #2563eb;
            --success: #16a34a;
            --danger: #dc2626;
            --line: rgba(47, 125, 50, 0.1);
            --shadow: 0 22px 50px rgba(34, 70, 44, 0.08);
        }

        body {
            font-family: 'Kanit', sans-serif;
            background:
                radial-gradient(circle at top right, rgba(245, 158, 11, 0.12), transparent 24%),
                radial-gradient(circle at top left, rgba(47, 125, 50, 0.16), transparent 22%),
                linear-gradient(180deg, #f7fbf6 0%, #eef5ed 100%);
            color: var(--text);
            min-height: 100vh;
        }

        .dashboard-shell {
            width: 100%;
            max-width: 100%;
            min-height: 100vh;
            box-sizing: border-box;
            padding: 88px 28px 28px;
            transition: padding 0.3s ease;
        }

        body.admin-sidebar-layout .dashboard-shell {
            width: 100%;
        }

        body.admin-sidebar-layout:not(.admin-sidebar-collapsed) .dashboard-shell {
            padding-left: 32px;
        }

        body.admin-sidebar-layout.admin-sidebar-collapsed .dashboard-shell {
            padding-left: 28px;
        }

        .page-stack {
            display: grid;
            gap: 24px;
        }

        .hero-panel,
        .glass-card {
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            box-shadow: var(--shadow);
            border-radius: 28px;
        }

        .hero-panel {
            overflow: hidden;
        }

        .hero-inner {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 22px;
            padding: 28px;
            background:
                linear-gradient(135deg, rgba(47, 125, 50, 0.96), rgba(25, 83, 36, 0.92)),
                linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0));
            color: #fff;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 0.92rem;
            margin-bottom: 14px;
        }

        .hero-title {
            font-size: clamp(2rem, 4vw, 3.1rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 12px;
        }

        .hero-text {
            max-width: 760px;
            color: rgba(255, 255, 255, 0.86);
            margin-bottom: 24px;
        }

        .hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }

        .hero-chip {
            min-width: 180px;
            padding: 14px 16px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .hero-chip-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 4px;
        }

        .hero-chip-value {
            font-size: 1.35rem;
            font-weight: 700;
        }

        .hero-side {
            display: grid;
            gap: 14px;
            align-content: start;
        }

        .account-card,
        .summary-card {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 22px;
            padding: 18px;
        }

        .account-top {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .avatar {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            font-size: 1.25rem;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .summary-grid {
            display: grid;
            gap: 10px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.96rem;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 18px;
        }

        .kpi-card {
            padding: 22px;
            position: relative;
            overflow: hidden;
        }

        .kpi-card::after {
            content: "";
            position: absolute;
            inset: auto -30px -30px auto;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(47, 125, 50, 0.12), rgba(245, 158, 11, 0.03));
        }

        .kpi-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .kpi-label {
            color: var(--muted);
            font-size: 0.95rem;
            margin-bottom: 6px;
        }

        .kpi-value {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .kpi-sub {
            color: var(--muted);
            font-size: 0.92rem;
        }

        .kpi-icon {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            font-size: 1.3rem;
            background: rgba(47, 125, 50, 0.08);
            color: var(--primary);
            flex-shrink: 0;
        }

        .kpi-trend {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.84rem;
            font-weight: 600;
        }

        .trend-up {
            background: rgba(22, 163, 74, 0.12);
            color: var(--success);
        }

        .trend-down {
            background: rgba(220, 38, 38, 0.1);
            color: var(--danger);
        }

        .section-card {
            padding: 24px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 22px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
        }

        .section-desc {
            color: var(--muted);
            margin: 4px 0 0;
            font-size: 0.94rem;
        }

        .chart-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .chart-tab {
            border: 1px solid rgba(47, 125, 50, 0.14);
            background: #fff;
            color: var(--primary);
            border-radius: 999px;
            padding: 9px 14px;
            font-size: 0.92rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .chart-tab.active,
        .chart-tab:hover {
            background: var(--primary);
            color: #fff;
        }

        .chart-box {
            position: relative;
            height: 360px;
        }

        .insight-grid {
            display: grid;
            grid-template-columns: 1.35fr 1fr;
            gap: 20px;
        }

        .finance-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .finance-card {
            padding: 22px;
            text-align: center;
        }

        .finance-card .value {
            font-size: 2rem;
            font-weight: 800;
            margin: 8px 0 4px;
        }

        .finance-card .label {
            color: var(--muted);
            font-size: 0.95rem;
        }

        .tables-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .table-shell {
            overflow: hidden;
        }

        .table-modern {
            margin: 0;
        }

        .table-modern thead th {
            border: 0;
            background: rgba(47, 125, 50, 0.06);
            color: var(--muted);
            font-weight: 600;
            font-size: 0.9rem;
            padding: 14px 16px;
            white-space: nowrap;
        }

        .table-modern tbody td {
            border-color: rgba(47, 125, 50, 0.08);
            padding: 14px 16px;
            vertical-align: middle;
        }

        .table-modern tbody tr:hover {
            background: rgba(47, 125, 50, 0.03);
        }

        .code-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(47, 125, 50, 0.08);
            color: var(--primary-deep);
            font-weight: 600;
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 12px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.82rem;
        }

        .badge-pending {
            background: rgba(245, 158, 11, 0.14);
            color: #a16207;
        }

        .badge-approved {
            background: rgba(22, 163, 74, 0.14);
            color: #166534;
        }

        .badge-completed {
            background: rgba(37, 99, 235, 0.14);
            color: #1d4ed8;
        }

        .badge-rejected {
            background: rgba(220, 38, 38, 0.12);
            color: #b91c1c;
        }

        .badge-info {
            background: rgba(59, 130, 246, 0.12);
            color: #1d4ed8;
        }

        .badge-muted {
            background: rgba(107, 114, 128, 0.12);
            color: #4b5563;
        }

        .empty-state {
            display: grid;
            place-items: center;
            min-height: 240px;
            color: var(--muted);
            text-align: center;
            gap: 10px;
        }

        .quick-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .quick-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 14px;
            border-radius: 14px;
            background: rgba(47, 125, 50, 0.08);
            text-decoration: none;
            color: var(--primary-deep);
            font-weight: 600;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .quick-link:hover {
            transform: translateY(-1px);
            background: rgba(47, 125, 50, 0.14);
            color: var(--primary-deep);
        }

        @media (max-width: 1399.98px) {
            .kpi-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 1199.98px) {
            .hero-inner,
            .insight-grid,
            .tables-grid,
            .finance-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991.98px) {
            .dashboard-shell {
                padding: 18px;
                padding-top: 74px;
            }

            body.admin-sidebar-layout:not(.admin-sidebar-collapsed) .dashboard-shell,
            body.admin-sidebar-layout.admin-sidebar-collapsed .dashboard-shell {
                padding-left: 18px;
            }

            .kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .hero-inner,
            .section-card,
            .kpi-card {
                padding: 18px;
            }
        }

        @media (max-width: 575.98px) {
            .kpi-grid {
                grid-template-columns: 1fr;
            }

            .hero-chip {
                min-width: 100%;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body class="admin-sidebar-layout">
<?php include 'sidebar.php'; ?>

    <main class="dashboard-shell">
            <div class="page-stack">
                <section class="hero-panel">
                    <div class="hero-inner">
                        <div>
                            <div class="hero-eyebrow">
                                <i class="bi bi-graph-up-arrow"></i>
                                ภาพรวมระบบผู้ดูแล Mango Paradise
                            </div>
                            <h1 class="hero-title">แดชบอร์ดแอดมินที่เห็นทั้งยอดขาย การจอง และสถานะระบบในหน้าเดียว</h1>
                            <p class="hero-text">
                                หน้านี้ดึงข้อมูลจริงจากคำสั่งซื้อ การจองสินค้า และสมาชิก เพื่อให้ติดตามภาพรวมธุรกิจได้ทันทีแบบเรียลไทม์มากขึ้น
                            </p>
                            <div class="hero-meta">
                                <div class="hero-chip">
                                    <div class="hero-chip-label">ยอดขายเดือนนี้</div>
                                    <div class="hero-chip-value">฿<?= formatCurrency($dashboardStats['sales_this_month']) ?></div>
                                </div>
                                <div class="hero-chip">
                                    <div class="hero-chip-label">การจองที่ยืนยันแล้ว</div>
                                    <div class="hero-chip-value"><?= formatCompactNumber($dashboardStats['confirmed_bookings']) ?> รายการ</div>
                                </div>
                                <div class="hero-chip">
                                    <div class="hero-chip-label">รายการที่ต้องติดตาม</div>
                                    <div class="hero-chip-value"><?= formatCompactNumber($dashboardStats['pending_booking_count'] + $dashboardStats['orders_pending']) ?> รายการ</div>
                                </div>
                            </div>
                        </div>

                        <div class="hero-side">
                            <div class="account-card">
                                <div class="account-top">
                                    <div class="avatar">
                                        <i class="bi bi-person-gear"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-5"><?= htmlspecialchars($admin_name) ?: 'ผู้ดูแลระบบ' ?></div>
                                        <div class="text-white-50 small"><?= htmlspecialchars($admin_email) ?: 'Admin account' ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="summary-card">
                                <div class="fw-semibold mb-3">สรุปด่วนของวันนี้</div>
                                <div class="summary-grid">
                                    <div class="summary-item">
                                        <span>ออเดอร์รอดำเนินการ</span>
                                        <strong><?= formatCompactNumber($dashboardStats['orders_pending']) ?></strong>
                                    </div>
                                    <div class="summary-item">
                                        <span>รอชำระ/รอยืนยันการจอง</span>
                                        <strong><?= formatCompactNumber($dashboardStats['pending_booking_count']) ?></strong>
                                    </div>
                                    <div class="summary-item">
                                        <span>สินค้าที่เปิดขาย</span>
                                        <strong><?= formatCompactNumber($dashboardStats['active_products']) ?></strong>
                                    </div>
                                    <div class="summary-item">
                                        <span>สมาชิกในระบบ</span>
                                        <strong><?= formatCompactNumber($dashboardStats['total_members']) ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="kpi-grid">
                    <article class="glass-card kpi-card">
                        <div class="kpi-top">
                            <div>
                                <div class="kpi-label">ยอดขายเดือนนี้</div>
                                <div class="kpi-value">฿<?= formatCurrency($dashboardStats['sales_this_month']) ?></div>
                            </div>
                            <div class="kpi-icon"><i class="bi bi-cash-coin"></i></div>
                        </div>
                        <div class="kpi-trend <?= $salesChange >= 0 ? 'trend-up' : 'trend-down' ?>">
                            <i class="bi <?= $salesChange >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' ?>"></i>
                            <?= renderChangeText($salesChange) ?>
                        </div>
                    </article>

                    <article class="glass-card kpi-card">
                        <div class="kpi-top">
                            <div>
                                <div class="kpi-label">การจองเดือนนี้</div>
                                <div class="kpi-value"><?= formatCompactNumber($dashboardStats['bookings_this_month']) ?></div>
                            </div>
                            <div class="kpi-icon"><i class="bi bi-calendar-check"></i></div>
                        </div>
                        <div class="kpi-trend <?= $bookingChange >= 0 ? 'trend-up' : 'trend-down' ?>">
                            <i class="bi <?= $bookingChange >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' ?>"></i>
                            <?= renderChangeText($bookingChange) ?>
                        </div>
                    </article>

                    <article class="glass-card kpi-card">
                        <div class="kpi-top">
                            <div>
                                <div class="kpi-label">ยอดค้างชำระรวม</div>
                                <div class="kpi-value">฿<?= formatCurrency($dashboardStats['pending_balance']) ?></div>
                            </div>
                            <div class="kpi-icon"><i class="bi bi-wallet2"></i></div>
                        </div>
                        <div class="kpi-sub">เฉพาะรายการจองที่ยังไม่ปิดสถานะ</div>
                    </article>

                    <article class="glass-card kpi-card">
                        <div class="kpi-top">
                            <div>
                                <div class="kpi-label">รายการรอยืนยัน</div>
                                <div class="kpi-value"><?= formatCompactNumber($dashboardStats['pending_booking_count']) ?></div>
                            </div>
                            <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
                        </div>
                        <div class="kpi-sub">การจองที่ยังต้องติดตาม</div>
                    </article>

                    <article class="glass-card kpi-card">
                        <div class="kpi-top">
                            <div>
                                <div class="kpi-label">สินค้าที่เปิดขาย</div>
                                <div class="kpi-value"><?= formatCompactNumber($dashboardStats['active_products']) ?></div>
                            </div>
                            <div class="kpi-icon"><i class="bi bi-box-seam"></i></div>
                        </div>
                        <div class="kpi-sub">จำนวนสินค้า active ในระบบ</div>
                    </article>

                    <article class="glass-card kpi-card">
                        <div class="kpi-top">
                            <div>
                                <div class="kpi-label">สมาชิกทั้งหมด</div>
                                <div class="kpi-value"><?= formatCompactNumber($dashboardStats['total_members']) ?></div>
                            </div>
                            <div class="kpi-icon"><i class="bi bi-people"></i></div>
                        </div>
                        <div class="kpi-sub">สมาชิกที่ใช้งานได้ในระบบ</div>
                    </article>
                </section>

                <section class="glass-card section-card">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">ภาพรวมยอดขาย</h2>
                            <p class="section-desc">สลับดูข้อมูลตามช่วงเวลาได้ทันทีจากข้อมูลคำสั่งซื้อจริง</p>
                        </div>
                        <div class="chart-actions">
                            <button class="chart-tab active" type="button" data-period="day">7 วันล่าสุด</button>
                            <button class="chart-tab" type="button" data-period="week">8 สัปดาห์</button>
                            <button class="chart-tab" type="button" data-period="month">12 เดือน</button>
                            <button class="chart-tab" type="button" data-period="year">5 ปี</button>
                        </div>
                    </div>
                    <div class="chart-box">
                        <canvas id="salesChart"></canvas>
                    </div>
                </section>

                <section class="insight-grid">
                    <article class="glass-card section-card">
                        <div class="section-header">
                            <div>
                                <h2 class="section-title">การจองแยกตามสถานะ</h2>
                                <p class="section-desc">เปรียบเทียบสถานะการจองในรอบ 12 เดือนล่าสุด</p>
                            </div>
                        </div>
                        <div class="chart-box">
                            <canvas id="bookingStatusChart"></canvas>
                        </div>
                    </article>

                    <article class="glass-card section-card">
                        <div class="section-header">
                            <div>
                                <h2 class="section-title">สัดส่วนสถานะการจอง</h2>
                                <p class="section-desc">ภาพรวมจำนวนการจองทั้งหมดแยกตามสถานะปัจจุบัน</p>
                            </div>
                        </div>
                        <div class="chart-box">
                            <canvas id="bookingDonutChart"></canvas>
                        </div>
                    </article>
                </section>

                <section class="finance-grid">
                    <article class="glass-card finance-card">
                        <div class="label">มูลค่าการจองที่ยืนยันแล้ว</div>
                        <div class="value text-success">฿<?= formatCurrency($dashboardStats['total_booking_value']) ?></div>
                        <div class="text-muted small">รวมเฉพาะสถานะ confirmed</div>
                    </article>
                    <article class="glass-card finance-card">
                        <div class="label">ยอดมัดจำที่ได้รับ</div>
                        <div class="value" style="color: var(--info);">฿<?= formatCurrency($dashboardStats['deposit_total']) ?></div>
                        <div class="text-muted small">ยอดรับล่วงหน้าจากการจอง</div>
                    </article>
                    <article class="glass-card finance-card">
                        <div class="label">ยอดคงเหลือที่ต้องชำระ</div>
                        <div class="value" style="color: var(--secondary);">฿<?= formatCurrency($dashboardStats['balance_total']) ?></div>
                        <div class="text-muted small">เฉพาะการจองที่ยืนยันแล้ว</div>
                    </article>
                </section>

                <section class="tables-grid">
                    <article class="glass-card section-card table-shell">
                        <div class="section-header">
                            <div>
                                <h2 class="section-title">ออเดอร์ล่าสุด</h2>
                                <p class="section-desc">คำสั่งซื้อล่าสุดจากลูกค้าในระบบ</p>
                            </div>
                            <a href="./manage_orders.php" class="quick-link">ดูทั้งหมด <i class="bi bi-arrow-right"></i></a>
                        </div>

                        <?php if (empty($recentOrders)): ?>
                            <div class="empty-state">
                                <i class="bi bi-bag-x fs-1"></i>
                                <div>ยังไม่มีข้อมูลออเดอร์ล่าสุด</div>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-modern align-middle">
                                    <thead>
                                        <tr>
                                            <th>รหัสออเดอร์</th>
                                            <th>ลูกค้า</th>
                                            <th class="text-end">ยอดรวม</th>
                                            <th>สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentOrders as $order): ?>
                                            <?php $badge = orderStatusBadge((string) $order['order_status']); ?>
                                            <tr>
                                                <td><span class="code-pill">#<?= htmlspecialchars((string) $order['order_code']) ?></span></td>
                                                <td><?= htmlspecialchars((string) $order['customer_name']) ?></td>
                                                <td class="text-end fw-semibold">฿<?= formatCurrency((float) $order['total_amount']) ?></td>
                                                <td><span class="badge-soft <?= $badge['class'] ?>"><?= $badge['label'] ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </article>

                    <article class="glass-card section-card table-shell">
                        <div class="section-header">
                            <div>
                                <h2 class="section-title">การจองล่าสุด</h2>
                                <p class="section-desc">รายการจองล่าสุดที่เข้ามาในระบบ</p>
                            </div>
                            <a href="./booking_list.php" class="quick-link">ดูทั้งหมด <i class="bi bi-arrow-right"></i></a>
                        </div>

                        <?php if (empty($recentBookings)): ?>
                            <div class="empty-state">
                                <i class="bi bi-calendar-x fs-1"></i>
                                <div>ยังไม่มีข้อมูลการจองล่าสุด</div>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-modern align-middle">
                                    <thead>
                                        <tr>
                                            <th>รหัสจอง</th>
                                            <th>ผู้จอง</th>
                                            <th>วันเข้าชม</th>
                                            <th>สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentBookings as $booking): ?>
                                            <?php
                                            $badge = bookingStatusBadge((string) $booking['status']);
                                            $visitDateTime = trim((string) $booking['booking_date'] . ' ' . (string) $booking['booking_time']);
                                            ?>
                                            <tr>
                                                <td><span class="code-pill">#<?= htmlspecialchars((string) $booking['booking_code']) ?></span></td>
                                                <td><?= htmlspecialchars((string) $booking['guest_name']) ?></td>
                                                <td><?= formatThaiDateTime($visitDateTime) ?></td>
                                                <td><span class="badge-soft <?= $badge['class'] ?>"><?= $badge['label'] ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </article>
                </section>
            </div>
        </main>
    </div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const salesChartData = <?= json_encode($salesChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const bookingStatusLabels = <?= json_encode($bookingStatusLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const bookingStatusDatasets = <?= json_encode($bookingStatusDatasets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const bookingDonutLabels = <?= json_encode($bookingDonutLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const bookingDonutData = <?= json_encode($bookingDonutData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    const currencyTick = (value) => {
        return '฿' + Number(value).toLocaleString('th-TH');
    };

    const commonGridColor = 'rgba(47, 125, 50, 0.10)';

    const salesCtx = document.getElementById('salesChart');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesChartData.day.labels,
            datasets: [{
                label: 'ยอดขาย',
                data: salesChartData.day.data,
                borderColor: '#2f7d32',
                backgroundColor: 'rgba(47, 125, 50, 0.12)',
                fill: true,
                tension: 0.35,
                borderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#2f7d32'
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: (context) => 'ยอดขาย: ' + currencyTick(context.raw)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: commonGridColor
                    },
                    ticks: {
                        callback: currencyTick
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    document.querySelectorAll('.chart-tab').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.chart-tab').forEach((item) => item.classList.remove('active'));
            button.classList.add('active');

            const period = button.dataset.period;
            const source = salesChartData[period];

            salesChart.data.labels = source.labels;
            salesChart.data.datasets[0].data = source.data;
            salesChart.update();
        });
    });

    const bookingStatusCtx = document.getElementById('bookingStatusChart');
    new Chart(bookingStatusCtx, {
        type: 'bar',
        data: {
            labels: bookingStatusLabels,
            datasets: bookingStatusDatasets
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 10
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: commonGridColor
                    }
                },
                x: {
                    stacked: false,
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    const bookingDonutCtx = document.getElementById('bookingDonutChart');
    new Chart(bookingDonutCtx, {
        type: 'doughnut',
        data: {
            labels: bookingDonutLabels,
            datasets: [{
                data: bookingDonutData,
                backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 10,
                        padding: 18
                    }
                }
            }
        }
    });
</script>
</body>
</html>
