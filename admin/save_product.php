<?php
session_start();

// เชื่อมต่อฐานข้อมูล
$servername = "localhost"; 
$username = "root";         
$password = "";            
$dbname = "db_mango";    

$conn = new mysqli($servername, $username, $password, $dbname);

// เช็คการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูลจากฟอร์ม
    $productName = $_POST['product_name'];
    $productDescription = $_POST['product_description'];
    $productCategory = $_POST['product_category'];
    $productPrice = $_POST['product_price'];

    // เตรียมบันทึกสินค้า
    $sql = "INSERT INTO products (name, description, category, price) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssd", $productName, $productDescription, $productCategory, $productPrice);

    if ($stmt->execute()) {
        $productId = $stmt->insert_id; // id สินค้าใหม่

        // เตรียมโฟลเดอร์รูป
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // เช็กว่ามีไฟล์ภาพไหม
        if (!empty($_FILES['product_images']['name'][0])) {
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            foreach ($_FILES['product_images']['tmp_name'] as $key => $tmpName) {
                $originalName = basename($_FILES['product_images']['name'][$key]);
                $fileExt = pathinfo($originalName, PATHINFO_EXTENSION);

                // ตั้งชื่อไฟล์ใหม่ ป้องกันซ้ำ
                $newFileName = time() . '_' . uniqid() . '.' . strtolower($fileExt);
                $targetFilePath = $uploadDir . $newFileName;

                // ตรวจสอบชนิดไฟล์
                if (in_array(strtolower($fileExt), $allowedTypes)) {
                    if (move_uploaded_file($tmpName, $targetFilePath)) {
                        // บันทึกรูปภาพเข้า DB
                        $insertImage = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                        $insertImage->bind_param("is", $productId, $newFileName);
                        $insertImage->execute();
                    } else {
                        $_SESSION['error'] = "การอัปโหลดรูปภาพล้มเหลว";
                        header("Location: manage_product.php");
                        exit();
                    }
                } else {
                    $_SESSION['error'] = "รูปภาพไม่ตรงตามประเภทที่อนุญาต";
                    header("Location: manage_product.php");
                    exit();
                }
            }
        }

        $_SESSION['success'] = "บันทึกข้อมูลสินค้าเรียบร้อยแล้ว!";
        header("Location: manage_product.php");
        exit();
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกสินค้า: " . $conn->error;
        header("Location: manage_product.php");
        exit();
    }
} else {
    // ป้องกันการเข้าหน้าโดยตรง
    $_SESSION['error'] = "ไม่อนุญาตให้เข้าถึงหน้านี้โดยตรง!";
    header("Location: manage_product.php");
    exit();
}
?>
