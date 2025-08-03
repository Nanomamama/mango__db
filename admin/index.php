<?php
require_once 'auth.php';
require_once 'db.php';

// ดึงชื่อ admin จาก session
$admin_name = $_SESSION['admin_name'] ?? '';
$admin_email = $_SESSION['admin_email'] ?? '';

// ดึงข้อมูลยอดขาย
// รายวัน 7 วันล่าสุด
$sales_day = [];
$labels_day = [];
$sql = "SELECT DATE(created_at) as day, SUM(total_price) as total 
            FROM orders 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY day ORDER BY day";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $labels_day[] = $row['day'];
    $sales_day[] = (float)$row['total'];
}

// รายสัปดาห์ 8 สัปดาห์ล่าสุด
$sales_week = [];
$labels_week = [];
$sql = "SELECT YEARWEEK(created_at, 1) as week, SUM(total_price) as total 
            FROM orders 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 WEEK)
            GROUP BY week ORDER BY week";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $labels_week[] = $row['week'];
    $sales_week[] = (float)$row['total'];
}

// รายเดือน 12 เดือนล่าสุด
$sales_month = [];
$labels_month = [];
$sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_price) as total 
            FROM orders 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
            GROUP BY month ORDER BY month";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $labels_month[] = $row['month'];
    $sales_month[] = (float)$row['total'];
}

// รายปี 5 ปีล่าสุด
$sales_year = [];
$labels_year = [];
$sql = "SELECT YEAR(created_at) as year, SUM(total_price) as total 
            FROM orders 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 4 YEAR)
            GROUP BY year ORDER BY year";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $labels_year[] = $row['year'];
    $sales_year[] = (float)$row['total'];
}

// ดึงจำนวนการจองเข้าชมสวน รายเดือน 12 เดือนล่าสุด
$booking_month = [];
$labels_booking_month = [];
$sql = "SELECT DATE_FORMAT(date, '%Y-%m') as month, COUNT(*) as total 
            FROM bookings 
            WHERE date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
            GROUP BY month ORDER BY month";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $labels_booking_month[] = $row['month'];
    $booking_month[] = (int)$row['total'];
}

// นับจำนวนสายพันธุ์มะม่วงที่ไม่ซ้ำกัน
$sql = "SELECT COUNT(DISTINCT mango_name) AS variety_count FROM mango_varieties";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$variety_count = $row['variety_count'];

// ดึงยอดมัดจำรวมและยอดคงเหลือรวมจาก bookings เฉพาะที่อนุมัติแล้ว
$sql = "SELECT SUM(deposit_amount) AS deposit_total, SUM(remain_amount) AS remain_total 
        FROM bookings 
        WHERE status = 'อนุมัติแล้ว'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$deposit_total = $row['deposit_total'] ?? 0;
$remain_total = $row['remain_total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Mango Paradise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --light: #f8f9fa;
            --dark: #212529;
            --purple: #7209b7;
            --teal: #20c997;
            --pink: #e83e8c;
            --cyan: #0dcaf0;
            --mango: #FFC107;
            --mango-dark: #E6A000;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            color: #333;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .dashboard-header {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: white;
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            position: relative;
            overflow: hidden;
            z-index: 10;
            border-radius: 50px;
        }

        .dashboard-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
            position: relative;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-card::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .stat-card.sales::after {
            background: linear-gradient(90deg, var(--primary), var(--info));
        }

        .stat-card.expenses::after {
            background: linear-gradient(90deg, var(--danger), var(--pink));
        }

        .stat-card.projects::after {
            background: linear-gradient(90deg, var(--success), var(--cyan));
        }

        .stat-card.invoices::after {
            background: linear-gradient(90deg, var(--purple), #9d4edd);
        }

        .stat-card .card-title {
            font-size: 1rem;
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .stat-card .card-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .stat-card .card-change {
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .increase {
            color: #2ecc71;
        }

        .decrease {
            color: #e74c3c;
        }

        .chart-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .chart-container::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, rgba(67, 97, 238, 0.05), transparent);
            border-bottom-left-radius: 100%;
            pointer-events: none;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .chart-header h5 {
            font-weight: 600;
            color: var(--dark);
            margin: 0;
            font-size: 1.1rem;
        }

        .chart-legend {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .chart-legend-item {
            display: flex;
            align-items: center;
            font-size: 0.8rem;
        }

        .legend-color {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 4px;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .admin-profile:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .admin-profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .admin-profile span {
            font-weight: 500;
            color: white;
            font-size: 0.9rem;
        }

        .dashboard-title {
            font-weight: 700;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            display: inline-block;
            font-size: 1.5rem;
        }

        .dashboard-title::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50%;
            height: 3px;
            background: white;
            border-radius: 3px;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .chart-toggle {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1.5rem;
            gap: 0.5rem;
        }

        .chart-toggle-btn {
            border: none;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            font-weight: 600;
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .chart-toggle-btn:hover {
            background: rgba(67, 97, 238, 0.2);
        }

        .chart-toggle-btn.active {
            background: var(--primary);
            color: white;
        }

        .center-number {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.5rem;
            font-weight: 700;
            color: #43cea2;
            pointer-events: none;
        }

        /* ปรับโครงสร้างกราฟแยก */
        .all-charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .chart-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            padding: 1.2rem;
            height: 280px;
            /* กำหนดความสูงตายตัว */
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chart-card-header {
            font-size: 0.95rem;
            font-weight: 600;
            color: #4e73df;
            margin-bottom: 0.8rem;
            text-align: center;
        }

        .chart-card-content {
            flex: 1;
            position: relative;
            min-height: 0;
            /* สำคัญสำหรับ flex ใน container */
        }

        .chart-card canvas {
            width: 100% !important;
            height: 100% !important;
        }

        /* การ์ดข้อมูล */
        .data-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            padding: 1.2rem;
            height: 280px;
            /* กำหนดความสูงเท่ากับกราฟ */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .data-card-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.8rem;
        }

        .data-card-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .data-card-extra {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .booking-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            position: relative;
        }

        .booking-count {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.5rem;
            font-weight: bold;
            color: #f6c23e;
            /* หรือสีที่ต้องการ */
            pointer-events: none;
            z-index: 2;
            width: 100%;
            /* เพิ่มให้เลขอยู่ตรงกลางแนวนอน */
            text-align: center;
            line-height: 1;
        }

        .deposit-card {
            background: linear-gradient(135deg, var(--info), var(--primary));
            color: white;
            border-radius: 16px;
            padding: 1.2rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .deposit-title {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .deposit-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .remain-card {
            background: linear-gradient(135deg, var(--warning), #f8b400);
            color: #333;
            border-radius: 16px;
            padding: 1.2rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .remain-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .variety-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            position: relative;
        }

        .variety-count {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--success);
            margin-bottom: 0.5rem;
        }

        .variety-label {
            font-size: 0.9rem;
            color: #6c757d;
            text-align: center;
        }

        /* กราฟเปรียบเทียบ */
        .compare-chart-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            height: 400px;
            position: relative;
            overflow: hidden;
        }

        .chart-wrapper {
            position: relative;
            height: 320px;
        }

        @media (max-width: 768px) {
            .chart-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .chart-toggle {
                justify-content: center;
            }

            .all-charts-container {
                grid-template-columns: 1fr;
            }

            .chart-card,
            .data-card {
                height: 250px;
            }

            .compare-chart-container {
                height: 350px;
            }

            .chart-wrapper {
                height: 280px;
            }
        }

        .center-variety-count {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            font-weight: bold;
            color: #43cea2;
            pointer-events: none;
            z-index: 2;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="p-4" style="margin-left: 250px; flex: 1;">

            <!-- Header -->
            <header class="dashboard-header">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h1 class="dashboard-title mb-0">Dashboard Admin</h1>
                            <p class="mb-0 mt-2 text-white-20">Manage Mango Orchard and Ordering System</p>
                        </div>
                        <div class="d-flex align-items-center gap-3 mt-2 mt-md-0">
                            <div class="position-relative">
                                <button class="btn btn-light rounded-circle p-2 shadow-sm position-relative" style="width:44px; height:44px;">
                                    <i class="bi bi-bell fs-5"></i>
                                    <span class="notification-badge position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger" style="font-size:0.75rem; min-width:20px; height:20px; display:flex; align-items:center; justify-content:center;">
                                        3
                                    </span>
                                </button>
                            </div>
                            <div class="admin-profile">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=random&color=fff" alt="Admin">
                                <span><?= htmlspecialchars($admin_name) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="container py-3">
                <!-- Chart Toggle -->
                <div class="chart-toggle">
                    <button class="chart-toggle-btn" id="showAllChartsBtn">แสดงกราฟแยก</button>
                    <button class="chart-toggle-btn" id="showCompareChartBtn">แสดงกราฟเปรียบเทียบยอดขาย</button>
                    <button class="chart-toggle-btn" id="showBookingCompareBtn">กราฟเปรียบเทียบยอดจองชมสวน (รายเดือน)</button>
                </div>

                <!-- All Charts View -->
                <div id="allSalesCharts" class="d-none">
                    <div class="row g-4 mb-4">
                        <!-- ยอดขายรายวัน -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="chart-card h-100">
                                <div class="chart-card-header">ยอดขายรายวัน (7 วัน)</div>
                                <div class="chart-card-content">
                                    <canvas id="salesDayChart"></canvas>
                                </div>
                                <div class="text-center mt-2">
                                    <small>รวม: <?= number_format(array_sum($sales_day), 2) ?> บาท</small>
                                </div>
                            </div>
                        </div>
                        <!-- ยอดขายรายสัปดาห์ -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="chart-card h-100">
                                <div class="chart-card-header">ยอดขายรายสัปดาห์ (8 สัปดาห์)</div>
                                <div class="chart-card-content">
                                    <canvas id="salesWeekChart"></canvas>
                                </div>
                                <div class="text-center mt-2">
                                    <small>รวม: <?= number_format(array_sum($sales_week), 2) ?> บาท</small>
                                </div>
                            </div>
                        </div>
                        <!-- ยอดขายรายเดือน -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="chart-card h-100">
                                <div class="chart-card-header">ยอดขายรายเดือน (12 เดือน)</div>
                                <div class="chart-card-content">
                                    <canvas id="salesMonthChart"></canvas>
                                </div>
                                <div class="text-center mt-2">
                                    <small>รวม: <?= number_format(array_sum($sales_month), 2) ?> บาท</small>
                                </div>
                            </div>
                        </div>
                        <!-- ยอดขายรายปี -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="chart-card h-100">
                                <div class="chart-card-header">ยอดขายรายปี (5 ปี)</div>
                                <div class="chart-card-content">
                                    <canvas id="salesYearChart"></canvas>
                                </div>
                                <div class="text-center mt-2">
                                    <small>รวม: <?= number_format(array_sum($sales_year), 2) ?> บาท</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="all-charts-container">
                        <!-- กราฟการจองเข้าชมสวน -->
                        <div class="chart-card">
                            <div class="chart-card-header">การจองเข้าชมสวน</div>
                            <div class="chart-card-content">
                                <div class="d-flex flex-column align-items-center h-100">
                                    <div style="position:relative; width:250px; height:250px;">
                                        <canvas id="bookingMonthChart"></canvas>
                                        <div class="booking-count" id="centerBookingCount"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ยอดมัดจำรวม -->
                        <div class="data-card deposit-card">
                            <div class="deposit-title">ยอดมัดจำรวม</div>
                            <div class="deposit-value"><?= number_format($deposit_total, 2) ?> บาท</div>
                            <div class="data-card-extra">
                                <i class="bi bi-arrow-up-circle"></i>
                                <span>เพิ่มขึ้น 15%</span>
                            </div>
                        </div>

                        <!-- ยอดคงเหลือรวม -->
                        <div class="data-card remain-card">
                            <div class="deposit-title">ยอดคงเหลือรวม</div>
                            <div class="remain-value"><?= number_format($remain_total, 2) ?> บาท</div>
                            <div class="data-card-extra">
                                <i class="bi bi-arrow-down-circle"></i>
                                <span>ลดลง 8%</span>
                            </div>
                        </div>

                        <!-- จำนวนสายพันธุ์มะม่วง -->
                        <div class="data-card">
                            <div class="data-card-title">สายพันธุ์มะม่วงในระบบ</div>
                            <div class="mt-3" style="height: 100px; width: 100px; position: relative;">
                                <canvas id="mangoVarietyCountChart"></canvas>
                                <div class="center-variety-count"><?= $variety_count ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Compare Chart View -->
                <div id="compareSalesChartWrapper">
                    <div class="compare-chart-container">
                        <div class="chart-header">
                            <h5>กราฟเปรียบเทียบยอดขาย</h5>
                            <div class="chart-legend">
                                <div class="chart-legend-item">
                                    <div class="legend-color" style="background-color: #4e73df;"></div>
                                    <span>รายวัน</span>
                                </div>
                                <div class="chart-legend-item">
                                    <div class="legend-color" style="background-color: #1cc88a;"></div>
                                    <span>รายสัปดาห์</span>
                                </div>
                                <div class="chart-legend-item">
                                    <div class="legend-color" style="background-color: #e74a3b;"></div>
                                    <span>รายเดือน</span>
                                </div>
                                <div class="chart-legend-item">
                                    <div class="legend-color" style="background-color: #36b9cc;"></div>
                                    <span>รายปี</span>
                                </div>
                            </div>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="compareSalesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- เพิ่ม div สำหรับกราฟเปรียบเทียบการจองรายเดือน -->
                <div id="bookingCompareChartWrapper" class="d-none">
                    <div class="compare-chart-container">
                        <div class="chart-header">
                            <h5>กราฟเปรียบเทียบการจองเข้าชมสวน (รายเดือน)</h5>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="bookingCompareChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                // ข้อมูลจาก PHP
                const salesDayLabels = <?= json_encode($labels_day) ?>;
                const salesDayData = <?= json_encode($sales_day) ?>;
                const salesWeekLabels = <?= json_encode($labels_week) ?>;
                const salesWeekData = <?= json_encode($sales_week) ?>;
                const salesMonthLabels = <?= json_encode($labels_month) ?>;
                const salesMonthData = <?= json_encode($sales_month) ?>;
                const salesYearLabels = <?= json_encode($labels_year) ?>;
                const salesYearData = <?= json_encode($sales_year) ?>;
                const bookingMonthLabels = <?= json_encode($labels_booking_month) ?>;
                const bookingMonthData = <?= json_encode($booking_month) ?>;

                // กราฟยอดขายรายวัน
                new Chart(document.getElementById('salesDayChart'), {
                    type: 'line',
                    data: {
                        labels: salesDayLabels,
                        datasets: [{
                            label: 'ยอดขาย (บาท)',
                            data: salesDayData,
                            borderColor: '#4e73df',
                            backgroundColor: 'rgba(78, 115, 223, 0.05)',
                            tension: 0.4,
                            fill: true,
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
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    callback: value => '฿' + value.toLocaleString()
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

                // กราฟยอดขายรายสัปดาห์
                new Chart(document.getElementById('salesWeekChart'), {
                    type: 'bar',
                    data: {
                        labels: salesWeekLabels,
                        datasets: [{
                            label: 'ยอดขาย (บาท)',
                            data: salesWeekData,
                            backgroundColor: '#1cc88a',
                            borderRadius: 5
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
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    callback: value => '฿' + value.toLocaleString()
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

                // กราฟยอดขายรายเดือน
                new Chart(document.getElementById('salesMonthChart'), {
                    type: 'bar',
                    data: {
                        labels: salesMonthLabels,
                        datasets: [{
                            label: 'ยอดขาย (บาท)',
                            data: salesMonthData,
                            backgroundColor: '#e74a3b',
                            borderRadius: 5
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
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    callback: value => '฿' + value.toLocaleString()
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

                // กราฟยอดขายรายปี
                new Chart(document.getElementById('salesYearChart'), {
                    type: 'bar',
                    data: {
                        labels: salesYearLabels,
                        datasets: [{
                            label: 'ยอดขาย (บาท)',
                            data: salesYearData,
                            backgroundColor: '#36b9cc',
                            borderRadius: 5
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
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    callback: value => '฿' + value.toLocaleString()
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

                // กราฟการจองเข้าชมสวน (รายเดือน)
                const bookingTotal = bookingMonthData.reduce((a, b) => a + Number(b), 0);
                document.getElementById('centerBookingCount').textContent = bookingTotal.toLocaleString() + ' คณะ';
                new Chart(document.getElementById('bookingMonthChart'), {
                    type: 'doughnut',
                    data: {
                        labels: bookingMonthLabels,
                        datasets: [{
                            label: 'จำนวนการจอง',
                            data: bookingMonthData,
                            backgroundColor: [
                                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e',
                                '#e74a3b', '#6f42c1', '#fd7e14', '#20c997',
                                '#6610f2', '#6f42c1', '#e83e8c', '#20c997'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.parsed.toLocaleString() + ' คณะ';
                                    }
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });

                // กราฟจำนวนสายพันธุ์มะม่วง
                new Chart(document.getElementById('mangoVarietyCountChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['สายพันธุ์มะม่วง', ''],
                        datasets: [{
                            data: [<?= $variety_count ?>, 100 - <?= $variety_count ?>],
                            backgroundColor: ['#43cea2', '#e9ecef'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: false
                            }
                        }
                    }
                });

                // กราฟเปรียบเทียบ
                const compareChart = new Chart(document.getElementById('compareSalesChart'), {
                    type: 'bar',
                    data: {
                        labels: ['รายวัน', 'รายสัปดาห์', 'รายเดือน', 'รายปี'],
                        datasets: [{
                            label: 'ยอดขายรวม',
                            data: [
                                salesDayData.reduce((a, b) => a + b, 0),
                                salesWeekData.reduce((a, b) => a + b, 0),
                                salesMonthData.reduce((a, b) => a + b, 0),
                                salesYearData.reduce((a, b) => a + b, 0)
                            ],
                            backgroundColor: [
                                '#4e73df', '#1cc88a', '#e74a3b', '#36b9cc'
                            ]
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
                                        return 'ยอดขายรวม: ฿' + context.parsed.y.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: value => '฿' + value.toLocaleString()
                                }
                            }
                        }
                    }
                });

                // Toggle between chart views
                document.getElementById('showCompareChartBtn').addEventListener('click', function() {
                    document.getElementById('allSalesCharts').classList.add('d-none');
                    document.getElementById('compareSalesChartWrapper').classList.remove('d-none');
                    this.classList.add('active');
                    document.getElementById('showAllChartsBtn').classList.remove('active');
                });

                document.getElementById('showAllChartsBtn').addEventListener('click', function() {
                    document.getElementById('allSalesCharts').classList.remove('d-none');
                    document.getElementById('compareSalesChartWrapper').classList.add('d-none');
                    this.classList.add('active');
                    document.getElementById('showCompareChartBtn').classList.remove('active');
                });

                // Initially show all charts (กราฟแยก) เป็นอันแรก
                document.getElementById('allSalesCharts').classList.remove('d-none');
                document.getElementById('compareSalesChartWrapper').classList.add('d-none');
                document.getElementById('showAllChartsBtn').classList.add('active');
                document.getElementById('showCompareChartBtn').classList.remove('active');

                // กราฟเปรียบเทียบการจองเข้าชมสวน (รายเดือน) แบบ Line Chart ทันสมัย
                const ctxBookingCompare = document.getElementById('bookingCompareChart').getContext('2d');
                // สร้าง gradient
                const gradient = ctxBookingCompare.createLinearGradient(0, 0, 0, 320);
                gradient.addColorStop(0, 'rgba(67, 206, 162, 0.35)');
                gradient.addColorStop(1, 'rgba(67, 206, 162, 0.02)');

                new Chart(ctxBookingCompare, {
                    type: 'line',
                    data: {
                        labels: bookingMonthLabels,
                        datasets: [{
                            label: 'จำนวนการจอง (คณะ) ต่อเดือน',
                            data: bookingMonthData,
                            borderColor: '#43cea2',
                            backgroundColor: gradient,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#43cea2',
                            pointBorderColor: '#185a9d',
                            pointRadius: 6,
                            pointHoverRadius: 10,
                            pointStyle: 'circle'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                labels: {
                                    color: '#185a9d',
                                    font: { weight: 'bold', size: 14 }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#185a9d',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                borderColor: '#43cea2',
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.parsed.y.toLocaleString() + ' คณะ';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: '#185a9d',
                                    font: { size: 13 },
                                    callback: value => value + ' คณะ'
                                },
                                grid: {
                                    color: 'rgba(67, 206, 162, 0.08)'
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#185a9d',
                                    font: { size: 13 }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });

                // เพิ่ม event listener สำหรับปุ่มกราฟเปรียบเทียบการจอง
                let bookingCompareActive = false;
                document.getElementById('showBookingCompareBtn').addEventListener('click', function() {
                    bookingCompareActive = !bookingCompareActive;
                    if (bookingCompareActive) {
                        document.getElementById('allSalesCharts').classList.add('d-none');
                        document.getElementById('compareSalesChartWrapper').classList.add('d-none');
                        document.getElementById('bookingCompareChartWrapper').classList.remove('d-none');
                        this.classList.add('active');
                        document.getElementById('showAllChartsBtn').classList.remove('active');
                        document.getElementById('showCompareChartBtn').classList.remove('active');
                    } else {
                        document.getElementById('allSalesCharts').classList.remove('d-none');
                        document.getElementById('compareSalesChartWrapper').classList.add('d-none');
                        document.getElementById('bookingCompareChartWrapper').classList.add('d-none');
                        this.classList.remove('active');
                        document.getElementById('showAllChartsBtn').classList.add('active');
                        document.getElementById('showCompareChartBtn').classList.remove('active');
                    }
                });
            </script>
        </div>
</body>

</html>