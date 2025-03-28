<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Latest compiled and minified CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Latest compiled JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/product.css">


</head>

<body>
<div class="container mt-4">
    <h2>➕ สินค้าผลิตภัณฑ์แปรรูป</h2>
    
    <form action="save_mango.php" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">ชื่อสินค้าผลิตภัณฑ์</label>
                    <input type="text" class="form-control" name="mango_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">รายละเอียดสินค้า</label>
                    <input type="text" class="form-control" name="scientific_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">หมวดหมู่สินค้า</label>
                    <input type="text" class="form-control" name="local_name" required>
                </div>
               
                <div class="mb-3">
                    <label class="form-label">รูปสินค้า</label>
                    <input type="file" class="form-control" name="product_image" accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">รูปต้นมะม่วง</label>
                    <input type="file" class="form-control" name="tree_image" accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">รูปใบมะม่วง</label>
                    <input type="file" class="form-control" name="leaf_image" accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">รูปดอกมะม่วง</label>
                    <input type="file" class="form-control" name="leaf_image" accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">รูปกลิ่งมะม่วง</label>
                    <input type="file" class="form-control" name="leaf_image" accept="image/*" required>
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
        <a href="index.php" class="btn btn-secondary">🔙 กลับ</a>

    </form>

</body>

</html>