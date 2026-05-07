<?php
session_start();
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

function uploadFile(array $file, string $targetDir = "uploads/")
{
    if (!isset($file["tmp_name"]) || !is_uploaded_file($file["tmp_name"])) {
        return "upload error";
    }

    $targetFile = $targetDir . basename($file["name"]);
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    if (!getimagesize($file["tmp_name"])) {
        return "not image";
    }

    $allowedTypes = ['jpg', 'png', 'jpeg', 'gif'];
    if (!in_array($fileType, $allowedTypes, true)) {
        return "invalid type";
    }

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return $targetFile;
    }

    return "upload failed";
}

$mango_name = $_POST['mango_name'] ?? '';
$scientific_name = $_POST['scientific_name'] ?? '';
$local_name = $_POST['local_name'] ?? '';
$morphology_stem = $_POST['morphology_stem'] ?? '';
$morphology_fruit = $_POST['morphology_fruit'] ?? '';
$morphology_leaf = $_POST['morphology_leaf'] ?? '';
$propagation_method = $_POST['propagation_method'] ?? '';
$soil_characteristics = $_POST['soil_characteristics'] ?? '';
$planting_period = $_POST['planting_period'] ?? '';
$harvest_season = $_POST['harvest_season'] ?? '';
$mango_category = $_POST['mango_category'] ?? '';
$processing_methods = isset($_POST['processing_methods']) ? implode(",", $_POST['processing_methods']) : '';

$fruit_image = uploadFile($_FILES['fruit_image']);
$tree_image = uploadFile($_FILES['tree_image']);
$leaf_image = uploadFile($_FILES['leaf_image']);
$flower_image = uploadFile($_FILES['flower_image']);
$branch_image = uploadFile($_FILES['branch_image']);

$invalidUpload = in_array("upload error", [$fruit_image, $tree_image, $leaf_image, $flower_image, $branch_image], true)
    || in_array("not image", [$fruit_image, $tree_image, $leaf_image, $flower_image, $branch_image], true)
    || in_array("invalid type", [$fruit_image, $tree_image, $leaf_image, $flower_image, $branch_image], true)
    || in_array("upload failed", [$fruit_image, $tree_image, $leaf_image, $flower_image, $branch_image], true);

if ($invalidUpload) {
    $_SESSION['error_message'] = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
    header("Location: add_mango.php");
    exit;
}

$sql = "INSERT INTO mango_varieties (
    mango_name, scientific_name, local_name, morphology_stem, morphology_fruit, morphology_leaf,
    fruit_image, tree_image, leaf_image, flower_image, branch_image,
    propagation_method, soil_characteristics, planting_period, harvest_season, processing_methods, mango_category
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    $_SESSION['error_message'] = "เกิดข้อผิดพลาดในการเตรียมคำสั่งบันทึกข้อมูล";
    header("Location: add_mango.php");
    exit;
}

$stmt->bind_param(
    "sssssssssssssssss",
    $mango_name,
    $scientific_name,
    $local_name,
    $morphology_stem,
    $morphology_fruit,
    $morphology_leaf,
    $fruit_image,
    $tree_image,
    $leaf_image,
    $flower_image,
    $branch_image,
    $propagation_method,
    $soil_characteristics,
    $planting_period,
    $harvest_season,
    $processing_methods,
    $mango_category
);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "บันทึกข้อมูลมะม่วงเรียบร้อยแล้ว!";
    header("Location: manage_mango.php");
} else {
    $_SESSION['error_message'] = "เกิดข้อผิดพลาด: " . $stmt->error;
    header("Location: add_mango.php");
}

$stmt->close();
$conn->close();
?>
