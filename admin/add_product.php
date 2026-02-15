<?php
require_once 'auth.php';

// ตรวจสอบ session และสร้าง CSRF Token
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสินค้าใหม่ | Senior Friendly UX</title>
    <!-- Bootstrap 5 ใช้เฉพาะ Grid และ Utilities ลดความขัดแย้ง -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ฟอนต์ ตัวโต ชัด -->
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* -------------------------------
           SENIOR FRIENDLY UX — สองฝั่ง ชัดเจน ใหญ่ อ่านง่าย
        ------------------------------- */
        :root {
            --senior-primary: #0a4b7a;      /* น้ำเงินเข้ม คอนทราสสูง */
            --senior-primary-dark: #063357;
            --senior-success: #1e7e34;
            --senior-danger: #b22222;
            --senior-text: #1e1e1e;
            --senior-text-soft: #2c3e50;
            --senior-border: #3a4e62;
            --senior-bg: #ffffff;
            --senior-bg-soft: #f2f6fc;
            --senior-input-bg: #ffffff;
            --senior-radius: 20px;          /* มนมากขึ้นแต่ไม่เล็ก */
            --senior-font-size: 1.25rem;    /* 20px */
            --senior-label-size: 1.4rem;    /* 22.4px */
            --senior-title-size: 2rem;      /* 32px */
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'IBM Plex Sans Thai', sans-serif;
            background-color: #e6edf5;      /* พื้นหลังอ่อนตา */
            color: var(--senior-text);
            font-size: var(--senior-font-size);
            line-height: 1.5;
        }

        /* ===== SIDEBAR (คงเดิม แต่ให้ main content เว้นระยะพอดี) ===== */
        .main-content {
            margin-left: 260px;
            padding: 2.5rem 3rem;
            transition: margin-left 0.2s;
        }

        /* ===== CARD FORM — ใหญ่ สว่าง ขอบชัด ===== */
        .card-form {
            background: var(--senior-bg);
            border-radius: var(--senior-radius);
            padding: 3rem 3.5rem;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.08);
            border: 2px solid #cbd5e1;      /* ขอบชัด */
            max-width: 1600px;
            margin: 0 auto;
        }

        /* ===== HEADER ===== */
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 4px solid var(--senior-primary);
        }
        .page-title {
            font-weight: 700;
            color: var(--senior-primary);
            font-size: var(--senior-title-size);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        .page-title i {
            font-size: 2.5rem;
            color: var(--senior-primary);
        }
        .page-subtitle {
            color: var(--senior-text-soft);
            font-size: 1.3rem;
            font-weight: 400;
            margin-top: 0.6rem;
        }

        /* ===== SECTION TITLE — ใหญ่, หัวชัด ===== */
        .section-title {
            font-size: 1.8rem;      /* 28.8px */
            font-weight: 700;
            color: var(--senior-primary);
            display: flex;
            align-items: center;
            gap: 15px;
            padding-bottom: 1rem;
            border-bottom: 4px solid #d0e0f0;
            margin-bottom: 2.2rem;
        }
        .section-title i {
            font-size: 2.2rem;
            color: var(--senior-primary);
        }

        /* ===== FORM LABEL — ใหญ่, หนา, ดำ ===== */
        .form-label {
            font-weight: 700;
            color: #000;
            font-size: var(--senior-label-size);
            margin-bottom: 0.7rem;
            letter-spacing: 0.3px;
        }

        /* ===== INPUT, SELECT, TEXTAREA — ตัวโต สบายตา ===== */
        .form-control, .form-select {
            border-radius: 18px;
            padding: 16px 22px;
            font-size: 1.2rem;      /* 19.2px */
            border: 2.5px solid #9aa6b2;
            background-color: var(--senior-input-bg);
            color: #0a0a0a;
            box-shadow: inset 0 3px 6px rgba(0,0,0,0.02);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--senior-primary);
            box-shadow: 0 0 0 5px rgba(10, 75, 122, 0.25);
            background-color: #fffefc;
        }
        /* placeholder จางแต่อ่านออก */
        .form-control::placeholder {
            color: #5e6f7e;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        /* ===== BUTTONS — ใหญ่ กดง่าย ===== */
        .btn {
            padding: 16px 36px;
            font-weight: 600;
            border-radius: 60px;      /* ปุ่มกลมมาก กดง่าย */
            border: 3px solid transparent;
            font-size: 1.3rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            transition: all 0.2s;
            letter-spacing: 0.8px;
        }
        .btn-primary {
            background-color: var(--senior-primary);
            border-color: #0e2f4a;
            color: white;
            box-shadow: 0 6px 0 #052136;
        }
        .btn-primary:hover {
            background-color: var(--senior-primary-dark);
            transform: translateY(-4px);
            box-shadow: 0 10px 0 #031a2b;
        }
        .btn-secondary {
            background-color: white;
            color: #0a4b7a;
            border: 3px solid #0a4b7a;
            box-shadow: 0 6px 0 #b0c4ce;
        }
        .btn-secondary:hover {
            background-color: #ecf3fa;
            transform: translateY(-4px);
            box-shadow: 0 10px 0 #8faebf;
        }

        /* ===== IMAGE UPLOAD — กล่องใหญ่ ชัด ===== */
        .image-upload-box {
            border: 4px dashed #2c5f7e;
            border-radius: 30px;
            padding: 2.8rem 1.8rem;
            text-align: center;
            background-color: #fafdff;
            cursor: pointer;
            transition: 0.2s;
        }
        .image-upload-box:hover {
            border-color: var(--senior-primary);
            background-color: #e8f0fe;
            border-width: 5px;
        }
        .upload-icon i {
            font-size: 4.8rem;
            color: var(--senior-primary);
        }
        .image-upload-box p {
            font-weight: 700;
            font-size: 1.5rem;
            color: #0a4b7a;
            margin-top: 0.8rem;
            margin-bottom: 0.3rem;
        }
        .image-upload-box small {
            font-size: 1.1rem;
            color: #2e4755;
        }

        /* ===== PREVIEW การ์ดใหญ่ ===== */
        .preview-container {
            background: #fefefe;
            border-radius: 24px;
            padding: 1.8rem;
            border: 3px solid #9bb7d4;
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-top: 1.5rem;
        }
        .preview-image {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 22px;
            border: 4px solid white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .preview-details {
            flex: 1;
        }
        .preview-filename {
            font-weight: 700;
            font-size: 1.4rem;
            color: #0a0a0a;
            margin-bottom: 0.3rem;
        }
        .preview-filesize {
            color: #2a4055;
            font-size: 1.1rem;
        }
        .remove-image {
            background: #fce4e4;
            color: #b22222;
            border-radius: 50px;
            padding: 0.8rem 1.8rem;
            font-size: 1.2rem;
            font-weight: 600;
            border: 2px solid #b22222;
        }
        .remove-image:hover {
            background: #b22222;
            color: white;
        }

        /* ===== VALIDATION — ชัดเจน เข้าใจง่าย ===== */
        .was-validated .form-control:invalid,
        .was-validated .form-select:invalid {
            border-color: #b22222;
            border-width: 4px;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23b22222'%3e%3cpath d='M8 2a6 6 0 1 0 0 12A6 6 0 0 0 8 2zm0 1a5 5 0 1 1 0 10A5 5 0 0 1 8 3zM7 6h2v4H7V6zm0 5h2v2H7v-2z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 1.8rem;
        }
        .was-validated .form-control:valid,
        .was-validated .form-select:valid {
            border-color: #1e7e34;
            border-width: 4px;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%231e7e34'%3e%3cpath d='M8 2a6 6 0 1 0 0 12A6 6 0 0 0 8 2zm0 1a5 5 0 1 1 0 10A5 5 0 0 1 8 3zm-1.5 7L4 7.8 5.2 6.6 6.5 7.9 10.8 3.6 12 4.8 6.5 10z'/%3e%3c/svg%3e");
            background-size: 1.8rem;
            background-position: right 20px center;
        }
        .invalid-feedback, .valid-feedback {
            font-size: 1.1rem;
            font-weight: 500;
            margin-top: 0.5rem;
        }

        /* ===== เส้นแบ่งสองฝั่งให้เห็นชัด (จอใหญ่) ===== */
        @media (min-width: 1200px) {
            .col-lg-7 {
                border-right: 4px solid #cbdbe9;
                padding-right: 3rem;
            }
        }

        /* ===== ปรับสำหรับมือถือ ===== */
        @media (max-width: 1199px) {
            .main-content {
                margin-left: 80px;
                padding: 2rem 1.8rem;
            }
            .card-form {
                padding: 2rem 1.8rem;
            }
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1.2rem;
            }
            .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="card-form">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="bi bi-plus-square-fill"></i>
                    เพิ่มสินค้าใหม่
                </h1>
                <p class="page-subtitle">กรอกข้อมูลให้ครบ ทุกช่องจำเป็นต้องใส่</p>
            </div>

            <form action="save_product.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <!-- สองฝั่งชัดเจน ซ้าย 7 ขวา 5 (สำหรับคนแก่ เห็นส่วนต่างทันที) -->
                <div class="row g-5">
                    <!-- LEFT COLUMN : รายละเอียดสินค้า, ราคา -->
                    <div class="col-xl-7 col-lg-7">
                        <div class="form-section">
                            <h3 class="section-title"><i class="bi bi-box"></i> ข้อมูลสินค้า</h3>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="product_name" class="form-label">ชื่อสินค้า <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="product_name" name="product_name" 
                                           placeholder="เช่น กล้วยอบเนย สูตรโบราณ" required>
                                    <div class="invalid-feedback">กรุณากรอกชื่อสินค้า</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="category" class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="category" name="category" 
                                           placeholder="ผลไม้แปรรูป/ขนมขบเคี้ยว" required>
                                    <div class="invalid-feedback">ระบุหมวดหมู่สินค้า</div>
                                </div>
                                <div class="col-12">
                                    <label for="product_description" class="form-label">รายละเอียด <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="product_description" name="product_description" 
                                              rows="5" placeholder="ส่วนผสม, วิธีเก็บ, จุดเด่น..." required></textarea>
                                    <div class="invalid-feedback">ใส่รายละเอียดสินค้า</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section mt-5">
                            <h3 class="section-title"><i class="bi bi-tag"></i> ราคาและหน่วยสินค้า</h3>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="price" class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="price" name="price" 
                                           min="0" step="0.01" placeholder="49.50" required>
                                    <div class="invalid-feedback">ระบุราคาสินค้า</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="unit" class="form-label">หน่วย <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="unit" name="unit" 
                                           placeholder="แพ็ค / กล่อง / กิโลกรัม" required>
                                    <div class="invalid-feedback">ระบุหน่วยขาย</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN : สถานะ, รูปภาพ -->
                    <div class="col-xl-5 col-lg-5">
                        <div class="form-section">
                            <h3 class="section-title"><i class="bi bi-toggle-on"></i> การตั้งค่าสถานะสินค้า</h3>
                            <div class="row g-4">
                                <div class="col-12">
                                    <label for="status" class="form-label">สถานะสินค้า</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active" selected>🟢 เปิดขาย (พร้อมส่ง)</option>
                                        <option value="inactive">🔴 ปิดขาย (หยุดพัก)</option>
                                    </select>
                                    <div class="invalid-feedback">เลือกสถานะสินค้า</div>
                                </div>
                                <div class="col-12">
                                    <label for="seasonal" class="form-label">สินค้าตามฤดู</label>
                                    <select class="form-select" id="seasonal" name="seasonal" required>
                                        <option value="1">✅ ใช่ สินค้าตามฤดูกาล</option>
                                        <option value="0" selected>❌ ไม่ใช่</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section mt-5">
                            <h3 class="section-title"><i class="bi bi-image"></i> รูปภาพสินค้า</h3>
                            <div class="image-upload-box" id="imageUploadBox" role="button" tabindex="0" aria-label="คลิกเพื่อเลือกรูปภาพ">
                                <div class="upload-icon"><i class="bi bi-cloud-upload"></i></div>
                                <p>คลิกหรือลากไฟล์วางที่นี่</p>
                                <small>รองรับ .jpg, .png, .gif • ขนาดไม่เกิน 2MB</small>
                            </div>
                            <input type="file" class="d-none" name="product_image" id="product_image" accept="image/*" required>
                            <!-- พรีวิวภาพ -->
                            <div id="image_preview" class="mt-3"></div>
                            <div class="invalid-feedback d-block" id="image-feedback" style="display: none !important;"></div>
                        </div>
                    </div>
                </div>

                <!-- ปุ่มขนาดใหญ่ แยกกันชัด -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mt-5 pt-4 border-top border-4" style="border-color: #b0c8dd !important;">
                    <a href="manage_product.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> กลับหน้ารายการ
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> บันทึกสินค้า
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            'use strict';

            // --- Senior friendly drag & drop + preview ---
            const uploadBox = document.getElementById('imageUploadBox');
            const fileInput = document.getElementById('product_image');
            const previewDiv = document.getElementById('image_preview');

            // เปิด file dialog เมื่อคลิก
            uploadBox.addEventListener('click', () => fileInput.click());

            // ป้องกัน default drag/drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadBox.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                });
            });

            // สไตล์ขณะลากผ่าน
            uploadBox.addEventListener('dragenter', () => uploadBox.style.backgroundColor = '#d9eafb');
            uploadBox.addEventListener('dragover', () => uploadBox.style.backgroundColor = '#d9eafb');
            uploadBox.addEventListener('dragleave', () => uploadBox.style.backgroundColor = '#fafdff');
            uploadBox.addEventListener('drop', () => uploadBox.style.backgroundColor = '#fafdff');

            // drop จัดการไฟล์
            uploadBox.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files.length) {
                    fileInput.files = files;
                    handleImagePreview(files[0]);
                }
            });

            fileInput.addEventListener('change', function() {
                if (this.files.length) handleImagePreview(this.files[0]);
            });

            function handleImagePreview(file) {
                previewDiv.innerHTML = '';

                if (!file) return;

                // ตรวจสอบชนิดไฟล์
                if (!file.type.startsWith('image/')) {
                    showImageError('กรุณาเลือกไฟล์รูปภาพเท่านั้น');
                    fileInput.value = '';
                    return;
                }

                // ขนาดไม่เกิน 2MB
                if (file.size > 2 * 1024 * 1024) {
                    showImageError('ไฟล์ใหญ่เกิน 2MB กรุณาเลือกใหม่');
                    fileInput.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    const card = document.createElement('div');
                    card.className = 'preview-container';

                    const sizeKB = (file.size / 1024).toFixed(1);
                    card.innerHTML = `
                        <img src="${e.target.result}" class="preview-image" alt="ตัวอย่างสินค้า">
                        <div class="preview-details">
                            <div class="preview-filename"><i class="bi bi-file-earmark-image"></i> ${file.name}</div>
                            <div class="preview-filesize">ขนาด ${sizeKB} KB</div>
                        </div>
                        <button type="button" class="remove-image btn" id="removeImageBtn">
                            <i class="bi bi-trash"></i> ลบภาพ
                        </button>
                    `;
                    previewDiv.appendChild(card);

                    document.getElementById('removeImageBtn').addEventListener('click', function(e) {
                        e.stopPropagation();
                        fileInput.value = '';
                        previewDiv.innerHTML = '';
                    });
                };
                reader.readAsDataURL(file);
            }

            function showImageError(msg) {
                previewDiv.innerHTML = `<div class="alert alert-danger py-3 px-4 rounded-4" style="font-size:1.2rem;">⚠️ ${msg}</div>`;
            }

            // --- Bootstrap validation + ปรับข้อความให้ใหญ่ ---
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
</body>
</html>