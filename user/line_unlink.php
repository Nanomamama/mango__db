<?php
session_start();
require_once __DIR__ . '/../db/db.php';

if (empty($_SESSION['member_id'])) {
    header('Location: member_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    $_SESSION['line_link_error'] = 'คำขอไม่ถูกต้อง กรุณาลองใหม่';
    header('Location: member_profile.php');
    exit;
}

$memberId = (int) $_SESSION['member_id'];
$stmt = $conn->prepare('UPDATE members SET line_user_id = NULL, line_display_name = NULL, line_linked_at = NULL WHERE member_id = ?');
$stmt->bind_param('i', $memberId);
$stmt->execute();
$stmt->close();

$_SESSION['line_link_success'] = 'ยกเลิกการเชื่อมต่อ LINE แล้ว';
header('Location: member_profile.php');
exit;
