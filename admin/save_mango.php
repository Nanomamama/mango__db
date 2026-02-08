<!-- ฟังก์ชันสำหรับ การเพิ่มข้อมูลลงในฐานข้อมูล -->
<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

// $servername = "localhost"; 
// $username = "root";         
// $password = "";            
// $dbname = "db_mango";      

// $conn = new mysqli($servername, $username, $password, $dbname);

// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

// ฟังก์ชันสำหรับการอัปโหลดไฟล์
function uploadFile($file, $targetDir = "uploads/")
{
    $targetFile = $targetDir . basename($file["name"]);
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // ตรวจสอบว่าไฟล์เป็นภาพหรือไม่
    if (!getimagesize($file["tmp_name"])) {
        return "ไฟล์ไม่ใช่รูปภาพ";
    }

    // ตรวจสอบประเภทไฟล์
    $allowedTypes = ['jpg', 'png', 'jpeg', 'gif'];
    if (!in_array($fileType, $allowedTypes)) {
        return "ไม่รองรับไฟล์ประเภทนี้";
    }

    // ย้ายไฟล์ไปยังโฟลเดอร์
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return $targetFile;
    } else {
        return "เกิดข้อผิดพลาดในการอัปโหลด";
    }
}

// รับค่าจากฟอร์ม
$mango_name = $_POST['mango_name'];
$scientific_name = $_POST['scientific_name'];
$local_name = $_POST['local_name'];
$morphology_stem = $_POST['morphology_stem'];
$morphology_fruit = $_POST['morphology_fruit'];
$morphology_leaf = $_POST['morphology_leaf'];
$propagation_method = $_POST['propagation_method'];
$soil_characteristics = $_POST['soil_characteristics'];
$planting_period = $_POST['planting_period'];
$harvest_season = $_POST['harvest_season'];
$mango_category = $_POST['mango_category'];

// การแปรรูป
$processing_methods = isset($_POST['processing_methods']) ? implode(",", $_POST['processing_methods']) : '';

// อัปโหลดรูปภาพ
$fruit_image = uploadFile($_FILES['fruit_image']);
$tree_image = uploadFile($_FILES['tree_image']);
$leaf_image = uploadFile($_FILES['leaf_image']);
$flower_image = uploadFile($_FILES['flower_image']);
$branch_image = uploadFile($_FILES['branch_image']);

// ตรวจสอบการอัปโหลด
if (strpos($fruit_image, "error") !== false || strpos($tree_image, "error") !== false || strpos($leaf_image, "error") !== false || strpos($flower_image, "error") !== false || strpos($branch_image, "error") !== false) {
    $_SESSION['error_message'] = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
    header("Location: add_mango.php");
    exit;
}

// เตรียมคำสั่ง SQL สำหรับการบันทึกข้อมูล
$sql = "INSERT INTO mango_varieties (mango_name, scientific_name, local_name, morphology_stem, morphology_fruit, morphology_leaf, fruit_image, tree_image, leaf_image, flower_image, branch_image, propagation_method, soil_characteristics, planting_period, harvest_season, processing_methods, mango_category) 
VALUES ('$mango_name', '$scientific_name', '$local_name', '$morphology_stem', '$morphology_fruit', '$morphology_leaf', '$fruit_image', '$tree_image', '$leaf_image', '$flower_image', '$branch_image', '$propagation_method', '$soil_characteristics', '$planting_period', '$harvest_season', '$processing_methods', '$mango_category')";

// บันทึกข้อมูลลงฐานข้อมูล
if ($conn->query($sql) === TRUE) {
    $_SESSION['success_message'] = "บันทึกข้อมูลมะม่วงเรียบร้อยแล้ว!";
    header("Location: manage_mango.php");
} else {
    $_SESSION['error_message'] = "เกิดข้อผิดพลาด: " . $conn->error;
    header("Location: add_mango.php");
}

$conn->close();
?>


