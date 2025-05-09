<?php
  require_once 'auth.php';
  require_once 'db.php';
  
  $mango_count = 200;
  $mango_max = 1000;

  $booking_count = 400;
  $booking_max = 1000;

  $course_total = 500;
  $course_max = 1000;

  $product_count = 300;
  $product_max = 1000;
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
      font-family: 'Kanit', sans-serif;
    }

    .dashboard-title {
      font-weight: 700;
      color: #4e73df;
      letter-spacing: 1px;
      margin-bottom: 32px;
    }

    .chart-container {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
      padding: 10px 4px 4px 4px;
      margin-bottom: 24px;
      min-height: 80px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      transition: box-shadow 0.3s;
      position: relative;
    }

    .chart-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: #5a5c69;
      margin-bottom: 12px;
      letter-spacing: 0.5px;
    }

    .chart-center {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-weight: bold;
      font-size: 1.2rem;
      pointer-events: none;
    }

    canvas {
      width: 150px !important;
      height: 150px !important;
    }

    /* เพิ่มใน <style> */
    .line-sales-chart {
      width: 100% !important;
      height: 220px !important;
      max-width: 100%;
      display: block;
      margin: 0 auto;
    }

  </style>
</head>

<body>
  <?php include 'sidebar.php'; ?>

  <div class="d-flex">
    <div class="p-4" style="margin-left: 250px; flex: 1;">
      <h1 class="dashboard-title mb-5">Admin Dashboard</h1>
      <div class="row g-4 justify-content-center">
        <div class="col-lg-3 col-md-6">
          <div class="chart-container">
            <div class="chart-title text-center mb-2"><i class='bx bxs-leaf text-success'></i> จำนวนสายพันธุ์มะม่วง</div>
            <div class="chart-center text-primary" id="mangoCenterText"></div>
            <canvas id="mangoChart"></canvas>
            <div class="mt-3 fs-5 fw-bold text-primary"><?= number_format($mango_count) ?> สายพันธุ์</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="chart-container">
            <div class="chart-title text-center mb-2"><i class='bx bxs-group text-warning'></i> จำนวนคณะจองเข้าชมสวน</div>
            <div class="chart-center text-warning" id="bookingCenterText"></div>
            <canvas id="bookingChart"></canvas>
            <div class="mt-3 fs-5 fw-bold text-warning"><?= number_format($booking_count) ?> คณะ</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="chart-container">
            <div class="chart-title text-center mb-2"><i class='bx bxs-book-content text-info'></i> จำนวนหลักสูตร</div>
            <div class="chart-center text-info" id="courseCenterText"></div>
            <canvas id="courseChart"></canvas>
            <div class="mt-3 fs-5 fw-bold text-info"><?= number_format($course_total) ?> หลักสูตร</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="chart-container">
            <div class="chart-title text-center mb-2"><i class='bx bx-package text-success'></i> จำนวนผลิตภัณฑ์</div>
            <div class="chart-center text-success" id="productCenterText"></div>
            <canvas id="productChart"></canvas>
            <div class="mt-3 fs-5 fw-bold text-success"><?= number_format($product_count) ?> ผลิตภัณฑ์</div>
          </div>
        </div>
      </div>

      <!-- ออเดอร์สินค้า -->

      <div class="row g-4 justify-content-center">
        <div class="col-lg-6 col-md-6">
          <div class="chart-container">
            <div class="chart-title text-center mb-2"><i class='bx bx-cart text-danger'></i> ออเดอร์วันนี้</div>
            <div class="chart-center text-danger" id="orderTodayCenterText"></div>
            <canvas id="orderTodayChart"></canvas>
            <div class="mt-3 fs-5 fw-bold text-danger"><?= number_format($order_today_count ?? 0) ?> ออเดอร์</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="chart-container">
            <div class="chart-title text-center mb-2"><i class='bx bx-calendar text-success'></i> ออเดอร์เดือนนี้</div>
            <div class="chart-center text-success" id="orderMonthCenterText"></div>
            <canvas id="orderMonthChart"></canvas>
            <div class="mt-3 fs-5 fw-bold text-success"><?= number_format($order_month_count ?? 0) ?> ออเดอร์</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="chart-container">
            <div class="chart-title text-center mb-2"><i class='bx bx-calendar-alt text-primary'></i> ออเดอร์ปีนี้</div>
            <div class="chart-center text-primary" id="orderYearCenterText"></div>
            <canvas id="orderYearChart"></canvas>
            <div class="mt-3 fs-5 fw-bold text-primary"><?= number_format($order_year_count ?? 0) ?> ออเดอร์</div>
          </div>
        </div>
      </div>

      <!-- เพิ่มกราฟยอดขายรายวัน รายเดือน รายปี -->
      <div class="row mt-4">
        <div class="col-lg-6 col-md-6 mb-4">
          <div class="chart-container" style="max-width:100%; margin:auto;">
            <div class="chart-title text-center mb-2">
              <i class='bx bx-line-chart text-danger'></i> กราฟยอดขายรายวัน
            </div>
            <canvas id="salesDayChart" class="line-sales-chart"></canvas>
          </div>
        </div>
        <div class="col-lg-6 col-md-6 mb-4">
          <div class="chart-container" style="max-width:100%; margin:auto;">
            <div class="chart-title text-center mb-2">
              <i class='bx bx-line-chart text-success'></i> กราฟยอดขายรายเดือน
            </div>
            <canvas id="salesMonthChart" class="line-sales-chart"></canvas>
          </div>
        </div>
        <div class="col-lg-6 col-md-12 mb-4">
          <div class="chart-container" style="max-width:100%; margin:auto;">
            <div class="chart-title text-center mb-2">
              <i class='bx bx-line-chart text-primary'></i> กราฟยอดขายรายปี
            </div>
            <canvas id="salesYearChart" class="line-sales-chart"></canvas>
          </div>
        </div>
      </div>

      <script>
        // ฟังก์ชันสร้าง Doughnut Chart แบบหมุน
        function createRotatingDonutChart(canvasId, count, max, color) {
          const ctx = document.getElementById(canvasId).getContext('2d');
          const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
              datasets: [{
                data: [count, max - count],
                backgroundColor: [color, '#e0e7ff'],
                borderColor: ['#fff', '#fff'],
                borderWidth: 1,
                hoverOffset: 10
              }]
            },
            options: {
              cutout: '70%',
              plugins: {
                legend: {
                  display: false
                },
                tooltip: {
                  enabled: false
                }
              },
              animation: false // ปิด animation ปกติ
            }
          });

          // หมุนกราฟ
          let angle = 0;

          function rotate() {
            angle += 0.5; // ปรับความเร็วหมุน
            chart.options.rotation = angle;
            chart.update('none');
            requestAnimationFrame(rotate);
          }
          rotate();
        }

        // เรียกใช้สำหรับแต่ละกราฟ
        createRotatingDonutChart('mangoChart', <?= $mango_count ?>, <?= $mango_max ?>, '#4e73df');
        createRotatingDonutChart('bookingChart', <?= $booking_count ?>, <?= $booking_max ?>, '#f6c23e');
        createRotatingDonutChart('courseChart', <?= $course_total ?>, <?= $course_max ?>, '#36b9cc');
        createRotatingDonutChart('productChart', <?= $product_count ?>, <?= $product_max ?>, '#1cc88a');

        // ตัวอย่างค่าจาก PHP (หรือดึงจากฐานข้อมูลจริง)
        const orderTodayCount = <?= isset($order_today_count) ? (int)$order_today_count : 12 ?>;
        const orderTodayMax = 100;
        const orderMonthCount = <?= isset($order_month_count) ? (int)$order_month_count : 320 ?>;
        const orderMonthMax = 3000;
        const orderYearCount = <?= isset($order_year_count) ? (int)$order_year_count : 2500 ?>;
        const orderYearMax = 20000;

        createRotatingDonutChart('orderTodayChart', orderTodayCount, orderTodayMax, '#e74a3b');
        createRotatingDonutChart('orderMonthChart', orderMonthCount, orderMonthMax, '#1cc88a');
        createRotatingDonutChart('orderYearChart', orderYearCount, orderYearMax, '#4e73df');

        document.getElementById('orderTodayCenterText').innerText = ((orderTodayCount / orderTodayMax * 100).toFixed(1)) + '%';
        document.getElementById('orderMonthCenterText').innerText = ((orderMonthCount / orderMonthMax * 100).toFixed(1)) + '%';
        document.getElementById('orderYearCenterText').innerText = ((orderYearCount / orderYearMax * 100).toFixed(1)) + '%';

        // ใส่ % ตรงกลาง
        document.getElementById('mangoCenterText').innerText = '<?= round($mango_count / $mango_max * 100, 1) ?>%';
        document.getElementById('bookingCenterText').innerText = '<?= round($booking_count / $booking_max * 100, 1) ?>%';
        document.getElementById('courseCenterText').innerText = '<?= round($course_total / $course_max * 100, 1) ?>%';
        document.getElementById('productCenterText').innerText = '<?= round($product_count / $product_max * 100, 1) ?>%';

        // ตัวอย่างข้อมูลยอดขาย (สุ่ม)
        let salesDay = Array.from({
          length: 7
        }, () => Math.floor(Math.random() * 1000) + 500);
        let salesMonth = Array.from({
          length: 12
        }, () => Math.floor(Math.random() * 20000) + 10000);
        let salesYear = Array.from({
          length: 5
        }, () => Math.floor(Math.random() * 300000) + 100000);

        // ฟังก์ชันสุ่มข้อมูลใหม่ (สำหรับ animation)
        function randomSales(arr, min, max) {
          for (let i = 0; i < arr.length; i++) {
            arr[i] += Math.floor(Math.random() * 200 - 100); // ขยับขึ้นลง
            arr[i] = Math.max(min, Math.min(max, arr[i]));
          }
        }

        // กราฟรายวัน
        const salesDayChart = new Chart(document.getElementById('salesDayChart').getContext('2d'), {
          type: 'line',
          data: {
            labels: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
            datasets: [{
              label: 'ยอดขาย (บาท)',
              data: salesDay,
              borderColor: '#e74a3b',
              backgroundColor: 'rgba(231,74,59,0.08)',
              tension: 0.4,
              fill: true,
              pointRadius: 3
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                enabled: true,
                backgroundColor: '#232946',
                titleColor: '#fff',
                bodyColor: '#fff'
              }
            },
            scales: {
              x: {
                grid: {
                  display: false
                },
                ticks: {
                  font: {
                    family: 'Kanit'
                  }
                }
              },
              y: {
                beginAtZero: true,
                grid: {
                  color: '#e0e7ff'
                },
                ticks: {
                  font: {
                    family: 'Kanit'
                  }
                }
              }
            },
            animation: false
          }
        });

        // กราฟรายเดือน
        const salesMonthChart = new Chart(document.getElementById('salesMonthChart').getContext('2d'), {
          type: 'line',
          data: {
            labels: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
            datasets: [{
              label: 'ยอดขาย (บาท)',
              data: salesMonth,
              borderColor: '#1cc88a',
              backgroundColor: 'rgba(28,200,138,0.08)',
              tension: 0.4,
              fill: true,
              pointRadius: 3
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                enabled: true,
                backgroundColor: '#232946',
                titleColor: '#fff',
                bodyColor: '#fff'
              }
            },
            scales: {
              x: {
                grid: {
                  display: false
                },
                ticks: {
                  font: {
                    family: 'Kanit'
                  }
                }
              },
              y: {
                beginAtZero: true,
                grid: {
                  color: '#e0e7ff'
                },
                ticks: {
                  font: {
                    family: 'Kanit'
                  }
                }
              }
            },
            animation: false
          }
        });

        // กราฟรายปี
        const salesYearChart = new Chart(document.getElementById('salesYearChart').getContext('2d'), {
          type: 'line',
          data: {
            labels: ['2021', '2022', '2023', '2024', '2025'],
            datasets: [{
              label: 'ยอดขาย (บาท)',
              data: salesYear,
              borderColor: '#4e73df',
              backgroundColor: 'rgba(78,115,223,0.08)',
              tension: 0.4,
              fill: true,
              pointRadius: 3
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                enabled: true,
                backgroundColor: '#232946',
                titleColor: '#fff',
                bodyColor: '#fff'
              }
            },
            scales: {
              x: {
                grid: {
                  display: false
                },
                ticks: {
                  font: {
                    family: 'Kanit'
                  }
                }
              },
              y: {
                beginAtZero: true,
                grid: {
                  color: '#e0e7ff'
                },
                ticks: {
                  font: {
                    family: 'Kanit'
                  }
                }
              }
            },
            animation: false
          }
        });

        // Animation: ขยับเส้นตลอดเวลา
        function animateSalesCharts() {
          randomSales(salesDay, 200, 2000);
          randomSales(salesMonth, 2000, 15000);
          randomSales(salesYear, 25000, 85000); 
          salesDayChart.update('none');
          salesMonthChart.update('none');
          salesYearChart.update('none');
          setTimeout(animateSalesCharts, 100);
        }
        animateSalesCharts();
      </script>
</body>

</html>