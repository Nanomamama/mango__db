<?php
require_once 'db.php';
$order_id = $_POST['order_id'];
$status = $_POST['status'];
$stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $order_id);
$stmt->execute();
header("Location: order_details.php?order_id=" . $order_id);
exit;