<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

session_start();
require_once '../admin/db.php';

/* ---------- เช็ค method ---------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

/* ---------- เช็ค login ---------- */
if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

$member_id = $_SESSION['member_id'];

/* ---------- รับค่าจากฟอร์ม ---------- */
$customer_name   = trim($_POST['customer_name']);
$customer_phone  = trim($_POST['customer_phone']);
$address_number  = trim($_POST['address_number']);
$province_id     = (int) $_POST['province_id'];
$district_id     = (int) $_POST['district_id'];
$subdistrict_id  = (int) $_POST['subdistrict_id'];
$postal_code     = trim($_POST['postal_code']);
$payment_method  = $_POST['payment_method'];

/* ---------- cart (JSON) ---------- */
$cartData = json_decode($_POST['cart'], true);

if (!$cartData || empty($cartData['items'])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลสินค้า']);
    exit;
}

$items = $cartData['items'];
$total_weight  = (float) $cartData['total_weight'];
$shipping_cost = (float) $cartData['shipping_cost'];
$total_price   = (float) $cartData['total_price'];

/* ---------- อัปโหลดสลิป ---------- */
$slip_path = null;

if (
    ($payment_method === 'bank' || $payment_method === 'promptpay') &&
    isset($_FILES['payment_slip']) &&
    $_FILES['payment_slip']['error'] === UPLOAD_ERR_OK
) {
    $ext = pathinfo($_FILES['payment_slip']['name'], PATHINFO_EXTENSION);
    $slip_name = time() . "_" . uniqid() . "." . $ext;
    $upload_dir = "../uploads/slips/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    move_uploaded_file(
        $_FILES['payment_slip']['tmp_name'],
        $upload_dir . $slip_name
    );

    $slip_path = "uploads/slips/" . $slip_name;
}

/* ---------- เริ่ม Transaction ---------- */
$conn->begin_transaction();

try {

    /* ---------- บันทึก orders ---------- */
    $sql = "INSERT INTO orders (
        member_id, customer_name, customer_phone, address_number,
        province_id, district_id, subdistrict_id, postal_code,
        payment_method, total_price, shipping_cost, total_weight,
        slip_path, status, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "isssiiissddds",
        $member_id,
        $customer_name,
        $customer_phone,
        $address_number,
        $province_id,
        $district_id,
        $subdistrict_id,
        $postal_code,
        $payment_method,
        $total_price,
        $shipping_cost,
        $total_weight,
        $slip_path
    );
    $stmt->execute();

    $order_id = $stmt->insert_id;
    $stmt->close();

    /* ---------- บันทึก order_items + ตัด stock ---------- */
    foreach ($items as $item) {

        $product_id = (int) $item['id'];
        $quantity   = (int) $item['quantity'];
        $price      = (float) $item['price'];
        $weight     = (float) ($item['weight'] ?? 0);

        // order_items
        $stmtItem = $conn->prepare("
            INSERT INTO order_items
            (order_id, product_id, quantity, price, weight, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmtItem->bind_param(
            "iiidd",
            $order_id,
            $product_id,
            $quantity,
            $price,
            $weight
        );
        $stmtItem->execute();
        $stmtItem->close();

        // ตัด stock
        $stmtStock = $conn->prepare("
            UPDATE products
            SET stock = stock - ?
            WHERE id = ? AND stock >= ?
        ");
        $stmtStock->bind_param("iii", $quantity, $product_id, $quantity);
        $stmtStock->execute();

        if ($stmtStock->affected_rows === 0) {
            throw new Exception("สินค้า ID {$product_id} สต๊อกไม่เพียงพอ");
        }

        $stmtStock->close();
    }

    /* ---------- Commit ---------- */
    $conn->commit();

    echo json_encode([
        'success'  => true,
        'message'  => 'สั่งซื้อสำเร็จ',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
