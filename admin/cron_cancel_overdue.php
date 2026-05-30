<?php
date_default_timezone_set('Asia/Bangkok');

require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/auto_cancel_overdue_lib.php';

$logFile = __DIR__ . '/cron_log.txt';
$logger = static function (string $message) use ($logFile): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    echo $line;
    file_put_contents($logFile, $line, FILE_APPEND);
};

$summary = auto_cancel_overdue_bookings($conn, $logger);
$logger(
    'Auto cancel job finished. Found: ' . $summary['found'] .
    ', Cancelled: ' . $summary['cancelled'] .
    ', Emails sent: ' . $summary['emails_sent'] .
    ', Errors: ' . $summary['errors']
);

$conn->close();
