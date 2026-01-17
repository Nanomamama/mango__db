<!-- ด้านล่างนี้คือโค้ดสำหรับหน้า update_mango.php ที่ทำการรับค่าจากฟอร์มในหน้า edit_mango.php
โดยจะรองรับการอัปโหลดรูปภาพใหม่สำหรับแต่ละฟิลด์ ถ้าไม่มีการเลือกไฟล์ใหม่ก็จะคงรูปเดิมไว้ -->
<?php
session_start();

// เชื่อมต่อฐานข้อมูล โดยใช้การตั้งค่าจากไฟล์กลาง admin/db.php
require_once __DIR__ . '/db.php';

// ฟังก์ชันสำหรับอัปโหลดไฟล์
function uploadFile($fileInputName, $currentFilePath) {
    $uploadDir = 'uploads/';
    
    // ตรวจสอบว่ามีการอัปโหลดไฟล์ใหม่หรือไม่
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === 0) {
        // กำหนดชื่อไฟล์ใหม่โดยใช้เวลารวมกับชื่อไฟล์เดิม
        $filename = time() . "_" . basename($_FILES[$fileInputName]['name']);
        $targetFile = $uploadDir . $filename;

        // ตรวจสอบประเภทไฟล์ (สามารถเพิ่มเงื่อนไขได้ตามต้องการ)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES[$fileInputName]['type'], $allowedTypes)) {
            echo "ประเภทไฟล์ " . $_FILES[$fileInputName]['name'] . " ไม่ได้รับอนุญาต";
            return $currentFilePath;
        }

        // พยายามอัปโหลดไฟล์
        if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetFile)) {
            // หากการอัปโหลดสำเร็จ ให้ส่งคืน path ของไฟล์ใหม่
            return $targetFile;
        } else {
            echo "เกิดข้อผิดพลาดในการอัปโหลดไฟล์ " . $_FILES[$fileInputName]['name'];
            return $currentFilePath;
        }
    }

    // หากไม่มีการอัปโหลดไฟล์ใหม่ ให้ส่งคืนค่าเดิม
    return $currentFilePath;
}

// รับค่าจากฟอร์ม
$id                   = $_POST['id'];
$mango_name           = $_POST['mango_name'];
$scientific_name      = $_POST['scientific_name'];
$local_name           = isset($_POST['local_name']) ? $_POST['local_name'] : '';
$morphology_stem      = isset($_POST['morphology_stem']) ? $_POST['morphology_stem'] : '';
$morphology_fruit     = isset($_POST['morphology_fruit']) ? $_POST['morphology_fruit'] : '';
$morphology_leaf      = isset($_POST['morphology_leaf']) ? $_POST['morphology_leaf'] : '';
$propagation_method   = isset($_POST['propagation_method']) ? $_POST['propagation_method'] : '';
$soil_characteristics = isset($_POST['soil_characteristics']) ? $_POST['soil_characteristics'] : '';
$planting_period      = isset($_POST['planting_period']) ? $_POST['planting_period'] : '';
$harvest_season       = isset($_POST['harvest_season']) ? $_POST['harvest_season'] : '';
$mango_category       = isset($_POST['mango_category']) ? $_POST['mango_category'] : '';
$processing_methods   = isset($_POST['processing_methods']) ? implode(',', $_POST['processing_methods']) : ''; // แปลงเป็น string

// ดึงข้อมูลเดิมของสายพันธุ์มะม่วง เพื่อเก็บค่าเดิมของรูปภาพ ถ้ายังไม่มีการอัปโหลดใหม่
$sqlSelect = "SELECT fruit_image, tree_image, leaf_image, flower_image, branch_image FROM mango_varieties WHERE mango_id = $id";
$result = $conn->query($sqlSelect);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $currentFruitImage  = $row['fruit_image'];
    $currentTreeImage   = $row['tree_image'];
    $currentLeafImage   = $row['leaf_image'];
    $currentFlowerImage = $row['flower_image'];
    $currentBranchImage = $row['branch_image'];
} else {
    echo "ไม่พบข้อมูลสำหรับอัปเดต";
    exit;
}

// ตรวจสอบและอัปโหลดไฟล์ใหม่ (ถ้ามี)
$fruit_image  = uploadFile('fruit_image', $currentFruitImage);
$tree_image   = uploadFile('tree_image', $currentTreeImage);
$leaf_image   = uploadFile('leaf_image', $currentLeafImage);
$flower_image = uploadFile('flower_image', $currentFlowerImage);
$branch_image = uploadFile('branch_image', $currentBranchImage);

// เตรียมคำสั่ง UPDATE
$sqlUpdate = "UPDATE mango_varieties SET 
    mango_name = ?, 
    scientific_name = ?, 
    local_name = ?, 
    morphology_stem = ?, 
    morphology_fruit = ?, 
    morphology_leaf = ?, 
    propagation_method = ?, 
    soil_characteristics = ?, 
    planting_period = ?, 
    harvest_season = ?, 
    mango_category = ?, 
    processing_methods = ?, 
    fruit_image = ?, 
    tree_image = ?, 
    leaf_image = ?, 
    flower_image = ?, 
    branch_image = ? 
    WHERE mango_id = ?";

$stmt = $conn->prepare($sqlUpdate);
if(!$stmt){
    die("Error in prepare: " . $conn->error);
}

// ผูกค่าให้กับพารามิเตอร์ (s = string, i = integer)
$stmt->bind_param("sssssssssssssssssi",
    $mango_name,
    $scientific_name,
    $local_name,
    $morphology_stem,
    $morphology_fruit,
    $morphology_leaf,
    $propagation_method,
    $soil_characteristics,
    $planting_period,
    $harvest_season,
    $mango_category,
    $processing_methods,
    $fruit_image,
    $tree_image,
    $leaf_image,
    $flower_image,
    $branch_image,
    $id // เพิ่มตัวแปร id สำหรับ integer
);

if ($stmt->execute()) {
    // เมื่ออัปเดตสำเร็จให้เปลี่ยนเส้นทางกลับไปหน้า manage_mango หรือหน้าที่ต้องการ
    header("Location: manage_mango.php");
    exit;
} else {
    echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
