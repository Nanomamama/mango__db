<?php
require_once 'db.php';

$id = $_GET['id'];
$status = $_GET['s'];

$stmt = $conn->prepare("
UPDATE orders 
SET order_status=?
WHERE order_id=?
");
$stmt->bind_param("si", $status, $id);
$stmt->execute();

header("Location: manage_orders.php");
