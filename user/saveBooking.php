<?php
// saveBooking.php — clean handler to insert into `bookings` table
header('Content-Type: application/json; charset=utf-8');
set_time_limit(60);

// Load DB config from ./db/db.php (preferred) or root ./db.php (fallback)
$dbConfig = [
	'host' => '119.59.120.143',
	'name' => 'kratipho_db_mango',
	'user' => 'kratipho_db_mango',
	'pass' => 'kratipho_db_mango',
	'charset' => 'utf8mb4'
];

// Prefer db/db.php
if (file_exists(__DIR__ . '/db/db.php')) {
	include __DIR__ . '/db/db.php';
} elseif (file_exists(__DIR__ . '/db.php')) {
	include __DIR__ . '/db.php';
}

// If variables are present from included file, use them
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
	echo json_encode(['status' => 'error', 'message' => 'DB connection failed', 'debug' => $e->getMessage()]);
	exit;
}

function get($k, $d=null){ return isset($_POST[$k]) ? trim($_POST[$k]) : $d; }
function errorJson($code,$data){ http_response_code($code); echo json_encode($data); exit; }

// Gather inputs (match your table columns)
$member_id = get('member_id', null);
$guest_name = get('name', get('guest_name', null));
$guest_email = get('email', get('guest_email', null));
$guest_phone = get('phone', get('guest_phone', null));
$booking_date = get('selected_date', get('booking_date', null));
$booking_time = get('booking_time', null);
$visitor_count = (int)get('visitor_count', 1);
$lunch_request = (bool)get('lunch_request') ? 1 : 0; // แปลงเป็น boolean อย่างชัดเจนก่อน
$price_total = (float)get('price_total', 0.00);
$deposit_amount = (float)get('deposit_amount', 0.00);
$balance_amount = (float)get('balance_amount', 0.00);
$booking_type = get('booking_type', 'private');

// If client didn't send totals, compute server-side
if (empty($price_total) || $price_total <= 0) {
	$price_per_person = 150.00;
	$price_total = $visitor_count * $price_per_person;
}
if (empty($deposit_amount) || $deposit_amount <= 0) {
	$deposit_amount = round($price_total * 0.3, 2);
}
if (empty($balance_amount) || $balance_amount <= 0) {
	$balance_amount = round($price_total - $deposit_amount, 2);
}
$status = get('status', 'pending');
$is_member_booking = (int)get('is_member_booking', ($member_id ? 1 : 0));

$errors = [];
if (empty($booking_date)) $errors[] = 'booking_date is required';
if (empty($booking_time)) $errors[] = 'booking_time is required';
if (empty($guest_name) && empty($member_id)) $errors[] = 'guest name or member_id is required';
if (!empty($errors)) errorJson(422, ['status'=>'error','errors'=>$errors]);

$booking_code = get('booking_code');
if (empty($booking_code)) $booking_code = 'GV'.date('Ymd').str_pad(mt_rand(1,9999),4,'0',STR_PAD_LEFT);

// Handle optional file upload
$attachment_path = null;
if (isset($_FILES['document']) && $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE) {
	$f = $_FILES['document'];
	if ($f['error'] === UPLOAD_ERR_OK) {
		$max = 10 * 1024 * 1024; //10MB
		$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
		$allow = ['pdf','jpg','jpeg','png'];
		if ($f['size'] > $max) errorJson(413, ['status'=>'error','message'=>'file too large']);
		if (!in_array($ext,$allow)) errorJson(415, ['status'=>'error','message'=>'invalid file type']);
		$dir = __DIR__.'/uploads/'; if (!is_dir($dir)) mkdir($dir,0777,true);
		$safe = time().'_'.preg_replace('/[^A-Za-z0-9_\\-\\.]/','_',basename($f['name']));
		$target = $dir.$safe;
		if (!move_uploaded_file($f['tmp_name'],$target)) {
			errorJson(500,['status'=>'error','message'=>'failed to move uploaded file']);
		}
		$attachment_path = 'uploads/'.$safe;
	} else {
		errorJson(400,['status'=>'error','message'=>'upload error code '.$_FILES['document']['error']]);
	}
}

// Insert into bookings
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
		':member_id'=>($member_id!==''? $member_id : null),
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
	$res = ['status'=>'success','booking_id'=>$id,'booking_code'=>$booking_code, 'booking_type_saved'=>$booking_type];
	if ($attachment_path) $res['attachment_path']=$attachment_path;

	// Fetch the inserted row to confirm saved values (helpful for debugging booking_type)
	try {
		$chk = $pdo->prepare('SELECT * FROM bookings WHERE bookings_id = :id LIMIT 1');
		$chk->execute([':id'=>$id]);
		$inserted = $chk->fetch(PDO::FETCH_ASSOC);
		if ($inserted) {
			// Expose the stored booking_type and related fields for verification
			$res['stored'] = [
				'booking_type' => $inserted['booking_type'] ?? null,
				'price_total' => isset($inserted['price_total']) ? (float)$inserted['price_total'] : null,
				'deposit_amount' => isset($inserted['deposit_amount']) ? (float)$inserted['deposit_amount'] : null,
				'balance_amount' => isset($inserted['balance_amount']) ? (float)$inserted['balance_amount'] : null,
				'attachment_path' => $inserted['attachment_path'] ?? null
			];
		}
	} catch (Exception $e) {
		// ignore — this is only for debug visibility
		$res['stored_error'] = $e->getMessage();
	}

	// Dispatch email sending asynchronously by notifying sendEmail.php with booking_code only.
	// sendEmail.php will load booking from DB and attach local file path — avoids uploading file and blocking the request.
	try {
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
		$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
		$base = rtrim(dirname($_SERVER['PHP_SELF']), '\/');
		$sendUrl = $protocol . '://' . $host . $base . '/sendEmail.php';

		// Fire-and-forget POST using a short socket write (do not wait for response)
		$asyncPost = function($url, $params){
			$parts = parse_url($url);
			$scheme = $parts['scheme'] ?? 'http';
			$host = $parts['host'] ?? 'localhost';
			$port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
			$path = ($parts['path'] ?? '/') . (isset($parts['query']) ? '?'.$parts['query'] : '');

			$body = http_build_query($params);
			$headers = "POST {$path} HTTP/1.1\r\n";
			$headers .= "Host: {$host}\r\n";
			$headers .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$headers .= "Content-Length: " . strlen($body) . "\r\n";
			$headers .= "Connection: Close\r\n\r\n";

			$fp = @fsockopen(($scheme === 'https' ? 'ssl://' : '') . $host, $port, $errno, $errstr, 2);
			if (!$fp) return false;
			fwrite($fp, $headers . $body);
			fclose($fp);
			return true;
		};

		$dispatched = $asyncPost($sendUrl, ['booking_code'=>$booking_code, 'async'=>1]);
		$res['sendEmail_dispatched'] = $dispatched ? 1 : 0;

	} catch (Exception $e) {
		$res['sendEmail_exception'] = $e->getMessage();
	}

	echo json_encode($res);

} catch (PDOException $e) {
	error_log('saveBooking error: '.$e->getMessage());
	errorJson(500,['status'=>'error','message'=>'DB error','debug'=>$e->getMessage()]);
}

?>
