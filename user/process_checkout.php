<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once '../admin/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['customer_name'];
    $customer_phone = $_POST['customer_phone'];
    $address_number = $_POST['address_number'];
    $province_id = intval($_POST['province_id']);
    $district_id = intval($_POST['district_id']);
    $subdistrict_id = intval($_POST['subdistrict_id']);
    $postal_code = $_POST['postal_code'];
    $payment_method = $_POST['payment_method'];
    $cart = json_decode($_POST['cart'], true);

    $slip_data = null;
    if (($payment_method === 'bank' || $payment_method === 'promptpay') && isset($_FILES['payment_slip']) && $_FILES['payment_slip']['error'] === UPLOAD_ERR_OK) {
        $slip_data = base64_encode(file_get_contents($_FILES['payment_slip']['tmp_name']));
    }

    $total_price = 0;
    foreach ($cart as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

    $query = "INSERT INTO orders (
        customer_name, customer_phone, address_number,
        province_id, district_id, subdistrict_id,
        postal_code, payment_method, total_price, slip_path, status, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล', 'error' => $conn->error]);
        exit;
    }
    $stmt->bind_param(
        "ssssiiisds",
        $customer_name,         // s
        $customer_phone,        // s
        $address_number,        // s
        $province_id,           // i
        $district_id,           // i
        $subdistrict_id,        // i
        $postal_code,           // s
        $payment_method,        // s
        $total_price,           // d
        $slip_data              // s
    );
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถบันทึกคำสั่งซื้อได้', 'error' => $stmt->error]);
        exit;
    }

    $order_id = $stmt->insert_id;

    // บันทึกรายการสินค้าใน order_items
    foreach ($cart as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];

        $query = "INSERT INTO order_items (order_id, product_id, quantity, price, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt2 = $conn->prepare($query);
        if ($stmt2) {
            $stmt2->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            $stmt2->execute();
        }

        // ลดสต๊อกสินค้า
        $updateStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        if ($updateStock) {
            $updateStock->bind_param("ii", $quantity, $product_id);
            $updateStock->execute();
        }
    }

    echo json_encode(['success' => true, 'message' => 'การสั่งซื้อสำเร็จ', 'order_id' => $order_id]);
    exit;
}