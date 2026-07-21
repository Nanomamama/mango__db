<?php
session_start();
require_once __DIR__ . '/../db/db.php';

const MAX_CART_ITEMS = 50;
const MAX_ITEM_QUANTITY = 99;

function redirect_with_error(string $message): void
{
    $_SESSION['order_error'] = $message;
    header('Location: order.php');
    exit;
}

function generate_order_code(mysqli $conn): string
{
    do {
        // สร้างรหัสคำสั่งซื้อที่ไม่ซ้ำกัน โดยใช้วันที่และตัวอักษรสุ่ม
        $random = strtoupper(bin2hex(random_bytes(2))); // 4 ตัว
        $orderCode = 'ORD' . date('ymd') . $random;

        $stmt = $conn->prepare('SELECT order_id FROM orders WHERE order_code = ? LIMIT 1');
        $stmt->bind_param('s', $orderCode);
        $stmt->execute();

        $exists = $stmt->get_result()->num_rows > 0;

        $stmt->close();

    } while ($exists);

    return $orderCode;
}

function save_delivery_location(
    mysqli $conn,
    ?int $memberId,
    float $latitude,
    float $longitude,
    string $addressNote
): void {
    $ipAddress = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

    $stmt = $conn->prepare("
        INSERT INTO delivery_locations (
            member_id,
            latitude,
            longitude,
            address_note,
            ip_address,
            user_agent
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'iddsss',
        $memberId,
        $latitude,
        $longitude,
        $addressNote,
        $ipAddress,
        $userAgent
    );
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$sessionMemberId = isset($_SESSION['member_id']) ? (int) $_SESSION['member_id'] : null;
$memberId = $sessionMemberId ?: null;
$customerName = trim($_POST['customer_name'] ?? '');
$customerPhone = preg_replace('/[^0-9]/', '', trim($_POST['customer_phone'] ?? ''));
$customerAddress = trim($_POST['customer_address'] ?? '');
$receiveType = $_POST['receive_type'] ?? '';
$receiveDatetimeRaw = trim($_POST['receive_datetime'] ?? '');
$cartJson = $_POST['cart_data'] ?? '';
$deliveryLatitudeRaw = trim((string) ($_POST['delivery_latitude'] ?? ''));
$deliveryLongitudeRaw = trim((string) ($_POST['delivery_longitude'] ?? ''));

if ($customerName === '' || $customerPhone === '') {
    redirect_with_error('กรุณากรอกชื่อและเบอร์โทรศัพท์ให้ครบถ้วน');
}

if (!in_array($receiveType, ['pickup', 'delivery'], true)) {
    redirect_with_error('รูปแบบการรับสินค้าไม่ถูกต้อง');
}

if ($receiveType === 'delivery' && $customerAddress === '') {
    redirect_with_error('กรุณากรอกรายละเอียดที่อยู่สำหรับจัดส่ง');
}

$deliveryLatitude = null;
$deliveryLongitude = null;
if ($receiveType === 'delivery') {
    if ($deliveryLatitudeRaw === '' || $deliveryLongitudeRaw === '') {
        redirect_with_error('กรุณาปักหมุดตำแหน่งจัดส่งบนแผนที่');
    }

    $deliveryLatitude = (float) $deliveryLatitudeRaw;
    $deliveryLongitude = (float) $deliveryLongitudeRaw;

    if ($deliveryLatitude < -90 || $deliveryLatitude > 90 || $deliveryLongitude < -180 || $deliveryLongitude > 180) {
        redirect_with_error('พิกัดตำแหน่งจัดส่งไม่ถูกต้อง');
    }
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

if (count($cart) > MAX_CART_ITEMS) {
    redirect_with_error('รายการสินค้าในตะกร้ามากเกินไป');
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

    if ($requestedItems[$productId] > MAX_ITEM_QUANTITY) {
        redirect_with_error('จำนวนสินค้าในตะกร้ามากเกินไป');
    }
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

    if ($receiveType === 'delivery' && $deliveryLatitude !== null && $deliveryLongitude !== null) {
        save_delivery_location($conn, $memberId, $deliveryLatitude, $deliveryLongitude, $customerAddress);
    }

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    redirect_with_error('ไม่สามารถบันทึกคำสั่งซื้อได้ กรุณาลองใหม่อีกครั้ง');
}

// ===============================
// Guest order history (COOKIE)
// ===============================

$guestOrders = [];

if (isset($_COOKIE['guest_orders'])) {
    $guestOrders = json_decode($_COOKIE['guest_orders'], true);
}

// กันข้อมูลเสีย
if (!is_array($guestOrders)) {
    $guestOrders = [];
}

// เพิ่ม order ใหม่
$guestOrders[$orderCode] = time();

// เก็บแค่ล่าสุด 20 รายการ
if (count($guestOrders) > 20) {
    asort($guestOrders);
    $guestOrders = array_slice($guestOrders, -20, null, true);
}

// เก็บ cookie 30 วัน
setcookie(
    'guest_orders',
    json_encode($guestOrders),
    time() + (86400 * 30),
    '/',
    '',
    isset($_SERVER['HTTPS']),
    true
);




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
$message .= "\nตรวจสอบออเดอร์: https://khamaon.com/mango/admin/order_detail.php?code=" . urlencode($orderCode);

$lineNotifyFile = __DIR__ . '/../admin/line_notify.php';
if (is_file($lineNotifyFile)) {
    ob_start();
    include $lineNotifyFile;
    ob_end_clean();
}

header('Location: success.php?code=' . urlencode($orderCode));
exit;
?>
