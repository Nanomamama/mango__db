<?php
require_once __DIR__ . '/../db/db.php';
$id = $_REQUEST['id'];
$status = $_REQUEST['s'];
$note = $_POST['admin_note'] ?? null;

if ($status == 'rejected') {
    $stmt = $conn->prepare("UPDATE orders SET order_status=?, admin_note=? WHERE order_id=?");
    $stmt->bind_param("ssi", $status, $note, $id);
} else {
    $stmt = $conn->prepare("UPDATE orders SET order_status=? WHERE order_id=?");
    $stmt->bind_param("si", $status, $id);
}

if ($status == 'completed') {

    $sumQ = $conn->prepare("
        SELECT SUM(price * quantity) AS total 
        FROM order_items 
        WHERE order_id=?
    ");
    $sumQ->bind_param("i", $id);
    $sumQ->execute();
    $total = $sumQ->get_result()->fetch_assoc()['total'];

    $stmt = $conn->prepare("
        UPDATE orders 
        SET order_status='completed', total_amount=? 
        WHERE order_id=?
    ");
    $stmt->bind_param("di", $total, $id);
    $stmt->execute();
}

$stmt->execute();
header("Location: manage_orders.php");
