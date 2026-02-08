<?php
require_once __DIR__ . '/../db/db.php';

$id = $_POST['id'];
$status = $_POST['s'];
$note = $_POST['admin_note'] ?? null;

if ($status === 'rejected') {
    $stmt = $conn->prepare("
        UPDATE orders 
        SET order_status = ?, admin_note = ?
        WHERE order_id = ?
    ");
    $stmt->bind_param("ssi", $status, $note, $id);
} else {
    $stmt = $conn->prepare("
        UPDATE orders 
        SET order_status = ?
        WHERE order_id = ?
    ");
    $stmt->bind_param("si", $status, $id);
}

$stmt->execute();
header("Location: manage_orders.php");
