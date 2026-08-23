<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';
require_once 'sidebar.php';

date_default_timezone_set('Asia/Bangkok');

$bookingId = isset($_GET['booking_id']) ? max(0, (int)$_GET['booking_id']) : 0;
$booking = null;
$logs = [];
$logsByBooking = [];
$bookingLogGroups = [];
$tableExists = false;

function booking_timeline_table_exists(mysqli $conn): bool
{
    $result = $conn->query("SHOW TABLES LIKE 'booking_status_logs'");
    return $result && $result->num_rows > 0;
}

function booking_timeline_h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function booking_timeline_status_label(?string $status): string
{
    $labels = [
        'pending' => 'รอยืนยัน',
        'awaiting_payment' => 'รอชำระเงิน/รอตรวจสลิป',
        'confirmed' => 'ยืนยันแล้ว',
        'completed' => 'เสร็จสิ้น',
        'no_show' => 'ไม่มา/ยึดมัดจำ',
        'cancelled' => 'ยกเลิกแล้ว',
    ];

    return $labels[$status ?? ''] ?? ($status ?: '-');
}

function booking_timeline_action_meta(string $action): array
{
    $map = [
        'booking_created' => ['สร้างการจอง', 'bi-calendar-plus', 'timeline-info'],
        'edit_booking' => ['แก้ไขการจอง', 'bi-pencil-square', 'timeline-info'],
        'send_qr' => ['ส่ง QR', 'bi-qr-code', 'timeline-warning'],
        'resend_qr' => ['ส่ง QR ใหม่', 'bi-arrow-repeat', 'timeline-warning'],
        'slip_uploaded' => ['ลูกค้าอัปโหลดสลิป', 'bi-receipt', 'timeline-primary'],
        'confirm' => ['ยืนยันการชำระเงิน', 'bi-check2-circle', 'timeline-success'],
        'cancel' => ['ยกเลิกการจอง', 'bi-x-circle', 'timeline-danger'],
        'complete' => ['ปิดงานเสร็จสิ้น', 'bi-flag-fill', 'timeline-success'],
        'no_show' => ['ไม่มา/ยึดมัดจำ', 'bi-person-x', 'timeline-secondary'],
        'auto_cancel_payment_timeout' => ['ยกเลิกอัตโนมัติ', 'bi-clock-history', 'timeline-danger'],
    ];

    return $map[$action] ?? [$action, 'bi-dot', 'timeline-secondary'];
}

function booking_timeline_file_url(string $type, ?string $path): string
{
    if (empty($path)) {
        return '';
    }

    $filename = basename(str_replace('\\', '/', $path));
    if ($filename === '') {
        return '';
    }

    return '../user/download.php?type=' . rawurlencode($type) . '&file=' . rawurlencode($filename);
}

function booking_timeline_thai_datetime(?string $datetime): string
{
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return '-';
    }

    $timestamp = strtotime($datetime);
    if (!$timestamp) {
        return (string)$datetime;
    }

    return date('d/m/', $timestamp) . (date('Y', $timestamp) + 543) . ' ' . date('H:i', $timestamp) . ' น.';
}

if ($bookingId > 0) {
    $stmt = $conn->prepare("
        SELECT bookings_id, booking_code, guest_name, guest_email, guest_phone,
               booking_date, booking_time, visitor_count, price_total,
               deposit_amount, balance_amount, status, created_at, updated_at
        FROM bookings
        WHERE bookings_id = ?
        LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

$tableExists = booking_timeline_table_exists($conn);
if ($tableExists) {
    if ($bookingId > 0) {
        $stmt = $conn->prepare("
            SELECT l.*, b.booking_code, b.guest_name, b.booking_date, b.booking_time,
                   b.payment_qr_path, b.payment_slip,
                   a.username AS admin_username
            FROM booking_status_logs l
            LEFT JOIN bookings b ON b.bookings_id = l.booking_id
            LEFT JOIN system_administrator a ON a.id = l.changed_by_admin_id
            WHERE l.booking_id = ?
            ORDER BY l.created_at ASC, l.id ASC
        ");
        if ($stmt) {
            $stmt->bind_param('i', $bookingId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
            $stmt->close();
        }
    } else {
        $result = $conn->query("
            SELECT l.*, b.booking_code, b.guest_name, b.booking_date, b.booking_time,
                   b.payment_qr_path, b.payment_slip,
                   a.username AS admin_username
            FROM booking_status_logs l
            LEFT JOIN bookings b ON b.bookings_id = l.booking_id
            LEFT JOIN system_administrator a ON a.id = l.changed_by_admin_id
            ORDER BY l.created_at DESC, l.id DESC
            LIMIT 300
        ");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
        }
    }
}

if ($bookingId === 0 && !empty($logs)) {
    foreach ($logs as $log) {
        $groupBookingId = (int)($log['booking_id'] ?? 0);
        if ($groupBookingId <= 0) {
            continue;
        }

        if (!isset($logsByBooking[$groupBookingId])) {
            $logsByBooking[$groupBookingId] = [];
            $bookingLogGroups[$groupBookingId] = [
                'booking_id' => $groupBookingId,
                'booking_code' => $log['booking_code'] ?? ('ID ' . $groupBookingId),
                'guest_name' => $log['guest_name'] ?? '-',
                'booking_date' => $log['booking_date'] ?? null,
                'booking_time' => $log['booking_time'] ?? null,
                'latest_log_at' => $log['created_at'] ?? null,
            ];
        }

        $logsByBooking[$groupBookingId][] = $log;
    }

    foreach ($logsByBooking as $groupBookingId => $groupLogs) {
        usort($groupLogs, static function (array $a, array $b): int {
            $timeCompare = strtotime((string)($a['created_at'] ?? '')) <=> strtotime((string)($b['created_at'] ?? ''));
            if ($timeCompare !== 0) {
                return $timeCompare;
            }

            return (int)($a['id'] ?? 0) <=> (int)($b['id'] ?? 0);
        });
        $logsByBooking[$groupBookingId] = $groupLogs;
    }
}

$adminPageExtraHead = <<<HTML
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
HTML;
adminPageStart('ไทม์ไลน์การจัดการจอง');
?>
<style>
    .booking-timeline-page {
        background: linear-gradient(180deg, #ffffff 0%, #f7fbfb 100%);
        min-height: 100vh;
    }

    .timeline-shell {
        color: #0f172a;
    }

    .timeline-toolbar,
    .timeline-booking-summary,
    .timeline-list-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }

    .timeline-toolbar {
        padding: 16px;
    }

    .timeline-booking-summary {
        padding: 18px;
    }

    .timeline-list-panel {
        padding: 22px;
    }

    .timeline-booking-group {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 18px;
    }

    .timeline-booking-group:last-child {
        margin-bottom: 0;
    }

    .timeline-booking-group-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        padding: 16px 18px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .timeline-booking-group-body {
        padding: 18px;
    }

    .timeline-heading {
        font-weight: 700;
        color: #016A70;
    }

    .timeline-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .timeline-summary-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px;
    }

    .timeline-summary-label {
        display: block;
        color: #64748b;
        font-size: 0.82rem;
        margin-bottom: 4px;
    }

    .timeline-summary-value {
        font-weight: 700;
        color: #0f172a;
        overflow-wrap: anywhere;
    }

    .booking-timeline {
        position: relative;
        padding-left: 48px;
    }

    .booking-timeline::before {
        content: '';
        position: absolute;
        left: 18px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: #dbe7e8;
    }

    .booking-timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .booking-timeline-item:last-child {
        padding-bottom: 0;
    }

    .booking-timeline-icon {
        position: absolute;
        left: -47px;
        top: 0;
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 3px solid #ffffff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
    }

    .timeline-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px;
    }

    .timeline-card-title {
        font-weight: 700;
        color: #0f172a;
    }

    .timeline-meta {
        color: #64748b;
        font-size: 0.9rem;
    }

    .timeline-note {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        padding: 10px 12px;
        white-space: pre-wrap;
    }

    .timeline-media-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 220px));
        gap: 12px;
        margin-top: 12px;
    }

    .timeline-media-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        background: #f8fafc;
    }

    .timeline-media-card img {
        display: block;
        width: 100%;
        height: 180px;
        object-fit: contain;
        background: #ffffff;
    }

    .timeline-media-caption {
        padding: 8px 10px;
        color: #475569;
        font-size: 0.86rem;
        border-top: 1px solid #e2e8f0;
    }

    .timeline-primary { background: #e0f2fe; color: #0369a1; }
    .timeline-info { background: #dcfce7; color: #166534; }
    .timeline-warning { background: #fef3c7; color: #92400e; }
    .timeline-success { background: #dcfce7; color: #15803d; }
    .timeline-danger { background: #fee2e2; color: #b91c1c; }
    .timeline-secondary { background: #e2e8f0; color: #475569; }

    @media (max-width: 991px) {
        .timeline-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575px) {
        .timeline-summary-grid {
            grid-template-columns: 1fr;
        }

        .booking-timeline {
            padding-left: 42px;
        }

        .timeline-media-grid {
            grid-template-columns: 1fr;
        }

        .timeline-booking-group-header {
            flex-direction: column;
        }
    }
</style>
<script>
    document.querySelector('.page-content')?.classList.add('booking-timeline-page');
</script>

<div class="timeline-shell">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
        <div>
            <h3 class="timeline-heading mb-1">ไทม์ไลน์การจัดการจอง</h3>
            <div class="text-muted">ย้อนดูว่าแต่ละรายการถูกทำอะไร เปลี่ยนสถานะเมื่อไหร่ และใครเป็นคนดำเนินการ</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="booking_list.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> กลับรายการจอง
            </a>
            <?php if ($bookingId > 0): ?>
                <a href="booking_timeline.php" class="btn btn-outline-primary">
                    <i class="bi bi-clock-history me-1"></i> ดูทั้งหมด
                </a>
            <?php endif; ?>
        </div>
    </div>

    <form class="timeline-toolbar mb-3" method="get" action="booking_timeline.php">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-8 col-lg-5">
                <label for="booking_id" class="form-label mb-1">ค้นหาด้วย Booking ID</label>
                <input type="number" min="1" class="form-control" id="booking_id" name="booking_id" value="<?= $bookingId > 0 ? (int)$bookingId : '' ?>" placeholder="เช่น 301">
            </div>
            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> ดูไทม์ไลน์
                </button>
            </div>
        </div>
    </form>

    <?php if (!$tableExists): ?>
        <div class="alert alert-warning">
            ยังไม่พบตาราง <strong>booking_status_logs</strong> กรุณารัน migration ใน <code>Database/booking_management_updates.sql</code> ก่อน
        </div>
    <?php endif; ?>

    <?php if ($bookingId > 0 && !$booking): ?>
        <div class="alert alert-danger">
            ไม่พบรายการจอง Booking ID: <?= (int)$bookingId ?>
        </div>
    <?php endif; ?>

    <?php if ($booking): ?>
        <div class="timeline-booking-summary mb-3">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <div class="text-muted small">รายการจอง</div>
                    <h4 class="mb-0">#<?= booking_timeline_h($booking['booking_code'] ?? '') ?></h4>
                </div>
                <span class="badge rounded-pill text-bg-primary px-3 py-2"><?= booking_timeline_h(booking_timeline_status_label($booking['status'] ?? '')) ?></span>
            </div>
            <div class="timeline-summary-grid">
                <div class="timeline-summary-item">
                    <span class="timeline-summary-label">ลูกค้า</span>
                    <span class="timeline-summary-value"><?= booking_timeline_h($booking['guest_name'] ?? '-') ?></span>
                </div>
                <div class="timeline-summary-item">
                    <span class="timeline-summary-label">วัน/เวลาเข้าชม</span>
                    <span class="timeline-summary-value">
                        <?= booking_timeline_h(($booking['booking_date'] ?? '-') . ' ' . substr((string)($booking['booking_time'] ?? ''), 0, 5)) ?>
                    </span>
                </div>
                <div class="timeline-summary-item">
                    <span class="timeline-summary-label">ยอดรวม</span>
                    <span class="timeline-summary-value">฿<?= number_format((float)($booking['price_total'] ?? 0), 2) ?></span>
                </div>
                <div class="timeline-summary-item">
                    <span class="timeline-summary-label">มัดจำ / คงเหลือ</span>
                    <span class="timeline-summary-value">฿<?= number_format((float)($booking['deposit_amount'] ?? 0), 2) ?> / ฿<?= number_format((float)($booking['balance_amount'] ?? 0), 2) ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="timeline-list-panel">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h5 class="mb-0">
                <i class="bi bi-clock-history me-2"></i>
                <?= $bookingId > 0 ? 'ประวัติของรายการนี้' : 'ประวัติแยกตามรายการจอง' ?>
            </h5>
            <span class="text-muted small">
                <?= $bookingId > 0 ? count($logs) . ' รายการ' : count($bookingLogGroups) . ' รายการจอง / ' . count($logs) . ' ประวัติ' ?>
            </span>
        </div>

        <?php if (empty($logs)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-1"></i>
                <p class="mt-3 mb-0">ยังไม่มีข้อมูลไทม์ไลน์สำหรับเงื่อนไขนี้</p>
            </div>
        <?php elseif ($bookingId > 0): ?>
            <div class="booking-timeline">
                <?php foreach ($logs as $log): ?>
                    <?php [$actionLabel, $actionIcon, $actionClass] = booking_timeline_action_meta((string)($log['action'] ?? '')); ?>
                    <?php
                    $qrUrl = in_array((string)($log['action'] ?? ''), ['send_qr', 'resend_qr'], true)
                        ? booking_timeline_file_url('qr', $log['payment_qr_path'] ?? null)
                        : '';
                    $slipUrl = in_array((string)($log['action'] ?? ''), ['slip_uploaded', 'confirm'], true)
                        ? booking_timeline_file_url('slip', $log['payment_slip'] ?? null)
                        : '';
                    ?>
                    <div class="booking-timeline-item">
                        <div class="booking-timeline-icon <?= booking_timeline_h($actionClass) ?>">
                            <i class="bi <?= booking_timeline_h($actionIcon) ?>"></i>
                        </div>
                        <div class="timeline-card">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
                                <div>
                                    <div class="timeline-card-title"><?= booking_timeline_h($actionLabel) ?></div>
                                    <?php if ($bookingId === 0): ?>
                                        <div class="timeline-meta">
                                            <a href="booking_timeline.php?booking_id=<?= (int)($log['booking_id'] ?? 0) ?>" class="text-decoration-none fw-semibold">
                                                #<?= booking_timeline_h($log['booking_code'] ?? ('ID ' . ($log['booking_id'] ?? '-'))) ?>
                                            </a>
                                            <span> · <?= booking_timeline_h($log['guest_name'] ?? '-') ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-meta text-md-end">
                                    <?= booking_timeline_h(booking_timeline_thai_datetime($log['created_at'] ?? null)) ?>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-12 col-md-4">
                                    <span class="timeline-meta d-block">สถานะเดิม</span>
                                    <strong><?= booking_timeline_h(booking_timeline_status_label($log['old_status'] ?? null)) ?></strong>
                                </div>
                                <div class="col-12 col-md-4">
                                    <span class="timeline-meta d-block">สถานะใหม่</span>
                                    <strong><?= booking_timeline_h(booking_timeline_status_label($log['new_status'] ?? null)) ?></strong>
                                </div>
                                <div class="col-12 col-md-4">
                                    <span class="timeline-meta d-block">ผู้ดำเนินการ</span>
                                    <strong>
                                        <?php if (!empty($log['admin_username'])): ?>
                                            <?= booking_timeline_h($log['admin_username']) ?>
                                        <?php elseif (!empty($log['changed_by_admin_id'])): ?>
                                            Admin ID <?= (int)$log['changed_by_admin_id'] ?>
                                        <?php else: ?>
                                            ระบบ/ลูกค้า
                                        <?php endif; ?>
                                    </strong>
                                </div>
                            </div>

                            <?php if (!empty($log['note'])): ?>
                                <div class="timeline-note"><?= booking_timeline_h($log['note']) ?></div>
                            <?php endif; ?>

                            <?php if ($qrUrl !== '' || $slipUrl !== ''): ?>
                                <div class="timeline-media-grid">
                                    <?php if ($qrUrl !== ''): ?>
                                        <a class="timeline-media-card text-decoration-none" href="<?= booking_timeline_h($qrUrl) ?>" target="_blank" rel="noopener">
                                            <img src="<?= booking_timeline_h($qrUrl) ?>" alt="QR Code">
                                            <div class="timeline-media-caption">
                                                <i class="bi bi-qr-code me-1"></i> QR Code
                                            </div>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($slipUrl !== ''): ?>
                                        <a class="timeline-media-card text-decoration-none" href="<?= booking_timeline_h($slipUrl) ?>" target="_blank" rel="noopener">
                                            <img src="<?= booking_timeline_h($slipUrl) ?>" alt="Payment Slip">
                                            <div class="timeline-media-caption">
                                                <i class="bi bi-receipt me-1"></i> สลิปการชำระเงิน
                                            </div>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <?php foreach ($bookingLogGroups as $groupBookingId => $group): ?>
                <?php $groupLogs = $logsByBooking[$groupBookingId] ?? []; ?>
                <div class="timeline-booking-group">
                    <div class="timeline-booking-group-header">
                        <div>
                            <div class="timeline-meta mb-1">รายการจอง</div>
                            <h5 class="mb-1">
                                <a href="booking_timeline.php?booking_id=<?= (int)$groupBookingId ?>" class="text-decoration-none">
                                    #<?= booking_timeline_h($group['booking_code'] ?? ('ID ' . $groupBookingId)) ?>
                                </a>
                            </h5>
                            <div class="timeline-meta">
                                <?= booking_timeline_h($group['guest_name'] ?? '-') ?>
                                <span class="mx-1">·</span>
                                <?= booking_timeline_h(($group['booking_date'] ?? '-') . ' ' . substr((string)($group['booking_time'] ?? ''), 0, 5)) ?>
                            </div>
                        </div>
                        <div class="text-lg-end">
                            <span class="badge rounded-pill text-bg-light border mb-2"><?= count($groupLogs) ?> รายการ</span>
                            <div class="timeline-meta">ล่าสุด: <?= booking_timeline_h(booking_timeline_thai_datetime($group['latest_log_at'] ?? null)) ?></div>
                        </div>
                    </div>
                    <div class="timeline-booking-group-body">
                        <div class="booking-timeline">
                            <?php foreach ($groupLogs as $log): ?>
                                <?php [$actionLabel, $actionIcon, $actionClass] = booking_timeline_action_meta((string)($log['action'] ?? '')); ?>
                                <?php
                                $qrUrl = in_array((string)($log['action'] ?? ''), ['send_qr', 'resend_qr'], true)
                                    ? booking_timeline_file_url('qr', $log['payment_qr_path'] ?? null)
                                    : '';
                                $slipUrl = in_array((string)($log['action'] ?? ''), ['slip_uploaded', 'confirm'], true)
                                    ? booking_timeline_file_url('slip', $log['payment_slip'] ?? null)
                                    : '';
                                ?>
                                <div class="booking-timeline-item">
                                    <div class="booking-timeline-icon <?= booking_timeline_h($actionClass) ?>">
                                        <i class="bi <?= booking_timeline_h($actionIcon) ?>"></i>
                                    </div>
                                    <div class="timeline-card">
                                        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
                                            <div class="timeline-card-title"><?= booking_timeline_h($actionLabel) ?></div>
                                            <div class="timeline-meta text-md-end">
                                                <?= booking_timeline_h(booking_timeline_thai_datetime($log['created_at'] ?? null)) ?>
                                            </div>
                                        </div>

                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-md-4">
                                                <span class="timeline-meta d-block">สถานะเดิม</span>
                                                <strong><?= booking_timeline_h(booking_timeline_status_label($log['old_status'] ?? null)) ?></strong>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <span class="timeline-meta d-block">สถานะใหม่</span>
                                                <strong><?= booking_timeline_h(booking_timeline_status_label($log['new_status'] ?? null)) ?></strong>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <span class="timeline-meta d-block">ผู้ดำเนินการ</span>
                                                <strong>
                                                    <?php if (!empty($log['admin_username'])): ?>
                                                        <?= booking_timeline_h($log['admin_username']) ?>
                                                    <?php elseif (!empty($log['changed_by_admin_id'])): ?>
                                                        Admin ID <?= (int)$log['changed_by_admin_id'] ?>
                                                    <?php else: ?>
                                                        ระบบ/ลูกค้า
                                                    <?php endif; ?>
                                                </strong>
                                            </div>
                                        </div>

                                        <?php if (!empty($log['note'])): ?>
                                            <div class="timeline-note"><?= booking_timeline_h($log['note']) ?></div>
                                        <?php endif; ?>

                                        <?php if ($qrUrl !== '' || $slipUrl !== ''): ?>
                                            <div class="timeline-media-grid">
                                                <?php if ($qrUrl !== ''): ?>
                                                    <a class="timeline-media-card text-decoration-none" href="<?= booking_timeline_h($qrUrl) ?>" target="_blank" rel="noopener">
                                                        <img src="<?= booking_timeline_h($qrUrl) ?>" alt="QR Code">
                                                        <div class="timeline-media-caption">
                                                            <i class="bi bi-qr-code me-1"></i> QR Code
                                                        </div>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($slipUrl !== ''): ?>
                                                    <a class="timeline-media-card text-decoration-none" href="<?= booking_timeline_h($slipUrl) ?>" target="_blank" rel="noopener">
                                                        <img src="<?= booking_timeline_h($slipUrl) ?>" alt="Payment Slip">
                                                        <div class="timeline-media-caption">
                                                            <i class="bi bi-receipt me-1"></i> สลิปการชำระเงิน
                                                        </div>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php adminPageEnd(); ?>
