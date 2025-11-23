<?php
session_start();
require_once '../admin/db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['member_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthenticated']);
    exit;
}

$member_id = (int)$_SESSION['member_id'];

// ดึง booking ของสมาชิก (ล่าสุด 10 รายการ)
// Select all columns so we can include optional fields like rejection_reason or approved_by if present
$stmt = $conn->prepare("SELECT * FROM bookings WHERE member_id = ? ORDER BY date DESC, id DESC LIMIT 20");
$stmt->bind_param("i", $member_id);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'db_error', 'detail' => $conn->error]);
    exit;
}
$res = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) {
    // normalize approved_at to null or ISO8601
    $approved_at = null;
    $approved_at_display = null;
    if (!empty($r['approved_at'])) {
        $ts = strtotime($r['approved_at']);
        if ($ts !== false) $approved_at = date('c', $ts);
        // create a Thai-friendly display string (วันที่ เดือน พ.ศ. เวลา HH:MM)
        if ($ts !== false) {
            $months = ["", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
                "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
            $d = date('j', $ts);
            $m = $months[(int)date('n', $ts)];
            $y = date('Y', $ts) + 543;
            $hh = date('H', $ts);
            $mm = date('i', $ts);
            $approved_at_display = sprintf('%d %s %d เวลา %s:%s น.', $d, $m, $y, $hh, $mm);
        }
    }
    // include optional fields if present
    $rejection_reason = $r['rejection_reason'] ?? null;
    $approved_by = $r['approved_by'] ?? null;
    $created_at = $r['created_at'] ?? null;

    $rows[] = [
        'id' => (int)$r['id'],
        'date' => $r['date'],
        'time' => $r['time'],
        'name' => $r['name'],
        'status' => $r['status'],
        'approved_at' => $approved_at,
        'approved_at_display' => $approved_at_display,
        'approved_by' => $approved_by,
        'rejection_reason' => $rejection_reason,
        'created_at' => $created_at,
        // Payment related fields (if present in the bookings table)
        'total_amount' => isset($r['total_amount']) ? (float)$r['total_amount'] : null,
        'deposit_amount' => isset($r['deposit_amount']) ? (float)$r['deposit_amount'] : null,
        'remain_amount' => isset($r['remain_amount']) ? (float)$r['remain_amount'] : null,
        'paid_amount' => isset($r['paid_amount']) ? (float)$r['paid_amount'] : null,
        'slip' => $r['slip'] ?? null,
        'doc' => $r['doc'] ?? null,
    ];
}
$stmt->close();

echo json_encode(['data' => $rows], JSON_UNESCAPED_UNICODE);
