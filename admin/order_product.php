

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการคำสั่งซื้อ</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="d-flex">
    <div class="p-4" style="margin-left: 250px; flex: 2;">
        <h2>📦 จัดการคำสั่งซื้อ</h2>
        <input type="text" id="searchBox" class="form-control mb-3" placeholder="🔍 ค้นหาคำสั่งซื้อ...">
        <table  class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ลูกค้า</th>
                    <th>วันที่</th>
                    <th>สถานะ</th>
                    <th>ยอดรวม</th>
                    <th>การดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1001</td>
                    <td>สมชาย ใจดี</td>
                    <td>2025-03-30</td>
                    <td><span class="badge bg-warning">รอดำเนินการ</span></td>
                    <td>฿500</td>
                    <td class="d-flex ">
                        <a href="order_details.php" class="btn btn-info btn-sm">🔍 ดูรายละเอียด</a>
                        <button class="btn btn-success btn-sm" onclick="updateStatus(1001, 'กำลังจัดส่ง')">🚚 จัดส่ง</button>
                    </td>
                </tr>
                <!-- เพิ่มรายการอื่น ๆ -->
            </tbody>
        </table>
       <div class="ps-4">
       <a href="manage_product.php" class=" btn btn-info " >🔙 กลับ</a>

       </div>

    </div>
</div>

<script>
$(document).ready(function () {
    $("#ordersTable").DataTable();
});

function updateStatus(orderId, status) {
    if (confirm("ต้องการเปลี่ยนสถานะเป็น '" + status + "' ใช่หรือไม่?")) {
        alert("อัปเดตสถานะเป็น '" + status + "' สำเร็จ!");
    }
}
</script>

</body>
</html>
