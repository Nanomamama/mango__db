<?php
session_start();
require_once '../admin/db.php';

if (!isset($_GET['order_id'])) {
  header("Location: index.php");
  exit;
}

$order_id = (int)$_GET['order_id'];
$member_id = $_SESSION['member_id'];

$stmt = $conn->prepare("
  SELECT total_price, shipping_cost, status, created_at
  FROM orders
  WHERE order_id = ? AND member_id = ?
");
$stmt->bind_param("ii", $order_id, $member_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
  echo "ไม่พบคำสั่งซื้อ";
  exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำสั่งซื้อสำเร็จ</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<br>
<br>
<div class="container text-center mt-5">

  <h1 class="text-success">✔ สั่งซื้อสำเร็จ</h1>

  <p class="mt-3">คำสั่งซื้อของคุณถูกสร้างเรียบร้อยแล้ว</p>

  <div class="card mx-auto mt-4" style="max-width: 420px;">
    <div class="card-body text-start">
      <p><strong>เลขคำสั่งซื้อ:</strong> #<?= $order_id ?></p>
      <p><strong>ยอดชำระ:</strong>
        <?= number_format($order['total_price'] + $order['shipping_cost'], 2) ?> บาท
      </p>
      <p><strong>สถานะ:</strong>
        <span class="badge bg-warning">รอชำระเงิน</span>
      </p>
    </div>
  </div>

  <div class="mt-4">
    <a href="payment.php?order_id=<?= $order_id ?>" class="btn btn-primary">
      ไปชำระเงิน
    </a>

    <a href="my_orders.php" class="btn btn-outline-secondary ms-2">
      ดูคำสั่งซื้อของฉัน
    </a>
  </div>

</div>

</body>
</html>
