<?php
require_once 'auth.php';
require_once 'db.php';

// รับสถานะจาก query string
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// ฟังก์ชันแปลงสถานะเป็นภาษาไทยและสี
function getStatusInfo($status) {
    switch($status) {
        case 'pending':   return ['รอยืนยัน', 'bg-warning'];
        case 'confirmed': return ['ยืนยันคำสั่งซื้อ', 'bg-info'];
        case 'shipping':  return ['กำลังจัดส่ง', 'bg-primary'];
        case 'completed': return ['สำเร็จ', 'bg-success'];
        case 'cancelled': return ['ยกเลิก', 'bg-danger'];
        default:          return ['ไม่ทราบสถานะ', 'bg-secondary'];
    }
}

// ดึงข้อมูลคำสั่งซื้อจากฐานข้อมูล
if ($status_filter === 'all') {
    $query = "SELECT id, created_at, status, total_price, customer_name FROM orders ORDER BY created_at DESC";
    $result = $conn->query($query);
} else {
    $query = "SELECT id, created_at, status, total_price, customer_name FROM orders WHERE status = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $status_filter);
    $stmt->execute();
    $result = $stmt->get_result();
}

if (!$result) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลคำสั่งซื้อ: " . $conn->error);
}

// รายการสถานะทั้งหมด
$status_menu = [
    'all'       => 'ทั้งหมด',
    'pending'   => 'รอยืนยัน',
    'confirmed' => 'ยืนยันคำสั่งซื้อ',
    'shipping'  => 'กำลังจัดส่ง',
    'completed' => 'สำเร็จ',
    'cancelled' => 'ยกเลิก'
];
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

        <!-- ปุ่มเมนูสถานะ -->
        <div class="mb-3">
            <?php foreach ($status_menu as $key => $label): ?>
                <a href="?status=<?= $key ?>" class="btn btn-<?=
                    $status_filter === $key ? 'primary' : 'outline-primary'
                ?> btn-sm mb-1"><?= $label ?></a>
            <?php endforeach; ?>
        </div>

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
                            list($statusText, $badgeClass) = getStatusInfo($row['status']);
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
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
</script>

</body>
</html>
