<?php
session_start();
require_once __DIR__ . '/../db/db.php';

function redirect_with_error(string $message): void
{
    $_SESSION['order_error'] = $message;
    header('Location: order.php');
    exit;
}

function generate_order_code(mysqli $conn): string
{
    do {
        $orderCode = 'ORD' . date('YmdHis') . strtoupper(bin2hex(random_bytes(3)));
        $stmt = $conn->prepare('SELECT order_id FROM orders WHERE order_code = ? LIMIT 1');
        $stmt->bind_param('s', $orderCode);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
    } while ($exists);

    return $orderCode;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$memberId = isset($_POST['member_id']) && $_POST['member_id'] !== '' ? (int) $_POST['member_id'] : null;
$customerName = trim($_POST['customer_name'] ?? '');
$customerPhone = trim($_POST['customer_phone'] ?? '');
$customerAddress = trim($_POST['customer_address'] ?? '');
$receiveType = $_POST['receive_type'] ?? '';
$receiveDatetimeRaw = trim($_POST['receive_datetime'] ?? '');
$cartJson = $_POST['cart_data'] ?? '';

if ($customerName === '' || $customerPhone === '') {
    redirect_with_error('กรุณากรอกชื่อและเบอร์โทรศัพท์ให้ครบถ้วน');
}

if (!in_array($receiveType, ['pickup', 'delivery'], true)) {
    redirect_with_error('รูปแบบการรับสินค้าไม่ถูกต้อง');
}

if ($receiveType === 'delivery' && $customerAddress === '') {
    redirect_with_error('กรุณากรอกรายละเอียดที่อยู่สำหรับจัดส่ง');
}

$receiveDatetime = DateTime::createFromFormat('Y-m-d H:i', $receiveDatetimeRaw);
if (!$receiveDatetime || $receiveDatetime->format('Y-m-d H:i') !== $receiveDatetimeRaw) {
    redirect_with_error('กรุณาเลือกวันและเวลารับสินค้าให้ถูกต้อง');
}
$receiveDatetimeValue = $receiveDatetime->format('Y-m-d H:i:s');

$cart = json_decode($cartJson, true);
if (!is_array($cart) || empty($cart)) {
    redirect_with_error('ไม่พบรายการสินค้าในคำสั่งซื้อ');
}

$requestedItems = [];
foreach ($cart as $item) {
    $productId = isset($item['product_id']) ? (int) $item['product_id'] : 0;
    $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;

    if ($productId <= 0 || $quantity <= 0) {
        redirect_with_error('ข้อมูลสินค้าในตะกร้าไม่ถูกต้อง');
    }

    if (!isset($requestedItems[$productId])) {
        $requestedItems[$productId] = 0;
    }
    $requestedItems[$productId] += $quantity;
}

$productIds = array_keys($requestedItems);
$placeholders = implode(',', array_fill(0, count($productIds), '?'));
$types = str_repeat('i', count($productIds));

$productSql = "
    SELECT product_id, product_name, price, status
    FROM products
    WHERE product_id IN ($placeholders)
";
$productStmt = $conn->prepare($productSql);
$productStmt->bind_param($types, ...$productIds);
$productStmt->execute();
$productResult = $productStmt->get_result();

$products = [];
while ($row = $productResult->fetch_assoc()) {
    $products[(int) $row['product_id']] = $row;
}
$productStmt->close();

if (count($products) !== count($requestedItems)) {
    redirect_with_error('พบสินค้าบางรายการไม่ถูกต้องหรือถูกลบออกจากระบบแล้ว');
}

$normalizedItems = [];
$totalAmount = 0.0;
foreach ($requestedItems as $productId => $quantity) {
    $product = $products[$productId];

    if (($product['status'] ?? '') !== 'active') {
        redirect_with_error('มีสินค้าที่ไม่พร้อมจำหน่ายอยู่ในตะกร้า กรุณาตรวจสอบอีกครั้ง');
    }

    $price = (float) $product['price'];
    $lineTotal = $price * $quantity;
    $totalAmount += $lineTotal;

    $normalizedItems[] = [
        'product_id' => $productId,
        'product_name' => $product['product_name'],
        'quantity' => $quantity,
        'price' => $price,
    ];
}

if ($receiveType === 'delivery' && $totalAmount < 500) {
    redirect_with_error('การจัดส่งถึงบ้านต้องมียอดสั่งซื้อขั้นต่ำ 500 บาท');
}

$orderCode = generate_order_code($conn);
$orderStatus = 'pending';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn->begin_transaction();

    $orderStmt = $conn->prepare("
        INSERT INTO orders (
            order_code,
            member_id,
            customer_name,
            customer_phone,
            customer_address,
            receive_type,
            receive_datetime,
            order_status,
            order_date,
            total_amount
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
    ");
    $orderStmt->bind_param(
        'sissssssd',
        $orderCode,
        $memberId,
        $customerName,
        $customerPhone,
        $customerAddress,
        $receiveType,
        $receiveDatetimeValue,
        $orderStatus,
        $totalAmount
    );
    $orderStmt->execute();
    $orderId = $orderStmt->insert_id;
    $orderStmt->close();

    $itemStmt = $conn->prepare("
        INSERT INTO order_items (
            order_id,
            product_id,
            quantity,
            price,
            product_name
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($normalizedItems as $item) {
        $itemStmt->bind_param(
            'iiids',
            $orderId,
            $item['product_id'],
            $item['quantity'],
            $item['price'],
            $item['product_name']
        );
        $itemStmt->execute();
    }
    $itemStmt->close();

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    redirect_with_error('ไม่สามารถบันทึกคำสั่งซื้อได้ กรุณาลองใหม่อีกครั้ง');
}

$receiveMap = [
    'pickup' => 'รับที่สวน',
    'delivery' => 'ส่งถึงบ้าน',
];
$receiveTypeText = $receiveMap[$receiveType] ?? '-';

$message = "มีคำสั่งซื้อใหม่\n";
$message .= "รหัสออเดอร์: {$orderCode}\n";
$message .= "ลูกค้า: {$customerName}\n";
$message .= "โทร: {$customerPhone}\n";
$message .= "วิธีรับสินค้า: {$receiveTypeText}\n";
$message .= "วันรับสินค้า: {$receiveDatetimeRaw}\n";
$message .= "ที่อยู่/หมายเหตุ: " . ($customerAddress !== '' ? $customerAddress : '-') . "\n\n";
$message .= "รายการสินค้า\n";

foreach ($normalizedItems as $item) {
    $message .= '- ' . $item['product_name'] . ' x' . $item['quantity'] . "\n";
}

$message .= "\nยอดรวม: " . number_format($totalAmount, 2) . " บาท";
$message .= "\nตรวจสอบออเดอร์: http://localhost:8000/admin/order_detail.php?id=" . $orderId;

$lineNotifyFile = __DIR__ . '/../admin/line_notify.php';
if (is_file($lineNotifyFile)) {
    ob_start();
    include $lineNotifyFile;
    ob_end_clean();
}

header('Location: success.php?code=' . urlencode($orderCode));
exit;
?>
