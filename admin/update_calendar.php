<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function calendar_json(int $statusCode, array $payload): never
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function calendar_valid_date(string $date): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    [$year, $month, $day] = array_map('intval', explode('-', $date));
    return checkdate($month, $day, $year);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    calendar_json(405, ['success' => false, 'message' => 'Method not allowed']);
}

$data = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($data)) {
    calendar_json(400, ['success' => false, 'message' => 'Invalid JSON payload']);
}

$postedToken = (string) ($data['csrf_token'] ?? '');
$sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
if ($sessionToken === '' || !hash_equals($sessionToken, $postedToken)) {
    calendar_json(403, ['success' => false, 'message' => 'Invalid CSRF token']);
}

$dates = $data['dates'] ?? null;
$status = (string) ($data['status'] ?? '');
$allowedStatuses = ['available', 'unavailable'];

if (!is_array($dates) || $dates === [] || !in_array($status, $allowedStatuses, true)) {
    calendar_json(422, ['success' => false, 'message' => 'Invalid dates or status']);
}

if (count($dates) > 366) {
    calendar_json(422, ['success' => false, 'message' => 'Too many dates']);
}

$uniqueDates = [];
foreach ($dates as $date) {
    $date = (string) $date;
    if (!calendar_valid_date($date)) {
        calendar_json(422, ['success' => false, 'message' => 'Invalid date: ' . $date]);
    }
    $uniqueDates[$date] = true;
}

$stmt = $conn->prepare('
    INSERT INTO calendar_dates (date, status)
    VALUES (?, ?)
    ON DUPLICATE KEY UPDATE status = VALUES(status)
');
if (!$stmt) {
    calendar_json(500, ['success' => false, 'message' => 'Database prepare failed']);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn->begin_transaction();
    foreach (array_keys($uniqueDates) as $date) {
        $stmt->bind_param('ss', $date, $status);
        $stmt->execute();
    }
    $stmt->close();
    $conn->commit();

    calendar_json(200, [
        'success' => true,
        'updated' => count($uniqueDates),
    ]);
} catch (Throwable $e) {
    if ($conn instanceof mysqli) {
        try {
            $conn->rollback();
        } catch (Throwable $rollbackError) {
            // Keep the original database error for the server log.
        }
    }

    error_log('update_calendar failed: ' . $e->getMessage());
    calendar_json(500, ['success' => false, 'message' => 'Calendar update failed']);
}
