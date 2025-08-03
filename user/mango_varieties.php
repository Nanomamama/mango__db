<?php
require_once '../admin/db.php'; // เชื่อมต่อฐานข้อมูล

// ดึงข้อมูลมะม่วงจากฐานข้อมูล
$query = "SELECT * FROM mango_varieties";
$result = $conn->query($query);

if (!$result) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลมะม่วง: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สายพันธุ์มะม่วง</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --green-color: #016A70;
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

        .mango-card {
            border-radius: 12px;
            transition: transform 0.3s;
            cursor: pointer;
            background-color: #f8f9fa;
            perspective: 600px;
            overflow: hidden;
            animation: fadeIn 0.7s;
        }

        .mango-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.13);
        }

        .mango-card img {
            width: 100%;
            height: 250px;
            object-fit: contain;
            padding: 15px;
            transition: transform 0.35s cubic-bezier(.34,1.56,.64,1);
            will-change: transform;
            display: block;
        }

        .mango-card:hover img {
            transform: translateY(-10px) scale(1.05) rotate(-2deg);
        }

        .mango-card .card-body {
            text-align: center;
        }

        .mango-card .card-title {
            font-weight: bold;
        }

        .container h2 {
            font-weight: 600;
            /* color: var(--Danger); */
        }

        .fade-in {
            animation: fadeIn 0.5s forwards;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="container py-5">
        <br>
        <h2 class="text-center mb-4 mt-5">สายพันธุ์มะม่วง ในจังหวัดเลย</h2>
        <br>
        <div class="mb-4 d-flex justify-content-center">
            <div class="input-group" style="max-width: 500px;">
                <span class="input-group-text bg-white border-end-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#888" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11 6a5 5 0 1 1-1.001-9.999A5 5 0 0 1 11 6zm-1 0a4 4 0 1 0-8 0 4 4 0 0 0 8 0zm6.707 11.293-3.387-3.387A6.978 6.978 0 0 0 13 6a7 7 0 1 0-7 7 6.978 6.978 0 0 0 3.906-1.08l3.387 3.387a1 1 0 0 0 1.414-1.414z"/>
                    </svg>
                </span>
                <input type="text" id="searchInput" class="form-control border-start-0" placeholder="ค้นหาสายพันธุ์มะม่วง" aria-label="ค้นหาสายพันธุ์มะม่วง">
                <select id="categorySelect" class="form-select ms-2" style="max-width:180px;">
                    <option value="">ทุกประเภท</option>
                    <option value="เชิงพาณิชย์">เชิงพาณิชย์</option>
                    <option value="เชิงอนุรักษ์">เชิงอนุรักษ์</option>
                    <option value="บริโภคในครัวเรือน">บริโภคในครัวเรือน</option>
                </select>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4" id="mangoList">
            <?php
            while ($row = $result->fetch_assoc()) {
                // ตัด 'uploads/' ออกถ้ามี
                $img_file = isset($row['fruit_image']) ? basename($row['fruit_image']) : null;
                $fruit_image = $img_file ? "../admin/uploads/{$img_file}" : null;
                $name = isset($row['mango_name']) ? $row['mango_name'] : "ไม่ทราบชื่อ";
                $scientificName = isset($row['scientific_name']) ? $row['scientific_name'] : "ไม่ทราบชื่อวิทยาศาสตร์";
                $mango_category = isset($row['mango_category']) ? $row['mango_category'] : "ไม่ทราบประเภท";

                // สร้าง absolute path สำหรับ file_exists
                $abs_path = __DIR__ . "/../admin/uploads/" . $img_file;

                // DEBUG: แสดง path และผลลัพธ์ file_exists
                echo "<!-- fruit_image: {$fruit_image}, abs_path: {$abs_path}, exists: " . ($img_file && file_exists($abs_path) ? 'YES' : 'NO') . " -->";

                echo "<div class='col mango-item' data-category='" . htmlspecialchars($mango_category, ENT_QUOTES) . "'>
                <a href='mango_detail.php?name=" . urlencode($name) . "' class='text-decoration-none text-dark'>
                    <div class='card mango-card'>";
                if ($img_file && file_exists($abs_path)) {
                    echo "<img src='{$fruit_image}' class='card-img-top' alt='{$name}'>";
                } else {
                    echo "<div class='text-center py-5'>ไม่มีรูปภาพ</div>";
                }
                echo "      <div class='card-body'>
                            <h5 class='card-title'>{$name}</h5>
                            <p class='text-muted'>{$scientificName}</p> 
                            <p class='text-muted'>{$mango_category}</p> 
                        </div>
                    </div>
                </a>
              </div>";
            }
            ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('searchInput').addEventListener('input', filterMangoes);
        document.getElementById('categorySelect').addEventListener('change', filterMangoes);

        function filterMangoes() {
            let filter = document.getElementById('searchInput').value.toLowerCase();
            let category = document.getElementById('categorySelect').value;
            let mangoItems = document.querySelectorAll('.mango-item');

            let visibleIndex = 0;
            mangoItems.forEach(function(item) {
                let name = item.querySelector('.card-title').textContent.toLowerCase();
                let cat = item.getAttribute('data-category');
                let nameMatch = name.includes(filter);
                let catMatch = !category || cat === category;
                let card = item.querySelector('.mango-card');

                // รีเซ็ต animation ก่อน
                card.style.animation = 'none';
                card.offsetHeight; // trigger reflow

                if (nameMatch && catMatch) {
                    item.style.display = "block";
                    card.classList.add('fade-in');
                    card.style.animation = `fadeIn 0.7s`;
                    card.style.animationDelay = `${visibleIndex * 0.1}s`;
                    visibleIndex++;
                } else {
                    item.style.display = "none";
                    card.classList.remove('fade-in');
                    card.style.animation = 'none';
                    card.style.animationDelay = '0s';
                }
            });
        }
    </script>
</body>

</html>