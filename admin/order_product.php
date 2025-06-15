<?php
require_once 'auth.php';
require_once 'db.php'; // เชื่อมต่อฐานข้อมูล

// ดึงข้อมูลคำสั่งซื้อจากฐานข้อมูล
$query = "SELECT id, created_at, status, total_price, customer_name 
          FROM orders 
          ORDER BY created_at DESC";
$result = $conn->query($query);

if (!$result) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลคำสั่งซื้อ: " . $conn->error);
}
?>

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
        <table id="ordersTable" class="table table-bordered">
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
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td>
                            <?php
                            $status = $row['status'];
                            $badgeClass = $status === 'รอดำเนินการ' ? 'bg-warning' :
                                          ($status === 'กำลังจัดส่ง' ? 'bg-primary' : 'bg-success');
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                        </td>
                        <td>฿<?php echo number_format($row['total_price'], 2); ?></td>
                        <td class="d-flex">
                            <a href="order_details.php?order_id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">🔍 ดูรายละเอียด</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
     <a href="manage_product.php" class="btn btn-info mt-3">🔙 กลับ</a>    
    </div>
</div>

<script>
$(document).ready(function () {
    $("#ordersTable").DataTable({
        language: {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            paginate: {
                first: "หน้าแรก",
                last: "หน้าสุดท้าย",
                next: "ถัดไป",
                previous: "ก่อนหน้า"
            }
        }
    });
});

function updateStatus(orderId, status) {
    if (confirm("ต้องการเปลี่ยนสถานะเป็น '" + status + "' ใช่หรือไม่?")) {
        $.ajax({
            url: "update_order_status.php",
            type: "POST",
            data: { order_id: orderId, status: status },
            success: function (response) {
                alert("อัปเดตสถานะเป็น '" + status + "' สำเร็จ!");
                location.reload();
            },
            error: function () {
                alert("เกิดข้อผิดพลาดในการอัปเดตสถานะ");
            }
        });
    }
}
</script>

</body>
</html>
