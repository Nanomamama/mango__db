<?php
include 'sidebar.php';
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';
$customer_name = "สมชาย ใจดี"; // จำลองข้อมูลลูกค้า
$customer_address = "123 ถนนสุขใจ, เขตบางรัก, กรุงเทพฯ 10500";
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดคำสั่งซื้อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="d-flex">
    <div class="p-4" style="margin-left: 250px; flex: 2;">
        <h2>📄 รายละเอียดคำสั่งซื้อ #<?php echo $order_id; ?></h2>
        
        <h5>👤 ลูกค้า: <?php echo $customer_name; ?></h5>
        <h5>📍 ที่อยู่จัดส่ง: <?php echo $customer_address; ?></h5>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>สินค้า</th>
                    <th>จำนวน</th>
                    <th>ราคา</th>
                    <th>รวม</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>กล้วยทอดอบเนย</td>
                    <td>2</td>
                    <td>฿50</td>
                    <td>฿100</td>
                </tr>
                <!-- เพิ่มสินค้ารายการอื่น -->
            </tbody>
        </table>

        <h5>สถานะคำสั่งซื้อ:
            <span id="order-status" class="badge bg-warning">รอดำเนินการ</span>
        </h5>
        
        <select id="statusSelect" class="form-select w-25" onchange="updateStatus(<?php echo $order_id; ?>)">
            <option value="รอดำเนินการ">รอดำเนินการ</option>
            <option value="กำลังจัดส่ง">กำลังจัดส่ง</option>
            <option value="สำเร็จ">สำเร็จ</option>
            <option value="ยกเลิก">ยกเลิก</option>
        </select>
        <div class="ps-4">
        <a href="order_product.php" class="btn btn-info mt-3">🔙 กลับ</a>

        </div>
    </div>
</div>

<script>
function updateStatus(orderId) {
    let status = document.getElementById("statusSelect").value;
    
    $.post('update_order_status.php', { order_id: orderId, status: status }, function(response) {
        alert(response);
        
        let statusBadge = $('#order-status');
        statusBadge.text(status);
        statusBadge.removeClass().addClass('badge');
        
        switch(status) {
            case 'รอดำเนินการ': statusBadge.addClass('bg-warning'); break;
            case 'กำลังจัดส่ง': statusBadge.addClass('bg-primary'); break;
            case 'สำเร็จ': statusBadge.addClass('bg-success'); break;
            case 'ยกเลิก': statusBadge.addClass('bg-danger'); break;
        }
    });
}
</script>

</body>
</html>
