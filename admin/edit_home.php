<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขหน้าแรก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: "Kanit", sans-serif;
        }
    </style>
</head>

<body>

<div class="d-flex">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="container mt-4" style="margin-left: 270px; max-width: 800px;">
        <h2>📝 แก้ไขเนื้อหาในหน้าแรก</h2>
        <form action="save_home.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">ข้อความหน้าแรก</label>
                <textarea class="form-control" name="home_text" rows="4">ใส่เนื้อหาที่ต้องการแก้ไข...</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">อัปโหลดรูปภาพหน้าแรก</label>
                <input type="file" class="form-control" name="home_image">
            </div>
            <button type="submit" class="btn btn-primary">💾 บันทึก</button>
        </form>
    </div>
</div>

</body>

</html>
