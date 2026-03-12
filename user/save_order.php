<?php
require_once __DIR__ . '/../db/db.php';

$member_id = $_POST['member_id'] ?: null;
$customer_name = $_POST['customer_name'];
$customer_phone = $_POST['customer_phone'];
$customer_address = $_POST['customer_address'];
$receive_type = $_POST['receive_type'];
$receive_datetime = $_POST['receive_datetime'];
$cart_json = $_POST['cart_data'];

/* ===== แปลงค่าเป็นภาษาไทย ===== */
$receive_map = [
    "pickup" => "รับที่สวน",
    "delivery" => "ส่งถึงบ้าน"
];

$receive_type_text = $receive_map[$receive_type] ?? "-";

$cart = json_decode($cart_json, true);

$order_code = "ORD".date("YmdHis").rand(100,999);
$order_status = "pending";

/* ===== คำนวณยอดรวม ===== */
$total_amount = 0;
foreach($cart as $p){
    $total_amount += $p['quantity'] * $p['price'];
}

/* ===== INSERT orders (มี total_amount) ===== */
$stmt = $conn->prepare("
INSERT INTO orders 
(order_code, member_id, customer_name, customer_phone,
 customer_address, receive_type, receive_datetime,
 order_status, order_date, total_amount)
VALUES (?,?,?,?,?,?,?,?,NOW(),?)
");

$stmt->bind_param(
"ssssssssd",
$order_code,
$member_id,
$customer_name,
$customer_phone,
$customer_address,
$receive_type,
$receive_datetime,
$order_status,
$total_amount
);

$stmt->execute();
$order_id = $stmt->insert_id;

/* ===== INSERT order_items ===== */
$item = $conn->prepare("
INSERT INTO order_items
(order_id, product_id, quantity, price, product_name)
VALUES (?,?,?,?,?)
");

foreach($cart as $p){
    $item->bind_param(
    "iiids",
    $order_id,
    $p['product_id'],
    $p['quantity'],
    $p['price'],
    $p['name']
    );
    $item->execute();
}

/* ===== สร้างข้อความแจ้งเตือน ===== */

$message = "📦 มีคำสั่งซื้อใหม่\n";
$message .= "รหัสออเดอร์: $order_code\n";
$message .= "ลูกค้า: $customer_name\n";
$message .= "โทร: $customer_phone\n";
$message .= "วิธีรับสินค้า: $receive_type_text\n";
$message .= "วันรับสินค้า: $receive_datetime\n";
$message .= "ถึงแอดมิน: $customer_address\n\n";

$message .= "รายการสินค้า\n";
foreach($cart as $p){
    $message .= "- ".$p['name']." x".$p['quantity']."\n";
}

$message .= "\nยอดรวม: ".$total_amount." บาท";

$message .= "\n🔎 เข้าสู่ระบบเพื่อยืนยันคำสั่งซื้อ\n";
$message .= "http://localhost:8000/admin/order_detail.php?id=".$order_id;





/* ===== ส่ง LINE ===== */
include __DIR__ . '/../admin/line_notify.php';

header("Location: success.php?code=".$order_code);
exit;
?>
