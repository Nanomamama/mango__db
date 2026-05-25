<?php
require_once 'auth.php';
requireAdminRole('main');
require_once __DIR__ . '/../db/db.php';
require_once 'sidebar.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: manage_product.php?error=' . urlencode('ไม่มีรหัสสินค้า'));
    exit;
}

$stmt = $conn->prepare('SELECT * FROM products WHERE product_id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: manage_product.php?error=' . urlencode('ไม่พบสินค้าที่ต้องการแก้ไข'));
    exit;
}

$adminPageExtraHead = <<<'HTML'
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/product_admin.css">
HTML;

adminPageStart('แก้ไขสินค้า');
?>

<div class="product-admin-page">
    <section class="product-hero">
        <span class="product-topline"><i class="bi bi-pencil-square"></i> Product Management</span>
        <div class="product-hero-grid">
            <div>
                <h1>แก้ไขสินค้า</h1>
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

        <form action="update_product.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">

            <div class="product-form-grid">
                <div>
                    <div class="product-section">
                        <h2 class="product-section-title"><i class="bi bi-card-text"></i> ข้อมูลสินค้า</h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="product_name" class="form-label">ชื่อสินค้า <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="product_name" name="product_name" value="<?= htmlspecialchars((string) $product['product_name'], ENT_QUOTES, 'UTF-8') ?>" required>
                                <div class="invalid-feedback">กรุณากรอกชื่อสินค้า</div>
                            </div>
                            <div class="col-md-6">
                                <label for="category" class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="category" name="category" value="<?= htmlspecialchars((string) $product['category'], ENT_QUOTES, 'UTF-8') ?>" required>
                                <div class="invalid-feedback">กรุณาระบุหมวดหมู่</div>
                            </div>
                            <div class="col-12">
                                <label for="product_description" class="form-label">รายละเอียดสินค้า</label>
                                <textarea class="form-control" id="product_description" name="product_description" rows="5"><?= htmlspecialchars((string) ($product['product_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="product-section">
                        <h2 class="product-section-title"><i class="bi bi-cash-coin"></i> ราคาและหน่วยขาย</h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="<?= htmlspecialchars((string) $product['price'], ENT_QUOTES, 'UTF-8') ?>" required>
                                <div class="invalid-feedback">กรุณาระบุราคา</div>
                            </div>
                            <div class="col-md-6">
                                <label for="unit" class="form-label">หน่วย <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="unit" name="unit" value="<?= htmlspecialchars((string) $product['unit'], ENT_QUOTES, 'UTF-8') ?>" required>
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
                                    <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>เปิดขาย</option>
                                    <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>ปิดขาย</option>
                                </select>
                                <div class="product-help mt-2">สินค้าที่ปิดขายจะไม่แสดงในหน้าร้าน</div>
                            </div>
                            <div class="col-12">
                                <label for="seasonal" class="form-label">สินค้าตามฤดูกาล <span class="text-danger">*</span></label>
                                <select class="form-select" id="seasonal" name="seasonal" required>
                                    <option value="1" <?= (int) $product['seasonal'] === 1 ? 'selected' : '' ?>>ใช่</option>
                                    <option value="0" <?= (int) $product['seasonal'] === 0 ? 'selected' : '' ?>>ไม่ใช่</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="product-section">
                        <h2 class="product-section-title"><i class="bi bi-image"></i> รูปภาพสินค้า</h2>

                        <?php if (!empty($product['product_image'])): ?>
                            <div class="image-card mb-3">
                                <img src="uploads/products/<?= htmlspecialchars((string) $product['product_image'], ENT_QUOTES, 'UTF-8') ?>" alt="รูปสินค้าปัจจุบัน">
                                <div class="image-card-body">
                                    <div class="image-card-title"><?= htmlspecialchars((string) $product['product_image'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="image-card-meta">รูปภาพปัจจุบัน</div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="delete_image" id="deleteImageCheck" value="1">
                                        <label class="form-check-label text-danger fw-bold" for="deleteImageCheck">ลบรูปภาพนี้</label>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="product-alert mb-3">
                                ยังไม่มีรูปภาพสินค้า
                            </div>
                        <?php endif; ?>

                        <div class="upload-zone" id="imageUploadBox" role="button" tabindex="0">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <strong>คลิกหรือลากไฟล์เพื่อเปลี่ยนรูป</strong>
                            <span>รองรับ JPG, PNG, GIF ขนาดไม่เกิน 2MB</span>
                        </div>
                        <input type="file" class="d-none" name="product_image" id="product_image" accept="image/*">
                        <div id="image_preview" class="mt-3"></div>
                        <div class="product-help mt-2">ถ้าไม่เลือกไฟล์ใหม่ ระบบจะใช้รูปเดิม</div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="manage_product.php" class="product-btn product-btn-secondary">
                    <i class="bi bi-arrow-left-circle"></i> ยกเลิก
                </a>
                <button type="submit" class="product-btn product-btn-primary">
                    <i class="bi bi-check-circle"></i> บันทึกการแก้ไข
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
                    <img src="${event.target.result}" alt="ตัวอย่างรูปสินค้าใหม่">
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
