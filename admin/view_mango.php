<?php
require_once 'auth.php';

// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_mango";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// รับ id จาก query string
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { die("ไม่พบข้อมูล"); }

// ดึงข้อมูลตามโครงสร้างฟิลด์ใหม่
$stmt = $conn->prepare("SELECT 
    id,
    mango_name,
    scientific_name,
    local_name,
    morphology_stem,
    fruit_image,
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
FROM mango_varieties WHERE id = ?");
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
    <title>รายละเอียดมะม่วง: <?= htmlspecialchars($mango['local_name']) ?></title>
    
    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4CAF50;
            --primary-dark: #388E3C;
            --primary-light: #C8E6C9;
            --accent-color: #FF9800;
            --text-dark: #333;
            --text-light: #666;
            --light-bg: #f8f9fa;
            --white: #ffffff;
            --border-radius: 12px;
            --shadow-sm: 0 4px 12px rgba(0,0,0,0.05);
            --shadow-md: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Prompt', sans-serif;
            color: var(--text-dark);
            padding: 20px 0 50px;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
        }
        
        .mango-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 40px 30px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            box-shadow: var(--shadow-md);
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
        
        .header-icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.2);
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .detail-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }
        
        .detail-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .section-title {
            font-size: 1.3rem;
            color: var(--primary-dark);
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            background: var(--primary-light);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
        }
        
        .morphology-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .morphology-item {
            text-align: center;
        }
        
        .morphology-img {
            width: 100%;
            height: 210px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }
        
        .morphology-img:hover {
            transform: scale(1.05);
        }
        
        .morphology-label {
            margin-top: 10px;
            font-weight: 500;
            color: var(--primary-dark);
        }
        
        .info-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 5px;
        }
        
        .info-value {
            color: var(--text-dark);
        }
        
        .badge-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .info-badge {
            background: var(--primary-light);
            color: var(--primary-dark);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
        }
        
        .btn-action {
            background: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border-radius: 30px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            border: none;
        }
        
        .btn-action:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            color: white;
        }
        
        .btn-back {
            color: var(--text-light);
            text-decoration: none;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            padding: 8px 15px;
            border-radius: 30px;
            background: var(--light-bg);
        }
        
        .btn-back:hover {
            color: var(--primary-dark);
            background: var(--primary-light);
            text-decoration: none;
        }
        
        .header-image {
            width: 100%;
            height: 350px;
            object-fit: cover;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            margin-bottom: 25px;
        }
        
        .no-image {
            background: var(--light-bg);
            height: 350px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            font-size: 1.2rem;
        }
        
        .no-image i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .header-icon {
                width: 70px;
                height: 70px;
                font-size: 2.5rem;
            }
            
            .mango-header h1 {
                font-size: 1.8rem;
            }
            
            .header-image, .no-image {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="mango-header text-center position-relative"
             style="background-image: url('<?= htmlspecialchars($mango['fruit_image']) ?>'); background-size: cover; background-position: center;">
            <div class="header-overlay" style="position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(44,62,80,0.45);border-radius:var(--border-radius);"></div>
            <div class="header-icon" style="position:relative;z-index:2;">
                <i class="fas fa-mango"></i>
            </div>
            <h1 class="mb-3" style="position:relative;z-index:2;"><?= htmlspecialchars($mango['local_name']) ?></h1>
            <p class="mb-0 lead" style="position:relative;z-index:2;"><?= htmlspecialchars($mango['scientific_name']) ?></p>
        </div>
        
        <div class="d-flex justify-content-start mb-4">
            <a href="manage_mango.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> กลับสู่หน้าจัดการมะม่วง
            </a>
        </div>
        
        <!-- ส่วนข้อมูลหลัก -->
        <div class="row">
            <!-- คอลัมน์ซ้าย: ข้อมูลทั่วไป -->
            <div class="col-lg-6 mb-4">
                <div class="detail-card">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        ข้อมูลทั่วไป
                    </h3>
                    
                    <div class="info-item">
                        <div class="info-label">ชื่อวิทยาศาสตร์</div>
                        <div class="info-value"><?= htmlspecialchars($mango['scientific_name']) ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">ชื่อท้องถิ่น</div>
                        <div class="info-value"><?= htmlspecialchars($mango['local_name']) ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">ลักษณะของดินที่เหมาะสม</div>
                        <div class="info-value"><?= nl2br(htmlspecialchars($mango['soil_characteristics'])) ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">ระยะเวลาเพาะปลูก</div>
                        <div class="info-value"><?= nl2br(htmlspecialchars($mango['planting_period'])) ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">หมวดหมู่</div>
                        <div class="info-value"><?= htmlspecialchars($mango['mango_category']) ?></div>
                    </div>
                </div>
                
                <!-- การขยายพันธุ์ -->
                <div class="detail-card">
                    <h3 class="section-title">
                        <i class="fas fa-seedling"></i>
                        การขยายพันธุ์
                    </h3>
                    
                    <div class="badge-list">
                        <?php if (!empty($propagation)): ?>
                            <?php foreach ($propagation as $method): ?>
                                <span class="info-badge"><?= htmlspecialchars(trim($method)) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="info-value">ไม่มีข้อมูลการขยายพันธุ์</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- การประมวลผล -->
                <div class="detail-card">
                    <h3 class="section-title">
                        <i class="fas fa-cogs"></i>
                        การประมวลผล
                    </h3>
                    
                    <div class="badge-list">
                        <?php if (!empty($processing)): ?>
                            <?php foreach ($processing as $method): ?>
                                <span class="info-badge"><?= htmlspecialchars(trim($method)) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="info-value">ไม่มีข้อมูลการประมวลผล</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- คอลัมน์ขวา: ลักษณะสัณฐานวิทยา -->
            <div class="col-lg-6">
                <div class="detail-card">
                    <h3 class="section-title">
                        <i class="fas fa-leaf"></i>
                        ลักษณะสัณฐานวิทยา
                    </h3>
                    
                    <div class="morphology-grid">
                        <!-- ผล -->
                        <div class="morphology-item">
                            <?php if (!empty($mango['fruit_image'])): ?>
                                <img src="<?= htmlspecialchars($mango['fruit_image']) ?>" class="morphology-img" alt="รูปภาพผลมะม่วง">
                            <?php else: ?>
                                <div class="no-image" style="height: 180px;">
                                    <i class="fas fa-apple-alt"></i>
                                </div>
                            <?php endif; ?>
                            <div class="morphology-label">ผล</div>
                        </div>
                        
                        <!-- ต้น -->
                        <div class="morphology-item">
                            <?php if (!empty($mango['tree_image'])): ?>
                                <img src="<?= htmlspecialchars($mango['tree_image']) ?>" class="morphology-img" alt="รูปภาพต้นมะม่วง">
                            <?php else: ?>
                                <div class="no-image" style="height: 180px;">
                                    <i class="fas fa-tree"></i>
                                </div>
                            <?php endif; ?>
                            <div class="morphology-label">ต้น</div>
                        </div>
                        
                        <!-- ใบ -->
                        <div class="morphology-item">
                            <?php if (!empty($mango['leaf_image'])): ?>
                                <img src="<?= htmlspecialchars($mango['leaf_image']) ?>" class="morphology-img" alt="รูปภาพใบมะม่วง">
                            <?php else: ?>
                                <div class="no-image" style="height: 180px;">
                                    <i class="fas fa-leaf"></i>
                                </div>
                            <?php endif; ?>
                            <div class="morphology-label">ใบ</div>
                        </div>
                        
                        <!-- ดอก -->
                        <div class="morphology-item">
                            <?php if (!empty($mango['flower_image'])): ?>
                                <img src="<?= htmlspecialchars($mango['flower_image']) ?>" class="morphology-img" alt="รูปภาพดอกมะม่วง">
                            <?php else: ?>
                                <div class="no-image" style="height: 180px;">
                                    <i class="fas fa-spa"></i>
                                </div>
                            <?php endif; ?>
                            <div class="morphology-label">ดอก</div>
                        </div>
                        
                        <!-- กิ่ง -->
                        <div class="morphology-item">
                            <?php if (!empty($mango['branch_image'])): ?>
                                <img src="<?= htmlspecialchars($mango['branch_image']) ?>" class="morphology-img" alt="รูปภาพกิ่งมะม่วง">
                            <?php else: ?>
                                <div class="no-image" style="height: 180px;">
                                    <i class="fas fa-tree"></i>
                                </div>
                            <?php endif; ?>
                            <div class="morphology-label">กิ่ง</div>
                        </div>
                    </div>
                </div>
            </div>
        </div></div>
        
        <!-- ปุ่มดำเนินการ -->
        <div class="text-center my-5">
            <a href="edit_mango.php?id=<?= $mango['id'] ?>" class="btn-action">
                <i class="fas fa-edit"></i> แก้ไขข้อมูลมะม่วง
            </a>
            <a href="manage_mango.php" class="btn-action" style="background: #6c757d;">
                <i class="fas fa-arrow-left"></i> กลับสู่หน้าจัดการมะม่วง
            </a>
        </div>
    </div>

    <!-- Bootstrap & jQuery Script -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // เพิ่มเอฟเฟกต์เมื่อโหลดหน้า
        $(document).ready(function() {
            $('.detail-card').each(function(i) {
                $(this).delay(100 * i).animate({opacity: 1}, 500);
            });
        });
    </script>
</body>
</html>