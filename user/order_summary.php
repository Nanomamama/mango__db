<?php
require_once '../admin/db.php';

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
$query = "SELECT oi.*, p.name AS product_name, p.images AS product_image 
          FROM order_items oi 
          JOIN products p ON oi.product_id = p.id 
          WHERE oi.order_id = ?";
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
            <h5 class="card-title">คำสั่งซื้อ #<?php echo htmlspecialchars($order['id']); ?></h5>
            <p class="card-text">
                <strong>ชื่อ-นามสกุล:</strong> <?php echo htmlspecialchars($order['customer_name']); ?><br>
                <strong>เบอร์โทรศัพท์:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?><br>
                <strong>ที่อยู่:</strong>
                <?php
                    function getNameById($array, $id) {
                        foreach ($array as $item) {
                            if ($item['id'] == $id) return $item['name_th'];
                        }
                        return '';
                    }
                    $provinces = json_decode(file_get_contents('../data/api_province.json'), true);
                    $districts = json_decode(file_get_contents('../data/thai_amphures.json'), true);
                    $subdistricts = json_decode(file_get_contents('../data/thai_tambons.json'), true);

                    $province_name = getNameById($provinces, $order['province_id']);
                    $district_name = getNameById($districts, $order['district_id']);
                    $subdistrict_name = getNameById($subdistricts, $order['subdistrict_id']);

                    echo htmlspecialchars($order['address_number']) . ' ';
                    echo htmlspecialchars($subdistrict_name) . ' ';
                    echo htmlspecialchars($district_name) . ' ';
                    echo htmlspecialchars($province_name) . ' ';
                    echo htmlspecialchars($order['postal_code']);
                ?><br>
                <strong>วิธีการชำระเงิน:</strong>
                <?php
                    if ($order['payment_method'] === 'bank') {
                        echo 'โอนเงินผ่านธนาคาร';
                    } else if ($order['payment_method'] === 'promptpay') {
                        echo 'PromptPay';
                    } else {
                        echo 'เก็บเงินปลายทาง';
                    }
                ?><br>
                <strong>สถานะ:</strong> <?php echo htmlspecialchars($order['status']); ?><br>
                <strong>วันที่สั่งซื้อ:</strong> <?php echo htmlspecialchars($order['created_at']); ?><br>
                <?php if (!empty($order['slip_path'])): ?>
                    <strong>สลิปโอนเงิน:</strong><br>
                    <img src="data:image/png;base64,<?php echo $order['slip_path']; ?>" style="max-width:200px;max-height:200px;">
                <?php endif; ?>
            </p>
        </div>
    </div>

    <h3 class="mt-4">รายการสินค้า</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>รูปภาพ</th>
                <th>สินค้า</th>
                <th>จำนวน</th>
                <th>ราคา (ต่อหน่วย)</th>
                <th>ราคารวม</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items->fetch_assoc()): ?>
                <?php
                $images = json_decode($item['product_image'], true);
                $product_image = 'default.jpg';
                if (is_array($images) && isset($images[0]) && !empty($images[0])) {
                    $product_image = $images[0];
                }
                ?>
                <tr>
                    <td>
                        <img src="../admin/productsimage/<?php echo htmlspecialchars($product_image); ?>"
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                             style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;"
                             onerror="this.onerror=null;this.src='../admin/productsimage/default.jpg';">
                    </td>
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