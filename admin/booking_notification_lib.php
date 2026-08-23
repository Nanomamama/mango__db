<?php
declare(strict_types=1);

function admin_booking_notifications_ensure_mysqli(mysqli $conn): bool
{
    $sql = "
        CREATE TABLE IF NOT EXISTS admin_notifications (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            type VARCHAR(64) NOT NULL,
            ref_table VARCHAR(64) NOT NULL DEFAULT 'bookings',
            ref_id INT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            read_at DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_admin_notification_event (type, ref_table, ref_id),
            KEY idx_admin_notifications_unread (read_at, created_at),
            KEY idx_admin_notifications_ref (ref_table, ref_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    return $conn->query($sql) === true;
}

function admin_booking_notifications_ensure_pdo(PDO $pdo): bool
{
    $sql = "
        CREATE TABLE IF NOT EXISTS admin_notifications (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            type VARCHAR(64) NOT NULL,
            ref_table VARCHAR(64) NOT NULL DEFAULT 'bookings',
            ref_id INT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            read_at DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_admin_notification_event (type, ref_table, ref_id),
            KEY idx_admin_notifications_unread (read_at, created_at),
            KEY idx_admin_notifications_ref (ref_table, ref_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    $pdo->exec($sql);
    return true;
}

function admin_booking_notification_insert_mysqli(mysqli $conn, string $type, int $bookingId): bool
{
    if ($bookingId <= 0 || !admin_booking_notifications_ensure_mysqli($conn)) {
        return false;
    }

    $stmt = $conn->prepare("
        INSERT INTO admin_notifications (type, ref_table, ref_id, created_at)
        VALUES (?, 'bookings', ?, NOW())
        ON DUPLICATE KEY UPDATE read_at = NULL, created_at = VALUES(created_at)
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('si', $type, $bookingId);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function admin_booking_notification_insert_pdo(PDO $pdo, string $type, int $bookingId): bool
{
    if ($bookingId <= 0) {
        return false;
    }

    $stmt = $pdo->prepare("
        INSERT INTO admin_notifications (type, ref_table, ref_id, created_at)
        VALUES (:type, 'bookings', :booking_id, NOW())
        ON DUPLICATE KEY UPDATE read_at = NULL, created_at = VALUES(created_at)
    ");

    return $stmt->execute([
        ':type' => $type,
        ':booking_id' => $bookingId,
    ]);
}
