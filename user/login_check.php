<?php
session_start();
require_once '../admin/db.php';

$email = trim($_POST['email']);
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT id, fullname, password FROM members WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
        $_SESSION['member_id'] = $row['id'];
        $_SESSION['fullname'] = $row['fullname'];
        header("Location: index.php");
        exit;
    } else {
        echo "<script>alert('รหัสผ่านไม่ถูกต้อง');history.back();</script>";
    }
} else {
    echo "<script>alert('ไม่พบอีเมล์นี้ในระบบ');history.back();</script>";
}
$stmt->close();
$conn->close();
?>
