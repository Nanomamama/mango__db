<?php
require_once '../admin/db.php';

if (!isset($_GET['name'])) {
    header('Location: mango_varieties.php');
    exit;
}

$name = $_GET['name'];

// ดึงข้อมูลมะม่วงจากฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM mango_varieties WHERE mango_name = ?");
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();
$mango = $result->fetch_assoc();

if (!$mango) {
    echo "ไม่พบข้อมูลสายพันธุ์";
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($mango['mango_name']) ?> - รายละเอียดสายพันธุ์มะม่วง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --Primary: #4e73df;
            --Success: #1cc88a;
            --Info: #36b9cc;
            --Warning: #f6c23e;
            --Danger: #e74a3b;
            --Secondary: #858796;
            --Light: #f8f9fc;
            --Dark: #5a5c69;
            --Darkss: #000000;
            --Green: #016A70;
            --LightGreen: #A3C9A8;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f8f9fa;
        }

        .mango-header {
            background: linear-gradient(135deg, var(--Green), var(--LightGreen));
            color: white;
            margin-top: 2rem;
            padding: 4rem;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .mango-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }

        .mango-scientific {
            font-style: italic;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .category-badge {
            display: inline-block;
            padding: 0.35rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 0.5rem;
            background-color: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .info-card {
            border-radius: 16px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .info-card .card-header {
            background: linear-gradient(135deg, var(--Green), var(--LightGreen));
            color: white;
            border-bottom: none;
            padding: 1rem 1.5rem;
            font-weight: 500;
        }

        .info-card .card-body {
            padding: 1.5rem;
        }

        .info-item {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .info-label {
            font-weight: 500;
            color: var(--Dark);
            margin-bottom: 0.25rem;
        }

        .info-value {
            color: var(--Secondary);
        }

        .main-image-container {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
            cursor: pointer;
            background-color: #f8f9fa;
        }

        .main-image-container:hover {
            transform: scale(1.02);
        }

        .main-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            display: block;
            padding: 15px;
            background-color: #fff;
        }

        .gallery-item {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            height: 200px;
            position: relative;
            cursor: pointer;
            background-color: #f8f9fa;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.5s ease;
            padding: 10px;
            background-color: #fff;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
            color: white;
            padding: 0.75rem;
            text-align: center;
            font-weight: 500;
        }

        .back-btn {
            background: linear-gradient(135deg, var(--Green), var(--LightGreen));
            border: none;
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
        }

        .back-btn:hover {
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(1, 106, 112, 0.3);
            color: white;
        }

        .empty-image {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
            color: var(--Secondary);
        }

        .empty-image i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .section-title {
            position: relative;
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--Green), var(--LightGreen));
            border-radius: 3px;
        }

        .processing-content {
            line-height: 1.7;
            white-space: pre-line;
        }

        /* Modal สำหรับรูปภาพขยาย */
        .image-modal .modal-content {
            border-radius: 16px;
            overflow: hidden;
            border: none;
        }

        .image-modal .modal-header {
            background: linear-gradient(135deg, var(--Green), var(--LightGreen));
            color: white;
            border-bottom: none;
        }

        .image-modal .modal-body {
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
        }

        .image-modal .modal-body img {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
            padding: 15px;
        }

        .image-modal .btn-close {
            filter: invert(1);
        }

        @media (max-width: 768px) {
            .main-image {
                height: 300px;
            }
            
            .gallery-item {
                height: 180px;
            }
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="mango-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mango-title"><?= htmlspecialchars($mango['mango_name']) ?></h1>
                <p class="mango-scientific"><?= htmlspecialchars($mango['scientific_name']) ?></p>
                <span class="category-badge"><?= htmlspecialchars($mango['mango_category']) ?></span>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="mango_varieties.php" class="back-btn">
                    <i class="bi bi-arrow-left"></i> กลับหน้ารวมสายพันธุ์
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <div class="row">
        <!-- คอลัมน์ซ้าย - รูปภาพหลัก -->
        <div class="col-lg-5 mb-4">
            <div class="main-image-container" data-bs-toggle="modal" data-bs-target="#imageModal" data-image="<?= !empty($mango['fruit_image']) ? '../admin/uploads/' . htmlspecialchars(basename($mango['fruit_image'])) : '' ?>" data-title="<?= htmlspecialchars($mango['mango_name']) ?>">
                <?php
                $fruit_image = !empty($mango['fruit_image']) ? '../admin/uploads/' . htmlspecialchars(basename($mango['fruit_image'])) : '';
                $fruit_image_path = !empty($mango['fruit_image']) ? __DIR__ . '/../admin/uploads/' . basename($mango['fruit_image']) : '';
                
                if (!empty($mango['fruit_image']) && file_exists($fruit_image_path)): 
                ?>
                    <img src="<?= $fruit_image ?>" class="main-image" alt="<?= htmlspecialchars($mango['mango_name']) ?>">
                <?php else: ?>
                    <div class="main-image empty-image">
                        <i class="bi bi-image"></i>
                        <span>ไม่มีรูปภาพ</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- ข้อมูลทั่วไป -->
            <div class="info-card">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>ข้อมูลทั่วไป
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">ชื่อภาษาอังกฤษ</div>
                        <div class="info-value"><?= htmlspecialchars($mango['scientific_name']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ชื่อท้องถิ่น</div>
                        <div class="info-value"><?= !empty($mango['local_name']) ? htmlspecialchars($mango['local_name']) : '<span class="text-muted">-</span>' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ประเภทมะม่วง</div>
                        <div class="info-value"><?= htmlspecialchars($mango['mango_category']) ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- คอลัมน์ขวา - ข้อมูลรายละเอียด -->
        <div class="col-lg-7">
            <!-- ลักษณะสัณฐานวิทยา -->
            <div class="info-card">
                <div class="card-header">
                    <i class="bi bi-tree me-2"></i>ลักษณะสัณฐานวิทยา
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">ลำต้น</div>
                        <div class="info-value"><?= htmlspecialchars($mango['morphology_stem']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ผล</div>
                        <div class="info-value"><?= htmlspecialchars($mango['morphology_fruit']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ใบ</div>
                        <div class="info-value"><?= htmlspecialchars($mango['morphology_leaf']) ?></div>
                    </div>
                </div>
            </div>
            
            <!-- การเพาะปลูก -->
            <div class="info-card">
                <div class="card-header">
                    <i class="bi bi-flower1 me-2"></i>การเพาะปลูก
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">การขยายพันธุ์</div>
                        <div class="info-value"><?= htmlspecialchars($mango['propagation_method']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ลักษณะดิน</div>
                        <div class="info-value"><?= htmlspecialchars($mango['soil_characteristics']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ระยะเวลาเพาะปลูก</div>
                        <div class="info-value"><?= htmlspecialchars($mango['planting_period']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ช่วงฤดูกาลออกดอก</div>
                        <div class="info-value"><?= htmlspecialchars($mango['harvest_season']) ?></div>
                    </div>
                </div>
            </div>
            
            <!-- การแปรรูป -->
            <div class="info-card">
                <div class="card-header">
                    <i class="bi bi-gear me-2"></i>การแปรรูป
                </div>
                <div class="card-body">
                    <div class="processing-content"><?= nl2br(htmlspecialchars($mango['processing_methods'])) ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- แกลเลอรี่รูปภาพ -->
    <div class="row mt-4">
        <div class="col-12">
            <h3 class="section-title">แกลเลอรี่รูปภาพ</h3>
        </div>
        
        <?php
        // ฟังก์ชันตรวจสอบและแสดงรูปภาพ
        function displayGalleryImage($image_field, $label, $mango) {
            $image_path = !empty($mango[$image_field]) ? '../admin/uploads/' . htmlspecialchars(basename($mango[$image_field])) : '';
            $abs_path = !empty($mango[$image_field]) ? __DIR__ . '/../admin/uploads/' . basename($mango[$image_field]) : '';
            
            echo '<div class="col-md-6 col-lg-3">';
            echo '<div class="gallery-item" data-bs-toggle="modal" data-bs-target="#imageModal" data-image="' . $image_path . '" data-title="' . $label . '">';
            
            if (!empty($mango[$image_field]) && file_exists($abs_path)) {
                echo '<img src="' . $image_path . '" alt="' . $label . '">';
            } else {
                echo '<div class="empty-image">';
                echo '<i class="bi bi-image"></i>';
                echo '<span>ไม่มีรูปภาพ</span>';
                echo '</div>';
            }
            
            echo '<div class="gallery-label">' . $label . '</div>';
            echo '</div>';
            echo '</div>';
        }
        
        // แสดงรูปภาพในแกลเลอรี่
        displayGalleryImage('tree_image', 'ต้น', $mango);
        displayGalleryImage('leaf_image', 'ใบ', $mango);
        displayGalleryImage('branch_image', 'กิ่ง', $mango);
        displayGalleryImage('flower_image', 'ดอก', $mango);
        ?>
    </div>
    
    <!-- ปุ่มกลับด้านล่าง -->
    <div class="text-center mt-5">
        <a href="mango_varieties.php" class="back-btn">
            <i class="bi bi-arrow-left"></i> กลับหน้ารวมสายพันธุ์
        </a>
    </div>
</div>

<!-- Modal สำหรับแสดงรูปภาพขยาย -->
<div class="modal fade image-modal" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">รูปภาพ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="modalImage" src="" alt="" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // เพิ่มเอฟเฟกต์เมื่อโหลดหน้า
    document.addEventListener('DOMContentLoaded', function() {
        // เพิ่มคลาสแสดงผลทีละอย่างเมื่อโหลดหน้าเสร็จ
        setTimeout(() => {
            document.querySelector('.mango-header').style.opacity = '1';
            document.querySelector('.mango-header').style.transform = 'translateY(0)';
        }, 100);
        
        // เอฟเฟกต์ hover บนการ์ดข้อมูล
        const infoCards = document.querySelectorAll('.info-card');
        infoCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // จัดการการคลิกรูปภาพเพื่อแสดงใน modal
        const imageModal = document.getElementById('imageModal');
        if (imageModal) {
            imageModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget; // ปุ่มที่เรียก modal
                const imageUrl = button.getAttribute('data-image');
                const imageTitle = button.getAttribute('data-title');
                
                // อัพเดทรูปภาพและหัวข้อใน modal
                const modalImage = document.getElementById('modalImage');
                const modalTitle = document.getElementById('imageModalLabel');
                
                modalImage.src = imageUrl;
                modalImage.alt = imageTitle;
                modalTitle.textContent = imageTitle;
            });
        }
        
        // ฟังก์ชันสำหรับจัดการรูปภาพที่ไม่มี
        const emptyImages = document.querySelectorAll('.empty-image');
        emptyImages.forEach(emptyImage => {
            emptyImage.addEventListener('click', function(e) {
                e.stopPropagation(); // ป้องกันไม่ให้คลิกส่งต่อไปยัง parent element
            });
        });
    });
</script>
</body>
</html>