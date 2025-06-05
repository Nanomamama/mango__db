<?php
require_once '../admin/db.php';
header('Content-Type: application/json');

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    $stmt = $conn->prepare("SELECT id, customer_name, address_number, status FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'order' => [
                'id' => $row['id'],
                'customer' => $row['customer_name'],
                'address' => $row['address_number'],
                'status' => $row['status']
            ]
        ]);
        exit;
    }
    echo json_encode(['success' => false]);
    exit;
}

if (isset($_GET['customer_name'])) {
    $name = '%' . trim($_GET['customer_name']) . '%';
    $stmt = $conn->prepare("SELECT id, customer_name, address_number, status FROM orders WHERE customer_name LIKE ? ORDER BY created_at DESC LIMIT 10");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    echo json_encode(['success' => true, 'orders' => $orders]);
    exit;
}

echo json_encode(['success' => false]);