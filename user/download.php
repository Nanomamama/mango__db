<?php
// Serve uploaded files after basic authorization checks.
session_start();
require_once __DIR__ . '/../db/db.php';

// Allowed types and their directories
$allowed = [
    'doc' => __DIR__ . DIRECTORY_SEPARATOR . 'Doc' . DIRECTORY_SEPARATOR,
    'slip' => __DIR__ . DIRECTORY_SEPARATOR . 'Paymentslip-Gardenreservation' . DIRECTORY_SEPARATOR,
];

$type = $_GET['type'] ?? '';
$file = $_GET['file'] ?? '';

if (!isset($allowed[$type]) || empty($file)) {
    http_response_code(400);
    exit('Bad request');
}

// sanitize filename
$filename = basename($file);
$filepath = $allowed[$type] . $filename;

if (!file_exists($filepath) || !is_file($filepath)) {
    http_response_code(404);
    exit('File not found');
}

// Authorization: allow admin, or member who owns the booking
$isAdmin = isset($_SESSION['admin_id']);
$memberId = $_SESSION['member_id'] ?? null;

if (!$isAdmin) {
    // check bookings table for ownership (doc or slip)
    $col = $type === 'doc' ? 'doc' : 'slip';
    $stmt = $conn->prepare("SELECT member_id FROM bookings WHERE $col = ? LIMIT 1");
    $stmt->bind_param('s', $filename);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if (!$row || intval($row['member_id']) !== intval($memberId)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

// Serve file with proper headers
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $filepath) ?: 'application/octet-stream';
finfo_close($finfo);

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: private, max-age=10800');

readfile($filepath);
exit;

?>
