<?php
require_once '../admin/db.php';

$member_id = $_POST['member_id'] ?: null;
$customer_name = $_POST['customer_name'];
$customer_phone = $_POST['customer_phone'];
$customer_address = $_POST['customer_address'];
$receive_type = $_POST['receive_type'];
$receive_datetime = $_POST['receive_datetime'];
$cart_json = $_POST['cart_data'];

$cart = json_decode($cart_json, true);

$order_code = "ORD".date("YmdHis").rand(100,999); // กันชน 100%
$order_status = "pending";

/* INSERT orders */
$stmt = $conn->prepare("
INSERT INTO orders 
(order_code, member_id, customer_name, customer_phone,
 customer_address, receive_type, receive_datetime,
 order_status, order_date)
VALUES (?,?,?,?,?,?,?,?,NOW())
");

$stmt->bind_param(
"ssssssss",
$order_code,
$member_id,
$customer_name,
$customer_phone,
$customer_address,
$receive_type,
$receive_datetime,
$order_status
);

$stmt->execute();
$order_id = $stmt->insert_id;

$receive_type = $_POST['receive_type'];
$customer_address = $_POST['customer_address'] ?? null;

if ($receive_type === 'pickup' && empty($customer_address)) {
    $customer_address = null;
}

/* INSERT order_items */
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

header("Location: success.php?code=".$order_code);
exit;
?>
