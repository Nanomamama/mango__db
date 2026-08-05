<?php
session_start();
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/line_service.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['member_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'login required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'invalid request']);
    exit;
}

$sessionToken = $_SESSION['csrf_token'] ?? '';
$postedToken = (string) ($input['csrf_token'] ?? '');
if ($sessionToken === '' || $postedToken === '' || !hash_equals($sessionToken, $postedToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'invalid csrf token']);
    exit;
}

$lineUserId = trim((string) ($input['line_user_id'] ?? ''));
$displayName = trim((string) ($input['line_display_name'] ?? ''));

if ($lineUserId === '' || !str_starts_with($lineUserId, 'U')) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'invalid LINE user']);
    exit;
}

if (!line_can_push_to_user($lineUserId)) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'message' => 'กรุณาเพิ่ม LINE Official Account สวนลุงเผือกเป็นเพื่อนก่อน ระบบจึงจะส่งแจ้งเตือนให้คุณได้'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$memberId = (int) $_SESSION['member_id'];
$stmt = $conn->prepare('UPDATE members SET line_user_id = ?, line_display_name = ?, line_linked_at = NOW() WHERE member_id = ?');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'database error']);
    exit;
}

$stmt->bind_param('ssi', $lineUserId, $displayName, $memberId);

try {
    $stmt->execute();
    $_SESSION['line_link_success'] = 'เชื่อมต่อ LINE สำเร็จ';
    echo json_encode(['success' => true]);
} catch (mysqli_sql_exception $e) {
    error_log('LINE LIFF save error: ' . $e->getMessage());
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'LINE บัญชีนี้อาจถูกผูกกับสมาชิกอื่นแล้ว']);
}

$stmt->close();
