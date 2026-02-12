<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

// ตรวจสอบ session และสร้าง CSRF Token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ดึงข้อมูลสินค้าตาม id
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $product = $result->fetch_assoc();
    } else {
        echo "<script>alert('ไม่พบสินค้าที่ต้องการแก้ไข'); window.location='manage_product.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('ไม่มี ID สินค้า'); window.location='manage_product.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสินค้า | Modern Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* ===== GLOBAL VARIABLES ===== */
        :root {
            --primary: #4361ee;
            --primary-light: #6c8cff;
            --primary-soft: #eef2ff;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --text-heading: #1e293b;
            --text-body: #334155;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
            --bg-light: #f8fafc;
            --card-bg: rgba(255, 255, 255, 0.9);
            --shadow-sm: 0 8px 20px rgba(0,0,0,0.02);
            --shadow-md: 0 12px 30px rgba(0,0,0,0.05);
            --shadow-lg: 0 20px 40px rgba(0,0,0,0.08);
            --glass-border: 1px solid rgba(255,255,255,0.5);
            --border-radius-card: 28px;
            --border-radius-element: 14px;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: radial-gradient(circle at 10% 30%, #f1f5f9 0%, #e6ecf4 100%);
            color: var(--text-body);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ADJUSTMENT ===== */
        .main-content {
            margin-left: 260px;
            padding: 2rem 2.5rem;
            transition: margin-left 0.25s ease;
        }

        /* ===== MAIN CARD — WIDE & MODERN ===== */
        .card-form {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: var(--glass-border);
            border-radius: var(--border-radius-card);
            padding: 2.8rem 3rem;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            border: 1px solid rgba(255,255,255,0.8);
        }

        /* ===== HEADER ===== */
        .page-header {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.8rem;
            border-bottom: 2px dashed rgba(67, 97, 238, 0.15);
        }
        .page-title {
            font-weight: 700;
            color: var(--primary);
            font-size: 2.2rem;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .page-subtitle {
            color: var(--text-muted);
            font-size: 1.1rem;
            font-weight: 300;
            margin-top: 0.25rem;
        }

        /* ===== SECTION TITLE ===== */
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid var(--primary-soft);
            margin-bottom: 1.8rem;
        }
        .section-title i {
            font-size: 1.5rem;
            background: linear-gradient(145deg, var(--primary), #304d9c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ===== FORM CONTROLS ===== */
        .form-label {
            font-weight: 500;
            color: var(--text-heading);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }
        .form-control, .form-select {
            border-radius: var(--border-radius-element);
            padding: 12px 18px;
            border: 1.5px solid var(--border-light);
            background-color: white;
            transition: all 0.2s ease;
            font-size: 1rem;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.01);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.12);
            background-color: white;
        }

        /* ===== BUTTONS ===== */
        .btn {
            padding: 12px 28px;
            font-weight: 500;
            border-radius: 40px;
            transition: all 0.25s cubic-bezier(0.02, 0.88, 0.41, 1.01);
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1rem;
            letter-spacing: 0.3px;
        }
        .btn-primary {
            background: linear-gradient(145deg, var(--primary), #3a56d4);
            color: white;
            box-shadow: 0 6px 14px rgba(67, 97, 238, 0.25);
        }
        .btn-primary:hover {
            background: linear-gradient(145deg, #3a56d4, #2a46b0);
            transform: translateY(-3px);
            box-shadow: 0 12px 20px rgba(67, 97, 238, 0.35);
        }
        .btn-secondary {
            background: white;
            color: var(--text-heading);
            border: 1.5px solid var(--border-light);
            box-shadow: none;
        }
        .btn-secondary:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            transform: translateY(-3px);
        }
        .btn-warning {
            background: linear-gradient(145deg, #f39c12, #e67e22);
            color: white;
            border: none;
        }
        .btn-warning:hover {
            background: linear-gradient(145deg, #e67e22, #d35400);
            transform: translateY(-3px);
        }

        /* ===== IMAGE UPLOAD & PREVIEW ===== */
        .image-upload-box {
            border: 2px dashed #cbd5e1;
            border-radius: 22px;
            padding: 2rem 1.8rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s;
            background: linear-gradient(145deg, #f8fafc, #f1f5f9);
            margin-bottom: 1rem;
        }
        .image-upload-box:hover {
            border-color: var(--primary);
            background: linear-gradient(145deg, #ffffff, #eef2ff);
            transform: scale(1.01);
            box-shadow: var(--shadow-md);
        }
        .image-upload-box.dragover {
            border-color: var(--primary);
            background: rgba(67, 97, 238, 0.06);
        }
        .upload-icon {
            font-size: 3.8rem;
            color: var(--primary);
            opacity: 0.8;
            margin-bottom: 0.5rem;
        }

        /* preview card */
        .preview-container {
            background: white;
            border-radius: 20px;
            padding: 1.2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-top: 0.8rem;
            animation: fadeSlide 0.4s;
        }
        .preview-image {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 16px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.05);
            border: 2px solid white;
        }
        .preview-details {
            flex: 1;
        }
        .preview-filename {
            font-weight: 600;
            color: var(--text-heading);
            margin-bottom: 0.2rem;
        }
        .preview-filesize {
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        .remove-image {
            color: var(--danger);
            background: rgba(231, 76, 60, 0.1);
            border-radius: 50px;
            padding: 0.45rem 1.2rem;
            font-size: 0.9rem;
            border: none;
            transition: all 0.2s;
        }
        .remove-image:hover {
            background: var(--danger);
            color: white;
        }

        /* current image card */
        .current-image-card {
            background: #f1f5f9;
            border-radius: 20px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1.2rem;
            border: 1px solid var(--border-light);
        }
        .current-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 14px;
            border: 2px solid white;
        }

        @keyframes fadeSlide {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1200px) {
            .main-content { margin-left: 80px; }
            .card-form { padding: 2rem; }
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1.5rem 1rem;
            }
            .card-form { padding: 1.8rem; }
            .page-title { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="card-form">
            <!-- HEADER -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="bi bi-pencil-square" style="background: none; -webkit-text-fill-color: var(--primary);"></i>
                    แก้ไขสินค้า
                </h1>
                <p class="page-subtitle">แก้ไขรายละเอียดสินค้า #<?= $product['product_id'] ?></p>
            </div>

            <form action="update_product.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <!-- ส่ง product_id ไปอัปเดต -->
                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">

                <!-- 2 คอลัมน์หลัก สัดส่วน 6 : 6 บนจอใหญ่ -->
                <div class="row g-5">
                    <!-- LEFT COLUMN -->
                    <div class="col-lg-6">
                        <!-- ข้อมูลสินค้า -->
                        <div class="form-section">
                            <h3 class="section-title"><i class="bi bi-box-seam"></i> ข้อมูลสินค้า</h3>
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label">ชื่อสินค้า <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="product_name" 
                                           value="<?= htmlspecialchars($product['product_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="category" 
                                           value="<?= htmlspecialchars($product['category']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" name="price" 
                                           value="<?= htmlspecialchars($product['price']) ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">รายละเอียดสินค้า</label>
                                    <textarea class="form-control" name="product_description" rows="5"><?= htmlspecialchars($product['product_description']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="col-lg-6">
                        <!-- หน่วยและสถานะ -->
                        <div class="form-section">
                            <h3 class="section-title"><i class="bi bi-gear"></i> หน่วยและสถานะ</h3>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">หน่วย <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="unit" 
                                           value="<?= htmlspecialchars($product['unit']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ตามฤดูกาล</label>
                                    <select name="seasonal" class="form-select">
                                        <option value="1" <?= $product['seasonal'] == 1 ? 'selected' : '' ?>>✅ ใช่</option>
                                        <option value="0" <?= $product['seasonal'] == 0 ? 'selected' : '' ?>>❌ ไม่ใช่</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">สถานะสินค้า <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="active" <?= $product['status'] == 'active' ? 'selected' : '' ?>>🟢 เปิดขาย</option>
                                        <option value="inactive" <?= $product['status'] == 'inactive' ? 'selected' : '' ?>>🔴 ปิดขาย</option>
                                    </select>
                                    <small class="text-muted">* หากปิดขาย สินค้าจะไม่แสดงหน้าร้าน</small>
                                </div>
                            </div>
                        </div>

                        <!-- รูปภาพสินค้า (แสดงรูปปัจจุบัน + อัปโหลดใหม่) -->
                        <div class="form-section mt-4">
                            <h3 class="section-title"><i class="bi bi-image"></i> รูปภาพสินค้า</h3>

                            <!-- แสดงรูปปัจจุบัน (ถ้ามี) -->
                            <?php if (!empty($product['product_image'])): ?>
                                <div class="current-image-card mb-4">
                                    <img src="uploads/products/<?= htmlspecialchars($product['product_image']) ?>" 
                                         class="current-image" alt="Current product image">
                                    <div class="flex-grow-1">
                                        <div class="fw-medium text-dark">รูปปัจจุบัน</div>
                                        <small class="text-muted"><?= htmlspecialchars($product['product_image']) ?></small>
                                    </div>
                                    <!-- ตัวเลือกให้ลบรูป? (ถ้าต้องการ) อาจเพิ่ม checkbox หรือปุ่มแยก -->
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="delete_image" id="deleteImageCheck" value="1">
                                        <label class="form-check-label text-danger" for="deleteImageCheck">
                                            <i class="bi bi-trash3"></i> ลบรูป
                                        </label>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-3"><i class="bi bi-info-circle"></i> ยังไม่มีรูปภาพสินค้า</p>
                            <?php endif; ?>

                            <!-- กล่องอัปโหลดรูปใหม่ -->
                            <div class="image-upload-box" id="imageUploadBox">
                                <div class="upload-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                                <p class="fw-medium mb-1">คลิกหรือลากไฟล์เพื่อเปลี่ยนรูปภาพ</p>
                                <small class="text-muted">รองรับ .jpg, .png, .gif · ขนาดไม่เกิน 2MB</small>
                            </div>
                            <input type="file" class="d-none" name="product_image" id="product_image" accept="image/*">

                            <!-- พรีวิวรูปใหม่ (ซ่อนถ้ายังไม่เลือกไฟล์) -->
                            <div id="image_preview" class="mt-2"></div>
                            <small class="text-muted">* หากไม่เลือกรูปใหม่ ระบบจะใช้รูปเดิม</small>
                        </div>
                    </div>
                </div>

                <!-- BUTTONS : กลับ และ บันทึก -->
                <div class="d-flex justify-content-between align-items-center mt-5 pt-4 border-top border-2" style="border-color: rgba(67,97,238,0.1) !important;">
                    <a href="manage_product.php" class="btn btn-secondary px-4">
                        <i class="bi bi-arrow-left"></i> กลับ
                    </a>
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="bi bi-check2-circle"></i> บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            'use strict';

            // ---------- IMAGE UPLOAD & PREVIEW ----------
            const uploadBox = document.getElementById('imageUploadBox');
            const fileInput = document.getElementById('product_image');
            const imagePreview = document.getElementById('image_preview');

            // คลิกที่กล่อง → เรียก file input
            uploadBox.addEventListener('click', () => fileInput.click());

            // ป้องกัน default drag & drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadBox.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            // เพิ่ม/ลบ class dragover
            ['dragenter', 'dragover'].forEach(eventName => {
                uploadBox.addEventListener(eventName, () => uploadBox.classList.add('dragover'), false);
            });
            ['dragleave', 'drop'].forEach(eventName => {
                uploadBox.addEventListener(eventName, () => uploadBox.classList.remove('dragover'), false);
            });

            // เมื่อ drop ไฟล์
            uploadBox.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files.length) {
                    fileInput.files = files;
                    handleFilePreview(files[0]);
                }
            });

            // เมื่อเลือกไฟล์จาก input
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length) {
                    handleFilePreview(e.target.files[0]);
                } else {
                    imagePreview.innerHTML = ''; // เคลียร์ preview
                }
            });

            // ฟังก์ชันสร้าง preview รูปใหม่
            function handleFilePreview(file) {
                imagePreview.innerHTML = '';

                if (!file) return;

                // ตรวจสอบชนิดไฟล์
                if (!file.type.startsWith('image/')) {
                    imagePreview.innerHTML = `<div class="alert alert-danger py-2 px-3 rounded-pill"><i class="bi bi-exclamation-triangle me-2"></i>กรุณาเลือกไฟล์รูปภาพเท่านั้น</div>`;
                    fileInput.value = '';
                    return;
                }

                // ตรวจสอบขนาด (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    imagePreview.innerHTML = `<div class="alert alert-warning py-2 px-3 rounded-pill"><i class="bi bi-exclamation-circle me-2"></i>ไฟล์มีขนาดใหญ่เกิน 2MB</div>`;
                    fileInput.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    const previewCard = document.createElement('div');
                    previewCard.className = 'preview-container';

                    const fileSizeKB = (file.size / 1024).toFixed(1);

                    previewCard.innerHTML = `
                        <img src="${e.target.result}" class="preview-image" alt="Preview">
                        <div class="preview-details">
                            <div class="preview-filename"><i class="bi bi-file-image me-1"></i>${file.name}</div>
                            <div class="preview-filesize">ขนาด ${fileSizeKB} KB</div>
                        </div>
                        <button type="button" class="remove-image btn btn-sm" id="removePreviewBtn">
                            <i class="bi bi-trash3"></i> ลบ
                        </button>
                    `;

                    imagePreview.appendChild(previewCard);

                    // ปุ่มลบรูปที่พรีวิว (เคลียร์ file input)
                    document.getElementById('removePreviewBtn').addEventListener('click', function(e) {
                        e.stopPropagation();
                        fileInput.value = '';
                        imagePreview.innerHTML = '';
                    });
                };
                reader.readAsDataURL(file);
            }

            // ---------- BOOTSTRAP VALIDATION ----------
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });

        })();
    </script>

    <!-- ปรับแต่ง Validation style เพิ่มเติม -->
    <style>
        .was-validated .form-control:invalid,
        .was-validated .form-select:invalid {
            border-color: #e74c3c;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23e74c3c'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23e74c3c' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        .was-validated .form-control:valid,
        .was-validated .form-select:valid {
            border-color: #2ecc71;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%232ecc71' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
    </style>
</body>
</html>