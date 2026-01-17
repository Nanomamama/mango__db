<?php
require_once 'auth.php';
require_once '../admin/db.php';

// รับ id จาก query string
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { die("ไม่พบข้อมูล"); }

// ดึงข้อมูลตามโครงสร้างฟิลด์ใหม่
$stmt = $conn->prepare("SELECT 
    mango_id AS id,
    mango_name,
    scientific_name,
    local_name,
    morphology_stem,
    morphology_fruit,
    morphology_leaf,
    fruit_image,
    tree_image,
    leaf_image,
    flower_image,
    branch_image,
    propagation_method,
    soil_characteristics,
    planting_period,
    harvest_season,
    processing_methods,
    mango_category,
    created_at
FROM mango_varieties WHERE mango_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$mango = $result->fetch_assoc();
if (!$mango) { die("ไม่พบข้อมูล"); }

$propagation = array_filter(array_map('trim', explode(',', $mango['propagation_method'])));
$processing = array_filter(array_map('trim', explode(',', $mango['processing_methods'])));
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดมะม่วง: <?= htmlspecialchars($mango['mango_name']) ?></title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    
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
            font-family: 'Kanit', sans-serif;
        }
        
        body {
            background-color: var(--Light);
            color: var(--Dark);
            padding: 20px 0 50px;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
        }
        
        .mango-header {
            background: linear-gradient(135deg, var(--Primary) 0%, #6a8bef 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 0.5rem 1.5rem rgba(58, 59, 69, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .mango-header::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .mango-header::after {
            content: "";
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }
        
        .header-icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.2);
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            z-index: 2;
            position: relative;
        }
        
        .card {
            border-radius: 10px;
            border: 1px solid #e3e6f0;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .card:hover {
            transform: translateY(-5px);
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
        
        .section-title {
            font-size: 1.3rem;
            color: var(--Primary);
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            background: rgba(78, 115, 223, 0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--Primary);
        }
        
        .info-item {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--Primary);
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: var(--Dark);
            font-size: 1rem;
        }
        
        .badge-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .info-badge {
            background: linear-gradient(135deg, var(--Primary) 0%, #6a8bef 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .info-badge-success {
            background: linear-gradient(135deg, var(--Success) 0%, #2d6a4f 100%);
        }
        
        .info-badge-warning {
            background: linear-gradient(135deg, var(--Warning) 0%, #f8d568 100%);
            color: var(--Dark);
        }
        
        .btn {
            border-radius: 6px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--Primary) 0%, #6a8bef 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #3a56c4 0%, #5a7ceb 100%);
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(78, 115, 223, 0.3);
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--Success) 0%, #2d6a4f 100%);
            color: white;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #0f2e22 0%, #1b4332 100%);
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(20, 58, 44, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, var(--Secondary) 0%, #9fa1b5 100%);
            color: white;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #6c6e80 0%, #858796 100%);
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(133, 135, 150, 0.3);
        }
        
        .breadcrumb {
            background-color: var(--Light);
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            border: 1px solid #e3e6f0;
        }
        
        .breadcrumb-item.active {
            color: var(--Primary);
            font-weight: 600;
        }
        
        .breadcrumb-item a {
            color: var(--Secondary);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .breadcrumb-item a:hover {
            color: var(--Primary);
        }
        
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0.15rem 0.5rem rgba(58, 59, 69, 0.15);
            transition: all 0.3s;
            height: 180px;
            background-color: #f8f9fc;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(58, 59, 69, 0.2);
        }
        
        .gallery-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .gallery-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
            text-align: center;
            font-weight: 500;
        }
        
        .no-image {
            height: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--Secondary);
            background: #f8f9fc;
            border-radius: 8px;
        }
        
        .no-image i {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: var(--Secondary);
        }
        
        .mango-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-item {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid var(--Primary);
            flex: 1;
            min-width: 150px;
            box-shadow: 0 0.15rem 0.5rem rgba(58, 59, 69, 0.1);
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: var(--Secondary);
            margin-bottom: 0.25rem;
        }
        
        .stat-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--Primary);
        }
        
        .text-primary {
            color: var(--Primary) !important;
        }
        
        .text-success {
            color: var(--Success) !important;
        }
        
        .text-warning {
            color: var(--Warning) !important;
        }
        
        .border-primary {
            border-color: var(--Primary) !important;
        }
        
        @media (max-width: 768px) {
            .header-icon {
                width: 80px;
                height: 80px;
                font-size: 2.5rem;
            }
            
            .mango-header h1 {
                font-size: 1.8rem;
            }
            
            .image-gallery {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .gallery-item {
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> หน้าแรก</a></li>
                <li class="breadcrumb-item"><a href="manage_mango.php"><i class="bi bi-tree"></i> จัดการสายพันธุ์มะม่วง</a></li>
                <li class="breadcrumb-item active" aria-current="page"><i class="bi bi-eye"></i> รายละเอียดมะม่วง</li>
            </ol>
        </nav>
        
        <!-- Header Section -->
        <div class="mango-header text-center position-relative">
            <div class="header-icon">
                <i class="bi bi-tree-fill"></i>
            </div>
            <h1 class="mb-3" style="position:relative;z-index:2;"><?= htmlspecialchars($mango['mango_name']) ?></h1>
            <p class="mb-0 lead" style="position:relative;z-index:2;">
                <i class="bi bi-translate"></i> <?= htmlspecialchars($mango['scientific_name']) ?>
            </p>
            <div class="mt-3" style="position:relative;z-index:2;">
                <span class="badge bg-white text-primary me-2">ID: <?= $mango['id'] ?></span>
                <span class="badge bg-white text-success"><?= htmlspecialchars($mango['mango_category']) ?></span>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="mango-stats">
            <div class="stat-item">
                <div class="stat-label">ชื่อท้องถิ่น</div>
                <div class="stat-value"><?= htmlspecialchars($mango['local_name']) ?: 'ไม่มีข้อมูล' ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-label">หมวดหมู่</div>
                <div class="stat-value"><?= htmlspecialchars($mango['mango_category']) ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-label">วันที่เพิ่มข้อมูล</div>
                <div class="stat-value"><?= date('d/m/Y', strtotime($mango['created_at'])) ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-label">จำนวนรูปภาพ</div>
                <div class="stat-value">
                    <?php 
                    $imageCount = 0;
                    $imageFields = ['fruit_image', 'tree_image', 'leaf_image', 'flower_image', 'branch_image'];
                    foreach ($imageFields as $field) {
                        if (!empty($mango[$field])) $imageCount++;
                    }
                    echo $imageCount . '/5';
                    ?>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Left Column: General Information -->
            <div class="col-lg-6 mb-4">
                <!-- General Information Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> ข้อมูลทั่วไป</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <div class="info-label">ชื่อวิทยาศาสตร์</div>
                            <div class="info-value"><?= htmlspecialchars($mango['scientific_name']) ?: 'ไม่มีข้อมูล' ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">ชื่อท้องถิ่น</div>
                            <div class="info-value"><?= htmlspecialchars($mango['local_name']) ?: 'ไม่มีข้อมูล' ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">ลักษณะลำต้น</div>
                            <div class="info-value"><?= nl2br(htmlspecialchars($mango['morphology_stem'])) ?: 'ไม่มีข้อมูล' ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">ลักษณะผล</div>
                            <div class="info-value"><?= nl2br(htmlspecialchars($mango['morphology_fruit'])) ?: 'ไม่มีข้อมูล' ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">ลักษณะใบ</div>
                            <div class="info-value"><?= nl2br(htmlspecialchars($mango['morphology_leaf'])) ?: 'ไม่มีข้อมูล' ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Cultivation Information Card -->
                <div class="card">
                    <div class="card-header card-header-success">
                        <h5 class="mb-0"><i class="bi bi-tree"></i> การปลูกและดูแล</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <div class="info-label">ลักษณะดินที่เหมาะสม</div>
                            <div class="info-value"><?= nl2br(htmlspecialchars($mango['soil_characteristics'])) ?: 'ไม่มีข้อมูล' ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">ระยะเวลาเพาะปลูก</div>
                            <div class="info-value"><?= htmlspecialchars($mango['planting_period']) ?: 'ไม่มีข้อมูล' ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">ฤดูกาลเก็บเกี่ยว</div>
                            <div class="info-value"><?= htmlspecialchars($mango['harvest_season']) ?: 'ไม่มีข้อมูล' ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Images and Additional Info -->
            <div class="col-lg-6">
                <!-- Images Gallery Card -->
                <div class="card">
                    <div class="card-header card-header-warning">
                        <h5 class="mb-0"><i class="bi bi-images"></i> รูปภาพประกอบ</h5>
                    </div>
                    <div class="card-body">
                        <div class="image-gallery">
                            <!-- ผล -->
                            <div class="gallery-item">
                                <?php if (!empty($mango['fruit_image'])): ?>
                                    <img src="<?= htmlspecialchars($mango['fruit_image']) ?>" class="gallery-img" alt="รูปภาพผลมะม่วง">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="bi bi-apple"></i>
                                        <small>ไม่มีรูปภาพ</small>
                                    </div>
                                <?php endif; ?>
                                <div class="gallery-label">ผลมะม่วง</div>
                            </div>
                            
                            <!-- ต้น -->
                            <div class="gallery-item">
                                <?php if (!empty($mango['tree_image'])): ?>
                                    <img src="<?= htmlspecialchars($mango['tree_image']) ?>" class="gallery-img" alt="รูปภาพต้นมะม่วง">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="bi bi-tree"></i>
                                        <small>ไม่มีรูปภาพ</small>
                                    </div>
                                <?php endif; ?>
                                <div class="gallery-label">ต้นมะม่วง</div>
                            </div>
                            
                            <!-- ใบ -->
                            <div class="gallery-item">
                                <?php if (!empty($mango['leaf_image'])): ?>
                                    <img src="<?= htmlspecialchars($mango['leaf_image']) ?>" class="gallery-img" alt="รูปภาพใบมะม่วง">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="bi bi-leaf"></i>
                                        <small>ไม่มีรูปภาพ</small>
                                    </div>
                                <?php endif; ?>
                                <div class="gallery-label">ใบมะม่วง</div>
                            </div>
                            
                            <!-- ดอก -->
                            <div class="gallery-item">
                                <?php if (!empty($mango['flower_image'])): ?>
                                    <img src="<?= htmlspecialchars($mango['flower_image']) ?>" class="gallery-img" alt="รูปภาพดอกมะม่วง">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="bi bi-flower1"></i>
                                        <small>ไม่มีรูปภาพ</small>
                                    </div>
                                <?php endif; ?>
                                <div class="gallery-label">ดอกมะม่วง</div>
                            </div>
                            
                            <!-- กิ่ง -->
                            <div class="gallery-item">
                                <?php if (!empty($mango['branch_image'])): ?>
                                    <img src="<?= htmlspecialchars($mango['branch_image']) ?>" class="gallery-img" alt="รูปภาพกิ่งมะม่วง">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="bi bi-branch"></i>
                                        <small>ไม่มีรูปภาพ</small>
                                    </div>
                                <?php endif; ?>
                                <div class="gallery-label">กิ่งมะม่วง</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Propagation and Processing Cards -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-header card-header-info">
                                <h5 class="mb-0"><i class="bi bi-branch"></i> การขยายพันธุ์</h5>
                            </div>
                            <div class="card-body">
                                <div class="badge-list">
                                    <?php if (!empty($propagation)): ?>
                                        <?php foreach ($propagation as $method): ?>
                                            <span class="info-badge"><?= htmlspecialchars(trim($method)) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-muted"><i class="bi bi-info-circle"></i> ไม่มีข้อมูลการขยายพันธุ์</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-gear"></i> การแปรรูป</h5>
                            </div>
                            <div class="card-body">
                                <div class="badge-list">
                                    <?php if (!empty($processing)): ?>
                                        <?php foreach ($processing as $method): ?>
                                            <span class="info-badge info-badge-success"><?= htmlspecialchars(trim($method)) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-muted"><i class="bi bi-info-circle"></i> ไม่มีข้อมูลการแปรรูป</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="d-flex justify-content-center gap-3 my-5">
            <a href="edit_mango.php?id=<?= $mango['id'] ?>" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> แก้ไขข้อมูล
            </a>
            <a href="manage_mango.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> กลับหน้าจัดการ
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add animation to cards on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Set initial state for animation
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            });
            
            // Add hover effect to gallery items
            const galleryItems = document.querySelectorAll('.gallery-item');
            galleryItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    const img = this.querySelector('img');
                    if (img) {
                        img.style.transform = 'scale(1.1)';
                        img.style.transition = 'transform 0.3s ease';
                    }
                });
                
                item.addEventListener('mouseleave', function() {
                    const img = this.querySelector('img');
                    if (img) {
                        img.style.transform = 'scale(1)';
                    }
                });
            });
        });
    </script>
</body>
</html>