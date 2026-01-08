<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
header('Content-Type: application/json');

session_start();
require_once '../admin/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit;
}

if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success'=>false,'message'=>'กรุณาเข้าสู่ระบบ']);
    exit;
}

$member_id = $_SESSION['member_id'];

$customer_name  = trim($_POST['customer_name'] ?? '');
$customer_phone = trim($_POST['customer_phone'] ?? '');
$address        = trim($_POST['address_number'] ?? '');
$payment_method = $_POST['payment_method'] ?? '';

if ($customer_name === '' || $customer_phone === '' || $address === '') {
    echo json_encode(['success'=>false,'message'=>'กรุณากรอกข้อมูลจัดส่งให้ครบ']);
    exit;
}

$cartData = json_decode($_POST['cart'] ?? '', true);
if (!$cartData || empty($cartData['items'])) {
    echo json_encode(['success'=>false,'message'=>'ตะกร้าว่าง']);
    exit;
}

$payment_slip = null;
$reject_reason = null;

$conn->begin_transaction();

try {
    $total_price = 0;
    $total_weight = 0;

    foreach ($cartData['items'] as $item) {
        $product_id = (int)$item['product_id'];
        $qty = (int)$item['quantity'];

        $stmt = $conn->prepare("
            SELECT price, weight, stock
            FROM products
            WHERE product_id = ?
            FOR UPDATE
        ");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$p || $p['stock'] < $qty) {
            throw new Exception("สินค้าไม่เพียงพอ");
        }

        $total_price += $p['price'] * $qty;
        $total_weight += $p['weight'] * $qty;
    }

    $shipping_cost = (float)($cartData['shipping_cost'] ?? 0);

    $stmt = $conn->prepare("
        INSERT INTO orders
        (member_id, total_price, shipping_cost, total_weight,
         payment_method, payment_slip, reject_reason,
         status, created_at, tracking_number, shipping_provider, shipped_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NULL, NULL, NULL)
    ");

    $stmt->bind_param(
        "idddsss",
        $member_id,
        $total_price,
        $shipping_cost,
        $total_weight,
        $payment_method,
        $payment_slip,
        $reject_reason
    );

    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    foreach ($cartData['items'] as $item) {
        $pid = (int)$item['product_id'];
        $qty = (int)$item['quantity'];

        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iii", $order_id, $pid, $qty);
        $stmt->execute();
        $stmt->close();

        $conn->query("
            UPDATE products
            SET stock = stock - $qty
            WHERE product_id = $pid
        ");
    }

    $conn->commit();

    echo json_encode(['success'=>true,'order_id'=>$order_id]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
