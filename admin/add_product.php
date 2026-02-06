<?php
require_once 'auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// สร้าง CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสินค้า</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 800px;
            background-color: #fff;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>

<div class="container mt-5">
    <h2 class="mb-4">➕ เพิ่มสินค้า</h2>

    <form action="save_product.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="row">
            <!-- ซ้าย -->
            <div class="col-md-6">

                <div class="mb-3">
                    <label class="form-label">ชื่อสินค้า</label>
                    <input type="text" class="form-control" name="product_name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">หมวดหมู่</label>
                    <input type="text" class="form-control" name="category" placeholder="เช่น ผลไม้แปรรูป" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">รายละเอียดสินค้า</label>
                    <textarea class="form-control" name="product_description" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">รูปสินค้า</label>
                    <input type="file" class="form-control" name="product_image" id="product_image" accept="image/*" required>
                    <div id="image_preview" class="mt-3"></div>
                </div>
            </div>

            <!-- ขวา -->
            <div class="col-md-6">

                <div class="mb-3">
                    <label class="form-label">ราคา (บาท)</label>
                    <input type="number" class="form-control" name="price" min="0" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">หน่วย</label>
                    <input type="text" class="form-control" name="unit" placeholder="เช่น แพ็ค / กก. / ชิ้น" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">สินค้าตามฤดู</label>
                    <select class="form-select" name="seasonal" required>
                        <option value="0">ไม่ใช่</option>
                        <option value="1">ใช่</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">สถานะสินค้า</label>
                    <select class="form-select" name="status" required>
                        <option value="active">ขาย</option>
                        <option value="inactive">ปิดขาย</option>
                    </select>
                </div>

            </div>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="manage_product.php" class="btn btn-secondary">🔙 กลับ</a>
            <button type="submit" class="btn btn-primary">💾 บันทึกสินค้า</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- preview รูป -->
<script>
document.getElementById('product_image').addEventListener('change', function(event) {
    const imagePreview = document.getElementById('image_preview');
    imagePreview.innerHTML = '';

    const file = event.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '150px';
            img.style.borderRadius = '8px';
            imagePreview.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>
