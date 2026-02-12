<?php
require_once __DIR__ . '/../db/db.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    $stmt = $conn->prepare("DELETE FROM orders WHERE order_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header("Location: manage_orders.php?msg=deleted");

exit;
