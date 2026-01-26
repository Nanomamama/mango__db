<?php
header('Content-Type: application/json; charset=utf-8');

// getBookings.php
// Returns bookings for the calendar as JSON.
// Optional query params: start=YYYY-MM-DD, end=YYYY-MM-DD

// Prefer the project's admin mysqli connection if available
// (most files in this project use ../admin/db.php to provide $conn)
if (file_exists(__DIR__ . '/../admin/db.php')) {
    include_once __DIR__ . '/../admin/db.php';
}

// If $conn (mysqli) is available use it; otherwise try legacy PDO approach
$use_mysqli = isset($conn) && $conn instanceof mysqli;
if (!$use_mysqli) {
    // Fallback: try local db.php or create PDO if variables exist
    if (file_exists(__DIR__ . '/db/db.php')) {
        include __DIR__ . '/db/db.php';
    } elseif (file_exists(__DIR__ . '/db.php')) {
        include __DIR__ . '/db.php';
    }

    if (!isset($pdo) && isset($servername, $username, $password, $dbname)) {
        try {
            $dsn = "mysql:host={$servername};dbname={$dbname};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status'=>'error','message'=>'DB connection failed','debug'=>$e->getMessage()]);
            exit;
        }
    }
}

$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-15 days'));
$end   = isset($_GET['end'])   ? $_GET['end']   : date('Y-m-d', strtotime('+365 days'));

// Basic validation
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid date format for start or end']);
    exit;
}

try {
    $out = [];
    if ($use_mysqli) {
        $sql = "SELECT bookings_id, booking_code, member_id, guest_name, guest_email, guest_phone, booking_date, booking_time, visitor_count, lunch_request, price_total, deposit_amount, balance_amount, status, is_member_booking, attachment_path, created_at, updated_at FROM bookings WHERE booking_date BETWEEN ? AND ? ORDER BY booking_date ASC";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('ss', $start, $end);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $out[] = [
                'bookings_id' => (int)$r['bookings_id'],
                'booking_code' => $r['booking_code'],
                'member_id' => $r['member_id'] !== null ? (int)$r['member_id'] : null,
                'name' => $r['guest_name'],
                'email' => $r['guest_email'],
                'phone' => $r['guest_phone'],
                'date' => $r['booking_date'],
                'time' => $r['booking_time'],
                'visitor_count' => (int)$r['visitor_count'],
                'lunch_request' => (int)$r['lunch_request'],
                'price_total' => (float)$r['price_total'],
                'deposit_amount' => (float)$r['deposit_amount'],
                'balance_amount' => (float)$r['balance_amount'],
                'status' => $r['status'],
                'is_member_booking' => (bool)$r['is_member_booking'],
                'attachment_path' => $r['attachment_path'],
                'created_at' => $r['created_at'],
                'updated_at' => $r['updated_at']
            ];
        }
        $stmt->close();
    } else {
        if (!isset($pdo)) throw new Exception('No DB connection available');
        $sql = "SELECT bookings_id, booking_code, member_id, guest_name, guest_email, guest_phone, booking_date, booking_time, visitor_count, lunch_request, price_total, deposit_amount, balance_amount, status, is_member_booking, attachment_path, created_at, updated_at FROM bookings WHERE DATE(booking_date) BETWEEN :start AND :end ORDER BY booking_date ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':start'=>$start,':end'=>$end]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $r) {
            $out[] = [
                'bookings_id' => (int)$r['bookings_id'],
                'booking_code' => $r['booking_code'],
                'member_id' => $r['member_id'] !== null ? (int)$r['member_id'] : null,
                'name' => $r['guest_name'],
                'email' => $r['guest_email'],
                'phone' => $r['guest_phone'],
                'date' => $r['booking_date'],
                'time' => $r['booking_time'],
                'visitor_count' => (int)$r['visitor_count'],
                'lunch_request' => (int)$r['lunch_request'],
                'price_total' => (float)$r['price_total'],
                'deposit_amount' => (float)$r['deposit_amount'],
                'balance_amount' => (float)$r['balance_amount'],
                'status' => $r['status'],
                'is_member_booking' => (bool)$r['is_member_booking'],
                'attachment_path' => $r['attachment_path'],
                'created_at' => $r['created_at'],
                'updated_at' => $r['updated_at']
            ];
        }
    }

    echo json_encode($out);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Query failed','debug'=>$e->getMessage()]);
}

?>
