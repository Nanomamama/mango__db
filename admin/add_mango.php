<?php
require_once 'auth.php';
// The auth.php file is assumed to handle session and authentication logic.
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสายพันธุ์มะม่วง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Global font setting for consistency */
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f8f9fa; /* Light gray background for a clean look */
        }

        /* Styling for the main content container (optional: adds a white background) */
        .card-form {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Subtle shadow for depth */
        }

        /* Custom style for the input fields for a modern feel */
        .form-control:focus, .form-select:focus {
            border-color: #28a745; /* A primary color (e.g., green for mangoes/nature) */
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
        }

        /* Improved button styling with focus on transition and color */
        .btn {
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .btn-primary {
            background-color: #28a745; /* Green for 'Save' */
            border-color: #28a745;
        }
        .btn-primary:hover {
            background-color: #1e7e34;
            border-color: #1e7e34;
            transform: translateY(-2px); /* Lift effect */
        }

        .btn-secondary {
            background-color: #6c757d; /* Standard gray for 'Back' */
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
            transform: translateY(-2px); /* Lift effect */
        }

        /* Styling for the image previews */
        .img-preview {
            width: 100%;
            height: 120px; /* Slightly taller for better view */
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ced4da; /* Subtle border */
        }

        /* Grouping for morphology inputs */
        .morphology-group input {
            margin-top: 8px; /* Consistent spacing */
        }

        /* Highlight the heading */
        h2 {
            font-weight: 600;
            color: #198754; /* Match primary color */
            margin-bottom: 25px;
        }
    </style>
</head>

<body>

    <div class="container my-5">
        <div class="card-form">
            <h2>➕ เพิ่มสายพันธุ์มะม่วง</h2>

            <form action="save_mango.php" method="POST" enctype="multipart/form-data">

                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="text-secondary border-bottom pb-2 mb-3">ข้อมูลจำเพาะ</h4>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="mango_name" class="form-label">ชื่อสายพันธุ์</label>
                        <input type="text" class="form-control" id="mango_name" name="mango_name" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="scientific_name" class="form-label">ชื่อวิทยาศาสตร์</label>
                        <input type="text" class="form-control" id="scientific_name" name="scientific_name" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="local_name" class="form-label">ชื่อท้องถิ่น</label>
                        <input type="text" class="form-control" id="local_name" name="local_name" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="text-secondary border-bottom pb-2 mb-3">ลักษณะสัณฐานวิทยา</h4>
                    </div>
                    <div class="col-md-12 mb-3 morphology-group">
                        <label class="form-label">ลักษณะสัณฐานวิทยา</label>
                        <input type="text" class="form-control" name="morphology_stem" placeholder="ลำต้น (เช่น สูง, ทรงพุ่ม)" required>
                        <input type="text" class="form-control" name="morphology_fruit" placeholder="ผล (เช่น รูปทรง, ขนาด)" required>
                        <input type="text" class="form-control" name="morphology_leaf" placeholder="ใบ (เช่น สี, รูปแบบ)" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="text-secondary border-bottom pb-2 mb-3">รูปภาพประกอบ</h4>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <label class="form-label">รูปผลมะม่วง</label>
                        <img id="fruit_preview" src="#" alt="preview" class="img-thumbnail img-preview mb-2" style="display: none;">
                        <input type="file" class="form-control" name="fruit_image" accept="image/*" required onchange="previewImage(event, 'fruit_preview')">
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <label class="form-label">รูปต้นมะม่วง</label>
                        <img id="tree_preview" src="#" alt="preview" class="img-thumbnail img-preview mb-2" style="display: none;">
                        <input type="file" class="form-control" name="tree_image" accept="image/*" required onchange="previewImage(event, 'tree_preview')">
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <label class="form-label">รูปใบมะม่วง</label>
                        <img id="leaf_preview" src="#" alt="preview" class="img-thumbnail img-preview mb-2" style="display: none;">
                        <input type="file" class="form-control" name="leaf_image" accept="image/*" required onchange="previewImage(event, 'leaf_preview')">
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <label class="form-label">รูปดอกมะม่วง</label>
                        <img id="flower_preview" src="#" alt="preview" class="img-thumbnail img-preview mb-2" style="display: none;">
                        <input type="file" class="form-control" name="flower_image" accept="image/*" required onchange="previewImage(event, 'flower_preview')">
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <label class="form-label">รูปกิ่งมะม่วง</label>
                        <img id="branch_preview" src="#" alt="preview" class="img-thumbnail img-preview mb-2" style="display: none;">
                        <input type="file" class="form-control" name="branch_image" accept="image/*" required onchange="previewImage(event, 'branch_preview')">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="text-secondary border-bottom pb-2 mb-3">การเพาะปลูกและการใช้ประโยชน์</h4>
                    </div>
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
                                <option value="" disabled selected>เลือกหมวดหมู่</option> <option value="เชิงพาณิชย์">เชิงพาณิชย์</option>
                                <option value="เชิงอนุรักษ์">เชิงอนุรักษ์</option>
                                <option value="บริโภคในครัวเรือน">บริโภคในครัวเรือน</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-start mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary me-2">💾 บันทึก</button>
                    <a href="manage_mango.php" class="btn btn-secondary">🔙 กลับ</a>
                </div>

            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
    </script>

</body>

</html>