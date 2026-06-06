<?php
session_start();
require_once __DIR__ . '/../db/db.php';

function alertBack(string $message): void
{
    $safeMessage = json_encode($message, JSON_UNESCAPED_UNICODE);
    echo "<script>alert($safeMessage);history.back();</script>";
    exit;
}

function columnExists(mysqli $conn, string $table, string $column): bool
{
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS column_count
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("ss", $table, $column);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return (int)($row['column_count'] ?? 0) > 0;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit;
}

if (($_POST['pdpa_consent'] ?? '') !== '1') {
    alertBack('กรุณายินยอมการเก็บรวบรวมข้อมูลส่วนบุคคลก่อนสมัครสมาชิก ระบบจะไม่บันทึกข้อมูลหากไม่ได้รับความยินยอม');
}

$fullname = trim($_POST['fullname'] ?? '');
$address = trim($_POST['address'] ?? '');
$province_id = (int)($_POST['province'] ?? 0);
$district_id = (int)($_POST['district'] ?? 0);
$subdistrict_id = (int)($_POST['subdistrict'] ?? 0);
$zipcode = trim($_POST['zipcode'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (
    $fullname === '' ||
    $address === '' ||
    $province_id <= 0 ||
    $district_id <= 0 ||
    $subdistrict_id <= 0 ||
    !preg_match('/^[0-9]{5}$/', $zipcode) ||
    !preg_match('/^[0-9]{10}$/', $phone) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($password) < 6
) {
    alertBack('กรุณากรอกข้อมูลสมัครสมาชิกให้ครบถ้วนและถูกต้อง');
}

if ($password !== $confirm_password) {
    alertBack('รหัสผ่านไม่ตรงกัน');
}

$stmt = $conn->prepare("SELECT member_id FROM members WHERE email = ?");
if (!$stmt) {
    alertBack('เกิดข้อผิดพลาด กรุณาลองใหม่');
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    alertBack('อีเมลนี้ถูกใช้แล้ว');
}

$stmt->close();

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$hasPdpaColumns = columnExists($conn, 'members', 'pdpa_consent')
    && columnExists($conn, 'members', 'pdpa_consent_at')
    && columnExists($conn, 'members', 'pdpa_consent_version');

if ($hasPdpaColumns) {
    $pdpaConsent = 1;
    $pdpaConsentVersion = 'PDPA-2562-register-v1';

    $stmt = $conn->prepare("
        INSERT INTO members (
            fullname,
            address,
            province_id,
            district_id,
            subdistrict_id,
            zipcode,
            phone,
            email,
            password,
            pdpa_consent,
            pdpa_consent_at,
            pdpa_consent_version
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
    ");

    if (!$stmt) {
        alertBack('เกิดข้อผิดพลาด กรุณาลองใหม่');
    }

    $stmt->bind_param(
        "ssiiissssis",
        $fullname,
        $address,
        $province_id,
        $district_id,
        $subdistrict_id,
        $zipcode,
        $phone,
        $email,
        $hashed_password,
        $pdpaConsent,
        $pdpaConsentVersion
    );
} else {
    $stmt = $conn->prepare("
        INSERT INTO members (
            fullname,
            address,
            province_id,
            district_id,
            subdistrict_id,
            zipcode,
            phone,
            email,
            password
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        alertBack('เกิดข้อผิดพลาด กรุณาลองใหม่');
    }

    $stmt->bind_param(
        "ssiiissss",
        $fullname,
        $address,
        $province_id,
        $district_id,
        $subdistrict_id,
        $zipcode,
        $phone,
        $email,
        $hashed_password
    );
}

if ($stmt->execute()) {
    echo "<script>alert('สมัครสมาชิกสำเร็จ!');window.location='member_login.php';</script>";
} else {
    alertBack('เกิดข้อผิดพลาด กรุณาลองใหม่');
}

$stmt->close();
$conn->close();
?>
