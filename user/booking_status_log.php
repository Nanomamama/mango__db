<?php

function booking_status_log_table_exists_mysqli(mysqli $conn): bool
{
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS table_count
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'booking_status_logs'
    ");
    if (!$stmt) {
        return false;
    }

    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return ((int)($row['table_count'] ?? 0)) > 0;
}

function booking_status_log_database_context_mysqli(mysqli $conn): string
{
    $database = 'unknown';
    $host = 'unknown';

    $dbResult = $conn->query("SELECT DATABASE() AS current_database");
    if ($dbResult) {
        $row = $dbResult->fetch_assoc();
        $database = (string)($row['current_database'] ?? 'unknown');
        $dbResult->close();
    }

    $hostResult = $conn->query("SELECT @@hostname AS db_host");
    if ($hostResult) {
        $row = $hostResult->fetch_assoc();
        $host = (string)($row['db_host'] ?? 'unknown');
        $hostResult->close();
    }

    return 'database=' . $database . ', db_host=' . $host;
}

function booking_status_log_database_context_pdo(PDO $pdo): string
{
    $database = 'unknown';
    $host = 'unknown';

    try {
        $database = (string)$pdo->query("SELECT DATABASE()")->fetchColumn();
    } catch (Throwable $e) {
        $database = 'unknown';
    }

    try {
        $host = (string)$pdo->query("SELECT @@hostname")->fetchColumn();
    } catch (Throwable $e) {
        $host = 'unknown';
    }

    return 'database=' . $database . ', db_host=' . $host;
}

function booking_status_log_insert_mysqli(mysqli $conn, int $booking_id, ?string $old_status, string $new_status, string $action, ?string $note = null, ?int $admin_id = null): void
{
    if (!booking_status_log_table_exists_mysqli($conn)) {
        error_log('booking_status_logs table not found in ' . booking_status_log_database_context_mysqli($conn) . '; skipped status log.');
        return;
    }

    $stmt = $conn->prepare("
        INSERT INTO booking_status_logs
            (booking_id, old_status, new_status, action, note, changed_by_admin_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    if (!$stmt) {
        error_log('booking_status_logs prepare failed: ' . $conn->error);
        return;
    }

    $stmt->bind_param('issssi', $booking_id, $old_status, $new_status, $action, $note, $admin_id);
    if (!$stmt->execute()) {
        error_log('booking_status_logs insert failed: ' . $stmt->error);
    }
    $stmt->close();
}

function booking_status_log_insert_pdo(PDO $pdo, int $booking_id, ?string $old_status, string $new_status, string $action, ?string $note = null, ?int $admin_id = null): void
{
    try {
        $tableCheck = $pdo->query("
            SELECT COUNT(*)
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'booking_status_logs'
        ");
        if (!$tableCheck || (int)$tableCheck->fetchColumn() === 0) {
            error_log('booking_status_logs table not found in ' . booking_status_log_database_context_pdo($pdo) . '; skipped status log.');
            return;
        }

        $stmt = $pdo->prepare("
            INSERT INTO booking_status_logs
                (booking_id, old_status, new_status, action, note, changed_by_admin_id, created_at)
            VALUES
                (:booking_id, :old_status, :new_status, :action, :note, :changed_by_admin_id, NOW())
        ");
        $stmt->execute([
            ':booking_id' => $booking_id,
            ':old_status' => $old_status,
            ':new_status' => $new_status,
            ':action' => $action,
            ':note' => $note,
            ':changed_by_admin_id' => $admin_id,
        ]);
    } catch (Throwable $e) {
        error_log('booking_status_logs insert failed: ' . $e->getMessage());
    }
}
