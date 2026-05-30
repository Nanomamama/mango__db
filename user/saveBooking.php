<?php
// saveBooking.php — handler to insert into `bookings` table (server-side authoritative)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
set_time_limit(60);

// include booking helpers
require_once __DIR__ . '/booking_utils.php';

// Load DB config
$dbConfig = [
    'host' => '119.59.120.143',
    'name' => 'kratipho_db_mango',
    'user' => 'kratipho_db_mango',
    'pass' => 'kratipho_db_mango',
    'charset' => 'utf8mb4'
];

if (file_exists(__DIR__ . '/../db/db.php')) {
    include __DIR__ . '/../db/db.php';
} elseif (file_exists(__DIR__ . '/../db.php')) {
    include __DIR__ . '/../db.php';
}

if (isset($servername, $username, $password, $dbname)) {
    $dbConfig['host'] = $servername;
    $dbConfig['user'] = $username;
    $dbConfig['pass'] = $password;
    $dbConfig['name'] = $dbname;
}

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit;
}

function get($k, $d=null){ return isset($_POST[$k]) ? trim($_POST[$k]) : $d; }
function errorJson($code,$data){ http_response_code($code); echo json_encode($data); exit; }
function requirePostCsrf(): void {
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $postedToken = $_POST['csrf_token'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_string($postedToken) || !hash_equals($sessionToken, $postedToken)) {
        errorJson(403, ['status'=>'error','message'=>'invalid csrf token']);
    }
}
function getActiveMember(PDO $pdo, int $member_id): array {
    $stmt = $pdo->prepare('SELECT fullname, email, phone, status FROM members WHERE member_id = :member_id LIMIT 1');
    $stmt->execute([':member_id' => $member_id]);
    $member = $stmt->fetch();
    if (!$member || (int)($member['status'] ?? 0) !== 1) {
        session_unset();
        session_destroy();
        errorJson(403, ['status'=>'error','message'=>'member account is disabled']);
    }
    return $member;
}

if (empty($_SESSION['member_id'])) {
    errorJson(401, ['status'=>'error','message'=>'login required']);
}
requirePostCsrf();

// Gather inputs
$member_id = (int)$_SESSION['member_id'];
$member = getActiveMember($pdo, $member_id);
$guest_name = trim((string)($member['fullname'] ?? ''));
$guest_email = trim((string)($member['email'] ?? ''));
$guest_phone = trim((string)($member['phone'] ?? ''));
$booking_date = get('selected_date', get('booking_date', null));
$booking_time = get('booking_time', null);
$visitor_count = max(1, (int)get('visitor_count', 1));
$lunch_request = (bool)get('lunch_request') ? 1 : 0;
$booking_type = get('booking_type', 'private');

if (!in_array($booking_type, ['private', 'organization'], true)) {
    $booking_type = 'private';
}

// Server-side totals
$totals = calculate_booking_totals($visitor_count);
$price_total = $totals['price_total'];
$deposit_amount = $totals['deposit_amount'];
$balance_amount = $totals['balance_amount'];
$status = 'pending';
$is_member_booking = 1;

$errors = [];
$allowed_times = ['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
if (empty($booking_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $booking_date)) {
    $errors[] = 'booking_date is invalid';
} else {
    [$year, $month, $day] = array_map('intval', explode('-', $booking_date));
    if (!checkdate($month, $day, $year) || strtotime($booking_date) < strtotime(date('Y-m-d'))) {
        $errors[] = 'booking_date is invalid';
    }
}
if (empty($booking_time) || !in_array($booking_time, $allowed_times, true)) $errors[] = 'booking_time is invalid';
if (empty($guest_name)) $errors[] = 'member name is required';
if ($guest_email !== '' && !filter_var($guest_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'member email is invalid';
if ($guest_phone !== '' && !preg_match('/^[0-9]{10}$/', $guest_phone)) $errors[] = 'member phone is invalid';
if ($visitor_count < 1 || $visitor_count > 500) $errors[] = 'visitor_count is invalid';
if (!empty($errors)) errorJson(422, ['status'=>'error','errors'=>$errors]);

// Duplicate check (transactional to reduce race conditions)
try {
    $pdo->beginTransaction();
    $dupSql = "SELECT bookings_id FROM bookings WHERE booking_date = :booking_date AND status IN ('pending','awaiting_payment','confirmed') LIMIT 1 FOR UPDATE";
    $dupStmt = $pdo->prepare($dupSql);
    $dupStmt->execute([':booking_date' => $booking_date]);
    if ($dupStmt->fetch()) {
        $pdo->rollBack();
        errorJson(409, ['status'=>'error','message'=>'selected date is already booked']);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    errorJson(500, ['status'=>'error','message'=>'DB error during duplicate check']);
}

// Booking code
$booking_code = get('booking_code');
if (empty($booking_code)) $booking_code = 'GV'.date('Ymd').str_pad(mt_rand(1,9999),4,'0',STR_PAD_LEFT);

// Handle optional document upload
$attachment_path = null;
if (isset($_FILES['document']) && $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE) {
    $f = $_FILES['document'];
    list($ok, $msg) = valid_upload_file($f);
    if (!$ok) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        errorJson(415, ['status'=>'error','message'=>'document upload invalid: '.$msg]);
    }
    $dir = __DIR__.'/uploads/'; if (!is_dir($dir)) mkdir($dir,0755,true);
    try {
        $safe = time().'_'.bin2hex(random_bytes(6)).'.'.strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    } catch (Exception $e) {
        $safe = time().'_'.mt_rand(1000,9999).'.'.strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    }
    $target = $dir.$safe;
    if (!move_uploaded_file($f['tmp_name'],$target)) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        errorJson(500,['status'=>'error','message'=>'failed to move uploaded file']);
    }
    $attachment_path = 'uploads/'.$safe;
}

// Insert booking
try {
    $sql = "INSERT INTO bookings (
        booking_code, member_id, guest_name, guest_email, guest_phone,
        booking_date, booking_time, visitor_count, lunch_request,
        price_total, deposit_amount, balance_amount, booking_type, status, is_member_booking, attachment_path, created_at, updated_at
    ) VALUES (
        :booking_code, :member_id, :guest_name, :guest_email, :guest_phone,
        :booking_date, :booking_time, :visitor_count, :lunch_request,
        :price_total, :deposit_amount, :balance_amount, :booking_type, :status, :is_member_booking, :attachment_path, NOW(), NOW()
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':booking_code'=>$booking_code,
        ':member_id'=>$member_id,
        ':guest_name'=>$guest_name,
        ':guest_email'=>$guest_email,
        ':guest_phone'=>$guest_phone,
        ':booking_date'=>$booking_date,
        ':booking_time'=>$booking_time,
        ':visitor_count'=>$visitor_count,
        ':lunch_request'=>$lunch_request,
        ':price_total'=>$price_total,
        ':deposit_amount'=>$deposit_amount,
        ':balance_amount'=>$balance_amount,
        ':booking_type'=>$booking_type,
        ':status'=>$status,
        ':is_member_booking'=>$is_member_booking,
        ':attachment_path'=>$attachment_path,
    ]);

    $id = $pdo->lastInsertId();
    if ($pdo->inTransaction()) $pdo->commit();

    $res = ['status'=>'success','booking_id'=>$id,'booking_code'=>$booking_code, 'booking_type_saved'=>$booking_type];
    if ($attachment_path) $res['attachment_path']=$attachment_path;

    // Fetch inserted row for verification
    try {
        $chk = $pdo->prepare('SELECT * FROM bookings WHERE bookings_id = :id LIMIT 1');
        $chk->execute([':id'=>$id]);
        $inserted = $chk->fetch(PDO::FETCH_ASSOC);
        if ($inserted) {
            $res['stored'] = [
                'booking_type' => $inserted['booking_type'] ?? null,
                'price_total' => isset($inserted['price_total']) ? (float)$inserted['price_total'] : null,
                'deposit_amount' => isset($inserted['deposit_amount']) ? (float)$inserted['deposit_amount'] : null,
                'balance_amount' => isset($inserted['balance_amount']) ? (float)$inserted['balance_amount'] : null,
                'attachment_path' => $inserted['attachment_path'] ?? null
            ];
        }
    } catch (Exception $e) {
        // ignore
    }

    // Send booking emails (best-effort)
    try {
        require_once __DIR__ . '/sendEmail.php';
        $emailResult = sendBookingEmails(['booking_code' => $booking_code, 'async' => 1]);
        $res['sendEmail_status'] = $emailResult['status'] ?? 'unknown';
    } catch (Exception $e) {
        $res['sendEmail_exception'] = $e->getMessage();
    }

    echo json_encode($res);
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('saveBooking error: '.$e->getMessage());
    errorJson(500,['status'=>'error','message'=>'DB error']);
}

?>
