<?php
require_once __DIR__ . '/../db/db.php';
$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) die("<div class='container mt-5 text-center'><h3>ไม่พบคำสั่งซื้อ</h3><a href='manage_orders.php' class='btn btn-primary'>กลับ</a></div>");

$item_stmt = $conn->prepare("SELECT oi.*, p.product_image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id=?");
$item_stmt->bind_param("i", $id);
$item_stmt->execute();
$items = $item_stmt->get_result();

$statusMap = [
    'pending' => ['text' => 'รอยืนยัน', 'class' => 'bg-warning text-dark'],
    'approved' => ['text' => 'ยืนยันแล้ว', 'class' => 'bg-success text-white'],
    'rejected' => ['text' => 'ถูกปฏิเสธ', 'class' => 'bg-danger text-white'],
    'completed' => ['text' => 'เสร็จสมบูรณ์', 'class' => 'bg-info text-white']
];
$status = $statusMap[$order['order_status']] ?? ['text' => $order['order_status'], 'class' => 'bg-secondary text-white'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= $order['order_code'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg: #f4f7f6; --card-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        body { background: var(--bg); font-family: 'Inter', sans-serif; padding-bottom: 50px; }
        .card { border: none; border-radius: 15px; box-shadow: var(--card-shadow); margin-bottom: 1.5rem; }
        .card-header { background: #fff; border-bottom: 1px solid #eee; font-weight: 700; border-radius: 15px 15px 0 0 !important; }
        .status-pill { padding: 6px 16px; border-radius: 50px; font-size: 0.85rem; font-weight: 600; }
        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .table thead th { background: #f8f9fa; border: none; font-size: 0.8rem; text-transform: uppercase; color: #666; }
        .info-label { color: #888; font-size: 0.9rem; }
        .info-value { font-weight: 600; color: #333; }
        @media (max-width: 576px) { .header-flex { flex-direction: column; gap: 10px; text-align: center; } }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 header-flex">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-receipt me-2"></i>ออเดอร์ <?= $order['order_code'] ?></h4>
            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i> <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></small>
        </div>
        <span class="status-pill <?= $status['class'] ?> shadow-sm"><?= $status['text'] ?></span>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="fas fa-user me-2 text-primary"></i>รายละเอียดลูกค้า</div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="info-label">ชื่อ-นามสกุล</div>
                        <div class="info-value"><?= htmlspecialchars($order['customer_name']) ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">เบอร์โทรศัพท์</div>
                        <div class="info-value"><?= htmlspecialchars($order['customer_phone']) ?></div>
                    </div>
                    <div>
                        <div class="info-label">ที่อยู่</div>
                        <div class="info-value"><?= nl2br(htmlspecialchars($order['customer_address'])) ?></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="fas fa-truck me-2 text-info"></i>การจัดส่ง</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>ช่องทาง:</span>
                        <span class="fw-bold"><?= $order['receive_type'] === 'pickup' ? 'รับที่สวน' : 'จัดส่งถึงบ้าน' ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>เวลานัดรับ:</span>
                        <span class="fw-bold text-primary"><?= $order['receive_datetime'] ? date('d/m/Y H:i', strtotime($order['receive_datetime'])) : '-' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header"><i class="fas fa-shopping-basket me-2 text-success"></i>รายการสินค้า</div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>สินค้า</th>
                                <th class="text-center">จำนวน</th>
                                <th class="text-end">รวม (บาท)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = 0;
                            while ($i = $items->fetch_assoc()): 
                                $sum = $i['price'] * $i['quantity']; $total += $sum;
                                $img = !empty($i['product_image']) ? "../admin/uploads/products/".$i['product_image'] : "../assets/no-image.png";
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $img ?>" class="product-img me-3">
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($i['product_name']) ?></div>
                                            <small class="text-muted">฿<?= number_format($i['price'],2) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">x <?= $i['quantity'] ?></td>
                                <td class="text-end fw-bold">฿<?= number_format($sum,2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="text-end fw-bold text-muted">ยอดรวมทั้งสิ้น:</td>
                                <td class="text-end h5 fw-bold text-danger">฿<?= number_format($total, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card bg-white">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <a href="update_order.php?id=<?= $id ?>&s=approved" class="btn btn-success w-100 py-2"><i class="fas fa-check me-1"></i> ยืนยัน</a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="update_order.php?id=<?= $id ?>&s=completed" class="btn btn-info w-100 py-2 text-white"><i class="fas fa-flag-checkered me-1"></i> จบงาน</a>
                        </div>
                        <div class="col-12 col-md-6 text-md-end pt-2 pt-md-0">
                            <button type="button" class="btn btn-outline-danger me-2" onclick="document.getElementById('rejectBox').classList.toggle('d-none')">ปฏิเสธ</button>
                            <a href="manage_orders.php" class="btn btn-light border"><i class="fas fa-arrow-left"></i></a>
                        </div>
                    </div>

                    <div id="rejectBox" class="mt-4 p-3 border rounded bg-light d-none">
                        <form action="update_order.php" method="post">
                            <input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="s" value="rejected">
                            <label class="form-label fw-bold text-danger">ระบุเหตุผลที่ปฏิเสธ</label>
                            <textarea name="admin_note" class="form-control mb-3" rows="2" required></textarea>
                            <button type="submit" class="btn btn-danger w-100">ยืนยันการปฏิเสธ</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>