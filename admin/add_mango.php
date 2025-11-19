<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสายพันธุ์มะม่วง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --text: #2d3748;
            --text-light: #718096;
            --white: #ffffff;
            --border: #e2e8f0;
            --light-bg: #f8f9fa;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            color: var(--text);
            min-height: 100vh;
        }

        /* Animation for page load */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-form {
            background-color: var(--white);
            padding: 30px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            animation: fadeInUp 0.6s ease-out;
            border: 1px solid var(--border);
        }

        /* Header styling */
        .page-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
            animation: fadeInUp 0.8s ease-out;
        }

        .page-title {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.8rem;
        }

        .page-subtitle {
            color: var(--text-light);
            font-weight: 400;
        }

        /* Section styling */
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 12px;
            background-color: var(--light-bg);
            transition: all 0.3s ease;
            animation: fadeInUp 0.8s ease-out;
            animation-fill-mode: both;
        }

        .form-section:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        }

        .section-title {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            font-size: 1.2rem;
        }

        /* Form controls */
        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }

        /* Button styling */
        .btn {
            padding: 12px 24px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
            border-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
            transform: translateY(-2px);
        }

        /* Image preview styling */
        .img-preview-container {
            position: relative;
            margin-bottom: 15px;
        }

        .img-preview {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            background-color: var(--light-bg);
        }

        .img-preview:hover {
            transform: scale(1.03);
        }

        .upload-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text);
        }

        /* Checkbox styling */
        .form-check {
            margin-bottom: 10px;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        /* Animation for image preview */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .img-preview[src="#"] {
            display: none;
        }

        .img-preview:not([src="#"]) {
            animation: fadeIn 0.5s ease;
        }

        /* Loading animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(67, 97, 238, 0.2);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Success message */
        .success-message {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.4s ease;
            z-index: 1000;
        }

        .success-message.show {
            transform: translateY(0);
            opacity: 1;
        }

        /* Stagger animation for form sections */
        .form-section:nth-child(1) { animation-delay: 0.1s; }
        .form-section:nth-child(2) { animation-delay: 0.2s; }
        .form-section:nth-child(3) { animation-delay: 0.3s; }
        .form-section:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>

<body>
    <?php
    require_once 'auth.php';
    // The auth.php file is assumed to handle session and authentication logic.
    ?>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Success Message -->
    <div class="success-message" id="successMessage">
        <i class="fas fa-check-circle me-2"></i>บันทึกข้อมูลสำเร็จ
    </div>

    <div class="container my-5">
        <div class="card-form">
            <div class="page-header">
                <h1 class="page-title">เพิ่มสายพันธุ์มะม่วง</h1>
                <p class="page-subtitle">กรอกข้อมูลสายพันธุ์มะม่วงใหม่ของคุณ</p>
            </div>

            <form action="save_mango.php" method="POST" enctype="multipart/form-data" id="mangoForm">

                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-info-circle"></i>ข้อมูลจำเพาะ</h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="mango_name" class="form-label">ชื่อสายพันธุ์</label>
                            <input type="text" class="form-control" id="mango_name" name="mango_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="scientific_name" class="form-label">ชื่อภาษาอังกฤษ</label>
                            <input type="text" class="form-control" id="scientific_name" name="scientific_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="local_name" class="form-label">ชื่อท้องถิ่น</label>
                            <input type="text" class="form-control" id="local_name" name="local_name" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-leaf"></i>ลักษณะสัณฐานวิทยา</h3>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">ลักษณะสัณฐานวิทยา</label>
                            <input type="text" class="form-control mb-2" name="morphology_stem" placeholder="ลำต้น (เช่น สูง, ทรงพุ่ม)" required>
                            <input type="text" class="form-control mb-2" name="morphology_fruit" placeholder="ผล (เช่น รูปทรง, ขนาด)" required>
                            <input type="text" class="form-control" name="morphology_leaf" placeholder="ใบ (เช่น สี, รูปแบบ)" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-images"></i>รูปภาพประกอบ</h3>
                    <div class="row">
                        <div class="col-md-4 col-sm-6 mb-4">
                            <label class="upload-label">รูปผลมะม่วง</label>
                            <div class="img-preview-container">
                                <img id="fruit_preview" src="#" alt="preview" class="img-preview">
                            </div>
                            <input type="file" class="form-control" name="fruit_image" accept="image/*" required onchange="previewImage(event, 'fruit_preview')">
                        </div>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <label class="upload-label">รูปต้นมะม่วง</label>
                            <div class="img-preview-container">
                                <img id="tree_preview" src="#" alt="preview" class="img-preview">
                            </div>
                            <input type="file" class="form-control" name="tree_image" accept="image/*" required onchange="previewImage(event, 'tree_preview')">
                        </div>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <label class="upload-label">รูปใบมะม่วง</label>
                            <div class="img-preview-container">
                                <img id="leaf_preview" src="#" alt="preview" class="img-preview">
                            </div>
                            <input type="file" class="form-control" name="leaf_image" accept="image/*" required onchange="previewImage(event, 'leaf_preview')">
                        </div>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <label class="upload-label">รูปดอกมะม่วง</label>
                            <div class="img-preview-container">
                                <img id="flower_preview" src="#" alt="preview" class="img-preview">
                            </div>
                            <input type="file" class="form-control" name="flower_image" accept="image/*" required onchange="previewImage(event, 'flower_preview')">
                        </div>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <label class="upload-label">รูปกิ่งมะม่วง</label>
                            <div class="img-preview-container">
                                <img id="branch_preview" src="#" alt="preview" class="img-preview">
                            </div>
                            <input type="file" class="form-control" name="branch_image" accept="image/*" required onchange="previewImage(event, 'branch_preview')">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-seedling"></i>การเพาะปลูกและการใช้ประโยชน์</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="propagation_method" class="form-label">การขยายพันธุ์</label>
                                <input type="text" class="form-control" id="propagation_method" name="propagation_method" required>
                            </div>
                            <div class="mb-3">
                                <label for="soil_characteristics" class="form-label">ลักษณะดิน</label>
                                <input type="text" class="form-control" id="soil_characteristics" name="soil_characteristics" required>
                            </div>
                            <div class="mb-3">
                                <label for="planting_period" class="form-label">ระยะเวลาเพาะปลูก</label>
                                <input type="text" class="form-control" id="planting_period" name="planting_period" required>
                            </div>
                            <div class="mb-3">
                                <label for="harvest_season" class="form-label">ช่วงฤดูกาลเก็บเกี่ยว</label>
                                <input type="text" class="form-control" id="harvest_season" name="harvest_season" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">การแปรรูป</label><br>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="proc_kawan" name="processing_methods[]" value="กวน">
                                    <label class="form-check-label" for="proc_kawan">กวน</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="proc_dong" name="processing_methods[]" value="ดอง">
                                    <label class="form-check-label" for="proc_dong">ดอง</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="proc_chaim" name="processing_methods[]" value="แช่อิ่ม">
                                    <label class="form-check-label" for="proc_chaim">แช่อิ่ม</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="proc_fresh" name="processing_methods[]" value="นิยมรับประทานสด">
                                    <label class="form-check-label" for="proc_fresh">นิยมรับประทานสด</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="mango_category" class="form-label">หมวดมะม่วง</label>
                                <select class="form-select" id="mango_category" name="mango_category" required>
                                    <option value="" disabled selected>เลือกหมวดหมู่</option>
                                    <option value="เชิงพาณิชย์">เชิงพาณิชย์</option>
                                    <option value="เชิงอนุรักษ์">เชิงอนุรักษ์</option>
                                    <option value="บริโภคในครัวเรือน">บริโภคในครัวเรือน</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-start mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>บันทึก
                    </button>
                    <a href="manage_mango.php" class="btn btn-secondary ms-2">
                        <i class="fas fa-arrow-left me-2"></i>กลับ
                    </a>
                </div>

            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview function
        function previewImage(event, previewId) {
            const input = event.target;
            const preview = document.getElementById(previewId);

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        }

        // Form submission with loading animation
        document.getElementById('mangoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading overlay
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.classList.add('active');
            
            // Simulate form submission (in a real scenario, this would be an actual form submission)
            setTimeout(function() {
                // Hide loading overlay
                loadingOverlay.classList.remove('active');
                
                // Show success message
                const successMessage = document.getElementById('successMessage');
                successMessage.classList.add('show');
                
                // Hide success message after 3 seconds
                setTimeout(function() {
                    successMessage.classList.remove('show');
                    
                    // In a real scenario, you would submit the form here
                    // For demonstration, we'll just reset the form
                    document.getElementById('mangoForm').reset();
                    
                    // Reset image previews
                    const previews = document.querySelectorAll('.img-preview');
                    previews.forEach(preview => {
                        preview.src = '#';
                        preview.style.display = 'none';
                    });
                    
                }, 3000);
            }, 2000);
        });

        // Add animation to form sections on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const formSections = document.querySelectorAll('.form-section');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            formSections.forEach(section => {
                section.style.opacity = 0;
                section.style.transform = 'translateY(20px)';
                section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(section);
            });
        });
    </script>
</body>
</html>