<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

header('Content-Type: application/json; charset=utf-8');

$member_id_filter = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 0;

if ($member_id_filter <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
    exit;
}

try {
    $sql = "SELECT attempted_at, ip_address, user_agent, success, reason 
            FROM login_logs 
            WHERE member_id = ? 
            ORDER BY attempted_at DESC
            LIMIT 50"; // จำกัดการแสดงผล 50 รายการล่าสุดเพื่อประสิทธิภาพ
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $member_id_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        // Sanitize output
        $logs[] = [
            'attempted_at' => htmlspecialchars($row['attempted_at']),
            'ip_address'   => htmlspecialchars($row['ip_address']),
            'user_agent'   => htmlspecialchars($row['user_agent'] ?? 'N/A'),
            'success'      => (bool)$row['success'],
            'reason'       => htmlspecialchars($row['reason'] ?? '')
        ];
    }
    $stmt->close();

    echo json_encode(['success' => true, 'logs' => $logs]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>