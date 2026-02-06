<?php
require_once 'db.php';

$id = $_GET['id'] ?? 0;

// ดึงข้อมูลออเดอร์
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("ไม่พบคำสั่งซื้อ");
}

// ดึงรายการสินค้า
$item = $conn->prepare("
SELECT * FROM order_items 
WHERE order_id=?
");
$item->bind_param("i", $id);
$item->execute();
$items = $item->get_result();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายละเอียดคำสั่งซื้อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-4">
        <h2 class="mb-3">รายละเอียดคำสั่งซื้อ</h2>

        <div class="row">

            <!-- ข้อมูลลูกค้า -->
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        ข้อมูลลูกค้า
                    </div>
                    <div class="card-body">
                        <p><b>รหัสออเดอร์:</b> <?= $order['order_code'] ?></p>
                        <p><b>ชื่อ:</b> <?= $order['customer_name'] ?></p>
                        <p><b>เบอร์:</b> <?= $order['customer_phone'] ?></p>
                        <p><b>ที่อยู่:</b> <?= $order['customer_address'] ?></p>
                    </div>
                </div>
            </div>

            <!-- ข้อมูลการรับ -->
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        ข้อมูลการรับสินค้า
                    </div>
                    <div class="card-body">
                        <p><b>วิธีรับ:</b><?php if ($order['receive_type'] === 'pickup'): ?>
                                <strong>หมายเหตุ:</strong>
                                <?= $order['customer_address'] ?? 'ไม่มี' ?>
                            <?php else: ?>
                                <strong>ที่อยู่จัดส่ง:</strong>
                                <?= $order['customer_address'] ?>
                            <?php endif; ?>
                        </p>
                        <p><b>วันเวลารับ:</b> <?= $order['receive_datetime'] ?></p>
                        <p><b>สถานะ:</b>
                            <span class="badge bg-warning"><?= $order['order_status'] ?></span>
                        </p>
                        <p><b>วันที่สั่ง:</b> <?= $order['order_date'] ?></p>
                    </div>
                </div>
            </div>

        </div>

        <!-- ตารางสินค้า -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                รายการสินค้า
            </div>
            <div class="card-body">

                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>สินค้า</th>
                            <th>ราคา/หน่วย</th>
                            <th>จำนวน</th>
                            <th>รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        while ($i = $items->fetch_assoc()):
                            $sum = $i['price'] * $i['quantity'];
                            $total += $sum;
                        ?>
                            <tr>
                                <td><?= $i['product_name'] ?></td>
                                <td><?= number_format($i['price'], 2) ?></td>
                                <td><?= $i['quantity'] ?></td>
                                <td><?= number_format($sum, 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <h4 class="text-end">
                    ยอดรวมทั้งสิ้น:
                    <span class="text-danger"><?= number_format($total, 2) ?></span> บาท
                </h4>

            </div>
        </div>

        <!-- ปุ่มจัดการ -->
        <div class="mt-4 text-center">
            <a href="update_order.php?id=<?= $id ?>&s=approved"
                class="btn btn-success btn-lg">ยืนยันคำสั่งซื้อ</a>

            <a href="update_order.php?id=<?= $id ?>&s=rejected"
                class="btn btn-danger btn-lg">ปฏิเสธ</a>

            <a href="update_order.php?id=<?= $id ?>&s=completed"
                class="btn btn-primary btn-lg">เสร็จสิ้น</a>

            <a href="manage_orders.php"
                class="btn btn-secondary btn-lg">กลับ</a>
        </div>

    </div>
</body>

</html>