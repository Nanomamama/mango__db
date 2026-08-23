<?php
session_start();
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/delivery_area.php';
require_once __DIR__ . '/line_service.php';

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
    int $orderId,
    ?int $memberId,
    float $latitude,
    float $longitude,
    string $addressNote
): void {
    ensure_delivery_locations_order_column($conn);

    $ipAddress = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

    $stmt = $conn->prepare("
        INSERT INTO delivery_locations (
            order_id,
            member_id,
            latitude,
            longitude,
            address_note,
            ip_address,
            user_agent
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'iiddsss',
        $orderId,
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

function ensure_delivery_locations_order_column(mysqli $conn): void
{
    $conn->query("
        CREATE TABLE IF NOT EXISTS delivery_locations (
            location_id INT(11) NOT NULL AUTO_INCREMENT,
            order_id INT(11) DEFAULT NULL,
            member_id INT(11) DEFAULT NULL,
            latitude DECIMAL(10,7) NOT NULL,
            longitude DECIMAL(10,7) NOT NULL,
            address_note TEXT DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (location_id),
            KEY idx_delivery_locations_order_id (order_id),
            KEY idx_delivery_locations_member_id (member_id),
            KEY idx_delivery_locations_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $columnResult = $conn->query("SHOW COLUMNS FROM delivery_locations LIKE 'order_id'");
    $hasOrderColumn = $columnResult && $columnResult->num_rows > 0;
    if ($columnResult instanceof mysqli_result) {
        $columnResult->free();
    }

    if (!$hasOrderColumn) {
        $conn->query("ALTER TABLE delivery_locations ADD COLUMN order_id INT(11) DEFAULT NULL AFTER location_id");
    }

    $indexResult = $conn->query("SHOW INDEX FROM delivery_locations WHERE Key_name = 'idx_delivery_locations_order_id'");
    $hasOrderIndex = $indexResult && $indexResult->num_rows > 0;
    if ($indexResult instanceof mysqli_result) {
        $indexResult->free();
    }

    if (!$hasOrderIndex) {
        $conn->query("ALTER TABLE delivery_locations ADD KEY idx_delivery_locations_order_id (order_id)");
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$sessionMemberId = isset($_SESSION['member_id']) ? (int) $_SESSION['member_id'] : null;
$memberId = $sessionMemberId ?: null;
$orderLineUserId = '';
$orderLineDisplayName = '';
$orderLineLinkedAt = null;
$usedGuestLineSession = false;

if ($memberId) {
    $lineStmt = $conn->prepare('SELECT line_user_id, line_display_name, line_linked_at FROM members WHERE member_id = ? LIMIT 1');
    if ($lineStmt) {
        $lineStmt->bind_param('i', $memberId);
        $lineStmt->execute();
        $lineMember = $lineStmt->get_result()->fetch_assoc();
        $lineStmt->close();

        $orderLineUserId = trim((string) ($lineMember['line_user_id'] ?? ''));
        $orderLineDisplayName = trim((string) ($lineMember['line_display_name'] ?? ''));
        $orderLineLinkedAt = $lineMember['line_linked_at'] ?? null;
    }
} else {
    $orderLineUserId = trim((string) ($_SESSION['guest_line_user_id'] ?? ''));
    $orderLineDisplayName = trim((string) ($_SESSION['guest_line_display_name'] ?? ''));
    $orderLineLinkedAt = $_SESSION['guest_line_linked_at'] ?? null;
    $usedGuestLineSession = $orderLineUserId !== '';
}

if ($orderLineUserId === '' && !empty($_SESSION['guest_line_user_id'])) {
    $orderLineUserId = trim((string) ($_SESSION['guest_line_user_id'] ?? ''));
    $orderLineDisplayName = trim((string) ($_SESSION['guest_line_display_name'] ?? ''));
    $orderLineLinkedAt = $_SESSION['guest_line_linked_at'] ?? null;
    $usedGuestLineSession = $orderLineUserId !== '';
}

if ($orderLineUserId === '') {
    $orderLineUserId = null;
    $orderLineDisplayName = null;
    $orderLineLinkedAt = null;
}

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

    try {
        if (!point_in_delivery_area($deliveryLatitude, $deliveryLongitude)) {
            redirect_with_error('ตำแหน่งนี้อยู่นอกพื้นที่จัดส่ง กรุณาปักหมุดใหม่ภายในพื้นที่ที่กำหนด');
        }
    } catch (Throwable $e) {
        error_log('Delivery area validation failed: ' . $e->getMessage());
        redirect_with_error('ไม่สามารถตรวจสอบพื้นที่จัดส่งได้ กรุณาลองใหม่อีกครั้ง');
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
            line_user_id,
            line_display_name,
            line_linked_at,
            customer_name,
            customer_phone,
            customer_address,
            receive_type,
            receive_datetime,
            order_status,
            order_date,
            total_amount
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
    ");
    $orderStmt->bind_param(
        'sisssssssssd',
        $orderCode,
        $memberId,
        $orderLineUserId,
        $orderLineDisplayName,
        $orderLineLinkedAt,
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
        save_delivery_location($conn, $orderId, $memberId, $deliveryLatitude, $deliveryLongitude, $customerAddress);
    }

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    redirect_with_error('ไม่สามารถบันทึกคำสั่งซื้อได้ กรุณาลองใหม่อีกครั้ง');
}

if (!empty($orderLineUserId)) {
    line_send_order_created(
        (string) $orderLineUserId,
        $orderCode,
        $totalAmount,
        $receiveType,
        $receiveDatetimeValue
    );
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

if ($usedGuestLineSession) {
    unset($_SESSION['guest_line_user_id'], $_SESSION['guest_line_display_name'], $_SESSION['guest_line_linked_at']);
}

header('Location: success.php?code=' . urlencode($orderCode));
exit;
?>
