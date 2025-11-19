<?php
session_start();
require_once '../admin/db.php'; // เชื่อมต่อฐานข้อมูล

// ดึงข้อมูลมะม่วงจากฐานข้อมูล
$query = "SELECT * FROM mango_varieties";
$result = $conn->query($query);

if (!$result) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลมะม่วง: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สายพันธุ์มะม่วง</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --green-color: #016A70;
            --light-green: #A3C9A8;
            --white-color: #fff;
            --Primary: #4e73df;
            --Success: #1cc88a;
            --Info: #36b9cc;
            --Warning: #f6c23e;
            --Danger: #e74a3b;
            --Secondary: #858796;
            --Light: #f8f9fc;
            --Dark: #5a5c69;
            --Darkss: #000000;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Kanit', sans-serif;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        .mango-card {
            border-radius: 16px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            background: linear-gradient(145deg, #ffffff, #f5f5f5);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            animation: fadeIn 0.7s;
            position: relative;
            border: none;
            height: 100%;
        }

        .mango-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--green-color), var(--light-green));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }

        .mango-card:hover::before {
            transform: scaleX(1);
        }

        .mango-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .mango-card img {
            width: 100%;
            height: 220px;
            object-fit: contain;
            padding: 15px;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            will-change: transform;
            display: block;
        }

        .mango-card:hover img {
            transform: translateY(-10px) scale(1.08) rotate(-3deg);
        }

        .mango-card .card-body {
            text-align: center;
            padding: 1.5rem 1rem;
            position: relative;
            z-index: 1;
        }

        .mango-card .card-title {
            font-weight: 600;
            color: var(--Dark);
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .mango-card .scientific-name {
            font-style: italic;
            color: var(--Secondary);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .category-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-top: 0.5rem;
        }

        .category-commercial {
            background-color: rgba(28, 200, 138, 0.15);
            color: var(--Success);
        }

        .category-conservation {
            background-color: rgba(246, 194, 62, 0.15);
            color: var(--Warning);
        }

        .category-household {
            background-color: rgba(78, 115, 223, 0.15);
            color: var(--Primary);
        }

        .container h2 {
            font-weight: 600;
            position: relative;
            display: inline-block;
        }

        .container h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, var(--green-color), var(--light-green));
            border-radius: 3px;
        }

        .fade-in {
            animation: fadeIn 0.5s forwards;
        }

        .search-container {
            position: relative;
            max-width: 500px;
        }

        .search-container .form-control {
            padding-left: 2.5rem;
            border-radius: 50px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            color: var(--Secondary);
        }

        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .filter-btn {
            border-radius: 50px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .filter-btn.active {
            background-color: var(--green-color);
            color: white;
            border-color: var(--green-color);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--Secondary);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--Light);
        }

        .mango-count {
            background-color: var(--green-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-left: 0.5rem;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease;
        }

        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(1, 106, 112, 0.2);
            border-radius: 50%;
            border-top-color: var(--green-color);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .view-toggle {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .view-toggle-btn {
            background: white;
            border: 1px solid #dee2e6;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .view-toggle-btn:first-child {
            border-radius: 50px 0 0 50px;
        }

        .view-toggle-btn:last-child {
            border-radius: 0 50px 50px 0;
        }

        .view-toggle-btn.active {
            background-color: var(--green-color);
            color: white;
            border-color: var(--green-color);
        }

        .grid-view .mango-card {
            height: 100%;
        }

        .list-view .col {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .list-view .mango-card {
            display: flex;
            flex-direction: row;
            height: auto;
        }

        .list-view .mango-card img {
            width: 180px;
            height: 180px;
            flex-shrink: 0;
        }

        .list-view .mango-card .card-body {
            text-align: left;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .list-view .mango-card {
                flex-direction: column;
            }
            
            .list-view .mango-card img {
                width: 100%;
                height: 200px;
            }
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>
    
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>
    
    <div class="container py-5">
        <br>
        <h2 class="text-center mb-4 mt-5">สายพันธุ์มะม่วงของเรา</h2>
        <br>
        
        <div class="mb-4 d-flex justify-content-center">
            <div class="search-container w-100">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="searchInput" class="form-control" placeholder="ค้นหาสายพันธุ์มะม่วง...">
            </div>
        </div>
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
            <div class="filter-buttons mb-3 mb-md-0">
                <button class="btn filter-btn active" data-filter="">ทั้งหมด</button>
                <button class="btn filter-btn" data-filter="เชิงพาณิชย์">เชิงพาณิชย์</button>
                <button class="btn filter-btn" data-filter="เชิงอนุรักษ์">เชิงอนุรักษ์</button>
                <button class="btn filter-btn" data-filter="บริโภคในครัวเรือน">ครัวเรือน</button>
            </div>
            
            <div class="d-flex align-items-center">
                <div class="view-toggle me-3">
                    <button class="btn view-toggle-btn active" data-view="grid">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                    <button class="btn view-toggle-btn" data-view="list">
                        <i class="bi bi-list-ul"></i>
                    </button>
                </div>
                
                <div class="d-none d-md-block">
                    <span id="mangoCount" class="mango-count">0 รายการ</span>
                </div>
            </div>
        </div>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4" id="mangoList">
            <?php
            while ($row = $result->fetch_assoc()) {
                // ตัด 'uploads/' ออกถ้ามี
                $img_file = isset($row['fruit_image']) ? basename($row['fruit_image']) : null;
                $fruit_image = $img_file ? "../admin/uploads/{$img_file}" : null;
                $name = isset($row['mango_name']) ? $row['mango_name'] : "ไม่ทราบชื่อ";
                $scientificName = isset($row['scientific_name']) ? $row['scientific_name'] : "ไม่ทราบชื่อภาษาอังกฤษ";
                $mango_category = isset($row['mango_category']) ? $row['mango_category'] : "ไม่ทราบประเภท";
                
                // กำหนดคลาสสำหรับแบดจ์ตามประเภท
                $category_class = '';
                if ($mango_category === 'เชิงพาณิชย์') {
                    $category_class = 'category-commercial';
                } elseif ($mango_category === 'เชิงอนุรักษ์') {
                    $category_class = 'category-conservation';
                } elseif ($mango_category === 'บริโภคในครัวเรือน') {
                    $category_class = 'category-household';
                }
                
                // สร้าง absolute path สำหรับ file_exists
                $abs_path = __DIR__ . "/../admin/uploads/" . $img_file;

                echo "<div class='col mango-item' data-category='" . htmlspecialchars($mango_category, ENT_QUOTES) . "'>
                <a href='mango_detail.php?name=" . urlencode($name) . "' class='text-decoration-none text-dark'>
                    <div class='card mango-card'>";
                if ($img_file && file_exists($abs_path)) {
                    echo "<img src='{$fruit_image}' class='card-img-top' alt='{$name}'>";
                } else {
                    echo "<div class='d-flex justify-content-center align-items-center py-5 bg-light'>
                            <i class='bi bi-image text-muted' style='font-size: 3rem;'></i>
                          </div>";
                }
                echo "      <div class='card-body'>
                            <h5 class='card-title'>{$name}</h5>
                            <p class='scientific-name'>{$scientificName}</p> 
                            <span class='category-badge {$category_class}'>{$mango_category}</span>
                        </div>
                    </div>
                </a>
              </div>";
            }
            ?>
        </div>
        
        <div id="emptyState" class="empty-state d-none">
            <i class="bi bi-search"></i>
            <h4>ไม่พบสายพันธุ์มะม่วงที่คุณค้นหา</h4>
            <p>ลองเปลี่ยนคำค้นหาหรือหมวดหมู่ดูนะคะ</p>
            <button class="btn btn-outline-secondary mt-2" id="resetFilters">ล้างตัวกรองทั้งหมด</button>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const filterButtons = document.querySelectorAll('.filter-btn');
            const viewToggleButtons = document.querySelectorAll('.view-toggle-btn');
            const mangoList = document.getElementById('mangoList');
            const emptyState = document.getElementById('emptyState');
            const mangoCount = document.getElementById('mangoCount');
            const resetFiltersBtn = document.getElementById('resetFilters');
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            let currentFilter = '';
            let currentView = 'grid';
            let mangoItems = document.querySelectorAll('.mango-item');
            
            // อัพเดทจำนวนการ์ดเริ่มต้น
            updateMangoCount();
            
            // ฟังก์ชันแสดงผลโหลด
            function showLoading() {
                loadingOverlay.classList.add('show');
            }
            
            function hideLoading() {
                loadingOverlay.classList.remove('show');
            }
            
            // ฟังก์ชันกรองมะม่วง
            function filterMangoes() {
                showLoading();
                
                // หน่วงเวลาเล็กน้อยเพื่อให้เห็นเอฟเฟกต์โหลด
                setTimeout(() => {
                    let filter = searchInput.value.toLowerCase();
                    let visibleItems = 0;
                    
                    mangoItems.forEach(function(item) {
                        let name = item.querySelector('.card-title').textContent.toLowerCase();
                        let cat = item.getAttribute('data-category');
                        let nameMatch = name.includes(filter);
                        let catMatch = !currentFilter || cat === currentFilter;
                        
                        if (nameMatch && catMatch) {
                            item.style.display = "block";
                            visibleItems++;
                        } else {
                            item.style.display = "none";
                        }
                    });
                    
                    // แสดงหรือซ่อน empty state
                    if (visibleItems === 0) {
                        emptyState.classList.remove('d-none');
                        mangoList.classList.add('d-none');
                    } else {
                        emptyState.classList.add('d-none');
                        mangoList.classList.remove('d-none');
                    }
                    
                    // อัพเดทจำนวนการ์ด
                    mangoCount.textContent = `${visibleItems} รายการ`;
                    
                    hideLoading();
                }, 300);
            }
            
            // ฟังก์ชันอัพเดทจำนวนการ์ด
            function updateMangoCount() {
                const visibleItems = document.querySelectorAll('.mango-item:not([style*="display: none"])').length;
                mangoCount.textContent = `${visibleItems} รายการ`;
            }
            
            // ฟังก์ชันเปลี่ยนมุมมอง
            function changeView(viewType) {
                currentView = viewType;
                mangoList.className = 'row g-4';
                
                if (viewType === 'grid') {
                    mangoList.classList.add('row-cols-1', 'row-cols-md-2', 'row-cols-lg-4', 'grid-view');
                } else {
                    mangoList.classList.add('list-view');
                }
                
                // อัพเดทปุ่มมุมมอง
                viewToggleButtons.forEach(btn => {
                    if (btn.getAttribute('data-view') === viewType) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            }
            
            // Event Listeners
            searchInput.addEventListener('input', filterMangoes);
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // อัพเดทปุ่มที่ active
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    currentFilter = this.getAttribute('data-filter');
                    filterMangoes();
                });
            });
            
            viewToggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    changeView(this.getAttribute('data-view'));
                });
            });
            
            resetFiltersBtn.addEventListener('click', function() {
                searchInput.value = '';
                currentFilter = '';
                
                filterButtons.forEach(btn => {
                    btn.classList.remove('active');
                    if (btn.getAttribute('data-filter') === '') {
                        btn.classList.add('active');
                    }
                });
                
                filterMangoes();
            });
            
            // เพิ่มเอฟเฟกต์เมื่อโหลดหน้าเสร็จ
            window.addEventListener('load', function() {
                setTimeout(() => {
                    document.body.classList.add('loaded');
                }, 500);
            });
        });
    </script>
</body>

</html>