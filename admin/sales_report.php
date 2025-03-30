<?php
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานการขาย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="d-flex">
    <div class="p-4" style="margin-left: 250px; flex: 2;">
        <h2>📊 รายงานการขาย</h2>
        <canvas id="salesChart" width="400" height="200"></canvas>
        
        <h3 class="mt-4">📆 รายงานยอดขายแต่ละเดือน</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>เดือน</th>
                    <th>ยอดขายรวม (บาท)</th>
                    <th>ดูรายละเอียด</th>
                </tr>
            </thead>
            <tbody id="monthlySalesTable">
                <!-- ข้อมูลยอดขายรายเดือน -->
            </tbody>
        </table>

        <h3 class="mt-4">📦 รายละเอียดการขายสินค้า</h3>
        <table class="table table-bordered" id="productSalesTable">
            <thead>
                <tr>
                    <th>สินค้า</th>
                    <th>จำนวนที่ขาย (ชิ้น)</th>
                    <th>ยอดขายรวม (บาท)</th>
                </tr>
            </thead>
            <tbody id="salesTableBody">
                <!-- ข้อมูลสินค้าจะถูกแสดงที่นี่ -->
            </tbody>
        </table>

        <a href="manage_product.php" class="btn btn-info">🔙 กลับ</a>
    </div>
</div>

<script>
// ข้อมูลจำลองยอดขายรายเดือน
const monthlySales = [
    { month: "ม.ค.", total: 12000, details: [
        { name: "กล้วยทอดอบเนย", quantity: 40, total: 2000 },
        { name: "มันฝรั่งทอดกรอบ", quantity: 30, total: 1500 }
    ]},
    { month: "ก.พ.", total: 15000, details: [
        { name: "กล้วยทอดอบเนย", quantity: 50, total: 2500 },
        { name: "มันฝรั่งทอดกรอบ", quantity: 35, total: 1750 }
    ]},
    { month: "มี.ค.", total: 13000, details: [
        { name: "กล้วยทอดอบเนย", quantity: 45, total: 2250 },
        { name: "ขนมข้าวโพด", quantity: 40, total: 2000 }
    ]}
];

// แสดงข้อมูลยอดขายรายเดือน
const monthlyTable = document.getElementById("monthlySalesTable");
monthlySales.forEach((item, index) => {
    let row = `<tr>
        <td>${item.month}</td>
        <td>${item.total.toLocaleString()} บาท</td>
        <td><button class="btn btn-primary btn-sm" onclick="showDetails(${index})">🔍 ดูรายละเอียด</button></td>
    </tr>`;
    monthlyTable.innerHTML += row;
});

// ฟังก์ชันแสดงรายละเอียดสินค้าที่ขายในเดือนนั้น
function showDetails(index) {
    const salesTableBody = document.getElementById("salesTableBody");
    salesTableBody.innerHTML = ""; // เคลียร์ข้อมูลก่อนหน้า
    
    monthlySales[index].details.forEach(item => {
        let row = `<tr>
            <td>${item.name}</td>
            <td>${item.quantity}</td>
            <td>${item.total.toLocaleString()} บาท</td>
        </tr>`;
        salesTableBody.innerHTML += row;
    });
}

// สร้างกราฟด้วย Chart.js
const salesData = {
    labels: monthlySales.map(item => item.month),
    datasets: [{
        label: "ยอดขาย (บาท)",
        data: monthlySales.map(item => item.total),
        backgroundColor: "rgba(75, 192, 192, 0.2)",
        borderColor: "rgba(75, 192, 192, 1)",
        borderWidth: 2
    }]
};

const ctx = document.getElementById("salesChart").getContext("2d");
new Chart(ctx, {
    type: "line",
    data: salesData,
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

</body>
</html>