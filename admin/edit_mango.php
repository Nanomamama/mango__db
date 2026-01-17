<?php
require_once 'auth.php';
require_once 'db.php';

// รับ id จาก URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ดึงข้อมูลจากฐานข้อมูล
$sql = "SELECT *, mango_id AS id FROM mango_varieties WHERE mango_id = $id";
$result = $conn->query($sql);
$mango = $result->fetch_assoc();

if (!$mango) {
    echo "ไม่พบข้อมูล";
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แก้ไขสายพันธุ์มะม่วง - <?= htmlspecialchars($mango['mango_name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        :root {
            --Primary: #4e73df;
            --Success: rgb(20, 58, 44);
            --Info: #36b9cc;
            --Warning: #f6c23e;
            --Danger: #e74a3b;
            --Secondary: #858796;
            --Light: #f8f9fc;
            --Dark: #5a5c69;
            --Darkss: #000;
        }
        
        * {
            font-family: "Kanit", sans-serif;
        }
        
        body {
            background-color: var(--Light);
            color: var(--Dark);
        }
        
        .card {
            border-radius: 10px;
            border: 1px solid #e3e6f0;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--Primary) 0%, #6a8bef 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 1rem 1.25rem;
            font-weight: 600;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .card-header-success {
            background: linear-gradient(135deg, var(--Primary) 0%, #6a8bef 100%);
        }
        
        .card-header-info {
            background: linear-gradient(135deg, var(--Primary) 0%, #6a8bef 100%);
        }
        
        .card-header-warning {
            background: linear-gradient(135deg, var(--Primary) 0%, #6a8bef 100%);
        }
        
        .form-control, .form-select {
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d3e2;
            transition: all 0.3s;
            font-size: 0.875rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--Primary);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .btn {
            border-radius: 6px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.875rem;
            border: none;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--Success) 0%, #2d6a4f 100%);
            color: white;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #0f2e22 0%, #1b4332 100%);
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(20, 58, 44, 0.3);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--Primary) 0%, #6a8bef 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #3a56c4 0%, #5a7ceb 100%);
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(78, 115, 223, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, var(--Secondary) 0%, #9fa1b5 100%);
            color: white;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #6c6e80 0%, #858796 100%);
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(133, 135, 150, 0.3);
        }
        
        .btn-outline-primary {
            color: var(--Primary);
            border: 1px solid var(--Primary);
            background: transparent;
        }
        
        .btn-outline-primary:hover {
            background: var(--Primary);
            color: white;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--Primary);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #e3e6f0;
        }
        
        .nav-tabs .nav-link {
            color: var(--Secondary);
            border: none;
            border-bottom: 3px solid transparent;
            margin: 0 0.25rem;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 6px 6px 0 0;
            transition: all 0.3s;
            font-size: 0.875rem;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--Primary);
            background-color: rgba(78, 115, 223, 0.1);
            border-bottom: 3px solid var(--Primary);
        }
        
        .nav-tabs .nav-link:hover {
            color: var(--Primary);
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        .image-upload-container {
            background-color: white;
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid #e3e6f0;
            transition: all 0.3s;
        }
        
        .image-upload-container:hover {
            border-color: var(--Primary);
            box-shadow: 0 0.15rem 1rem rgba(78, 115, 223, 0.1);
        }
        
        .image-preview-container {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0.15rem 0.5rem rgba(58, 59, 69, 0.15);
            transition: all 0.3s;
            height: 140px;
            background-color: #f8f9fc;
        }
        
        .image-preview-container:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(58, 59, 69, 0.2);
        }
        
        .image-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .image-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            text-align: center;
            font-weight: 500;
        }
        
        .image-upload-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
            border: 1px solid #e3e6f0;
        }
        
        .image-upload-btn:hover {
            background: white;
            transform: scale(1.1);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }
        
        .file-input-hidden {
            display: none;
        }
        
        .mango-icon {
            color: var(--Warning);
            margin-right: 0.5rem;
        }
        
        .header-title {
            color: var(--Primary);
            font-weight: 700;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid var(--Primary);
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
        }
        
        .form-check-input:checked {
            background-color: var(--Primary);
            border-color: var(--Primary);
        }
        
        .section-divider {
            border-top: 2px dashed #e3e6f0;
            margin: 1.5rem 0;
        }
        
        .required-field::after {
            content: " *";
            color: var(--Danger);
        }
        
        .input-group-text {
            background-color: var(--Light);
            border: 1px solid #d1d3e2;
            color: var(--Primary);
            font-weight: 500;
        }
        
        .alert-info {
            background-color: rgba(54, 185, 204, 0.1);
            border-color: rgba(54, 185, 204, 0.3);
            color: var(--Info);
        }
        
        .text-primary {
            color: var(--Primary) !important;
        }
        
        .text-success {
            color: var(--Success) !important;
        }
        
        .border-primary {
            border-color: var(--Primary) !important;
        }
        
        .bg-primary-light {
            background-color: rgba(78, 115, 223, 0.1) !important;
        }
        
        .badge-primary {
            background-color: var(--Primary);
            color: white;
        }
        
        .badge-success {
            background-color: var(--Success);
            color: white;
        }
        
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-active {
            background-color: var(--Success);
        }
        
        .status-inactive {
            background-color: var(--Secondary);
        }
        
        .breadcrumb {
            background-color: var(--Light);
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        
        .breadcrumb-item.active {
            color: var(--Primary);
            font-weight: 600;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--Primary) 0%, #3a56c4 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .sidebar-title {
            color: white;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .sidebar-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-item:last-child {
            border-bottom: none;
        }
        
        .sidebar-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-item a:hover {
            color: white;
            padding-left: 0.5rem;
        }
        
        .progress {
            height: 0.5rem;
            border-radius: 3px;
            margin-bottom: 1rem;
        }
        
        .progress-bar {
            background-color: var(--Primary);
        }
        
        .table th {
            color: var(--Primary);
            border-bottom: 2px solid var(--Primary);
            font-weight: 600;
        }
        
        .pagination .page-link {
            color: var(--Primary);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--Primary);
            border-color: var(--Primary);
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> หน้าแรก</a></li>
                <li class="breadcrumb-item"><a href="manage_mango.php" class="text-decoration-none"><i class="bi bi-tree"></i> จัดการสายพันธุ์มะม่วง</a></li>
                <li class="breadcrumb-item active" aria-current="page"><i class="bi bi-pencil-square"></i> แก้ไขสายพันธุ์</li>
            </ol>
        </nav>
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="header-title">แก้ไขสายพันธุ์มะม่วง</h1>
                <p class="text-muted mb-0">แก้ไขข้อมูลของสายพันธุ์: <strong class="text-primary"><?= htmlspecialchars($mango['mango_name']) ?></strong></p>
                <div class="mt-2">
                    <span class="badge badge-primary me-2">ID: <?= $mango['id'] ?></span>
                    <span class="badge badge-success">หมวดหมู่: <?= htmlspecialchars($mango['mango_category']) ?></span>
                </div>
            </div>
            <a href="manage_mango.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> กลับหน้าจัดการ
            </a>
        </div>
        
        <!-- Sidebar and Main Content -->
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="sidebar">
                    <h5 class="sidebar-title"><i class="bi bi-info-circle"></i> ข้อมูลสรุป</h5>
                    <div class="sidebar-item">
                        <div class="d-flex justify-content-between">
                            <span>สถานะ:</span>
                            <span class="status-indicator status-active"></span>
                        </div>
                    </div>
                    <div class="sidebar-item">
                        <div class="d-flex justify-content-between">
                            <span>วันที่สร้าง:</span>
                            <span><?= date('d/m/Y', strtotime($mango['created_at'] ?? 'now')) ?></span>
                        </div>
                    </div>
                    <div class="sidebar-item">
                        <div class="d-flex justify-content-between">
                            <span>วันที่แก้ไขล่าสุด:</span>
                            <span><?= date('d/m/Y', strtotime($mango['updated_at'] ?? 'now')) ?></span>
                        </div>
                    </div>
                    <div class="sidebar-item">
                        <div class="d-flex justify-content-between">
                            <span>จำนวนรูปภาพ:</span>
                            <span>
                                <?php 
                                $imageCount = 0;
                                $imageFields = ['fruit_image', 'tree_image', 'leaf_image', 'flower_image', 'branch_image'];
                                foreach ($imageFields as $field) {
                                    if (!empty($mango[$field])) $imageCount++;
                                }
                                echo $imageCount;
                                ?>
                                /5
                            </span>
                        </div>
                    </div>
                    
                    <h5 class="sidebar-title mt-4"><i class="bi bi-lightning-charge"></i> คำแนะนำ</h5>
                    <div class="sidebar-item">
                        <small><i class="bi bi-check-circle"></i> กรอกข้อมูลให้ครบถ้วน</small>
                    </div>
                    <div class="sidebar-item">
                        <small><i class="bi bi-check-circle"></i> อัพโหลดรูปภาพที่ชัดเจน</small>
                    </div>
                    <div class="sidebar-item">
                        <small><i class="bi bi-check-circle"></i> ตรวจสอบข้อมูลก่อนบันทึก</small>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <form action="update_mango.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $mango['id'] ?>">
                    
                    <!-- แท็บสำหรับกลุ่มข้อมูล -->
                    <ul class="nav nav-tabs mb-4" id="mangoTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                                <i class="bi bi-card-text"></i> ข้อมูลพื้นฐาน
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="morphology-tab" data-bs-toggle="tab" data-bs-target="#morphology" type="button" role="tab">
                                <i class="bi bi-flower1"></i> ลักษณะทางสัณฐานวิทยา
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="cultivation-tab" data-bs-toggle="tab" data-bs-target="#cultivation" type="button" role="tab">
                                <i class="bi bi-tree"></i> การปลูกและดูแล
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="images-tab" data-bs-toggle="tab" data-bs-target="#images" type="button" role="tab">
                                <i class="bi bi-images"></i> รูปภาพ
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="mangoTabContent">
                        <!-- แท็บข้อมูลพื้นฐาน -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-card-text"></i> ข้อมูลพื้นฐานของสายพันธุ์มะม่วง</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label required-field">ชื่อสายพันธุ์</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                                <input type="text" name="mango_name" class="form-control" value="<?= htmlspecialchars($mango['mango_name']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">ชื่อภาษาอังกฤษ</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-translate"></i></span>
                                                <input type="text" name="scientific_name" class="form-control" value="<?= htmlspecialchars($mango['scientific_name']) ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">ชื่อท้องถิ่น</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                                <input type="text" name="local_name" class="form-control" value="<?= htmlspecialchars($mango['local_name']) ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label required-field">หมวดหมู่</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-grid-3x3-gap"></i></span>
                                                <select name="mango_category" class="form-select" required>
                                                    <?php
                                                    $categories = ['เชิงพาณิชย์', 'เชิงอนุรักษ์', 'บริโภคในครัวเรือน'];
                                                    foreach ($categories as $category):
                                                        $selected = ($mango['mango_category'] === $category) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $category ?>" <?= $selected ?>><?= $category ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label">การแปรรูป</label>
                                            <div class="row">
                                                <?php
                                                $selected_methods = explode(",", $mango['processing_methods']);
                                                $options = [
                                                    'กวน' => '',
                                                    'ดอง' => '',
                                                    'แช่อิ่ม' => '',
                                                    'นิยมรับประทานสด' => ''
                                                ];
                                                foreach ($options as $option => $icon):
                                                ?>
                                                    <div class="col-md-3 mb-2">
                                                        <div class="form-check p-3 border rounded bg-primary-light">
                                                            <input class="form-check-input" type="checkbox" name="processing_methods[]" value="<?= $option ?>"
                                                                id="processing_<?= $option ?>"
                                                                <?= in_array($option, $selected_methods) ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="processing_<?= $option ?>">
                                                                <i class="bi <?= $icon ?> me-2"></i><?= $option ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- แท็บลักษณะทางสัณฐานวิทยา -->
                        <div class="tab-pane fade" id="morphology" role="tabpanel">
                            <div class="card">
                                <div class="card-header card-header-success">
                                    <h5 class="mb-0"><i class="bi bi-flower1"></i> ลักษณะทางสัณฐานวิทยา</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">ลักษณะลำต้น</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-tree"></i></span>
                                                <textarea name="morphology_stem" class="form-control" rows="3"><?= htmlspecialchars($mango['morphology_stem']) ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">ลักษณะผล</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-apple"></i></span>
                                                <textarea name="morphology_fruit" class="form-control" rows="3"><?= htmlspecialchars($mango['morphology_fruit']) ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">ลักษณะใบ</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-leaf"></i></span>
                                                <textarea name="morphology_leaf" class="form-control" rows="3"><?= htmlspecialchars($mango['morphology_leaf']) ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- แท็บการปลูกและดูแล -->
                        <div class="tab-pane fade" id="cultivation" role="tabpanel">
                            <div class="card">
                                <div class="card-header card-header-info">
                                    <h5 class="mb-0"><i class="bi bi-tree"></i> การปลูกและดูแล</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">การขยายพันธุ์</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-branch"></i></span>
                                                <textarea name="propagation_method" class="form-control" rows="3"><?= htmlspecialchars($mango['propagation_method']) ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">ลักษณะดิน</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-moisture"></i></span>
                                                <textarea name="soil_characteristics" class="form-control" rows="3"><?= htmlspecialchars($mango['soil_characteristics']) ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">ระยะเวลาการปลูก</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-calendar-range"></i></span>
                                                <input type="text" name="planting_period" class="form-control" value="<?= htmlspecialchars($mango['planting_period']) ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">ฤดูกาลเก็บเกี่ยว</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                                <input type="text" name="harvest_season" class="form-control" value="<?= htmlspecialchars($mango['harvest_season']) ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- แท็บรูปภาพ -->
                        <div class="tab-pane fade" id="images" role="tabpanel">
                            <div class="card">
                                <div class="card-header card-header-warning">
                                    <h5 class="mb-0"><i class="bi bi-images"></i> รูปภาพประกอบ</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <?php
                                        $image_fields = [
                                            'fruit_image' => ['label' => 'รูปผลมะม่วง', 'icon' => 'bi-apple'],
                                            'tree_image' => ['label' => 'รูปต้นมะม่วง', 'icon' => 'bi-tree'],
                                            'leaf_image' => ['label' => 'รูปใบมะม่วง', 'icon' => 'bi-leaf'],
                                            'flower_image' => ['label' => 'รูปดอกมะม่วง', 'icon' => 'bi-flower1'],
                                            'branch_image' => ['label' => 'รูปกิ่งมะม่วง', 'icon' => 'bi-branch']
                                        ];
                                        
                                        foreach ($image_fields as $field_name => $field_info):
                                        ?>
                                            <div class="col-md-4 col-lg">
                                                <div class="image-upload-container text-center">
                                                    <label class="form-label d-block">
                                                        <i class="<?= $field_info['icon'] ?> me-1"></i> <?= $field_info['label'] ?>
                                                    </label>
                                                    <div class="image-preview-container mb-3">
                                                        <img id="<?= $field_name ?>_preview" 
                                                             src="<?= $mango[$field_name] ?>" 
                                                             class="image-preview" 
                                                             onerror="this.src='https://via.placeholder.com/300x200/e9ecef/5a5c69?text=ไม่มีรูป'">
                                                        <div class="image-label"><?= $field_info['label'] ?></div>
                                                        <div class="image-upload-btn" onclick="document.getElementById('<?= $field_name ?>').click()">
                                                            <i class="bi bi-camera text-dark"></i>
                                                        </div>
                                                        <input type="file" 
                                                               name="<?= $field_name ?>" 
                                                               id="<?= $field_name ?>" 
                                                               class="file-input-hidden" 
                                                               onchange="previewImage(event, '<?= $field_name ?>_preview')" 
                                                               accept="image/*">
                                                    </div>
                                                    <small class="text-muted">คลิกที่ไอคอนกล้องเพื่ออัพโหลด</small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="alert alert-info mt-3">
                                        <i class="bi bi-info-circle"></i> รูปภาพที่รองรับ: JPG, PNG, GIF ขนาดไฟล์ไม่เกิน 5MB
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ปุ่มดำเนินการ -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted"><i class="bi bi-info-circle"></i> ตรวจสอบข้อมูลให้เรียบร้อยก่อนบันทึก</span>
                                </div>
                                <div>
                                    <button type="reset" class="btn btn-outline-secondary me-2">
                                        <i class="bi bi-arrow-counterclockwise"></i> รีเซ็ต
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle"></i> บันทึกการแก้ไข
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <script>
        function previewImage(event, previewId) {
            const file = event.target.files[0];
            if (file) {
                // ตรวจสอบขนาดไฟล์ (ไม่เกิน 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('ไฟล์มีขนาดใหญ่เกิน 5MB กรุณาเลือกไฟล์ที่มีขนาดเล็กกว่า');
                    event.target.value = '';
                    return;
                }
                
                // ตรวจสอบประเภทไฟล์
                const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('กรุณาเลือกไฟล์ภาพเท่านั้น (JPG, PNG, GIF)');
                    event.target.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function() {
                    document.getElementById(previewId).src = reader.result;
                };
                reader.readAsDataURL(file);
            }
        }
        
        // เปลี่ยนแท็บเมื่อโหลดหน้าใหม่
        document.addEventListener('DOMContentLoaded', function() {
            // ตรวจสอบว่ามีแท็บที่เลือกจาก URL หรือไม่
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            
            if (tabParam) {
                const tabTrigger = document.querySelector(`#${tabParam}-tab`);
                if (tabTrigger) {
                    const tab = new bootstrap.Tab(tabTrigger);
                    tab.show();
                }
            }
            
            // เพิ่มไฮไลท์ให้กับฟิลด์ที่จำเป็น
            const requiredFields = document.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                field.addEventListener('invalid', function(e) {
                    e.preventDefault();
                    this.classList.add('is-invalid');
                    
                    // แสดงข้อความแจ้งเตือน
                    if (!this.nextElementSibling || !this.nextElementSibling.classList.contains('invalid-feedback')) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.textContent = 'กรุณากรอกข้อมูลในฟิลด์นี้';
                        this.parentNode.appendChild(errorDiv);
                    }
                });
                
                field.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                    const errorDiv = this.nextElementSibling;
                    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv.remove();
                    }
                });
            });
            
            // เพิ่มเอฟเฟกต์เมื่อคลิกที่แท็บ
            const tabLinks = document.querySelectorAll('.nav-link');
            tabLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // ลบคลาส active จากทั้งหมดก่อน
                    tabLinks.forEach(l => l.classList.remove('active'));
                    // เพิ่มคลาส active ให้กับแท็บที่คลิก
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>