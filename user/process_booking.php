<?php
session_start();
require_once '../admin/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['group_name'];
    $date = $_POST['booking_date'];
    $time = $_POST['visit_time'];
    $people = $_POST['number_of_people'];
    $phone = $_POST['phone_number'];
    $doc = null;
    $slip = null;

    // อัปโหลดเอกสาร
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../uploads/";
        $fileName = uniqid() . "_" . basename($_FILES["document"]["name"]);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["document"]["tmp_name"], $targetFile)) {
            $doc = $fileName;
        }
    }

    // อัปโหลดสลิป
    if (isset($_FILES['slip']) && $_FILES['slip']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../uploads/";
        $fileName = uniqid() . "_" . basename($_FILES["slip"]["name"]);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["slip"]["tmp_name"], $targetFile)) {
            $slip = $fileName;
        }
    }

    // เพิ่มข้อมูลลง bookings (status เริ่มต้น "รออนุมัติ")
    $stmt = $conn->prepare("INSERT INTO bookings (name, date, time, people, doc, slip, status) VALUES (?, ?, ?, ?, ?, ?, 'รออนุมัติ')");
    $stmt->bind_param("sssiss", $name, $date, $time, $people, $doc, $slip);
    if ($stmt->execute()) {
        header("Location: activities.php?success=1");
        exit;
    } else {
        header("Location: activities.php?error=1");
        exit;
    }
}
?>
