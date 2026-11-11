<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function calendar_clear_json(int $statusCode, array $payload): never
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function calendar_clear_valid_date(string $date): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    [$year, $month, $day] = array_map('intval', explode('-', $date));
    return checkdate($month, $day, $year);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    calendar_clear_json(405, ['success' => false, 'message' => 'Method not allowed']);
}

$data = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($data)) {
    calendar_clear_json(400, ['success' => false, 'message' => 'Invalid JSON payload']);
}

$postedToken = (string) ($data['csrf_token'] ?? '');
$sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
if ($sessionToken === '' || !hash_equals($sessionToken, $postedToken)) {
    calendar_clear_json(403, ['success' => false, 'message' => 'Invalid CSRF token']);
}

$dates = $data['dates'] ?? null;
if (!is_array($dates) || $dates === []) {
    calendar_clear_json(422, ['success' => false, 'message' => 'Invalid dates']);
}

if (count($dates) > 366) {
    calendar_clear_json(422, ['success' => false, 'message' => 'Too many dates']);
}

$uniqueDates = [];
foreach ($dates as $date) {
    $date = (string) $date;
    if (!calendar_clear_valid_date($date)) {
        calendar_clear_json(422, ['success' => false, 'message' => 'Invalid date: ' . $date]);
    }
    $uniqueDates[$date] = true;
}

$stmt = $conn->prepare('DELETE FROM calendar_dates WHERE date = ?');
if (!$stmt) {
    calendar_clear_json(500, ['success' => false, 'message' => 'Database prepare failed']);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn->begin_transaction();
    $deleted = 0;
    foreach (array_keys($uniqueDates) as $date) {
        $stmt->bind_param('s', $date);
        $stmt->execute();
        $deleted += $stmt->affected_rows;
    }
    $stmt->close();
    $conn->commit();

    calendar_clear_json(200, [
        'success' => true,
        'deleted' => $deleted,
    ]);
} catch (Throwable $e) {
    if ($conn instanceof mysqli) {
        try {
            $conn->rollback();
        } catch (Throwable $rollbackError) {
            // Keep the original database error for the server log.
        }
    }

    error_log('clear_update_date failed: ' . $e->getMessage());
    calendar_clear_json(500, ['success' => false, 'message' => 'Calendar clear failed']);
}
