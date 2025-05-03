<?php
require_once '../admin/db.php';

if (!isset($_GET['name'])) {
    header('Location: mango_varieties.php');
    exit;
}

$name = $_GET['name'];

// ดึงข้อมูลมะม่วงจากฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM mango_varieties WHERE mango_name = ?");
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();
$mango = $result->fetch_assoc();

if (!$mango) {
    echo "ไม่พบข้อมูลสายพันธุ์";
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($mango['mango_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    
    <style>
        :root {
        --Primary: #4e73df;
        --Success: #1cc88a;
        --Info: #36b9cc;
        --Warning: #f6c23e;
        --Danger:rgb(246, 49, 31);
        --Secondary: #858796;
        --Light: #f8f9fc;
        --Dark: #5a5c69;
        --Darkss: #000000;
        }

        .col-6 img {
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .col-6:hover img {
            transform: scale(1.1);
        }
        .row h2 {
            font-weight: 600;
        }
        .row h4 {
            font-weight: 600;
        }
        p strong {
            font-weight: 400;
        }
        .container h4 {
            font-weight: 600;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<br>
<div class="container py-5 mt-5">
    <div class="row">
        <!-- คอลัมน์ซ้าย -->
        <div class="col-md-4">
            <h2 class="mb-4"><?= htmlspecialchars($mango['mango_name']) ?></h2>
            <img src="../admin/uploads/<?= htmlspecialchars(basename($mango['fruit_image'])) ?>" class="img-fluid mb-3" alt="<?= htmlspecialchars($mango['mango_name']) ?>" style="max-height: 400px;">
        </div>
        <!-- คอลัมน์ขวา -->
        <div class="col-md-4">
            <h4>ข้อมูลทั่วไป</h4>
            <p><strong>ชื่อวิทยาศาสตร์:</strong> <?= htmlspecialchars($mango['scientific_name']) ?></p>
            <p><strong>ชื่อท้องถิ่น:</strong> <?= !empty($mango['local_name']) ? htmlspecialchars($mango['local_name']) : '-' ?></p>
            <h4 class="mt-4">ลักษณะสัณฐานวิทยา</h4>
            <p><strong>ลำต้น:</strong> <?= htmlspecialchars($mango['morphology_stem']) ?></p>
            <p><strong>ผล:</strong> <?= htmlspecialchars($mango['morphology_fruit']) ?></p>
            <p><strong>ใบ:</strong> <?= htmlspecialchars($mango['morphology_leaf']) ?></p>
            <h4>การเพาะปลูก</h4>
            <p><strong>การขยายพันธุ์:</strong> <?= htmlspecialchars($mango['propagation_method']) ?></p>
            <p><strong>ลักษณะดิน:</strong> <?= htmlspecialchars($mango['soil_characteristics']) ?></p>
            <p><strong>ระยะเวลาเพาะปลูก:</strong> <?= htmlspecialchars($mango['planting_period']) ?></p>
            <p><strong>ช่วงฤดูกาลออกดอก:</strong> <?= htmlspecialchars($mango['harvest_season']) ?></p>
        </div>
        <div class="col-md-4">
            <h4>การแปรรูป</h4>
            <p><?= nl2br(htmlspecialchars($mango['processing_methods'])) ?></p>
            <h4 class="mt-4">หมวดหมู่มะม่วง</h4>
            <p><strong>ประเภท:</strong> <?= htmlspecialchars($mango['mango_category']) ?></p>
        </div>
    </div>

    <h4 class="mt-5">รูปภาพ</h4>
    <div class="row text-center">
        <div class="col-6 col-md-3 mb-3">
            <h6>ต้น</h6>
            <img src="../admin/uploads/<?= htmlspecialchars(basename($mango['tree_image'])) ?>" class="img-fluid mb-3" alt="ต้นมะม่วง" style="object-fit: cover; width: 100%; height: 200px;">
        </div>
        <div class="col-6 col-md-3 mb-3">
            <h6>ใบ</h6>
            <img src="../admin/uploads/<?= htmlspecialchars(basename($mango['leaf_image'])) ?>" class="img-fluid mb-3" alt="ใบมะม่วง" style="object-fit: cover; width: 100%; height: 200px;">
        </div>
        <div class="col-6 col-md-3 mb-3">
            <h6>กิ่ง</h6>
            <img src="../admin/uploads/<?= htmlspecialchars(basename($mango['branch_image'])) ?>" class="img-fluid mb-3" alt="กิ่งมะม่วง" style="object-fit: cover; width: 100%; height: 200px;">
        </div>
        <div class="col-6 col-md-3 mb-3">
            <h6>ดอก</h6>
            <img src="../admin/uploads/<?= htmlspecialchars(basename($mango['flower_image'])) ?>" class="img-fluid mb-3" alt="ดอกมะม่วง" style="object-fit: cover; width: 100%; height: 200px;">
        </div>
    </div>
    <a href="mango_varieties.php" class="btn btn-secondary mt-4">← กลับหน้ารวม</a>
</div>
<?php include 'footer.php'; ?>
</body>
</html>