<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';
require_once 'sidebar.php';

// ===== รายการออเดอร์ =====
$allowedStatuses = ['all', 'pending', 'approved', 'rejected', 'completed'];
$status = $_GET['status'] ?? 'all';
$status = in_array($status, $allowedStatuses, true) ? $status : 'all';
$displayLimit = 9;
$currentPage = max(1, (int)($_GET['page'] ?? 1));

$where = "";
$params = [];

if ($status != 'all') {
    $where = "WHERE o.order_status = ?";
    $params[] = $status;
}

$countSql = "
SELECT COUNT(*) as total_orders
FROM orders o
$where
";

$countStmt = $conn->prepare($countSql);
if ($params) {
    $countStmt->bind_param("s", ...$params);
}
$countStmt->execute();
$totalFilteredOrders = (int)($countStmt->get_result()->fetch_assoc()['total_orders'] ?? 0);
$totalPages = max(1, (int)ceil($totalFilteredOrders / $displayLimit));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $displayLimit;
$showingFrom = $totalFilteredOrders > 0 ? $offset + 1 : 0;
$showingTo = min($offset + $displayLimit, $totalFilteredOrders);

$sql = "
SELECT 
    o.*
FROM orders o
$where
ORDER BY o.order_date DESC
LIMIT $displayLimit OFFSET $offset
";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param("s", ...$params);
}
$stmt->execute();
$result = $stmt->get_result();



// ===== สถิติออเดอร์ =====


$sql_stats = "
SELECT 
    COUNT(*) as total_count, -- ใช้สำหรับ filter (ทั้งหมดจริง)
    SUM(order_status = 'completed') as total_completed, -- ใช้แสดงออเดอร์ทั้งหมด (ที่คุณต้องการ)
    SUM(order_status = 'pending') as pending_count,
    SUM(order_status = 'approved') as approved_count,
    SUM(order_status = 'rejected') as rejected_count,
    SUM(order_status = 'completed') as completed_count
  
FROM orders
";

$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();


// ===== สถิติยอดเงิน ทั้งหมด ที่สำเร็จ =====
$sql_revenue = "
SELECT COALESCE(SUM(total_amount), 0) as revenue
FROM orders
WHERE order_status = 'completed'
";

$result_revenue = $conn->query($sql_revenue);
$overallRevenueStats = $result_revenue->fetch_assoc();


// ===== สถิติออเดอร์วันนี้ =====
$todayStart = date('Y-m-d 00:00:00');
$todayEnd = date('Y-m-d 23:59:59');

$sql_today_stats = "
SELECT
    COUNT(order_id) as total_count,
    SUM(order_status = 'pending') as pending_count,
    SUM(order_status = 'approved') as approved_count,
    SUM(order_status = 'completed') as completed_count,
    COALESCE(SUM(
        CASE 
            WHEN order_status = 'completed' THEN total_amount 
            ELSE 0 
        END
    ), 0) as revenue
FROM orders
WHERE receive_datetime BETWEEN ? AND ?
";

$stmt_today_stats = $conn->prepare($sql_today_stats);
$stmt_today_stats->bind_param("ss", $todayStart, $todayEnd);
$stmt_today_stats->execute();
$todayStats = $stmt_today_stats->get_result()->fetch_assoc();


// keep $todayStats from query above (do not overwrite)

$adminPageExtraHead = <<<'HTML'
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
        :root {
            --primary-color: #2ecc71;
            --secondary-color: #27ae60;
            --warning-color: #f3e412;
            --danger-color: #e74c3c;
            --info-color: #3498db;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', 'Prompt', sans-serif;
            margin-left: 250px;
            padding: 20px;
            max-width: calc(100vw - 250px);
            overflow-x: hidden;
        }

        .stat-card {
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            height: 100%;
            margin-bottom: 20px;
        }

        .stats-layout {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stats-group {
            background: #fff;
            border: 1px solid #edf0f3;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.04);
        }

        .stats-group-title {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #2c3e50;
            font-size: 1rem;
            font-weight: 700;
            margin: 0 0 14px;
        }

        .stats-group-title i {
            color: #4361ec;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .stats-grid .stat-card {
            border: 1px solid #f1f3f5;
            box-shadow: none;
            margin-bottom: 0;
            min-width: 0;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .icon-all {
            background-color: rgba(149, 165, 166, 0.1);
            color: #0aff0a;
        }

        .icon-all-today {
            background-color: rgba(149, 165, 166, 0.1);
            color: #f94df9;
        }

        .icon-pending {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }

        .icon-approved {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--primary-color);
        }

        .icon-rejected {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .icon-completed {
            background-color: rgba(13, 202, 240, 0.12);
            color: #0c8599;
        }

        .icon-today {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--info-color);
        }

        .icon-revenue {
            background-color: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-title {
            color: #666;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .dashboard-card {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.3rem;
            margin: 0;
        }

        .status-badge {
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-pending {
            background-color: #ffe600;
            color: #000000;

        }

        .badge-approved {
            background-color: #78ff3a;
            color: #000000;

        }

        .badge-rejected {
            background-color: #f30116;
            color: #ffffff;

        }

        .badge-completed {
            background-color: #0dcaf0;
            color: #062c33;
        }


        .btn-action {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: 0.2s;
        }

        /* ดูรายละเอียด = primary */
        .btn-view {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #38bdf8;
        }

        .btn-view:hover {
            background: #bae6fd;
        }

        /* ยืนยัน = success */
        .btn-approve {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #4ade80;
        }

        .btn-approve:hover {
            background: #bbf7d0;
        }

        /* ปฏิเสธ = danger (ชัดที่สุด) */
        .btn-reject {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #f87171;

        }

        .btn-reject:hover {
            background: #fecaca;
        }


        .btn-view {
            background-color: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }

        .btn-view:hover {
            background-color: #b8daff;
        }

        .btn-filter {
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 500;
            border: 2px solid transparent;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 42px;
        }

        .btn-filter-all {
            background-color: #e9ecef;
            color: #495057;
        }

        .btn-filter-all.active {
            background-color: #6c757d;
            color: white;
            border-color: #6c757d;
        }

        .btn-filter-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .btn-filter-pending.active {
            background-color: #ffea07;
            color: #212529;
            border-color: #ffc107;
        }

        .btn-filter-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .btn-filter-approved.active {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }

        .btn-filter-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .btn-filter-rejected.active {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        .btn-filter-completed {
            background-color: #cff4fc;
            color: #055160;
        }

        .btn-filter-completed.active {
            background-color: #0dcaf0;
            color: #062c33;
            border-color: #0dcaf0;
        }

        .navbar {
            background-color: #4361ec;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 27px 25px;
            border-radius: 50px;

        }

        @media (max-width: 768px) {
            .order-table {
                display: block;
                overflow-x: auto;
            }
        }

        .order-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .order-filter-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .pagination-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            border-top: 1px solid #edf0f3;
            margin-top: 20px;
            padding-top: 18px;
        }

        .pagination-info {
            color: #64748b;
            font-size: 0.92rem;
            font-weight: 500;
        }

        .pagination-list {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .page-link-local {
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            border-radius: 10px;
            border: 1px solid #dbe2ea;
            color: #334155;
            background: #fff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            transition: all 0.2s;
        }

        .page-link-local:hover {
            border-color: #4361ec;
            color: #4361ec;
            background: #f5f7ff;
        }

        .page-link-local.active {
            border-color: #4361ec;
            background: #4361ec;
            color: #fff;
        }

        .page-link-local.disabled {
            pointer-events: none;
            color: #a0aec0;
            background: #f8fafc;
        }

        @media (max-width: 992px) {
            .order-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .order-grid {
                grid-template-columns: 1fr;
            }
        }

        .order-card {
            background: #fff;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
            min-height: 210px;
        }

        .order-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .order-card-body p {
            margin: 4px 0;
            font-size: 14px;
        }

        .order-card-body .total {
            font-size: 18px;
            font-weight: bold;
            color: #00be4f;
            margin-top: 10px;
        }

        .badge-new {
            background-color: #ff4757;
            color: white;
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 100px;
            font-weight: 700;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        @keyframes blink {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0.4; }
    }


    .order-amount {
        font-size: 22px;
        font-weight: 500;
        color: #2c3e50;
    }

    .order-amount span {
        font-size: 13px;
        color: #888;
        font-weight: 400;
        margin-left: 4px;
    }

    .order-divider {
        border: none;
        border-top: 0.5px solid #e5e7eb;
        margin: 4px 0;
    }

    .order-info {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .info-row {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #555;
    }

    .info-row strong {
        color: #2c3e50;
        font-weight: 500;
        font-size: 18px;
    }

    .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .dot-pickup  { background: #1D9E75; }
    .dot-deliver { background: #378ADD; }

    .order-date-small {
        font-size: 11px;
        color: #aaa;
    }

    .btn-detail {
        display: block;
        text-align: center;
        padding: 8px;
        border-radius: 8px;
        border: 0.5px solid #d1d5db;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        background: #85c2ff;
        text-decoration: none;
        transition: background 0.15s;
    }

    .btn-detail:hover {
        background: #f3f4f6;
        color: #111;
    }
        </style>

    <style>
        body {
            margin-left: 0 !important;
            padding: 0 !important;
            max-width: none !important;
            overflow-x: hidden;
        }

        .page-content {
            width: 100%;
            max-width: 100%;
        }

        .admin-local-page {
            margin-left: 0 !important;
            padding: 0 !important;
            min-height: auto !important;
            width: 100%;
        }

        .navbar,
        .dashboard-header,
        .header-card,
        .filter-section,
        .dashboard-card,
        .card-form,
        .table-container,
        .course-card,
        .modal-content {
            max-width: 100%;
        }

        .card-header,
        .dashboard-header .d-flex,
        .header-card,
        .action-row,
        .course-card-header {
            min-width: 0;
        }

        .filter-buttons,
        .order-filter-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-filter,
        .filter-btn,
        .btn-add-large,
        .btn-add-course,
        .btn-action,
        .action-btn {
            white-space: normal;
            text-align: center;
        }

        .order-filter-list {
            margin-bottom: 0 !important;
        }

        .product-table th,
        .product-table td {
            white-space: nowrap;
        }

        .table-container {
            -webkit-overflow-scrolling: touch;
        }

        .current-image-card,
        .preview-container {
            min-width: 0;
        }

        .current-image-details,
        .preview-details,
        .title-section,
        .course-card-header h5,
        .card-title {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        @media (max-width: 1024px) {
            .page-content {
                padding: 22px;
            }

            .stats-layout {
                grid-template-columns: 1fr;
            }

            .order-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .page-content {
                padding: 16px;
            }

            .navbar,
            .dashboard-header,
            .header-card,
            .filter-section,
            .dashboard-card,
            .card-form {
                border-radius: 16px !important;
                padding: 18px !important;
            }

            .navbar .d-flex,
            .card-header,
            .dashboard-header .d-flex,
            .header-card,
            .action-row,
            .course-card-header,
            .d-flex.justify-content-between.align-items-center.mb-4 {
                align-items: stretch !important;
                flex-direction: column;
                gap: 12px;
            }

            .navbar h2,
            .dashboard-title,
            .title-section h1,
            .page-title {
                font-size: 1.45rem !important;
                line-height: 1.3;
            }

            .stat-card,
            .course-card-body {
                padding: 16px;
            }

            .stats-group {
                padding: 16px;
            }

            .stat-number,
            .stats-value {
                font-size: 1.45rem;
            }

            .order-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .order-card .d-flex.justify-content-between.align-items-center {
                align-items: flex-start !important;
                flex-direction: column;
                gap: 8px;
            }

            .btn-filter,
            .filter-btn,
            .btn-add-large,
            .btn-add-course,
            .search-box button,
            .btn-action,
            .action-btn {
                width: 100%;
                justify-content: center;
            }

            .pagination-wrap {
                align-items: stretch;
                flex-direction: column;
            }

            .pagination-list {
                justify-content: center;
                width: 100%;
            }

            .page-link-local {
                flex: 1 1 42px;
            }

            .search-box {
                flex-direction: column;
                max-width: none;
            }

            .search-box input,
            .search-box button,
            .form-control,
            .form-select {
                width: 100%;
                min-width: 0;
            }

            .product-table {
                min-width: 760px;
            }

            .current-image-card,
            .preview-container {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
                gap: 1rem;
            }

            .current-image,
            .preview-image {
                width: min(100%, 180px);
                height: 150px;
                margin-inline: auto;
            }

            .section-title {
                font-size: 1.35rem;
                align-items: flex-start;
            }

            .modal-dialog {
                margin: .75rem;
            }

            .modal-body {
                padding: 1rem !important;
            }
        }

        @media (max-width: 480px) {
            .page-content {
                padding: 12px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .navbar,
            .dashboard-header,
            .header-card,
            .filter-section,
            .dashboard-card,
            .card-form {
                padding: 14px !important;
            }

            .admin-card {
                width: 100%;
                border-radius: 18px;
                padding: 10px 12px;
            }

            .admin-card img,
            .admin-profile img {
                width: 40px;
                height: 40px;
            }

            .order-amount {
                font-size: 1.25rem;
            }

            .status-badge,
            .badge-count,
            .seasonal-tag {
                margin-left: 0;
                margin-top: 6px;
            }

            .product-name {
                white-space: normal;
            }
        }

        
    :root {
        --orders-primary: var(--green);
        --orders-primary-dark: var(--green-dark);
        --orders-surface: #ffffff;
        --orders-surface-soft: #f8fafc;
        --orders-border: var(--border);
        --orders-text: var(--text);
        --orders-text-soft: var(--text-soft);
        --orders-success: #16a34a;
        --orders-warning: #f5e20b;
        --orders-danger: #ef4444;
        --orders-info: #3b82f6;
    }

    body {
        margin-left: 0 !important;
        padding: 0 !important;
        max-width: none !important;
        background: linear-gradient(180deg, #ffffff 0%, #f7fbfb 100%) !important;
    }

    .page-content.orders-page {
        background:
            radial-gradient(circle at top right, rgba(13, 138, 146, 0.12), transparent 28%),
            linear-gradient(180deg, #f8fbfc 0%, #f3f7f8 100%);
    }

    .orders-shell {
        display: grid;
        gap: 24px;
    }

    .orders-hero {
        position: relative;
        overflow: hidden;
        padding: 28px;
        border: 1px solid rgba(1, 106, 112, 0.14);
        border-radius: 28px;
        background: linear-gradient(135deg, #ffffff 0%, #f2fbfb 52%, #e7f7f7 100%);
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    }

    .orders-hero::after {
        content: "";
        position: absolute;
        inset: auto -60px -80px auto;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(13, 138, 146, 0.18), rgba(13, 138, 146, 0));
        pointer-events: none;
    }

    .orders-topline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(1, 106, 112, 0.08);
        color: var(--orders-primary);
        font-size: 0.9rem;
        font-weight: 700;
    }

    .orders-hero-grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(260px, auto);
        gap: 22px;
        align-items: end;
        margin-top: 18px;
    }

    .orders-title {
        margin: 0;
        color: var(--orders-text);
        font-size: 2rem;
        line-height: 1.05;
        font-weight: 600;
    }

    .orders-copy {
        max-width: 64ch;
        margin: 12px 0 0;
        color: var(--orders-text-soft);
        line-height: 1.7;
    }




    .stats-layout {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin: 0;
    }

    .stats-group,
    .dashboard-card {
        background: var(--orders-surface);
        border: 1px solid var(--orders-border);
        border-radius: 24px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
    }

    .stats-group {
        padding: 22px;
    }

    .stats-group-title,
    .card-title {
        color: var(--orders-text);
    }

    .stats-group-title i,
    .card-title i {
        color: var(--orders-primary);
    }

    .stats-grid {
        gap: 14px;
    }

    .stat-card {
        margin: 0;
        padding: 18px;
        border: 1px solid #edf2f7 !important;
        border-radius: 20px;
        box-shadow: none !important;
        background: #fff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06) !important;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        margin-bottom: 14px;
        background: rgba(1, 106, 112, 0.1) !important;
        color: var(--orders-primary) !important;
    }

    .icon-revenue {
        background: rgba(245, 158, 11, 0.13) !important;
        color: #b45309 !important;
    }

    .stat-number {
        color: var(--orders-text);
        font-size: 1.75rem;
        line-height: 1.05;
    }

    .stat-title {
        color: var(--orders-text-soft);
    }

    .dashboard-card {
        padding: 22px;
        margin: 0;
    }

    .card-header {
        border-bottom: 1px solid var(--orders-border);
        padding: 0 0 16px;
        margin-bottom: 18px;
        background: transparent;
    }

    .card-header small {
        color: var(--orders-text-soft);
        font-weight: 600;
    }

    .order-filter-list {
        gap: 10px;
    }

   

    .btn-filter.active,
    .btn-filter:hover {
        background: var(--orders-primary) !important;
        border-color: var(--orders-primary) !important;
        color: #fff !important;
    }

    .orders-search {
        min-height: 46px;
        margin-bottom: 18px !important;
        border: 1px solid var(--orders-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: none;
    }

    .orders-search:focus {
        border-color: var(--orders-primary);
        box-shadow: 0 0 0 4px rgba(1, 106, 112, 0.12);
    }

    .order-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .order-card {
        min-height: 236px;
        padding: 18px;
        border: 1px solid var(--orders-border);
        border-radius: 22px;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .order-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.08);
    }

    .order-code {
        display: inline-flex;
        padding: 7px 11px;
        border-radius: 999px;
        background: #f8fafc;
        color: var(--orders-primary);
        font-weight: 800;
        font-size: 0.9rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
        white-space: nowrap;
        border: 1px solid transparent;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08);
    }

    .badge-pending {
         background: #ffee32;
            color: #2c2a29;
    }

    .badge-approved {
        background: #02f954;
        color: #2c2a29;
        border-color: #15803d;
    }

    .badge-completed {
        background: #03e2ee;
        color: #2c2a29;
        border-color: #99c2f1;
    }

    .badge-rejected {
        background: #dc2626;
        color: #ffffff;
        border-color: #b91c1c;
    }

    .badge-new {
        background: var(--orders-danger);
        color: #fff;
    }

    .order-amount {
        margin-top: 18px;
        color: var(--orders-text);
        font-size: 1.7rem;
        font-weight: 800;
    }

    .order-amount span,
    .info-row,
    .order-date-small {
        color: var(--orders-text-soft);
    }

    .info-row strong {
        color: var(--orders-text);
        font-weight: 700;
    }

    .order-divider {
        border-top: 1px solid #edf2f7;
        margin: 14px 0;
    }

    .btn-detail {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 18px;
        min-height: 42px;
        border: 1px solid rgba(1, 106, 112, 0.2);
        border-radius: 14px;
        background: var(--orders-primary);
        color: #fff;
        font-weight: 800;
    }

    .btn-detail:hover {
        background: var(--orders-primary-dark);
        color: #fff;
    }

    .empty-orders {
        grid-column: 1 / -1;
        padding: 44px 20px;
        border: 1px dashed var(--orders-border);
        border-radius: 20px;
        color: var(--orders-text-soft);
        text-align: center;
        background: #f8fafc;
    }

    .pagination-wrap {
        border-top: 1px solid var(--orders-border);
    }

    .page-link-local {
        border-radius: 12px;
        border-color: var(--orders-border);
    }

    .page-link-local.active,
    .page-link-local:hover {
        border-color: var(--orders-primary);
        background: var(--orders-primary);
        color: #fff;
    }

    @media (max-width: 1200px) {
        .order-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 1024px) {
        .orders-hero-grid,
        .stats-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .page-content.orders-page {
            padding: 18px;
        }

        .orders-hero,
        .dashboard-card,
        .stats-group,
        .order-card {
            border-radius: 20px !important;
        }

        .orders-hero {
            padding: 22px !important;
        }

        .orders-hero-meta,
        .stats-grid,
        .order-grid {
            grid-template-columns: 1fr;
        }

        .card-header {
            align-items: flex-start !important;
            flex-direction: column;
        }

        .btn-filter,
        .btn-detail {
            width: 100%;
        }
    }
</style>

HTML;
adminPageStart('จัดการคำสั่งซื้อ');
?>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
    <div id="successAlert" class="alert alert-success text-center fs-5 fw-bold">
        ✅ ลบคำสั่งซื้อเรียบร้อยแล้ว
    </div>

    <script>
        setTimeout(() => {
            document.getElementById('successAlert').remove();
        }, 3000);
    </script>
<?php endif; ?>

<div class="orders-shell">
    

    <!-- สถิติ -->
    <div class="stats-layout">
        <section class="stats-group" aria-labelledby="today-stats-title">
            <h5 class="stats-group-title" id="today-stats-title">
                <i class="fas fa-calendar-day"></i> สถิติวันนี้
            </h5>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon icon-completed">
                        <i class="fa-solid fa-check-double"></i>
                    </div>
                    <div class="stat-number"><?= number_format($todayStats['completed_count'] ?? 0) ?></div>
                    <div class="stat-title">ขายสำเร็จวันนี้ (ออเดอร์)</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon icon-revenue">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-number"><?= number_format($todayStats['revenue'] ?? 0, 2) ?></div>
                    <div class="stat-title">ยอดขายวันนี้ (บาท)</div>
                </div>
            </div>
        </section>

        <section class="stats-group" aria-labelledby="overall-stats-title">
            <h5 class="stats-group-title" id="overall-stats-title">
                <i class="fas fa-chart-pie"></i> สถิติทั้งหมด
            </h5>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon icon-all">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div class="stat-number"><?= number_format($stats['total_completed'] ?? 0) ?></div>
                    <div class="stat-title">ขายสำเร็จทั้งหมด (ออเดอร์)</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon icon-revenue">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-number"><?= number_format($overallRevenueStats['revenue'] ?? 0, 2) ?></div>
                    <div class="stat-title">ยอดขายรวมทั้งหมด (บาท)</div>
                </div>
            </div>
        </section>


    </div>


    <!-- ฟิลเตอร์สถานะ -->
    <div class="dashboard-card">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-filter"></i> กรองตามสถานะ</h5>
        </div>

        <div class="order-filter-list">
            <a href="?status=all&page=1"
                class="btn-filter btn-filter-all <?= $status == 'all' ? 'active' : '' ?>">
                <i class="fas fa-list"></i> ทั้งหมด (<?= number_format($stats['total_count'] ?? 0) ?>)
            </a>

            <a href="?status=pending&page=1"
                class="btn-filter btn-filter-pending <?= $status == 'pending' ? 'active' : '' ?>">
                <i class="fas fa-clock"></i> รอยืนยัน (<?= number_format($stats['pending_count'] ?? 0) ?>)
            </a>

            <a href="?status=approved&page=1"
                class="btn-filter btn-filter-approved <?= $status == 'approved' ? 'active' : '' ?>">
                <i class="fas fa-check-circle"></i> ยืนยันแล้ว (<?= number_format($stats['approved_count'] ?? 0) ?>)
            </a>

            <a href="?status=rejected&page=1"
                class="btn-filter btn-filter-rejected <?= $status == 'rejected' ? 'active' : '' ?>">
                <i class="fas fa-times-circle"></i> ปฏิเสธแล้ว (<?= number_format($stats['rejected_count'] ?? 0) ?>)
            </a>

            <a href="?status=completed&page=1"
                class="btn-filter btn-filter-completed <?= $status == 'completed' ? 'active' : '' ?>">

                <i class="fas fa-box-check"></i>

                เสร็จสิ้นทั้งหมด
                (<?= number_format($stats['completed_count'] ?? 0) ?>)

            </a>
        </div>
    </div>

    <!-- ตารางออเดอร์ -------------------------------------------------------------------------------------------------->


    <div class="dashboard-card">
        <div class="card-header">

            <h5 class="card-title">
                <i class="fas fa-table"></i> รายการคำสั่งซื้อ
                <?php if ($status != 'all'): ?>
                    - <span class="text-primary">
                        <?= $status == 'pending' ? 'รอยืนยัน' : ($status == 'approved' ? 'ยืนยันแล้ว' : ($status == 'rejected' ? 'ปฏิเสธแล้ว' : 'เสร็จสิ้นทั้งหมด')) ?>
                    </span>
                <?php endif; ?>
            </h5>
            <small>แสดง <?= number_format($showingFrom) ?>-<?= number_format($showingTo) ?> จาก <?= number_format($totalFilteredOrders) ?> รายการ</small>
        </div>

        <div class="order-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($order = $result->fetch_assoc()): ?>


                    <div class="order-card">

                        <?php
                        $orderTime = strtotime($order['order_date']);
                        $now = time();
                        $diffHours = ($now - $orderTime) / 3600;
                        ?>

                        <!-- Top: รหัส + badge ใหม่ + status -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="order-code">#<?= htmlspecialchars($order['order_code']) ?></span>
                                <?php if ($diffHours <= 24 && $order['order_status'] == 'pending'): ?>
                                    <span class="badge-new">ใหม่</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($order['order_status'] == 'pending'): ?>
                                <span class="status-badge badge-pending">รอยืนยัน</span>
                            <?php elseif ($order['order_status'] == 'approved'): ?>
                                <span class="status-badge badge-approved">ยืนยันแล้ว</span>
                            <?php elseif ($order['order_status'] == 'rejected'): ?>
                                <span class="status-badge badge-rejected">ปฏิเสธแล้ว</span>
                            <?php elseif ($order['order_status'] == 'completed'): ?>
                                <span class="status-badge badge-completed">เสร็จสิ้นแล้ว</span>
                            <?php endif; ?>
                        </div>

                        <!-- ยอดเงิน -->
                        <div class="order-amount">
                            ฿<?= number_format($order['total_amount'], 2) ?>
                            <span>บาท</span>
                        </div>

                        <hr class="order-divider">

                        <!-- ข้อมูล -->
                        <div class="order-info">
                            <div class="info-row">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <circle cx="8" cy="7" r="4" stroke="currentColor" stroke-width="1.5" />
                                    <path d="M8 1v2M8 13v2M1 8h2M13 8h2" stroke="currentColor" stroke-width="1.2" />
                                </svg>
                                <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                            </div>

                            <div class="info-row">
                                <span class="dot <?= $order['receive_type'] == 'pickup' ? 'dot-pickup' : 'dot-deliver' ?>"></span>
                                <?= $order['receive_type'] == 'pickup' ? 'รับเองที่สวน' : 'ส่งให้' ?> ·
                                <strong>
                                    <?= !empty($order['receive_datetime'])
                                        ? date('d/m/Y H:i', strtotime($order['receive_datetime']))
                                        : '-' ?>
                                </strong>
                            </div>

                            <div class="info-row order-date-small">
                                สั่งเมื่อ <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?>
                            </div>
                        </div>

                        <!-- ปุ่ม -->
                        <a href="order_detail.php?code=<?= $order['order_code'] ?>" class="btn-detail">
                            ดูรายละเอียด
                        </a>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>
                <div class="empty-orders">
                    <i class="fas fa-shopping-cart fa-3x"></i><br><br>
                    ไม่พบข้อมูลคำสั่งซื้อ
                </div>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination-wrap">
                <div class="pagination-info">
                    หน้า <?= number_format($currentPage) ?> จาก <?= number_format($totalPages) ?>
                </div>
                <nav aria-label="เปลี่ยนหน้ารายการคำสั่งซื้อ">
                    <ul class="pagination-list">
                        <?php
                        $paginationParams = $status != 'all' ? ['status' => $status] : ['status' => 'all'];
                        $prevParams = array_merge($paginationParams, ['page' => max(1, $currentPage - 1)]);
                        $nextParams = array_merge($paginationParams, ['page' => min($totalPages, $currentPage + 1)]);
                        ?>
                        <li>
                            <a class="page-link-local <?= $currentPage <= 1 ? 'disabled' : '' ?>"
                                href="?<?= http_build_query($prevParams) ?>">
                                ก่อนหน้า
                            </a>
                        </li>

                        <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                            <?php
                            if ($totalPages > 7 && $pageNumber != 1 && $pageNumber != $totalPages && abs($pageNumber - $currentPage) > 1) {
                                if ($pageNumber == 2 || $pageNumber == $totalPages - 1) {
                                    echo '<li><span class="page-link-local disabled">...</span></li>';
                                }
                                continue;
                            }
                            $pageParams = array_merge($paginationParams, ['page' => $pageNumber]);
                            ?>
                            <li>
                                <a class="page-link-local <?= $pageNumber == $currentPage ? 'active' : '' ?>"
                                    href="?<?= http_build_query($pageParams) ?>">
                                    <?= number_format($pageNumber) ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li>
                            <a class="page-link-local <?= $currentPage >= $totalPages ? 'disabled' : '' ?>"
                                href="?<?= http_build_query($nextParams) ?>">
                                ถัดไป
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <!-- ตารางออเดอร์ -->
    <!-- --------------------------------------------------------------------------------------------- -->
</div>


<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ค้นหาออเดอร์
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.className = 'form-control orders-search';
    searchInput.placeholder = 'ค้นหาออเดอร์... (รหัส, ชื่อ, เบอร์โทร)';
    searchInput.id = 'searchOrders';

    const allCards = document.querySelectorAll('.dashboard-card .card-header');
    const cardHeader = allCards[allCards.length - 1]; // อันสุดท้าย
    if (cardHeader) {
        cardHeader.parentNode.insertBefore(searchInput, cardHeader.nextElementSibling);
    }

    document.getElementById('searchOrders')?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.order-card').forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(searchTerm) ? '' : 'none';
        });

    });

    document.querySelector('.page-content')?.classList.add('orders-page');
</script>

<?php adminPageEnd(); ?>