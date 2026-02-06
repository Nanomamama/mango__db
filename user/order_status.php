<?php
session_start();
require_once '../admin/db.php';

$member_id = $_SESSION['member_id'] ?? null;
$orders = [];

if ($member_id) {
    // สมาชิก
    $stmt = $conn->prepare("
        SELECT * FROM orders 
        WHERE member_id = ?
        ORDER BY order_date DESC
    ");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ผู้ใช้ทั่วไปค้นหา
if (isset($_POST['phone'])) {
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("
        SELECT * FROM orders 
        WHERE customer_phone = ?
        ORDER BY order_date DESC
    ");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ติดตามสถานะคำสั่งซื้อ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
<h2 class="text-center mb-4">ติดตามสถานะคำสั่งซื้อ</h2>

<?php if (!$member_id): ?>
<form method="post" class="mb-4">
    <input type="text" name="phone" class="form-control"
           placeholder="กรอกเบอร์โทรที่ใช้สั่งซื้อ" required>
    <button class="btn btn-primary mt-2 w-100">ค้นหา</button>
</form>
<?php endif; ?>

<?php if (!empty($orders)): ?>
<table class="table table-bordered text-center">
<thead>
<tr>
<th>รหัส</th>
<th>วันที่</th>
<th>ชื่อ</th>
<th>สถานะ</th>
<th>หมายเหตุ</th>
</tr>
</thead>
<tbody>
<?php foreach($orders as $o): ?>
<tr>
<td><?= $o['order_code'] ?></td>
<td><?= $o['order_date'] ?></td>
<td><?= $o['customer_name'] ?></td>
<td>
<?php
$badge = match($o['order_status']){
'pending' => 'warning',
'approved' => 'success',
'rejected' => 'danger',
'completed' => 'primary',
default => 'secondary'
};
?>
<span class="badge bg-<?= $badge ?>">
<?= $o['order_status'] ?>
</span>
</td>
<td><?= $o['admin_note'] ?? '-' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php elseif(isset($_POST['phone'])): ?>
<div class="alert alert-danger text-center">
ไม่พบคำสั่งซื้อ
</div>
<?php endif; ?>

</div>
</body>
</html>
