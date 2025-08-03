<?php
require_once 'auth.php';
?>
<?php
// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "db_mango");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// รับ id จาก URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ดึงข้อมูลจากฐานข้อมูล
$sql = "SELECT * FROM mango_varieties WHERE id = $id";
$result = $conn->query($sql);
$mango = $result->fetch_assoc();

if (!$mango) {
    echo "ไม่พบข้อมูล";
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แก้ไขสายพันธุ์มะม่วง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: "Kanit", sans-serif;
        }

        .btn {
            transition: transform 0.3s ease;
            transition: 0.3s;
        }

        .btn:hover {
            transform: translateY(-5px);
        }
    </style>
</head>

<body class="container py-4">
    <h2 class="mb-4 ">แก้ไขสายพันธุ์มะม่วง</h2>
    <form action="update_mango.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $mango['id'] ?>">

        <!-- กรอบที่ใช้ Grid -->
        <div class="row g-4">

            <!-- ชื่อสายพันธุ์และชื่อวิทยาศาสตร์ -->
            <div class="col-md-6">
                <label class="form-label">ชื่อสายพันธุ์</label>
                <input type="text" name="mango_name" class="form-control" value="<?= $mango['mango_name'] ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">ชื่อวิทยาศาสตร์</label>
                <input type="text" name="scientific_name" class="form-control" value="<?= $mango['scientific_name'] ?>">
            </div>

            <!-- ชื่อท้องถิ่นและลักษณะลำต้น -->
            <div class="col-md-6">
                <label class="form-label">ชื่อท้องถิ่น</label>
                <input type="text" name="local_name" class="form-control" value="<?= $mango['local_name'] ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">ลักษณะลำต้น</label>
                <textarea name="morphology_stem" class="form-control"><?= $mango['morphology_stem'] ?></textarea>
            </div>

            <!-- ลักษณะผลและลักษณะใบ -->
            <div class="col-md-6">
                <label class="form-label">ลักษณะผล</label>
                <textarea name="morphology_fruit" class="form-control"><?= $mango['morphology_fruit'] ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">ลักษณะใบ</label>
                <textarea name="morphology_leaf" class="form-control"><?= $mango['morphology_leaf'] ?></textarea>
            </div>

            <!-- การขยายพันธุ์และลักษณะดิน -->
            <div class="col-md-6">
                <label class="form-label">การขยายพันธุ์</label>
                <textarea name="propagation_method" class="form-control"><?= $mango['propagation_method'] ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">ลักษณะดิน</label>
                <textarea name="soil_characteristics" class="form-control"><?= $mango['soil_characteristics'] ?></textarea>
            </div>

            <!-- ระยะเวลาการปลูกและฤดูกาลเก็บเกี่ยว -->
            <div class="col-md-6">
                <label class="form-label">ระยะเวลาการปลูก</label>
                <input type="text" name="planting_period" class="form-control" value="<?= $mango['planting_period'] ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">ฤดูกาลเก็บเกี่ยว</label>
                <input type="text" name="harvest_season" class="form-control" value="<?= $mango['harvest_season'] ?>">
            </div>

            <!-- หมวดหมู่ -->
            <div class="col-md-6">
                <label class="form-label">หมวดหมู่</label>
                <select name="mango_category" class="form-select" required>
                    <?php
                    $categories = ['เชิงพาณิชย์', 'เชิงอนุรักษ์', 'บริโภคในครัวเรือน'];
                    foreach ($categories as $category):
                        $selected = ($mango['mango_category'] === $category) ? 'selected' : '';
                    ?>
                        <option value="<?= $category ?>" <?= $selected ?>><?= $category ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- การแปรรูป -->
            <div class="col-md-12">
                <label class="form-label">การแปรรูป</label><br>
                <?php
                $selected_methods = explode(",", $mango['processing_methods']);
                $options = ['กวน', 'ดอง', 'แช่อิ่ม', 'นิยมรับประทานสด'];
                foreach ($options as $option):
                ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="processing_methods[]" value="<?= $option ?>"
                            <?= in_array($option, $selected_methods) ? 'checked' : '' ?>>
                        <label class="form-check-label"><?= $option ?></label>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- รูปภาพ -->
            <div class="row g-4">

                <div class="col-md-2">
                    <label class="form-label">รูปผลมะม่วง</label>
                    <div>
                        <img id="fruit_image_preview" src="<?= $mango['fruit_image'] ?>" class="img-thumbnail d-block mb-2" style="width: 150px; height: 110px; object-fit: cover;">
                        <input type="file" name="fruit_image" class="form-control" onchange="previewImage(event, 'fruit_image_preview')">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">รูปต้นมะม่วง</label>
                    <div>
                        <img id="tree_image_preview" src="<?= $mango['tree_image'] ?>" class="img-thumbnail d-block mb-2" style="width: 150px; height: 110px; object-fit: cover;">
                        <input type="file" name="tree_image" class="form-control" onchange="previewImage(event, 'tree_image_preview')">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">รูปใบมะม่วง</label>
                    <div>
                        <img id="leaf_image_preview" src="<?= $mango['leaf_image'] ?>" class="img-thumbnail d-block mb-2" style="width: 150px; height: 110px; object-fit: cover;">
                        <input type="file" name="leaf_image" class="form-control" onchange="previewImage(event, 'leaf_image_preview')">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">รูปดอกมะม่วง</label>
                    <div>
                        <img id="flower_image_preview" src="<?= $mango['flower_image'] ?>" class="img-thumbnail d-block mb-2" style="width: 150px; height: 110px; object-fit: cover;">
                        <input type="file" name="flower_image" class="form-control" onchange="previewImage(event, 'flower_image_preview')">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">รูปกิ่งมะม่วง</label>
                    <div>
                        <img id="branch_image_preview" src="<?= $mango['branch_image'] ?>" class="img-thumbnail d-block mb-2" style="width: 150px; height: 110px; object-fit: cover;">
                        <input type="file" name="branch_image" class="form-control" onchange="previewImage(event, 'branch_image_preview')">
                    </div>
                </div>

            </div>
            <hr>
            <div class="col-md-12 d-flex justify-content-between">
                <button type="submit" class="btn btn-success">💾 บันทึกการแก้ไข</button>
                <a href="manage_mango.php" class="btn btn-secondary">ย้อนกลับ</a>
            </div>
        </div>
    </form>
</body>

<script>
    function previewImage(event, previewId) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById(previewId).src = reader.result;
            };
            reader.readAsDataURL(file);
        }
    }
</script>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>


</html>