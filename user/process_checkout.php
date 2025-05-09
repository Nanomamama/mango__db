<?php
require_once '../admin/db.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูลจากฟอร์ม
    $customer_name = $_POST['customer_name'];
    $customer_phone = $_POST['customer_phone'];
    $address_number = $_POST['address_number'];
    $sub_district = $_POST['sub_district'];
    $district = $_POST['district'];
    $province = $_POST['province'];
    $postal_code = $_POST['postal_code'];
    $payment_method = $_POST['payment_method'];
    $cart = json_decode($_POST['cart'], true);

    // ตรวจสอบว่ามีการแนบไฟล์สลิปหรือไม่
    $slip_data = null;
    if ($payment_method === 'bank' || $payment_method === 'promptpay') {
        if (isset($_FILES['payment_slip']) && $_FILES['payment_slip']['error'] === UPLOAD_ERR_OK) {
            $slip_data = base64_encode(file_get_contents($_FILES['payment_slip']['tmp_name']));
        }
    }

    // คำนวณยอดรวม
    $total_price = 0;
    foreach ($cart as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

    // บันทึกคำสั่งซื้อในตาราง `orders`
    $query = "INSERT INTO orders (customer_name, customer_phone, address_number, sub_district, district, province, postal_code, payment_method, total_price, slip_path, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssssdss", $customer_name, $customer_phone, $address_number, $sub_district, $district, $province, $postal_code, $payment_method, $total_price, $slip_data);
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
        $stmt->execute();
    }

    echo json_encode(['success' => true, 'message' => 'การสั่งซื้อสำเร็จ', 'order_id' => $order_id]);
}
?>