<?php
require_once 'auth.php';
?>
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> เพิ่มสินค้าผลิตภัณฐ์แปรรูป</title>

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
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <h2 class="mb-4">➕ เพิ่มสินค้าผลิตภัณฐ์แปรรูป</h2>

        <form action="save_product.php" method="POST" enctype="multipart/form-data">
            <div class="row">
                <!-- ซ้าย -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">ชื่อสินค้าผลิตภัณฐ์</label>
                        <input type="text" class="form-control" name="product_name" placeholder="เช่น กล้วยอบเนย" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รายละเอียดสินค้า</label>
                        <textarea class="form-control" name="product_description" rows="3" placeholder="อธิบายลักษณะ รสชาติ หรือส่วนประกอบ" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">หมวดหมู่สินค้า</label>
                        <input type="text" class="form-control" name="product_category" placeholder="เช่น ขนมอบ, ของแห้ง" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รูปสินค้า</label>
                        <input type="file" class="form-control" name="product_images[]" accept="image/*" multiple required>
                    </div>
                </div>

                <!-- ขวา -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">ราคา (บาท)</label>
                        <input type="number" class="form-control" name="product_price" placeholder="กรอกราคา เช่น 59" min="1" required>
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
    <?php unset($_SESSION['success']); endif; ?>

</body>

</html>
