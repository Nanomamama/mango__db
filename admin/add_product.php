<?php
require_once 'auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// สร้าง CSRF Token หากยังไม่มี
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> เพิ่มสินค้าผลิตภัณฑ์แปรรูป</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f8f9fa;
        }

        .form-label {
            font-weight: 500;
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
        <h2 class="mb-4">➕ เพิ่มสินค้าผลิตภัณฑ์</h2>

        <form action="save_product.php" method="POST" enctype="multipart/form-data">
            <!-- เพิ่ม CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="row">
                <!-- ซ้าย -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">ชื่อสินค้าผลิตภัณฑ์</label>
                        <input type="text" class="form-control" name="product_name" placeholder="เช่น กล้วยอบเนย" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รายละเอียดสินค้า</label>
                        <textarea class="form-control" name="product_description" rows="3" placeholder="อธิบายลักษณะ รสชาติ หรือส่วนประกอบ" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รูปสินค้า</label>
                        <input type="file" class="form-control" name="product_image" accept="image/*" required>
                        <div id="image_preview" class="mt-3 d-flex flex-wrap"></div>
                    </div>
                </div>

                <!-- ขวา -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">น้ำหนักสินค้า (กิโลกรัม)</label>
                        <input type="number" step="0.01" class="form-control"
                            name="product_weight"
                            placeholder="เช่น 0.50"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ราคา (บาท)</label>
                        <input type="number" class="form-control" name="product_price" placeholder="กรอกราคา เช่น 59" min="1" required>
                    </div>

                    <!-- เพิ่มฟิลด์สำหรับสินค้าคงเหลือ -->
                    <div class="mb-3">
                        <label class="form-label">จำนวนสินค้าคงเหลือ</label>
                        <input type="number" class="form-control" name="product_stock" placeholder="กรอกจำนวน เช่น 100" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">แจ้งเตือนเมื่อสต๊อกต่ำกว่า</label>
                        <input type="number" class="form-control"
                            name="product_min_stock"
                            placeholder="เช่น 10"
                            min="0"
                            required>
                    </div>

                </div>
            </div>


            <div class="d-flex justify-content-between mt-4">
                <a href="manage_product.php" class="btn btn-secondary">🔙 กลับ</a>
                <button type="submit" class="btn btn-primary">💾 บันทึกสินค้า</button>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (isset($_SESSION['success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: '<?= $_SESSION['success'] ?>',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    <?php unset($_SESSION['success']);
    endif; ?>

    <script>
        document.getElementById('product_images').addEventListener('change', function(event) {
            const imagePreview = document.getElementById('image_preview');
            imagePreview.innerHTML = ''; // ล้างตัวอย่างรูปภาพก่อนหน้า

            const files = event.target.files;
            if (files) {
                Array.from(files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.alt = file.name;
                            img.style.width = '100px';
                            img.style.marginRight = '10px';
                            img.style.marginBottom = '10px';
                            img.style.border = '1px solid #ddd';
                            img.style.borderRadius = '5px';
                            imagePreview.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>

</body>

</html>