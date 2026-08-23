<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

date_default_timezone_set('Asia/Bangkok');

function export_booking_column_exists(mysqli $conn, string $column): bool
{
    $stmt = $conn->prepare("SHOW COLUMNS FROM bookings LIKE ?");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("s", $column);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    return $exists;
}

$optionalColumns = [
    'qr_sent_at',
    'payment_due_at',
    'payment_submitted_at',
    'payment_verified_at',
    'payment_received_amount',
    'payment_note',
    'cancelled_at',
    'cancel_reason',
];

$selectColumns = [
    'bookings_id',
    'booking_code',
    'guest_name',
    'guest_email',
    'guest_phone',
    'booking_date',
    'booking_time',
    'visitor_count',
    'lunch_request',
    'price_total',
    'deposit_amount',
    'balance_amount',
    'booking_type',
    'status',
    'payment_slip',
    'payment_qr_path',
    'created_at',
    'updated_at',
];

foreach ($optionalColumns as $column) {
    if (export_booking_column_exists($conn, $column)) {
        $selectColumns[] = $column;
    }
}

$sql = 'SELECT ' . implode(', ', array_map(static function (string $column): string {
    return '`' . str_replace('`', '``', $column) . '`';
}, $selectColumns)) . ' FROM bookings ORDER BY bookings_id DESC';

$result = $conn->query($sql);
if (!$result) {
    http_response_code(500);
    echo 'Export failed.';
    exit;
}

$filename = 'bookings_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
fputcsv($output, $selectColumns);

while ($row = $result->fetch_assoc()) {
    $line = [];
    foreach ($selectColumns as $column) {
        $line[] = $row[$column] ?? '';
    }
    fputcsv($output, $line);
}

fclose($output);
exit;
