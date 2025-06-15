<?php
header('Content-Type: application/json');
require_once '../admin/db.php';

function getOrderItems($conn, $order_id) {
    $sql = "SELECT 
                order_items.product_id, 
                order_items.quantity, 
                order_items.price, 
                products.name AS product_name, 
                products.images AS product_images
            FROM order_items
            JOIN products ON order_items.product_id = products.id
            WHERE order_items.order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        // แปลง images จาก JSON เป็น array และดึงรูปแรก
        $images = json_decode($row['product_images'], true);
        $row['product_image'] = (is_array($images) && isset($images[0])) ? $images[0] : null;
        unset($row['product_images']);
        $items[] = $row;
    }
    return $items;
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
$customer_name = isset($_GET['customer_name']) ? trim($_GET['customer_name']) : null;

if ($order_id) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    if ($order) {
        $order['items'] = getOrderItems($conn, $order['id']);
        echo json_encode(['success' => true, 'orders' => [$order]]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบคำสั่งซื้อ']);
    }
    exit;
}

if ($customer_name) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE customer_name LIKE ?");
    $like = "%$customer_name%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $row['items'] = getOrderItems($conn, $row['id']);
        $orders[] = $row;
    }
    if (count($orders) > 0) {
        echo json_encode(['success' => true, 'orders' => $orders]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบคำสั่งซื้อ']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'กรุณาระบุข้อมูลค้นหา']);
exit;