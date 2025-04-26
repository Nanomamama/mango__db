<!-- คำสั่ง SQL สำหรับการลบข้อมูล -->
<?php
require 'db.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);

    // ลบข้อมูล
    $stmt = $conn->prepare("DELETE FROM mango_varieties WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: manage_mango.php?deleted=1");
    } else {
        echo "เกิดข้อผิดพลาดในการลบข้อมูล";
    }
}
?>
