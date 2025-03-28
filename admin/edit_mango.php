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
    </style>
</head>

<body>
    <div class="container mt-4">
        <h2>✏️ แก้ไขสายพันธุ์มะม่วง</h2>
        <form action="#" method="POST" enctype="multipart/form-data">
            <div class="row">
                <!-- คอลัมน์ซ้าย -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">ชื่อสายพันธุ์</label>
                        <input type="text" class="form-control" name="mango_name" value="น้ำดอกไม้" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อวิทยาศาสตร์</label>
                        <input type="text" class="form-control" name="scientific_name" value="Mangifera indica" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อท้องถิ่น</label>
                        <input type="text" class="form-control" name="local_name" value="มะม่วงน้ำดอกไม้" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ลักษณะสัณฐานวิทยา</label>
                        <input type="text" class="form-control" name="morphology_stem" value="ลำต้นสูง 10-15 เมตร" required>
                        <input type="text" class="form-control mt-2" name="morphology_fruit" value="ผลยาว รูปไข่" required>
                        <input type="text" class="form-control mt-2" name="morphology_leaf" value="ใบเรียวยาว สีเขียวเข้ม" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ลักษณะดิน</label>
                        <input type="text" class="form-control" name="soil_characteristics" value="ดินร่วนซุย" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ระยะเวลาเพาะปลูก</label>
                        <input type="text" class="form-control" name="planting_period" value="3-5 ปี" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ช่วงฤดูกาลเก็บเกี่ยว</label>
                        <input type="text" class="form-control" name="harvest_season" value="มีนาคม - พฤษภาคม" required>
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

                <!-- คอลัมน์ขวา -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">รูปผลมะม่วง</label><br>
                        <img src="https://media.thairath.co.th/image/fmQpvmjp1V2ZIs1a2hU4OGKwkdosTnm1j4VXg22TebXFCs1a2hPSxQe9vA1.jpg" width="100">
                        <input type="file" class="form-control mt-2" name="image_fruit">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รูปต้นมะม่วง</label><br>
                        <img src="https://media.thairath.co.th/image/fmQpvmjp1V2ZIs1a2hU4OGKwkdosTnm1j4VXg22TebXFCs1a2hPSxQe9vA1.jpg" width="100">
                        <input type="file" class="form-control mt-2" name="image_tree">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รูปใบมะม่วง</label><br>
                        <img src="https://media.thairath.co.th/image/fmQpvmjp1V2ZIs1a2hU4OGKwkdosTnm1j4VXg22TebXFCs1a2hPSxQe9vA1.jpg" width="100">
                        <input type="file" class="form-control mt-2" name="image_leaf">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รูปดอกมะม่วง</label><br>
                        <img src="https://media.thairath.co.th/image/fmQpvmjp1V2ZIs1a2hU4OGKwkdosTnm1j4VXg22TebXFCs1a2hPSxQe9vA1.jpg" width="100">
                        <input type="file" class="form-control mt-2" name="image_flower">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รูปกลิ่งมะม่วง</label><br>
                        <img src="https://media.thairath.co.th/image/fmQpvmjp1V2ZIs1a2hU4OGKwkdosTnm1j4VXg22TebXFCs1a2hPSxQe9vA1.jpg" width="100">
                        <input type="file" class="form-control mt-2" name="image_seed">
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-success">💾 บันทึก</button>
                <a href="manage_mango.php" class="btn btn-secondary">🔙 กลับ</a>
            </div>
        </form>
    </div>
</body>

</html>