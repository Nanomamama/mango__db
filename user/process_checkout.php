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

$cartData = json_decode($_POST['cart'] ?? '', true);

if (!$cartData || empty($cartData['items'])) {
    echo json_encode(['success'=>false,'message'=>'ตะกร้าว่าง']);
    exit;
}

$conn->begin_transaction();

try {
    $total_price = 0;
    $total_weight = 0;

    foreach ($cartData['items'] as $item) {
        $product_id = (int)$item['id'];
        $qty = (int)$item['quantity'];

        $stmt = $conn->prepare("
            SELECT price, weight, stock
            FROM products
            WHERE id = ?
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

    $shipping_cost = $cartData['shipping_cost'];

    $stmt = $conn->prepare("
        INSERT INTO orders
        (member_id, customer_name, customer_phone, address_number,
         total_price, shipping_cost, total_weight, payment_method, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->bind_param(
        "isssddds",
        $member_id,
        $customer_name,
        $customer_phone,
        $address,
        $total_price,
        $shipping_cost,
        $total_weight,
        $payment_method
    );
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    foreach ($cartData['items'] as $item) {
        $pid = $item['id'];
        $qty = $item['quantity'];

        $conn->query("
            INSERT INTO order_items (order_id, product_id, quantity)
            VALUES ($order_id, $pid, $qty)
        ");

        $conn->query("
            UPDATE products SET stock = stock - $qty
            WHERE id = $pid
        ");
    }

    $conn->commit();

    echo json_encode(['success'=>true,'order_id'=>$order_id]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
