<?php
require_once '../admin/db.php'; // เชื่อมต่อฐานข้อมูล

if (!isset($_GET['order_id'])) {
    die("ไม่พบคำสั่งซื้อ");
}

$order_id = intval($_GET['order_id']);

// ดึงข้อมูลคำสั่งซื้อ
$query = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// ดึงรายการสินค้า
$query = "SELECT oi.*, p.name AS product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สรุปคำสั่งซื้อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>สรุปคำสั่งซื้อ</h2>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">คำสั่งซื้อ #<?php echo $order['id']; ?></h5>
            <p class="card-text">
                <strong>ชื่อ-นามสกุล:</strong> <?php echo htmlspecialchars($order['customer_name']); ?><br>
                <strong>เบอร์โทรศัพท์:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?><br>
                <strong>ที่อยู่:</strong> <?php echo htmlspecialchars($order['address_number'] . ', ' . $order['sub_district'] . ', ' . $order['district'] . ', ' . $order['province'] . ', ' . $order['postal_code']); ?><br>
                <strong>วิธีการชำระเงิน:</strong> <?php echo $order['payment_method'] === 'bank' ? 'โอนเงินผ่านธนาคาร' : 'เก็บเงินปลายทาง'; ?><br>
                <strong>สถานะ:</strong> <?php echo htmlspecialchars($order['status']); ?><br>
                <strong>วันที่สั่งซื้อ:</strong> <?php echo htmlspecialchars($order['created_at']); ?><br>
            </p>
        </div>
    </div>

    <h3 class="mt-4">รายการสินค้า</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>สินค้า</th>
                <th>จำนวน</th>
                <th>ราคา (ต่อหน่วย)</th>
                <th>ราคารวม</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td>฿<?php echo number_format($item['price'], 2); ?></td>
                    <td>฿<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h4 class="text-end">ยอดรวม: ฿<?php echo number_format($order['total_price'], 2); ?></h4>

    <div class="text-center mt-4">
        <a href="products.php" class="btn btn-success">เสร็จสิ้น</a>
    </div>
</div>
</body>
</html>