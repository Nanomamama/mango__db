<?php
require_once 'auth.php';
require_once 'db.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// ดึงข้อมูลคำสั่งซื้อ
$query_order = "SELECT customer_name, address_number, province_id, district_id, subdistrict_id, postal_code, payment_method, total_price, created_at, status, slip_path 
                FROM orders 
                WHERE id = ?";
$stmt_order = $conn->prepare($query_order);
$stmt_order->bind_param("i", $order_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();
$order = $result_order->fetch_assoc();

if (!$order) {
    die("ไม่พบคำสั่งซื้อที่ระบุ");
}

// โหลด json จังหวัด/อำเภอ/ตำบล
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

// ดึงข้อมูลสินค้าในคำสั่งซื้อ
$query_items = "SELECT products.name AS product_name, products.images AS product_image, 
                       order_items.quantity, order_items.price, 
                       (order_items.quantity * order_items.price) AS total 
                FROM order_items 
                JOIN products ON order_items.product_id = products.id 
                WHERE order_items.order_id = ?";
$stmt_items = $conn->prepare($query_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
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
<?php include 'sidebar.php'; ?>

<div class="d-flex">
    <div class="p-4" style="margin-left: 250px; flex: 2;">
        <h2>📄 รายละเอียดคำสั่งซื้อ #<?php echo $order_id; ?></h2>
        
        <h5>👤 ลูกค้า: <?php echo htmlspecialchars($order['customer_name']); ?></h5>
        <h5>📍 ที่อยู่จัดส่ง: 
            <?php
                echo htmlspecialchars($order['address_number']) . ', ';
                echo htmlspecialchars($subdistrict_name) . ', ';
                echo htmlspecialchars($district_name) . ', ';
                echo htmlspecialchars($province_name) . ', ';
                echo htmlspecialchars($order['postal_code']);
            ?>
        </h5>
        <h5>💳 วิธีชำระเงิน: <?php echo htmlspecialchars($order['payment_method']); ?></h5>
        <h5>🕒 วันที่สั่งซื้อ: <?php echo htmlspecialchars($order['created_at']); ?></h5>
        <h5>💰 ยอดรวม: ฿<?php echo number_format($order['total_price'], 2); ?></h5>

        <?php if (!empty($order['slip_path'])): ?>
            <h5>สลิปโอนเงิน:</h5>
            <img src="data:image/png;base64,<?php echo $order['slip_path']; ?>" style="max-width:200px;max-height:200px;">
        <?php endif; ?>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>รูปภาพ</th>
                    <th>สินค้า</th>
                    <th>จำนวน</th>
                    <th>ราคา</th>
                    <th>รวม</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $result_items->fetch_assoc()): ?>
                    <?php 
                    // ดึงชื่อไฟล์รูปแรกจาก JSON
                    $images = json_decode($item['product_image'], true);
                    $product_image = (is_array($images) && isset($images[0]) && !empty($images[0])) ? $images[0] : 'default.jpg';
                    $image_path = "../admin/productsimage/" . htmlspecialchars($product_image);
                    ?>
                    <tr>
                        <td>
                            <img src="<?php echo $image_path; ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;"
                                 onerror="this.onerror=null;this.src='../admin/productsimage/default.jpg';">
                        </td>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>฿<?php echo number_format($item['price'], 2); ?></td>
                        <td>฿<?php echo number_format($item['total'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h5>สถานะคำสั่งซื้อ:
            <span id="order-status" class="badge 
                <?php echo $order['status'] === 'รอดำเนินการ' ? 'bg-warning' : 
                           ($order['status'] === 'กำลังจัดส่ง' ? 'bg-primary' : 
                           ($order['status'] === 'สำเร็จ' ? 'bg-success' : 'bg-danger')); ?>">
                <?php echo htmlspecialchars($order['status']); ?>
            </span>
        </h5>
        
        <select id="statusSelect" class="form-select w-25" onchange="updateStatus(<?php echo $order_id; ?>)">
            <option value="รอดำเนินการ" <?php echo $order['status'] === 'รอดำเนินการ' ? 'selected' : ''; ?>>รอดำเนินการ</option>
            <option value="กำลังจัดส่ง" <?php echo $order['status'] === 'กำลังจัดส่ง' ? 'selected' : ''; ?>>กำลังจัดส่ง</option>
            <option value="สำเร็จ" <?php echo $order['status'] === 'สำเร็จ' ? 'selected' : ''; ?>>สำเร็จ</option>
            <option value="ยกเลิก" <?php echo $order['status'] === 'ยกเลิก' ? 'selected' : ''; ?>>ยกเลิก</option>
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
