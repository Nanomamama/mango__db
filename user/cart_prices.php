<?php
session_start();
require_once __DIR__ . '/../db/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function cart_price_response(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cart_price_response(405, [
        'status' => 'error',
        'message' => 'Method not allowed',
    ]);
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (!is_array($payload) || !isset($payload['cart']) || !is_array($payload['cart'])) {
    cart_price_response(400, [
        'status' => 'error',
        'message' => 'Invalid cart data',
    ]);
}

$requestedItems = [];
$maxItems = 50;
$maxQuantity = 99;

foreach ($payload['cart'] as $item) {
    $productId = isset($item['product_id']) ? (int) $item['product_id'] : 0;
    $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;

    if ($productId <= 0 || $quantity <= 0) {
        cart_price_response(400, [
            'status' => 'error',
            'message' => 'Invalid product or quantity',
        ]);
    }

    if (!isset($requestedItems[$productId])) {
        $requestedItems[$productId] = 0;
    }

    $requestedItems[$productId] += $quantity;

    if ($requestedItems[$productId] > $maxQuantity) {
        cart_price_response(400, [
            'status' => 'error',
            'message' => 'Quantity is too high',
        ]);
    }
}

if (count($requestedItems) > $maxItems) {
    cart_price_response(400, [
        'status' => 'error',
        'message' => 'Too many cart items',
    ]);
}

if (empty($requestedItems)) {
    cart_price_response(200, [
        'status' => 'success',
        'items' => [],
        'total' => 0,
    ]);
}

$productIds = array_keys($requestedItems);
$placeholders = implode(',', array_fill(0, count($productIds), '?'));
$types = str_repeat('i', count($productIds));

$stmt = $conn->prepare("
    SELECT product_id, product_name, price, unit, product_image, status
    FROM products
    WHERE product_id IN ($placeholders)
");

if (!$stmt) {
    cart_price_response(500, [
        'status' => 'error',
        'message' => 'Unable to check product prices',
    ]);
}

$stmt->bind_param($types, ...$productIds);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[(int) $row['product_id']] = $row;
}
$stmt->close();

if (count($products) !== count($requestedItems)) {
    cart_price_response(400, [
        'status' => 'error',
        'message' => 'Some products are no longer available',
    ]);
}

$items = [];
$total = 0.0;

foreach ($requestedItems as $productId => $quantity) {
    $product = $products[$productId];

    if (($product['status'] ?? '') !== 'active') {
        cart_price_response(400, [
            'status' => 'error',
            'message' => 'Some products are inactive',
        ]);
    }

    $price = (float) $product['price'];
    $imageName = basename((string) ($product['product_image'] ?? ''));
    $imagePath = $imageName !== ''
        ? '../admin/uploads/products/' . $imageName
        : '../assets/no-image.png';

    $items[] = [
        'product_id' => $productId,
        'name' => (string) $product['product_name'],
        'price' => $price,
        'unit' => (string) ($product['unit'] ?? ''),
        'image' => $imagePath,
        'quantity' => $quantity,
        'line_total' => $price * $quantity,
    ];

    $total += $price * $quantity;
}

cart_price_response(200, [
    'status' => 'success',
    'items' => $items,
    'total' => $total,
]);
