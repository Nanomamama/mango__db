<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เพิ่มสายพันธุ์มะม่วง</title>
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

<body>

    <div class="container mt-4">
        <h2>➕ เพิ่มสายพันธุ์มะม่วง</h2>

        <form action="save_mango.php" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">ชื่อสายพันธุ์</label>
                        <input type="text" class="form-control" name="mango_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อวิทยาศาสตร์</label>
                        <input type="text" class="form-control" name="scientific_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อท้องถิ่น</label>
                        <input type="text" class="form-control" name="local_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ลักษณะสัณฐานวิทยา</label>
                        <input type="text" class="form-control" name="morphology_stem" placeholder="ลำต้น" required>
                        <input type="text" class="form-control mt-2" name="morphology_fruit" placeholder="ผล" required>
                        <input type="text" class="form-control mt-2" name="morphology_leaf" placeholder="ใบ" required>
                    </div>

                    <div class="row ">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">รูปผลมะม่วง</label>
                            <img id="fruit_preview" src="#" alt="preview" class="img-thumbnail mb-2" style="display: none; width: 100%; height: 110px; object-fit: cover;">
                            <input type="file" class="form-control" name="fruit_image" accept="image/*" required onchange="previewImage(event, 'fruit_preview')">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">รูปต้นมะม่วง</label>
                            <img id="tree_preview" src="#" alt="preview" class="img-thumbnail mb-2" style="display: none; width: 100%; height: 110px; object-fit: cover;">
                            <input type="file" class="form-control" name="tree_image" accept="image/*" required onchange="previewImage(event, 'tree_preview')">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">รูปใบมะม่วง</label>
                            <img id="leaf_preview" src="#" alt="preview" class="img-thumbnail mb-2" style="display: none; width: 100%; height: 110px; object-fit: cover;">
                            <input type="file" class="form-control" name="leaf_image" accept="image/*" required onchange="previewImage(event, 'leaf_preview')">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">รูปดอกมะม่วง</label>
                            <img id="flower_preview" src="#" alt="preview" class="img-thumbnail mb-2" style="display: none; width: 100%; height: 110px; object-fit: cover;">
                            <input type="file" class="form-control" name="flower_image" accept="image/*" required onchange="previewImage(event, 'flower_preview')">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">รูปกิ่งมะม่วง</label>
                            <img id="branch_preview" src="#" alt="preview" class="img-thumbnail mb-2" style="display: none; width: 100%; height: 110px; object-fit: cover;">
                            <input type="file" class="form-control" name="branch_image" accept="image/*" required onchange="previewImage(event, 'branch_preview')">
                        </div>
                    </div>

                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">การขยายพันธุ์</label>
                        <input type="text" class="form-control" name="propagation_method" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ลักษณะดิน</label>
                        <input type="text" class="form-control" name="soil_characteristics" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ระยะเวลาเพาะปลูก</label>
                        <input type="text" class="form-control" name="planting_period" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ช่วงฤดูกาลเก็บเกี่ยว</label>
                        <input type="text" class="form-control" name="harvest_season" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">การแปรรูป</label><br>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="processing_methods[]" value="กวน">
                            <label class="form-check-label">กวน</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="processing_methods[]" value="ดอง">
                            <label class="form-check-label">ดอง</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="processing_methods[]" value="แช่อิ่ม">
                            <label class="form-check-label">แช่อิ่ม</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="processing_methods[]" value="นิยมรับประทานสด">
                            <label class="form-check-label">นิยมรับประทานสด</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">หมวดมะม่วง</label>
                        <select class="form-select" name="mango_category" required>
                            <option value="เชิงพาณิชย์">เชิงพาณิชย์</option>
                            <option value="เชิงอนุรักษ์">เชิงอนุรักษ์</option>
                            <option value="บริโภคในครัวเรือน">บริโภคในครัวเรือน</option>
                        </select>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">💾 บันทึก</button>
            <a href="manage_mango.php" class="btn btn-secondary">🔙 กลับ</a>
            <hr>
        </form>
    </div>

    <script>
function previewImage(event, previewId) {
    const input = event.target;
    const preview = document.getElementById(previewId);

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

</body>
</html>