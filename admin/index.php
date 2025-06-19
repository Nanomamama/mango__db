<?php
require_once 'auth.php';
require_once 'db.php';

// ตัวอย่าง: ดึงยอดขายรายวัน 7 วันล่าสุด
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

// ดึงยอดขายรายสัปดาห์ 8 สัปดาห์ล่าสุด
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

// ดึงยอดขายรายเดือน 12 เดือนล่าสุด
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

// ดึงยอดขายรายปี 5 ปีล่าสุด
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

// ดึงยอดมัดจำรวมและยอดคงเหลือรวมจาก bookings
$sql = "SELECT SUM(deposit_amount) AS deposit_total, SUM(remain_amount) AS remain_total FROM bookings";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$deposit_total = $row['deposit_total'] ?? 0;
$remain_total = $row['remain_total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Dashboard ข้อมูลการขายและการจอง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

        <style>
          * {
              font-family: "Kanit", sans-serif;
          }
        </style>

</head>

<body>

    <?php include 'sidebar.php'; ?>

    <div class="d-flex">
        <div class="p-4" style="margin-left: 250px; flex: 1;">
    <h2 class="mb-4">Dashboard ข้อมูลการขายสินค้า</h2>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card p-3">
                <h5 class="mb-3">ยอดขายรายวัน (7 วันล่าสุด)</h5>
                <canvas id="salesDayChart"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card p-3">
                <h5 class="mb-3">ยอดขายรายสัปดาห์ (8 สัปดาห์ล่าสุด)</h5>
                <canvas id="salesWeekChart"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card p-3">
                <h5 class="mb-3">ยอดขายรายเดือน (12 เดือนล่าสุด)</h5>
                <canvas id="salesMonthChart"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card p-3">
                <h5 class="mb-3">ยอดขายรายปี (5 ปีล่าสุด)</h5>
                <canvas id="salesYearChart"></canvas>
            </div>
        </div>
    </div>
    <h2 class="mt-5 mb-4">กราฟการจองเข้าชมสวน (รายเดือน)</h2>
    <div class="card shadow-sm p-4" style="max-width:600px;margin:auto;">
        <div class="d-flex flex-column align-items-center">
            <canvas id="bookingMonthChart" style="max-width:350px;max-height:350px;"></canvas>
            <div class="row mt-4 w-100 justify-content-center">
                <div class="col-auto">
                    <div class="card border-0 bg-gradient-info text-white shadow-sm px-4 py-2 mb-2" style="background: linear-gradient(90deg, #36b9cc 0%, #4e73df 100%);">
                        <span class="fw-bold">ยอดมัดจำรวม</span>
                        <span class="fs-5 ms-2"><?= number_format($deposit_total, 2) ?> <small>บาท</small></span>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="card border-0 bg-gradient-warning text-dark shadow-sm px-4 py-2 mb-2" style="background: linear-gradient(90deg, #f6c23e 0%, #f8f9fc 100%);">
                        <span class="fw-bold">ยอดคงเหลือรวม</span>
                        <span class="fs-5 ms-2"><?= number_format($remain_total, 2) ?> <small>บาท</small></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <h2 class="mb-4">กราฟยอดขายสินค้า</h2>
    <div class="mb-3" style="max-width:350px;">
        <select id="salesType" class="form-select">
            <option value="day">รายวัน</option>
            <option value="week">รายสัปดาห์</option>
            <option value="month">รายเดือน</option>
            <option value="year">รายปี</option>
        </select>
    </div>
    <div class="mb-2">
        <span id="totalLabel" class="badge bg-primary fs-5"></span>
    </div>
    <div class="card p-3">
        <canvas id="salesChart"></canvas>
    </div>
</div>



<script>
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
const total = bookingMonthData.reduce((a, b) => a + Number(b), 0); // เพิ่มบรรทัดนี้

// กราฟยอดขายรายวัน
new Chart(document.getElementById('salesDayChart'), {
    type: 'line',
    data: {
        labels: salesDayLabels,
        datasets: [{
            label: 'ยอดขาย (บาท)',
            data: salesDayData,
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78,115,223,0.08)',
            tension: 0.4,
            fill: true,
            pointRadius: 3
        }]
    }
});

// กราฟยอดขายรายสัปดาห์
new Chart(document.getElementById('salesWeekChart'), {
    type: 'line',
    data: {
        labels: salesWeekLabels,
        datasets: [{
            label: 'ยอดขาย (บาท)',
            data: salesWeekData,
            borderColor: '#1cc88a',
            backgroundColor: 'rgba(28,200,138,0.08)',
            tension: 0.4,
            fill: true,
            pointRadius: 3
        }]
    }
});
// กราฟยอดขายรายเดือน
new Chart(document.getElementById('salesMonthChart'), {
    type: 'line',
    data: {
        labels: salesMonthLabels,
        datasets: [{
            label: 'ยอดขาย (บาท)',
            data: salesMonthData,
            borderColor: '#e74a3b',
            backgroundColor: 'rgba(231,74,59,0.08)',
            tension: 0.4,
            fill: true,
            pointRadius: 3
        }]
    }
});
// กราฟยอดขายรายปี
new Chart(document.getElementById('salesYearChart'), {
    type: 'line',
    data: {
        labels: salesYearLabels,
        datasets: [{
            label: 'ยอดขาย (บาท)',
            data: salesYearData,
            borderColor: '#36b9cc',
            backgroundColor: 'rgba(54,185,204,0.08)',
            tension: 0.4,
            fill: true,
            pointRadius: 3
        }]
    }
});
// กราฟการจองเข้าชมสวน (รายเดือน) แบบวงกลม
new Chart(document.getElementById('bookingMonthChart'), {
    type: 'doughnut',
    data: {
        labels: bookingMonthLabels,
        datasets: [{
            label: 'จำนวนการจอง',
            data: bookingMonthData,
            backgroundColor: bookingMonthLabels.map(() => '#f6c23e')
        }]
    },
    options: {
        plugins: {
            legend: { position: 'bottom' },
            centerText: {
                display: true,
                text: total.toLocaleString() + ' คน'
            }
        }
    },
    plugins: [{
        id: 'centerText',
        afterDraw(chart) {
            if (chart.config.options.plugins.centerText && chart.config.options.plugins.centerText.display) {
                const ctx = chart.ctx;
                const text = chart.config.options.plugins.centerText.text;
                ctx.save();
                ctx.font = 'bold 1.5rem sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillStyle = '#333';
                ctx.fillText(text, chart.getDatasetMeta(0).data[0].x, chart.getDatasetMeta(0).data[0].y);
                ctx.restore();
            }
        }
    }]
});

const salesData = {
    day: {
        labels: <?= json_encode($labels_day) ?>,
        data: <?= json_encode($sales_day) ?>
    },
    week: {
        labels: <?= json_encode($labels_week) ?>,
        data: <?= json_encode($sales_week) ?>
    },
    month: {
        labels: <?= json_encode($labels_month) ?>,
        data: <?= json_encode($sales_month) ?>
    },
    year: {
        labels: <?= json_encode($labels_year) ?>,
        data: <?= json_encode($sales_year) ?>
    }
};

function updateTotalLabel(type) {
    const total = salesData[type].data.reduce((a, b) => a + Number(b), 0);
    let label = '';
    switch(type) {
        case 'day': label = 'ยอดขายรวม 7 วันล่าสุด: '; break;
        case 'week': label = 'ยอดขายรวม 8 สัปดาห์ล่าสุด: '; break;
        case 'month': label = 'ยอดขายรวม 12 เดือนล่าสุด: '; break;
        case 'year': label = 'ยอดขายรวม 5 ปีล่าสุด: '; break;
    }
    document.getElementById('totalLabel').textContent = label + total.toLocaleString() + ' บาท';
}

const ctx = document.getElementById('salesChart').getContext('2d');
let chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: salesData.day.labels,
        datasets: [{
            label: 'ยอดขาย (บาท)',
            data: salesData.day.data,
            backgroundColor: '#4e73df'
        }]
    },
    options: {
        plugins: {
            legend: { display: false },
            tooltip: { enabled: true }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) { return value.toLocaleString(); }
                }
            }
        }
    }
});

updateTotalLabel('day');

document.getElementById('salesType').addEventListener('change', function() {
    const type = this.value;
    chart.data.labels = salesData[type].labels;
    chart.data.datasets[0].data = salesData[type].data;
    chart.update();
    updateTotalLabel(type);
});
</script>
</body>
</html>