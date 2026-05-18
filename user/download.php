<?php
// Serve booking files after ownership checks.
session_start();
require_once __DIR__ . '/../db/db.php';

$types = [
    'doc' => [
        'dir' => __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR,
        'column' => 'attachment_path',
        'path_value' => static fn(string $filename): string => 'uploads/' . $filename,
    ],
    'slip' => [
        'dir' => __DIR__ . DIRECTORY_SEPARATOR . 'Paymentslip-Gardenreservation' . DIRECTORY_SEPARATOR,
        'column' => 'payment_slip',
        'path_value' => static fn(string $filename): string => $filename,
    ],
    'qr' => [
        'dir' => __DIR__ . DIRECTORY_SEPARATOR . 'PaymentQR-Gardenreservation' . DIRECTORY_SEPARATOR,
        'column' => 'payment_qr_path',
        'path_value' => static fn(string $filename): string => $filename,
    ],
];

$type = $_GET['type'] ?? '';
$file = $_GET['file'] ?? '';

if (!isset($types[$type]) || $file === '') {
    http_response_code(400);
    exit('Bad request');
}

$filename = basename($file);
$config = $types[$type];
$filepath = $config['dir'] . $filename;

if (!is_file($filepath)) {
    http_response_code(404);
    exit('File not found');
}

$isAdmin = isset($_SESSION['admin_id']);
$memberId = isset($_SESSION['member_id']) ? (int)$_SESSION['member_id'] : 0;

if (!$isAdmin) {
    if ($memberId <= 0) {
        http_response_code(403);
        exit('Forbidden');
    }

    $column = $config['column'];
    $storedValue = $config['path_value']($filename);
    $stmt = $conn->prepare("SELECT member_id FROM bookings WHERE ($column = ? OR $column = ?) LIMIT 1");
    $stmt->bind_param('ss', $storedValue, $filename);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || (int)$row['member_id'] !== $memberId) {
        http_response_code(403);
        exit('Forbidden');
    }
}

$mime = 'application/octet-stream';
if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo) {
        $mime = finfo_file($finfo, $filepath) ?: $mime;
        finfo_close($finfo);
    }
}

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: private, max-age=10800');

readfile($filepath);
exit;
