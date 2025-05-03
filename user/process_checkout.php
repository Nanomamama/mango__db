<?php
require_once '../admin/db.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบว่ามีคีย์ 'cart' อยู่ใน $_POST หรือไม่
    if (!isset($_POST['cart'])) {
        echo json_encode(['success' => false, 'message' => 'ไม่มีข้อมูลสินค้าในตะกร้า']);
        exit;
    }

    // รับข้อมูลจากฟอร์ม
    $customer_name = htmlspecialchars($_POST['customer_name']);
    $customer_phone = htmlspecialchars($_POST['customer_phone']);
    $address_number = htmlspecialchars($_POST['address_number']);
    $sub_district = htmlspecialchars($_POST['sub_district']);
    $district = htmlspecialchars($_POST['district']);
    $province = htmlspecialchars($_POST['province']);
    $postal_code = htmlspecialchars($_POST['postal_code']);
    $payment_method = htmlspecialchars($_POST['payment_method']);
    $cart = json_decode($_POST['cart'], true); // แปลง JSON เป็น array

    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($customer_name) || empty($customer_phone) || empty($address_number) || empty($sub_district) || empty($district) || empty($province) || empty($postal_code) || empty($payment_method) || empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
        exit;
    }

    // คำนวณยอดรวม
    $total_price = 0;
    foreach ($cart as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

    // บันทึกคำสั่งซื้อในตาราง `orders`
    $query = "INSERT INTO orders (customer_name, customer_phone, address_number, sub_district, district, province, postal_code, payment_method, total_price, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssssd", $customer_name, $customer_phone, $address_number, $sub_district, $district, $province, $postal_code, $payment_method, $total_price);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถบันทึกคำสั่งซื้อได้']);
        exit;
    }

    // รับ ID ของคำสั่งซื้อที่เพิ่งบันทึก
    $order_id = $stmt->insert_id;

    // บันทึกรายการสินค้าในตาราง `order_items`
    foreach ($cart as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];

        $query = "INSERT INTO order_items (order_id, product_id, quantity, price, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถบันทึกรายการสินค้าได้']);
            exit;
        }

        // อัปเดตจำนวนสินค้าคงเหลือในสต๊อก
        $query = "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $quantity, $product_id, $quantity);
        if (!$stmt->execute() || $stmt->affected_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'สินค้าบางรายการมีจำนวนไม่เพียงพอในสต๊อก']);
            exit;
        }
    }

    // ส่งผลลัพธ์กลับไปยังฝั่งไคลเอนต์
    echo json_encode(['success' => true, 'message' => 'การสั่งซื้อสำเร็จ', 'order_id' => $order_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>