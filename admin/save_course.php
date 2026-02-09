<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

// รับข้อมูลจากฟอร์ม
$course_name = htmlspecialchars($_POST['course_name'], ENT_QUOTES, 'UTF-8');
$course_description = htmlspecialchars($_POST['course_description'], ENT_QUOTES, 'UTF-8');

// ตรวจสอบความยาวข้อมูล
if (strlen($course_name) > 255 || strlen($course_description) > 1000) {
    // Redirect with an error message for better UX
    $_SESSION['error'] = "ข้อมูลชื่อหลักสูตรหรือคำอธิบายยาวเกินไป";
    header("Location: edit_courses.php");
    exit;
}

// กำหนดโฟลเดอร์สำหรับเก็บรูปภาพ
$target_dir = "../uploads";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
/**
 * Handles file upload, validation, and renaming.
 *
 * @param string $file_key The key in the $_FILES array.
 * @param string $target_dir The destination directory.
 * @param array $allowed_types Allowed MIME types.
 * @return string|null The new filename on success, or null on failure/no file.
 */
function handle_upload($file_key, $target_dir, $allowed_types) {
    // ตรวจสอบว่ามีไฟล์ถูกส่งมาและไม่มีข้อผิดพลาด
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$file_key];
        
        // ตรวจสอบประเภทไฟล์
        if (!in_array($file['type'], $allowed_types)) {
            $_SESSION['error'] = "ประเภทไฟล์ไม่ถูกต้องสำหรับ {$file_key} (รองรับเฉพาะ JPG, PNG, GIF)";
            header("Location: edit_courses.php");
            exit;
        }

        // สร้างชื่อไฟล์ใหม่ที่ไม่ซ้ำกันเพื่อป้องกันการเขียนทับ
        $new_filename = uniqid() . "_" . basename($file['name']);
        $target_path = $target_dir . $new_filename;

        // ย้ายไฟล์ไปยังโฟลเดอร์เป้าหมาย
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            return $new_filename; // คืนค่าชื่อไฟล์ใหม่
        } else {
            $_SESSION['error'] = "ไม่สามารถอัปโหลดไฟล์ {$file_key} ได้";
            header("Location: edit_courses.php");
            exit;
        }
    }
    // ถ้าไม่มีไฟล์ส่งมา หรือมีข้อผิดพลาด ให้คืนค่า null
    return null;
}

// เรียกใช้ฟังก์ชันสำหรับแต่ละไฟล์
$image1 = handle_upload('image1', $target_dir, $allowed_types);
$image2 = handle_upload('image2', $target_dir, $allowed_types);
$image3 = handle_upload('image3', $target_dir, $allowed_types);

// บันทึกข้อมูลลงฐานข้อมูล
$stmt = $conn->prepare("INSERT INTO courses (course_name, course_description, image1, image2, image3) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $course_name, $course_description, $image1, $image2, $image3);

if ($stmt->execute()) {
    // Redirect with a success message
    header("Location: edit_courses.php?success=add");
    exit();
} else {
    // Redirect with a specific error
    header("Location: edit_courses.php?error=" . urlencode($stmt->error));
    exit();
}

$stmt->close();
$conn->close();
?>