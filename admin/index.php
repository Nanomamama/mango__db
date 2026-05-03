<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

$admin_name  = $_SESSION['admin_name']  ?? '';
$admin_email = $_SESSION['admin_email'] ?? '';

// ---- helper: run query safely, return mysqli_result or false ----
function safeQuery(mysqli $conn, string $sql): mysqli_result|false {
    try {
        $r = $conn->query($sql);
        if (!$r) { error_log('DB error: ' . $conn->error . ' | SQL: ' . $sql); }
        return $r;
    } catch (mysqli_sql_exception $e) {
        error_log('DB exception: ' . $e->getMessage() . ' | SQL: ' . $sql);
        return false;
    }
}

// ============================================================
//  ยอดขายจาก orders  (ใช้ order_date, total_amount)
//  ตัดสถานะ rejected ออก
// ============================================================

// รายวัน – 7 วันล่าสุด แยกเป็นวัน
$labels_day = $sales_day = [];
$r = safeQuery($conn,
    "SELECT DATE_FORMAT(order_date,'%d/%m') AS lbl,
            COALESCE(SUM(total_amount),0)   AS total
     FROM   orders
     WHERE  order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
       AND  order_status <> 'rejected'
     GROUP  BY DATE(order_date)
     ORDER  BY DATE(order_date)");
if ($r) while ($row = $r->fetch_assoc()) {
    $labels_day[] = $row['lbl'];
    $sales_day[]  = (float)$row['total'];
}

// รายสัปดาห์ – 8 สัปดาห์ล่าสุด
$labels_week = $sales_week = [];
$r = safeQuery($conn,
    "SELECT CONCAT('สัปดาห์ที่ ', WEEK(order_date,3)) AS lbl,
            COALESCE(SUM(total_amount),0)              AS total
     FROM   orders
     WHERE  order_date >= DATE_SUB(CURDATE(), INTERVAL 7 WEEK)
       AND  order_status <> 'rejected'
     GROUP  BY YEARWEEK(order_date,3)
     ORDER  BY YEARWEEK(order_date,3)");
if ($r) while ($row = $r->fetch_assoc()) {
    $labels_week[] = $row['lbl'];
    $sales_week[]  = (float)$row['total'];
}

// รายเดือน – 12 เดือนล่าสุด
$thai_months = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.',
                'ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
$labels_month = $sales_month = [];
$r = safeQuery($conn,
    "SELECT MONTH(order_date) AS mnum,
            COALESCE(SUM(total_amount),0) AS total
     FROM   orders
     WHERE  order_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
       AND  order_status <> 'rejected'
     GROUP  BY YEAR(order_date), MONTH(order_date)
     ORDER  BY YEAR(order_date), MONTH(order_date)");
if ($r) while ($row = $r->fetch_assoc()) {
    $labels_month[] = $thai_months[(int)$row['mnum']];
    $sales_month[]  = (float)$row['total'];
}

// รายปี – 5 ปีล่าสุด
$labels_year = $sales_year = [];
$r = safeQuery($conn,
    "SELECT YEAR(order_date) AS yr,
            COALESCE(SUM(total_amount),0) AS total
     FROM   orders
     WHERE  order_date >= DATE_SUB(CURDATE(), INTERVAL 4 YEAR)
       AND  order_status <> 'rejected'
     GROUP  BY YEAR(order_date)
     ORDER  BY yr");
if ($r) while ($row = $r->fetch_assoc()) {
    $labels_year[] = (string)$row['yr'];
    $sales_year[]  = (float)$row['total'];
}

// ============================================================
//  การจองเข้าชมสวน (bookings)
//  ใช้ booking_date  – ตัด cancelled ออก
// ============================================================

// Donut: จำนวนจองรายเดือน 12 เดือนล่าสุด
$labels_booking_month = $booking_month = [];
$r = safeQuery($conn,
    "SELECT MONTH(booking_date) AS mnum, COUNT(*) AS cnt
     FROM   bookings
     WHERE  booking_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
       AND  status <> 'cancelled'
     GROUP  BY YEAR(booking_date), MONTH(booking_date)
     ORDER  BY YEAR(booking_date), MONTH(booking_date)");
if ($r) while ($row = $r->fetch_assoc()) {
    $labels_booking_month[] = $thai_months[(int)$row['mnum']];
    $booking_month[]        = (int)$row['cnt'];
}

// Line chart เปรียบเทียบ: จำนวนจองแยกสถานะ รายเดือน 12 เดือนล่าสุด
$bc_raw = [];
$r = safeQuery($conn,
    "SELECT MONTH(booking_date) AS mnum, status, COUNT(*) AS cnt
     FROM   bookings
     WHERE  booking_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
     GROUP  BY YEAR(booking_date), MONTH(booking_date), status
     ORDER  BY YEAR(booking_date), MONTH(booking_date)");
if ($r) while ($row = $r->fetch_assoc()) {
    $m = $thai_months[(int)$row['mnum']];
    $bc_raw[$m][$row['status']] = (int)$row['cnt'];
}
$bc_labels = array_keys($bc_raw);
$bc_status_config = [
    'pending'          => ['label'=>'รอดำเนินการ',  'color'=>'#f6c23e'],
    'awaiting_payment' => ['label'=>'รอชำระเงิน',   'color'=>'#4e73df'],
    'confirmed'        => ['label'=>'ยืนยันแล้ว',   'color'=>'#1cc88a'],
    'cancelled'        => ['label'=>'ยกเลิก',        'color'=>'#e74a3b'],
];
$bc_datasets = [];
foreach ($bc_status_config as $st => $cfg) {
    $data = array_map(fn($m) => $bc_raw[$m][$st] ?? 0, $bc_labels);
    $bc_datasets[] = [
        'label'           => $cfg['label'],
        'data'            => $data,
        'borderColor'     => $cfg['color'],
        'backgroundColor' => $cfg['color'] . '20',
        'tension'         => 0.4,
        'fill'            => false,
        'borderWidth'     => 2,
        'pointRadius'     => 4,
        'pointHoverRadius'=> 6,
    ];
}

// ============================================================
//  ยอดมัดจำ / ค้างชำระ / รวมทั้งหมด  (เฉพาะ confirmed)
// ============================================================
$deposit_total = $remain_total = $total_amount = 0;
$r = safeQuery($conn,
    "SELECT COALESCE(SUM(deposit_amount),0) AS dep,
            COALESCE(SUM(balance_amount),0) AS bal,
            COALESCE(SUM(price_total),0)    AS tot
     FROM   bookings
     WHERE  status = 'confirmed'");
if ($r) {
    $row           = $r->fetch_assoc();
    $deposit_total = (float)$row['dep'];
    $remain_total  = (float)$row['bal'];
    $total_amount  = (float)$row['tot'];
}

// ============================================================
//  สินค้า: จำนวนสินค้า active
// ============================================================
$product_count = 0;
$r = safeQuery($conn, "SELECT COUNT(*) AS cnt FROM products WHERE status = 'active'");
if ($r) $product_count = (int)$r->fetch_assoc()['cnt'];

// ============================================================
//  จำนวนสมาชิกทั้งหมด
// ============================================================
$member_count = 0;
$r = safeQuery($conn, "SELECT COUNT(*) AS cnt FROM members WHERE status = 1");
if ($r) $member_count = (int)$r->fetch_assoc()['cnt'];

// คำนวณเปอร์เซ็นต์การเปลี่ยนแปลง
$previous_month_sales = 0;
$r = safeQuery($conn,
    "SELECT COALESCE(SUM(total_amount),0) AS total
     FROM   orders
     WHERE  order_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 MONTH) AND DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
       AND  order_status <> 'rejected'");
if ($r && $row = $r->fetch_assoc()) {
    $previous_month_sales = (float)$row['total'];
}
$current_month_sales = !empty($sales_month) ? end($sales_month) : 0;
$sales_change = $previous_month_sales > 0 ? (($current_month_sales - $previous_month_sales) / $previous_month_sales) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Mango Paradise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
            --secondary: #7209b7;
            --success: #4cc9f0;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --dark: #1e1e2f;
            --light: #f8f9fc;
            --teal: #20c997;
            --purple: #b5179e;
            --orange: #f8961e;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
            transition: all 0.3s ease;
        }
        
        body.dark-mode {
            background: linear-gradient(135deg, #1e1e2f 0%, #2d2d44 100%);
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
        }
        
        /* Loading Animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(255,255,255,0.3);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Dashboard Header */
        .dashboard-header {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        body.dark-mode .dashboard-header {
            background: rgba(30,30,47,0.95);
            color: white;
        }
        
        .dashboard-title {
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 1.8rem;
        }
        
        .admin-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            background: linear-gradient(135deg, rgba(67,97,238,0.1), rgba(114,9,183,0.1));
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .admin-profile:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .admin-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 3px solid var(--primary);
            object-fit: cover;
        }
        
        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        body.dark-mode .stat-card {
            background: rgba(30,30,47,0.95);
            color: white;
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .stat-card:hover::before {
            left: 100%;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            margin: 0.5rem 0;
        }
        
        .stat-change {
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .stat-change.positive { color: #1cc88a; }
        .stat-change.negative { color: #e74a3b; }
        
        /* Chart Toggle Buttons */
        .chart-toggle {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .chart-toggle-btn {
            padding: 0.75rem 1.8rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-family: 'Kanit', sans-serif;
            transition: all 0.3s;
            cursor: pointer;
            background: white;
            color: var(--primary);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        body.dark-mode .chart-toggle-btn {
            background: rgba(30,30,47,0.95);
            color: var(--primary);
        }
        
        .chart-toggle-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .chart-toggle-btn.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }
        
        /* Chart Cards */
        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            height: 100%;
        }
        
        body.dark-mode .chart-card {
            background: rgba(30,30,47,0.95);
            color: white;
        }
        
        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
        }
        
        .chart-card-header {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .chart-wrapper {
            position: relative;
            height: 350px;
            margin-top: 1rem;
        }
        
        /* Data Cards */
        .data-card {
            border-radius: 20px;
            padding: 1.8rem;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .data-card:hover {
            transform: translateY(-5px);
        }
        
        .deposit-card {
            background: linear-gradient(135deg, var(--info), var(--primary));
            color: white;
        }
        
        .remain-card {
            background: linear-gradient(135deg, var(--warning), var(--orange));
            color: white;
        }
        
        .total-card {
            background: linear-gradient(135deg, var(--success), var(--teal));
            color: white;
        }
        
        .card-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }
        
        .card-value {
            font-size: 2rem;
            font-weight: 800;
        }
        
        /* Dark Mode Toggle */
        .dark-mode-toggle {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: all 0.3s;
            font-size: 1.5rem;
        }
        
        .dark-mode-toggle:hover {
            transform: scale(1.1);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-header {
                padding: 1rem;
            }
            
            .dashboard-title {
                font-size: 1.2rem;
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
            
            .chart-wrapper {
                height: 280px;
            }
            
            .chart-toggle-btn {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }
        }
        
        /* Fade In Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
</div>

<!-- Dark Mode Toggle Button -->
<button class="dark-mode-toggle" id="darkModeToggle">
    <i class="bi bi-moon-fill"></i>
</button>

<div class="d-flex">
    <?php include 'sidebar.php'; ?>
    <div class="p-4" style="margin-left: 250px; flex: 1;">
        
        <!-- Header -->
        <div class="dashboard-header fade-in">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="dashboard-title">
                        <i class="bi bi-speedometer2"></i> Dashboard Admin
                    </h1>
                    <p class="text-muted mb-0 mt-2">
                        <i class="bi bi-calendar3"></i> วันนี้: <?= date('d/m/Y') ?>
                    </p>
                </div>
                <div class="admin-profile" onclick="location.reload()">
                    <img class="admin-avatar" src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=4361ee&color=fff&bold=true" alt="Admin">
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($admin_name) ?></div>
                        <small class="opacity-75"><?= htmlspecialchars($admin_email) ?></small>
                    </div>
                    <i class="bi bi-arrow-repeat"></i>
                </div>
            </div>
        </div>
        
        <div class="container-fluid px-0">
            
            <!-- Stat Cards Row -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card fade-in">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <small class="text-muted">ยอดขายรวม (ปีนี้)</small>
                                <div class="stat-value">฿<span id="totalSales"><?= number_format(array_sum($sales_year), 2) ?></span></div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                        </div>
                        <div class="stat-change <?= $sales_change >= 0 ? 'positive' : 'negative' ?>">
                            <i class="bi bi-arrow-<?= $sales_change >= 0 ? 'up' : 'down' ?>-short"></i>
                            <?= number_format(abs($sales_change), 1) ?>% จากเดือนที่แล้ว
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card fade-in">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <small class="text-muted">การจองปีนี้</small>
                                <div class="stat-value"><span id="totalBookings"><?= number_format(array_sum($booking_month)) ?></span> คณะ</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card fade-in">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <small class="text-muted">สินค้า (Active)</small>
                                <div class="stat-value"><span id="productCount"><?= $product_count ?></span> รายการ</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card fade-in">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <small class="text-muted">สมาชิกทั้งหมด</small>
                                <div class="stat-value"><span id="memberCount"><?= $member_count ?></span> คน</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chart Toggle Buttons -->
            <div class="chart-toggle fade-in">
                <button class="chart-toggle-btn active" id="showAllChartsBtn">
                    <i class="bi bi-grid-3x3-gap-fill"></i> แสดงกราฟทั้งหมด
                </button>
                <button class="chart-toggle-btn" id="showCompareChartBtn">
                    <i class="bi bi-bar-chart-steps"></i> เปรียบเทียบยอดขาย
                </button>
                <button class="chart-toggle-btn" id="showBookingCompareBtn">
                    <i class="bi bi-calendar-week"></i> เปรียบเทียบการจอง
                </button>
            </div>
            
            <!-- All Charts View -->
            <div id="allSalesCharts" class="fade-in">
                <!-- Sales Charts Row -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="chart-card">
                            <div class="chart-card-header">
                                <i class="bi bi-calendar-day"></i> ยอดขายรายวัน (7 วันล่าสุด)
                            </div>
                            <div class="chart-wrapper">
                                <canvas id="salesDayChart"></canvas>
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-muted">รวม ฿<?= number_format(array_sum($sales_day), 2) ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="chart-card">
                            <div class="chart-card-header">
                                <i class="bi bi-calendar-week"></i> ยอดขายรายสัปดาห์ (8 สัปดาห์)
                            </div>
                            <div class="chart-wrapper">
                                <canvas id="salesWeekChart"></canvas>
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-muted">รวม ฿<?= number_format(array_sum($sales_week), 2) ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="chart-card">
                            <div class="chart-card-header">
                                <i class="bi bi-calendar-month"></i> ยอดขายรายเดือน (12 เดือน)
                            </div>
                            <div class="chart-wrapper">
                                <canvas id="salesMonthChart"></canvas>
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-muted">รวม ฿<?= number_format(array_sum($sales_month), 2) ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="chart-card">
                            <div class="chart-card-header">
                                <i class="bi bi-calendar-year"></i> ยอดขายรายปี (5 ปี)
                            </div>
                            <div class="chart-wrapper">
                                <canvas id="salesYearChart"></canvas>
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-muted">รวม ฿<?= number_format(array_sum($sales_year), 2) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Booking Donut Chart -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-6">
                        <div class="chart-card">
                            <div class="chart-card-header">
                                <i class="bi bi-pie-chart"></i> การจองเข้าชมสวน (รายเดือน)
                            </div>
                            <div class="chart-wrapper" style="height: 350px;">
                                <canvas id="bookingMonthChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Summary Cards -->
                    <div class="col-12 col-md-6">
                        <div class="row g-4 h-100">
                            <div class="col-12">
                                <div class="data-card deposit-card">
                                    <div class="card-label">
                                        <i class="bi bi-wallet2"></i> ยอดมัดจำรวม
                                    </div>
                                    <div class="card-value">฿<?= number_format($deposit_total, 2) ?></div>
                                    <small class="mt-2 d-block opacity-75">จากยอดทั้งหมด <?= number_format($total_amount, 2) ?> บาท</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="data-card remain-card">
                                    <div class="card-label">
                                        <i class="bi bi-clock-history"></i> ยอดค้างชำระรวม
                                    </div>
                                    <div class="card-value">฿<?= number_format($remain_total, 2) ?></div>
                                    <small class="mt-2 d-block opacity-75">คิดเป็น <?= $total_amount > 0 ? number_format(($remain_total/$total_amount)*100, 1) : 0 ?>% ของยอดทั้งหมด</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="data-card total-card">
                                    <div class="card-label">
                                        <i class="bi bi-cash-stack"></i> ยอดรวมทั้งหมด
                                    </div>
                                    <div class="card-value">฿<?= number_format($total_amount, 2) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Compare Sales Chart View -->
            <div id="compareSalesChartWrapper" class="d-none fade-in">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <i class="bi bi-bar-chart-fill"></i> กราฟเปรียบเทียบยอดขายรวม
                    </div>
                    <div class="chart-wrapper" style="height: 450px;">
                        <canvas id="compareSalesChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Booking Compare Chart View -->
            <div id="bookingCompareChartWrapper" class="d-none fade-in">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <i class="bi bi-calendar-range"></i> กราฟเปรียบเทียบการจองแยกตามสถานะ
                    </div>
                    <div class="chart-wrapper" style="height: 450px;">
                        <canvas id="bookingCompareChart"></canvas>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Charts Configuration
let charts = {};

// Function to create gradient
function createGradient(ctx, colorStart, colorEnd) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, colorStart);
    gradient.addColorStop(1, colorEnd);
    return gradient;
}

// Create Bar Chart with Gradient
function createBarChart(id, labels, data, colorStart, colorEnd) {
    const canvas = document.getElementById(id);
    if (!canvas) return null;
    const ctx = canvas.getContext('2d');
    const gradient = createGradient(ctx, colorStart, colorEnd);
    
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'ยอดขาย (บาท)',
                data: data,
                backgroundColor: gradient,
                borderRadius: 8,
                borderWidth: 0,
                barPercentage: 0.7,
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '฿' + context.parsed.y.toLocaleString('th-TH', {minimumFractionDigits: 2});
                        }
                    },
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#4361ee',
                    borderWidth: 2
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '฿' + value.toLocaleString();
                        },
                        font: {
                            size: 11
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });
}

// Create Line Chart
function createLineChart(id, labels, data, color) {
    const canvas = document.getElementById(id);
    if (!canvas) return null;
    
    return new Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'ยอดขาย (บาท)',
                data: data,
                borderColor: color,
                backgroundColor: color + '20',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 8,
                pointBackgroundColor: color,
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '฿' + context.parsed.y.toLocaleString('th-TH', {minimumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '฿' + value.toLocaleString();
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });
}

// Initialize all charts
document.addEventListener('DOMContentLoaded', function() {
    // Sales Charts
    charts.salesDay = createBarChart('salesDayChart', 
        <?= json_encode($labels_day) ?>, 
        <?= json_encode($sales_day) ?>, 
        '#4e73df', '#36b9cc');
    
    charts.salesWeek = createBarChart('salesWeekChart', 
        <?= json_encode($labels_week) ?>, 
        <?= json_encode($sales_week) ?>, 
        '#1cc88a', '#20c997');
    
    charts.salesMonth = createLineChart('salesMonthChart', 
        <?= json_encode($labels_month) ?>, 
        <?= json_encode($sales_month) ?>, 
        '#e74a3b');
    
    charts.salesYear = createBarChart('salesYearChart', 
        <?= json_encode($labels_year) ?>, 
        <?= json_encode($sales_year) ?>, 
        '#36b9cc', '#4e73df');
    
    // Booking Donut Chart
    const bookingCtx = document.getElementById('bookingMonthChart');
    if (bookingCtx) {
        charts.bookingDonut = new Chart(bookingCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($labels_booking_month) ?>,
                datasets: [{
                    data: <?= json_encode($booking_month) ?>,
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#b5179e', '#f8961e', '#20c997', '#3a0ca3', '#7209b7', '#f72585', '#4895ef'],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 11,
                                family: 'Kanit'
                            },
                            padding: 10,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a,b) => a+b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return `${context.label}: ${context.parsed} คณะ (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    duration: 1500
                }
            }
        });
    }
    
    // Compare Sales Chart
    const compareCtx = document.getElementById('compareSalesChart');
    if (compareCtx) {
        charts.compareSales = new Chart(compareCtx, {
            type: 'bar',
            data: {
                labels: ['รายวัน (7 วัน)', 'รายสัปดาห์ (8 สัปดาห์)', 'รายเดือน (12 เดือน)', 'รายปี (5 ปี)'],
                datasets: [{
                    label: 'ยอดขายรวม (บาท)',
                    data: [
                        <?= array_sum($sales_day) ?>,
                        <?= array_sum($sales_week) ?>,
                        <?= array_sum($sales_month) ?>,
                        <?= array_sum($sales_year) ?>
                    ],
                    backgroundColor: ['#4e73df', '#1cc88a', '#e74a3b', '#36b9cc'],
                    borderRadius: 10,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '฿' + context.parsed.y.toLocaleString('th-TH', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '฿' + value.toLocaleString();
                            }
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }
    
    // Booking Compare Chart (Line Chart)
    const bookingCompareCtx = document.getElementById('bookingCompareChart');
    if (bookingCompareCtx && <?= json_encode($bc_datasets) ?>.length > 0) {
        charts.bookingCompare = new Chart(bookingCompareCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($bc_labels) ?>,
                datasets: <?= json_encode($bc_datasets) ?>
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 12,
                                family: 'Kanit'
                            },
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.parsed.y} คณะ`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                return value + ' คณะ';
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }
    
    // Counter Animation for Stats
    function animateValue(element, start, end, duration) {
        if (!element) return;
        const range = end - start;
        const increment = range / (duration / 10);
        let current = start;
        const timer = setInterval(() => {
            current += increment;
            if ((range > 0 && current >= end) || (range < 0 && current <= end)) {
                clearInterval(timer);
                current = end;
            }
            element.textContent = Math.round(current).toLocaleString();
        }, 10);
    }
    
    // ตั้งค่า Animation สำหรับตัวเลข
    // (Optional: จะเพิ่มหรือไม่ก็ได้ เพราะตัวเลขแสดงอยู่แล้ว)
});

// Toggle Views
(function() {
    const panels = {
        all: document.getElementById('allSalesCharts'),
        compare: document.getElementById('compareSalesChartWrapper'),
        booking: document.getElementById('bookingCompareChartWrapper')
    };
    
    const btns = {
        all: document.getElementById('showAllChartsBtn'),
        compare: document.getElementById('showCompareChartBtn'),
        booking: document.getElementById('showBookingCompareBtn')
    };
    
    function activatePanel(panelName) {
        Object.keys(panels).forEach(key => {
            if (panels[key]) {
                panels[key].classList.toggle('d-none', key !== panelName);
            }
        });
        
        Object.keys(btns).forEach(key => {
            if (btns[key]) {
                btns[key].classList.toggle('active', key === panelName);
            }
        });
    }
    
    if (btns.all) btns.all.addEventListener('click', () => activatePanel('all'));
    if (btns.compare) btns.compare.addEventListener('click', () => activatePanel('compare'));
    if (btns.booking) btns.booking.addEventListener('click', () => activatePanel('booking'));
})();

// Dark Mode Toggle
const darkModeToggle = document.getElementById('darkModeToggle');
if (darkModeToggle) {
    darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const icon = this.querySelector('i');
        if (document.body.classList.contains('dark-mode')) {
            icon.classList.remove('bi-moon-fill');
            icon.classList.add('bi-sun-fill');
        } else {
            icon.classList.remove('bi-sun-fill');
            icon.classList.add('bi-moon-fill');
        }
    });
}

// Auto Refresh every 5 minutes
setInterval(() => {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.style.display = 'flex';
    setTimeout(() => {
        location.reload();
    }, 500);
}, 300000);

// Show loading on page load
window.addEventListener('beforeunload', function() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.style.display = 'flex';
});

// Hide loading when page loads
window.addEventListener('load', function() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 500);
    }
});
</script>
</body>
</html>