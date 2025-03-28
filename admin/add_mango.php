<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มสายพันธุ์มะม่วง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h2>➕ เพิ่มสายพันธุ์มะม่วง</h2>
    
    <form action="save_mango.php" method="POST" enctype="multipart/form-data">
        <!-- ชื่อสายพันธุ์ -->
        <div class="mb-3">
            <label class="form-label">ชื่อสายพันธุ์</label>
            <input type="text" class="form-control" name="mango_name" required>
        </div>

        <!-- รูปผลมะม่วง -->
        <div class="mb-3">
            <label class="form-label">รูปผลมะม่วง</label>
            <input type="file" class="form-control" name="fruit_image" accept="image/*" required>
        </div>

        <!-- รูปต้นมะม่วง -->
        <div class="mb-3">
            <label class="form-label">รูปต้นมะม่วง</label>
            <input type="file" class="form-control" name="tree_image" accept="image/*" required>
        </div>

        <!-- รูปใบมะม่วง -->
        <div class="mb-3">
            <label class="form-label">รูปใบมะม่วง</label>
            <input type="file" class="form-control" name="leaf_image" accept="image/*" required>
        </div>

        <!-- รูปดอกมะม่วง -->
        <div class="mb-3">
            <label class="form-label">รูปดอกมะม่วง</label>
            <input type="file" class="form-control" name="flower_image" accept="image/*" required>
        </div>

        <!-- รูปกลิ่งมะม่วง -->
        <div class="mb-3">
            <label class="form-label">รูปกลิ่งมะม่วง</label>
            <input type="file" class="form-control" name="stem_image" accept="image/*" required>
        </div>

        <!-- ลักษณะอื่นๆ เช่น ชื่อวิทยาศาสตร์, ชื่อท้องถิ่น, การขยายพันธุ์ ฯลฯ -->
        <div class="mb-3">
            <label class="form-label">ชื่อวิทยาศาสตร์</label>
            <input type="text" class="form-control" name="scientific_name" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">ชื่อท้องถิ่น</label>
            <input type="text" class="form-control" name="local_name" required>
        </div>

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
            <label class="form-label">ช่วงฤดูกาลเกี้ยว</label>
            <input type="text" class="form-control" name="harvest_season" required>
        </div>

        <div class="mb-3">
            <label class="form-label">การแปรรูป</label>
            <input type="text" class="form-control" name="processing_methods" required>
        </div>

        <div class="mb-3">
            <label class="form-label">หมวดมะม่วง</label>
            <select class="form-select" name="mango_category" required>
                <option value="เชิงพาณิชย์">เชิงพาณิชย์</option>
                <option value="เชิงอนุรักษ์">เชิงอนุรักษ์</option>
                <option value="บริโภคในครัวเรือน">บริโภคในครัวเรือน</option>
            </select>
        </div>

        <!-- ปุ่มบันทึกข้อมูล -->
        <button type="submit" class="btn btn-primary">💾 บันทึก</button>
        <a href="manage_mango.php" class="btn btn-secondary">🔙 กลับ</a>
    </form>
</div>

</body>
</html>
