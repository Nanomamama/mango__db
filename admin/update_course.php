<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: edit_courses.php");
    exit;
}

// รับข้อมูลจากฟอร์ม
$courses_id = (int)($_POST['id'] ?? 0);
$course_name = htmlspecialchars($_POST['course_name'], ENT_QUOTES, 'UTF-8');
$course_description = htmlspecialchars($_POST['course_description'], ENT_QUOTES, 'UTF-8');

if ($courses_id <= 0) {
    die("Invalid course ID.");
}

// ดึงข้อมูลรูปภาพปัจจุบัน
$stmt_old = $conn->prepare("SELECT image1, image2, image3 FROM courses WHERE courses_id = ?");
$stmt_old->bind_param("i", $courses_id);
$stmt_old->execute();
$old_images = $stmt_old->get_result()->fetch_assoc();
$stmt_old->close();

if (!$old_images) {
    die("Course not found.");
}

$target_dir = "../uploads/";
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

// ฟังก์ชันจัดการอัปโหลด
function handle_upload($file_key, $current_image, $target_dir, $allowed_types) {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$file_key];
        
        // ตรวจสอบประเภทไฟล์
        if (!in_array($file['type'], $allowed_types)) {
            return ['error' => "Invalid file type for {$file_key}."];
        }

        // สร้างชื่อไฟล์ใหม่ที่ไม่ซ้ำกัน
        $new_filename = uniqid() . "_" . basename($file['name']);
        $target_path = $target_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // ลบไฟล์เก่าถ้ามี
            if ($current_image && file_exists($target_dir . $current_image)) {
                @unlink($target_dir . $current_image);
            }
            return ['filename' => $new_filename];
        } else {
            return ['error' => "Failed to upload {$file_key}."];
        }
    }
    // ไม่มีไฟล์ใหม่, ใช้ไฟล์เดิม
    return ['filename' => $current_image];
}

$image_updates = [];
$image_updates['image1'] = handle_upload('image1', $old_images['image1'], $target_dir, $allowed_types);
$image_updates['image2'] = handle_upload('image2', $old_images['image2'], $target_dir, $allowed_types);
$image_updates['image3'] = handle_upload('image3', $old_images['image3'], $target_dir, $allowed_types);

// ตรวจสอบข้อผิดพลาดจากการอัปโหลด
foreach ($image_updates as $key => $result) {
    if (isset($result['error'])) {
        // สามารถจัดการ error ที่นี่ได้ เช่น redirect กลับพร้อมข้อความ
        die($result['error']);
    }
}

// สร้าง SQL query
$sql = "UPDATE courses SET course_name = ?, course_description = ?, image1 = ?, image2 = ?, image3 = ? WHERE courses_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", 
    $course_name, 
    $course_description, 
    $image_updates['image1']['filename'],
    $image_updates['image2']['filename'],
    $image_updates['image3']['filename'],
    $courses_id
);

if ($stmt->execute()) {
    // Redirect with success message
    header("Location: edit_courses.php?success=update");
} else {
    // Redirect with error message
    header("Location: edit_courses.php?error=" . urlencode($stmt->error));
}

$stmt->close();
$conn->close();
exit();
?>