<?php
require_once 'auth.php';
requireAdminRole('main');
require_once 'sidebar.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$adminPageExtraHead = <<<'HTML'
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/product_admin.css">
HTML;

adminPageStart('เพิ่มสินค้าใหม่');
?>

<div class="product-admin-page">
    <section class="product-hero">
        <span class="product-topline"><i class="bi bi-box-seam"></i> Product Management</span>
        <div class="product-hero-grid">
            <div>
                <h1>เพิ่มสินค้าใหม่</h1>
                <p>กรอกข้อมูลสินค้า ราคา สถานะ และรูปภาพให้ครบถ้วน ข้อมูลนี้จะถูกใช้ทั้งในหน้าร้านและระบบจัดการคำสั่งซื้อ</p>
            </div>
            <a href="manage_product.php" class="product-btn product-btn-secondary">
                <i class="bi bi-arrow-left"></i> กลับรายการสินค้า
            </a>
        </div>
    </section>

    <section class="product-form-card">
        <?php if (!empty($_SESSION['product_error'])): ?>
            <div class="product-alert product-alert-danger mb-3">
                <?= htmlspecialchars($_SESSION['product_error'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['product_error']); ?>
        <?php endif; ?>

        <form action="save_product.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

            <div class="product-form-grid">
                <div>
                    <div class="product-section">
                        <h2 class="product-section-title"><i class="bi bi-card-text"></i> ข้อมูลสินค้า</h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="product_name" class="form-label">ชื่อสินค้า <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="product_name" name="product_name" placeholder="เช่น มะม่วงน้ำดอกไม้" required>
                                <div class="invalid-feedback">กรุณากรอกชื่อสินค้า</div>
                            </div>
                            <div class="col-md-6">
                                <label for="category" class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="category" name="category" placeholder="เช่น ผลไม้สด / ผลิตภัณฑ์แปรรูป" required>
                                <div class="invalid-feedback">กรุณาระบุหมวดหมู่</div>
                            </div>
                            <div class="col-12">
                                <label for="product_description" class="form-label">รายละเอียดสินค้า <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="product_description" name="product_description" rows="5" placeholder="รายละเอียด จุดเด่น วิธีเก็บรักษา หรือข้อมูลสำคัญของสินค้า" required></textarea>
                                <div class="invalid-feedback">กรุณากรอกรายละเอียดสินค้า</div>
                            </div>
                        </div>
                    </div>

                    <div class="product-section">
                        <h2 class="product-section-title"><i class="bi bi-cash-coin"></i> ราคาและหน่วยขาย</h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" placeholder="120.00" required>
                                <div class="invalid-feedback">กรุณาระบุราคา</div>
                            </div>
                            <div class="col-md-6">
                                <label for="unit" class="form-label">หน่วย <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="unit" name="unit" placeholder="เช่น กิโลกรัม / กล่อง / แพ็ก" required>
                                <div class="invalid-feedback">กรุณาระบุหน่วยขาย</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="product-section">
                        <h2 class="product-section-title"><i class="bi bi-sliders"></i> สถานะสินค้า</h2>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="status" class="form-label">สถานะ <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" selected>เปิดขาย</option>
                                    <option value="inactive">ปิดขาย</option>
                                </select>
                                <div class="product-help mt-2">สินค้าที่ปิดขายจะไม่แสดงในหน้าร้าน</div>
                            </div>
                            <div class="col-12">
                                <label for="seasonal" class="form-label">สินค้าตามฤดูกาล <span class="text-danger">*</span></label>
                                <select class="form-select" id="seasonal" name="seasonal" required>
                                    <option value="1">ใช่</option>
                                    <option value="0" selected>ไม่ใช่</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="product-section">
                        <h2 class="product-section-title"><i class="bi bi-image"></i> รูปภาพสินค้า</h2>
                        <div class="upload-zone" id="imageUploadBox" role="button" tabindex="0">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <strong>คลิกหรือลากไฟล์มาวาง</strong>
                            <span>รองรับ JPG, PNG, GIF ขนาดไม่เกิน 2MB</span>
                        </div>
                        <input type="file" class="d-none" name="product_image" id="product_image" accept="image/*">
                        <div id="image_preview" class="mt-3"></div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="manage_product.php" class="product-btn product-btn-secondary">
                    <i class="bi bi-arrow-left-circle"></i> ยกเลิก
                </a>
                <button type="submit" class="product-btn product-btn-primary">
                    <i class="bi bi-check-circle"></i> บันทึกสินค้า
                </button>
            </div>
        </form>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelector('.page-content')?.classList.add('product-page-bg');

(function () {
    const uploadBox = document.getElementById('imageUploadBox');
    const fileInput = document.getElementById('product_image');
    const preview = document.getElementById('image_preview');

    uploadBox.addEventListener('click', () => fileInput.click());
    uploadBox.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            fileInput.click();
        }
    });

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((eventName) => {
        uploadBox.addEventListener(eventName, (event) => {
            event.preventDefault();
            event.stopPropagation();
        });
    });

    ['dragenter', 'dragover'].forEach((eventName) => {
        uploadBox.addEventListener(eventName, () => uploadBox.classList.add('is-dragover'));
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        uploadBox.addEventListener(eventName, () => uploadBox.classList.remove('is-dragover'));
    });

    uploadBox.addEventListener('drop', (event) => {
        if (event.dataTransfer.files.length) {
            fileInput.files = event.dataTransfer.files;
            renderPreview(event.dataTransfer.files[0]);
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) {
            renderPreview(fileInput.files[0]);
        }
    });

    function renderPreview(file) {
        preview.innerHTML = '';

        if (!file.type.startsWith('image/')) {
            preview.innerHTML = '<div class="product-alert product-alert-danger">กรุณาเลือกไฟล์รูปภาพเท่านั้น</div>';
            fileInput.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            preview.innerHTML = '<div class="product-alert product-alert-danger">ไฟล์ต้องมีขนาดไม่เกิน 2MB</div>';
            fileInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (event) => {
            const sizeKb = (file.size / 1024).toFixed(1);
            preview.innerHTML = `
                <div class="image-card">
                    <img src="${event.target.result}" alt="ตัวอย่างรูปสินค้า">
                    <div class="image-card-body">
                        <div class="image-card-title">${file.name}</div>
                        <div class="image-card-meta">ขนาด ${sizeKb} KB</div>
                    </div>
                    <button type="button" class="product-btn product-btn-danger" id="removeImageBtn">
                        <i class="bi bi-trash"></i> ลบรูป
                    </button>
                </div>
            `;
            document.getElementById('removeImageBtn').addEventListener('click', () => {
                fileInput.value = '';
                preview.innerHTML = '';
            });
        };
        reader.readAsDataURL(file);
    }

    document.querySelectorAll('.needs-validation').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
})();
</script>

<?php adminPageEnd(); ?>
