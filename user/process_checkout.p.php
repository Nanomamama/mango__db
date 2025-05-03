<?php
require_once '../admin/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['customer_name'];
    $customer_phone = $_POST['customer_phone'];
    $customer_address = $_POST['customer_address'];
    $cart = json_decode($_POST['cart'], true);

    // บันทึกข้อมูลลูกค้า
    $query = "INSERT INTO orders (customer_name, customer_phone, customer_address, total_price, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $total_price = array_reduce($cart, function ($sum, $item) {
        return $sum + ($item['price'] * $item['quantity']);
    }, 0);
    $stmt->bind_param("sssd", $customer_name, $customer_phone, $customer_address, $total_price);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // บันทึกรายการสินค้า
    $query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    foreach ($cart as $item) {
        $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
        $stmt->execute();
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>