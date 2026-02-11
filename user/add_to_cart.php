<?php
session_start();
require_once __DIR__ . '/../db/db.php';

$id = $_POST['product_id'];
$qty = (int)$_POST['qty'];

$stmt = $conn->prepare("SELECT * FROM products WHERE product_id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();

if(!$p) exit;

$_SESSION['cart'][$id]['id'] = $id;
$_SESSION['cart'][$id]['name'] = $p['product_name'];
$_SESSION['cart'][$id]['price'] = $p['price'];
$_SESSION['cart'][$id]['qty'] += $qty;

echo "ok";
