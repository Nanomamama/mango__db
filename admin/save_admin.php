<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

if (($_SESSION['admin_role'] ?? 'sub') !== 'main') {
    header("Location: index.php");
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    die("CSRF token is invalid");
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$adminId = isset($_POST['admin_id']) ? (int) $_POST['admin_id'] : 0;
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? 'sub';

if (!in_array($role, ['main', 'sub'], true)) {
    $role = 'sub';
}

$redirectBase = $adminId > 0 ? "add_admin.php?edit_id={$adminId}" : "add_admin.php";
$redirectJoiner = strpos($redirectBase, '?') === false ? '?' : '&';

if ($username === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: {$redirectBase}{$redirectJoiner}error=invalid");
    exit;
}

if ($password !== '' && $password !== $confirmPassword) {
    header("Location: {$redirectBase}{$redirectJoiner}error=password_mismatch");
    exit;
}

if ($adminId <= 0 && $password === '') {
    header("Location: add_admin.php?error=password_required");
    exit;
}

$stmt = $conn->prepare("SELECT id FROM system_administrator WHERE (username = ? OR email = ?) AND id <> ?");
$stmt->bind_param("ssi", $username, $email, $adminId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    header("Location: {$redirectBase}{$redirectJoiner}error=duplicate");
    exit;
}

$stmt->close();

if ($adminId > 0) {
    if ($password !== '') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE system_administrator SET username = ?, email = ?, role = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $username, $email, $role, $hashedPassword, $adminId);
    } else {
        $stmt = $conn->prepare("UPDATE system_administrator SET username = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $role, $adminId);
    }

    if ($stmt->execute()) {
        if ($adminId === (int) ($_SESSION['admin_id'] ?? 0)) {
            $_SESSION['admin_name'] = $username;
            $_SESSION['admin_email'] = $email;
            $_SESSION['admin_role'] = $role;
        }

        header("Location: add_admin.php?updated=1");
        exit;
    }
} else {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO system_administrator (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        header("Location: add_admin.php?success=1");
        exit;
    }
}

echo "Error: " . $stmt->error;

$stmt->close();
$conn->close();
