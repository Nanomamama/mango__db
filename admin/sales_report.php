<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';
require_once 'sidebar.php';

function reportDate(?string $value, string $fallback): string
{
    if (!$value) {
        return $fallback;
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value ? $value : $fallback;
}

function baht(float $amount): string
{
    return number_format($amount, 2);
}

function qty(int|float $amount): string
{
    return number_format($amount);
}

function reportDateFromParts(string $prefix, ?string $fallback): ?string
{
    $day = (int) ($_GET[$prefix . '_day'] ?? 0);
    $month = (int) ($_GET[$prefix . '_month'] ?? 0);
    $year = (int) ($_GET[$prefix . '_year'] ?? 0);

    if ($day < 1 || $month < 1 || $year < 1) {
        return $fallback;
    }

    if (!checkdate($month, $day, $year)) {
        return $fallback;
    }

    return sprintf('%04d-%02d-%02d', $year, $month, $day);
}

function productPriceText(array $row): string
{
    $minPrice = (float) ($row['min_price'] ?? 0);
    $maxPrice = (float) ($row['max_price'] ?? $minPrice);

    if (abs($minPrice - $maxPrice) < 0.01) {
        return '฿' . baht($minPrice);
    }

    return '฿' . baht($minPrice) . ' - ฿' . baht($maxPrice);
}

$thaiMonths = [
    1 => 'มกราคม',
    2 => 'กุมภาพันธ์',
    3 => 'มีนาคม',
    4 => 'เมษายน',
    5 => 'พฤษภาคม',
    6 => 'มิถุนายน',
    7 => 'กรกฎาคม',
    8 => 'สิงหาคม',
    9 => 'กันยายน',
    10 => 'ตุลาคม',
    11 => 'พฤศจิกายน',
    12 => 'ธันวาคม',
];

$today = date('Y-m-d');
$latestSalesDate = $today;

$stmt = $conn->prepare("
    SELECT DATE(MAX(receive_datetime)) AS latest_sales_date
    FROM orders
    WHERE order_status = 'completed'
      AND receive_datetime IS NOT NULL
");
if ($stmt) {
    $stmt->execute();
    $latestSalesRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $latestSalesDate = reportDate($latestSalesRow['latest_sales_date'] ?? null, $today);
}

$hasDateFilter = isset($_GET['start_date'], $_GET['end_date'])
    || isset($_GET['start_day'], $_GET['start_month'], $_GET['start_year'])
    || isset($_GET['end_day'], $_GET['end_month'], $_GET['end_year']);
$defaultEndDate = $hasDateFilter ? $today : $latestSalesDate;
$monthStart = date('Y-m-01', strtotime($defaultEndDate));
$startDate = reportDateFromParts('start', reportDate($_GET['start_date'] ?? null, $monthStart)) ?? $monthStart;
$endDate = reportDateFromParts('end', reportDate($_GET['end_date'] ?? null, $defaultEndDate)) ?? $defaultEndDate;

if ($startDate > $endDate) {
    [$startDate, $endDate] = [$endDate, $startDate];
}

$groupBy = $_GET['group_by'] ?? 'day';
$groupBy = in_array($groupBy, ['day', 'month', 'year'], true) ? $groupBy : 'day';

$startDateTime = $startDate . ' 00:00:00';
$endDateTime = $endDate . ' 23:59:59';

$summary = [
    'total_sales' => 0.0,
    'total_orders' => 0,
    'total_items' => 0,
    'avg_order_value' => 0.0,
];

$stmt = $conn->prepare("
    SELECT
        COALESCE(SUM(o.total_amount), 0) AS total_sales,
        COUNT(DISTINCT o.order_id) AS total_orders,
        COALESCE((
            SELECT SUM(oi.quantity)
            FROM order_items oi
            INNER JOIN orders item_orders ON item_orders.order_id = oi.order_id
            WHERE item_orders.order_status = 'completed'
              AND item_orders.receive_datetime BETWEEN ? AND ?
        ), 0) AS total_items
    FROM orders o
    WHERE o.order_status = 'completed'
      AND o.receive_datetime BETWEEN ? AND ?
");
$stmt->bind_param('ssss', $startDateTime, $endDateTime, $startDateTime, $endDateTime);
$stmt->execute();
$summaryRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($summaryRow) {
    $summary['total_sales'] = (float) ($summaryRow['total_sales'] ?? 0);
    $summary['total_orders'] = (int) ($summaryRow['total_orders'] ?? 0);
    $summary['total_items'] = (int) ($summaryRow['total_items'] ?? 0);
    $summary['avg_order_value'] = $summary['total_orders'] > 0
        ? $summary['total_sales'] / $summary['total_orders']
        : 0.0;
}

$productRows = [];
$stmt = $conn->prepare("
    SELECT
        oi.product_id,
        COALESCE(NULLIF(oi.product_name, ''), p.product_name, 'ไม่ระบุสินค้า') AS product_name,
        SUM(oi.quantity) AS total_quantity,
        MIN(oi.price) AS min_price,
        MAX(oi.price) AS max_price,
        SUM(oi.quantity * oi.price) AS total_sales
    FROM order_items oi
    INNER JOIN orders o ON o.order_id = oi.order_id
    LEFT JOIN products p ON p.product_id = oi.product_id
    WHERE o.order_status = 'completed'
      AND o.receive_datetime BETWEEN ? AND ?
    GROUP BY oi.product_id, product_name
    ORDER BY total_sales DESC, total_quantity DESC
");
$stmt->bind_param('ss', $startDateTime, $endDateTime);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $productRows[] = $row;
}
$stmt->close();

$groupExpression = match ($groupBy) {
    'month' => "DATE_FORMAT(o.receive_datetime, '%Y-%m')",
    'year' => "DATE_FORMAT(o.receive_datetime, '%Y')",
    default => "DATE(o.receive_datetime)",
};

$chartRows = [];
$sql = "
    SELECT
        {$groupExpression} AS period_label,
        COALESCE(SUM(o.total_amount), 0) AS total_sales,
        COUNT(DISTINCT o.order_id) AS total_orders
    FROM orders o
    WHERE o.order_status = 'completed'
      AND o.receive_datetime BETWEEN ? AND ?
    GROUP BY period_label
    ORDER BY period_label
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $startDateTime, $endDateTime);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $chartRows[] = $row;
}
$stmt->close();

$topProductName = $productRows[0]['product_name'] ?? '-';
$chartLabels = array_map(static fn(array $row): string => (string) $row['period_label'], $chartRows);
$chartSales = array_map(static fn(array $row): float => (float) $row['total_sales'], $chartRows);
$topLabels = array_map(static fn(array $row): string => (string) $row['product_name'], array_slice($productRows, 0, 8));
$topSales = array_map(static fn(array $row): float => (float) $row['total_sales'], array_slice($productRows, 0, 8));
$startParts = date_parse($startDate);
$endParts = date_parse($endDate);
$yearStart = (int) min(date('Y', strtotime($monthStart)), date('Y', strtotime($startDate)), date('Y', strtotime($endDate)), date('Y', strtotime('-11 months')));
$yearEnd = (int) max(date('Y'), date('Y', strtotime($latestSalesDate)), date('Y', strtotime($startDate)), date('Y', strtotime($endDate)));

adminPageStart('รายงานการขายสินค้า');
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<style>
    .sales-report-shell {
        display: grid;
        gap: 22px;
        max-width: 100%;
        min-width: 0;
    }

    .report-panel,
    .report-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
    }

    .report-panel {
        padding: 22px;
        min-width: 0;
    }

    .report-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
        min-width: 0;
    }

    .report-header > div {
        min-width: 0;
    }

    .report-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.35rem;
        font-weight: 700;
    }

    .report-subtitle {
        margin: 6px 0 0;
        color: #64748b;
        line-height: 1.7;
    }

    .report-filter {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 14px;
        align-items: end;
        margin-top: 20px;
        min-width: 0;
    }

    .report-filter .filter-field:nth-child(1),
    .report-filter .filter-field:nth-child(2) {
        grid-column: span 6;
    }

    .report-filter .filter-field:nth-child(3),
    .report-filter .filter-field:nth-child(4) {
        grid-column: span 4;
    }

    .report-filter > .report-btn {
        grid-column: span 4;
        width: 100%;
    }

    .filter-field label {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: 0.88rem;
        font-weight: 600;
    }

    .filter-field {
        min-width: 0;
    }

    .filter-field input,
    .filter-field select {
        width: 100%;
        height: 44px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 0 12px;
        color: #0f172a;
        font: inherit;
        background: #ffffff;
        transition: 0.2s ease;
        min-width: 0;
    }

    .date-select-row {
        display: grid;
        grid-template-columns: minmax(70px, 0.75fr) minmax(126px, 1.45fr) minmax(88px, 0.9fr);
        gap: 8px;
        min-width: 0;
    }

    .report-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .report-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        border: 1px solid transparent;
        border-radius: 12px;
        padding: 0 16px;
        color: #ffffff;
        background: #016A70;
        text-decoration: none;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .report-btn:hover {
        background: #01545a;
        color: #ffffff;
        transform: translateY(-1px);
    }

    .report-btn.secondary {
        border-color: #cbd5e1;
        color: #016A70;
        background: #f8fafc;
    }

    .report-btn.secondary:hover {
        background: #ecfdf5;
        color: #016A70;
    }

    .quick-ranges {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 14px;
    }

    .quick-ranges a {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0 12px;
        border: 1px solid #dbe4ea;
        border-radius: 999px;
        color: #0f766e;
        background: #f8fafc;
        font-size: 0.86rem;
        font-weight: 600;
        text-decoration: none;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .report-card {
        padding: 18px;
        min-width: 0;
    }

    .card-label {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .card-value {
        margin-top: 8px;
        color: #0f172a;
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1.1;
        overflow-wrap: anywhere;
    }

    .card-note {
        margin-top: 8px;
        color: #0f766e;
        font-size: 0.86rem;
        font-weight: 600;
    }

    .chart-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(300px, 0.9fr);
        gap: 18px;
        min-width: 0;
    }

    .chart-box {
        height: 320px;
        margin-top: 16px;
        max-width: 100%;
        min-width: 0;
        position: relative;
    }

    .table-wrap {
        margin-top: 16px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 760px;
    }

    .report-table th,
    .report-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
        vertical-align: middle;
    }

    .report-table th {
        color: #475569;
        background: #f8fafc;
        font-size: 0.86rem;
        font-weight: 700;
    }

    .report-table td {
        color: #0f172a;
    }

    .text-end {
        text-align: right !important;
    }

    .empty-state {
        padding: 34px 20px;
        text-align: center;
        color: #64748b;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
    }

    @media (max-width: 1180px) {
        .report-filter .filter-field:nth-child(1),
        .report-filter .filter-field:nth-child(2) {
            grid-column: span 6;
        }

        .report-filter .filter-field:nth-child(3),
        .report-filter .filter-field:nth-child(4) {
            grid-column: span 4;
        }

        .report-filter > .report-btn {
            grid-column: span 4;
        }
    }

    @media (max-width: 1100px) {
        .summary-grid {
            grid-template-columns: 1fr 1fr;
        }

        .chart-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 860px) {
        .report-filter .filter-field:nth-child(1),
        .report-filter .filter-field:nth-child(2),
        .report-filter .filter-field:nth-child(3),
        .report-filter .filter-field:nth-child(4),
        .report-filter > .report-btn {
            grid-column: 1 / -1;
        }

        .date-select-row {
            grid-template-columns: minmax(68px, 0.8fr) minmax(120px, 1.4fr) minmax(86px, 1fr);
        }
    }

    @media (max-width: 720px) {
        .sales-report-shell {
            gap: 16px;
        }

        .report-panel,
        .report-card {
            border-radius: 14px;
        }

        .report-panel {
            padding: 16px;
        }

        .report-card {
            padding: 16px;
        }

        .report-title {
            font-size: 1.12rem;
            line-height: 1.35;
        }

        .report-subtitle {
            font-size: 0.9rem;
        }

        .summary-grid,
        .chart-grid {
            grid-template-columns: 1fr;
        }

        .report-header {
            align-items: stretch;
        }

        .report-actions,
        .report-btn {
            width: 100%;
        }

        .quick-ranges {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .quick-ranges a {
            justify-content: center;
            min-height: 38px;
            text-align: center;
        }

        .date-select-row {
            grid-template-columns: 1fr;
        }

        .card-value {
            font-size: 1.35rem;
        }

        .chart-box {
            height: 260px;
        }

        .table-wrap {
            overflow-x: visible;
        }

        .report-table {
            min-width: 0;
            border-collapse: separate;
            border-spacing: 0 12px;
        }

        .report-table thead {
            display: none;
        }

        .report-table,
        .report-table tbody,
        .report-table tr,
        .report-table td {
            display: block;
            width: 100%;
        }

        .report-table tr {
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        }

        .report-table td {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 9px 0;
            border-bottom: 1px solid #edf2f7;
            text-align: right !important;
            overflow-wrap: anywhere;
        }

        .report-table td:last-child {
            border-bottom: 0;
        }

        .report-table td::before {
            content: attr(data-label);
            color: #64748b;
            font-weight: 700;
            text-align: left;
            flex: 0 0 42%;
        }

        .report-table td:nth-child(2) {
            display: block;
            text-align: left !important;
        }

        .report-table td:nth-child(2)::before {
            display: block;
            margin-bottom: 4px;
            flex: none;
        }
    }

    @media (max-width: 420px) {
        .sales-report-shell {
            gap: 12px;
        }

        .report-panel {
            padding: 14px 12px;
        }

        .quick-ranges {
            grid-template-columns: 1fr;
        }

        .filter-field input,
        .filter-field select,
        .report-btn {
            min-height: 46px;
        }

        .chart-box {
            height: 220px;
        }

        .report-table tr {
            padding: 10px;
        }

        .report-table td {
            display: block;
            text-align: left !important;
        }

        .report-table td::before {
            display: block;
            margin-bottom: 3px;
            flex: none;
        }

        .card-value {
            font-size: 1.22rem;
        }
    }
</style>

<div class="sales-report-shell">
    <section class="report-panel">
        <div class="report-header">
            <div>
                <h2 class="report-title">รายงานการขายสินค้า</h2>
                <p class="report-subtitle">เลือกช่วงวันที่รับสินค้าเพื่อดูยอดขายจริงจากออเดอร์ที่สำเร็จแล้ว แยกตามสินค้าและช่วงเวลา</p>
            </div>
            <div class="report-actions">
                <a href="manage_orders.php" class="report-btn secondary">
                    <i class="fa-solid fa-arrow-left"></i>
                    กลับคำสั่งซื้อ
                </a>
            </div>
        </div>

        <form method="GET" class="report-filter">
            <div class="filter-field">
                <label for="start_date">วันที่รับสินค้าเริ่มต้น</label>
                <div class="date-select-row" id="start_date">
                    <select name="start_day" aria-label="วันเริ่มต้น">
                        <?php for ($day = 1; $day <= 31; $day++): ?>
                            <option value="<?= $day ?>" <?= (int) $startParts['day'] === $day ? 'selected' : '' ?>><?= $day ?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="start_month" aria-label="เดือนเริ่มต้น">
                        <?php foreach ($thaiMonths as $month => $monthName): ?>
                            <option value="<?= $month ?>" <?= (int) $startParts['month'] === $month ? 'selected' : '' ?>><?= h($monthName) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="start_year" aria-label="ปีเริ่มต้น">
                        <?php for ($year = $yearEnd; $year >= $yearStart; $year--): ?>
                            <option value="<?= $year ?>" <?= (int) $startParts['year'] === $year ? 'selected' : '' ?>><?= $year + 543 ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="filter-field">
                <label for="end_date">วันที่รับสินค้าสิ้นสุด</label>
                <div class="date-select-row" id="end_date">
                    <select name="end_day" aria-label="วันสิ้นสุด">
                        <?php for ($day = 1; $day <= 31; $day++): ?>
                            <option value="<?= $day ?>" <?= (int) $endParts['day'] === $day ? 'selected' : '' ?>><?= $day ?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="end_month" aria-label="เดือนสิ้นสุด">
                        <?php foreach ($thaiMonths as $month => $monthName): ?>
                            <option value="<?= $month ?>" <?= (int) $endParts['month'] === $month ? 'selected' : '' ?>><?= h($monthName) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="end_year" aria-label="ปีสิ้นสุด">
                        <?php for ($year = $yearEnd; $year >= $yearStart; $year--): ?>
                            <option value="<?= $year ?>" <?= (int) $endParts['year'] === $year ? 'selected' : '' ?>><?= $year + 543 ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="filter-field">
                <label for="group_by">ดูกราฟแบบ</label>
                <select id="group_by" name="group_by">
                    <option value="day" <?= $groupBy === 'day' ? 'selected' : '' ?>>รายวัน</option>
                    <option value="month" <?= $groupBy === 'month' ? 'selected' : '' ?>>รายเดือน</option>
                    <option value="year" <?= $groupBy === 'year' ? 'selected' : '' ?>>รายปี</option>
                </select>
            </div>
            <div class="filter-field">
                <label>สถานะที่นับยอด</label>
                <input type="text" value="สำเร็จแล้วเท่านั้น" readonly>
            </div>
            <button type="submit" class="report-btn">
                <i class="fa-solid fa-filter"></i>
                ดูรายงาน
            </button>
        </form>

        <div class="quick-ranges">
            <a href="?start_date=<?= h($today) ?>&end_date=<?= h($today) ?>&group_by=day">วันนี้</a>
            <a href="?start_date=<?= h($monthStart) ?>&end_date=<?= h($today) ?>&group_by=day">เดือนนี้</a>
            <a href="?start_date=<?= h(date('Y-01-01')) ?>&end_date=<?= h($today) ?>&group_by=month">ปีนี้</a>
            <a href="?start_date=<?= h(date('Y-m-d', strtotime('-11 months'))) ?>&end_date=<?= h($today) ?>&group_by=month">12 เดือนล่าสุด</a>
        </div>
    </section>

    <section class="summary-grid">
        <article class="report-card">
            <div class="card-label">ยอดขายรวม</div>
            <div class="card-value">฿<?= baht($summary['total_sales']) ?></div>
            <div class="card-note"><?= h($startDate) ?> ถึง <?= h($endDate) ?></div>
        </article>
        <article class="report-card">
            <div class="card-label">จำนวนออเดอร์</div>
            <div class="card-value"><?= qty($summary['total_orders']) ?></div>
            <div class="card-note">ออเดอร์ที่สำเร็จแล้ว</div>
        </article>
        <article class="report-card">
            <div class="card-label">จำนวนสินค้าที่ขาย</div>
            <div class="card-value"><?= qty($summary['total_items']) ?></div>
            <div class="card-note">รวมทุกสินค้า</div>
        </article>
        <article class="report-card">
            <div class="card-label">สินค้าขายดีที่สุด</div>
            <div class="card-value" style="font-size:1.15rem;line-height:1.45;"><?= h($topProductName) ?></div>
    </section>

    <section class="chart-grid">
        <article class="report-panel">
            <div class="report-header">
                <div>
                    <h2 class="report-title">ยอดขายตามช่วงเวลา</h2>
                    <p class="report-subtitle">แสดงตามรูปแบบที่เลือกจากตัวกรอง</p>
                </div>
            </div>
            <div class="chart-box">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </article>

        <article class="report-panel">
            <div class="report-header">
                <div>
                    <h2 class="report-title">สินค้าอันดับต้น</h2>
                    <p class="report-subtitle">เรียงตามยอดขายรวม</p>
                </div>
            </div>
            <div class="chart-box">
                <canvas id="topProductChart"></canvas>
            </div>
        </article>
    </section>

    <section class="report-panel">
        <div class="report-header">
            <div>
                <h2 class="report-title">รายละเอียดการขายสินค้า</h2>
                <p class="report-subtitle">สรุปจำนวนและยอดขายของสินค้าแต่ละรายการในช่วงที่เลือก</p>
            </div>
        </div>

        <?php if ($productRows): ?>
            <div class="table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>สินค้า</th>
                            <th class="text-end">จำนวนที่ขาย</th>
                            <th class="text-end">ราคาสินค้า</th>
                            <th class="text-end">ยอดขายรวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productRows as $index => $row): ?>
                            <tr>
                                <td data-label="ลำดับ"><?= $index + 1 ?></td>
                                <td data-label="สินค้า"><?= h((string) $row['product_name']) ?></td>
                                <td data-label="จำนวนที่ขาย" class="text-end"><?= qty((int) $row['total_quantity']) ?></td>
                                <td data-label="ราคาสินค้า" class="text-end"><?= h(productPriceText($row)) ?></td>
                                <td data-label="ยอดขายรวม" class="text-end">฿<?= baht((float) $row['total_sales']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">ไม่พบข้อมูลการขายสินค้าในช่วงวันที่เลือก</div>
        <?php endif; ?>
    </section>
</div>

<script>
    const trendLabels = <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const trendSales = <?= json_encode($chartSales, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const topLabels = <?= json_encode($topLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const topSales = <?= json_encode($topSales, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    const moneyTick = value => Number(value || 0).toLocaleString('th-TH');

    new Chart(document.getElementById('salesTrendChart'), {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'ยอดขาย',
                data: trendSales,
                borderColor: '#016A70',
                backgroundColor: 'rgba(1, 106, 112, 0.12)',
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#016A70'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: context => `ยอดขาย ฿${moneyTick(context.parsed.y)}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: value => `฿${moneyTick(value)}` }
                }
            }
        }
    });

    new Chart(document.getElementById('topProductChart'), {
        type: 'bar',
        data: {
            labels: topLabels,
            datasets: [{
                label: 'ยอดขาย',
                data: topSales,
                backgroundColor: '#0d8a92',
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: context => `ยอดขาย ฿${moneyTick(context.parsed.x)}`
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { callback: value => `฿${moneyTick(value)}` }
                }
            }
        }
    });
</script>

<?php adminPageEnd(); ?>
