<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สายพันธุ์มะม่วง</title>
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
        }
        body {
            background-color: #f8f9fa;
            height: 200vh;
        }
        .mango-card {
            border-radius: 12px;
            transition: transform 0.3s;
            cursor: pointer;
            background-color: #f8f9fa;
        }
        .mango-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .mango-card img {
            width: 100%;
            height: 250px;
            object-fit: contain;
            padding: 15px;
        }
        .mango-card .card-body {
            text-align: center;
        }
        .mango-card .card-title {
            font-weight: bold;
        }
        .container h2 {
            font-weight: 600;
            color: var(--Danger);
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <br>
        <h2 class="text-center mb-4 mt-5">สายพันธุ์มะม่วง</h2>
        <br>
        <div class="mb-4 text-center">
            <input type="text" id="searchInput" class="form-control w-100 mx-auto" placeholder="ค้นหาสายพันธุ์มะม่วง">
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4" id="mangoList">
            <?php 
            $mangoes = [
                ["name" => "กะล่อนทอง", "eng_name" => "Kalon Thong", "image" => "กะล่อนทอง.png", "desc" => "มะม่วงที่มีสีเหลืองทอง รสชาติหวานอร่อย"],
                ["name" => "แก้วขมิ้น", "eng_name" => "Kaew Khamin", "image" => "แก้วขมิ้น.png", "desc" => "มะม่วงพันธุ์โบราณ เปลือกสีเหลืองเข้ม"],
                ["name" => "แก้วขาว", "eng_name" => "Kaew Khao", "image" => "แก้วขาว.png", "desc" => "มีเนื้อสีขาวใส รสชาติเปรี้ยวอมหวาน"],
                ["name" => "เขียวเสวย", "eng_name" => "Kheaw Swei", "image" => "เขียวเสวย.png", "desc" => "มะม่วงยอดนิยม รสชาติหวานมัน"],
                ["name" => "ขอช้าง", "eng_name" => "Khor Chang", "image" => "ขอช้าง.png", "desc" => "มะม่วงพันธุ์แปลก รูปทรงโค้งเหมือนงาช้าง"],
                ["name" => "แขกขายตึก", "eng_name" => "Kaek Khai Tuek", "image" => "แขกขายตึก.png", "desc" => "พันธุ์หายาก เนื้อแน่น หอมหวาน"],
                ["name" => "โชคอนันต์", "eng_name" => "Chok Anan", "image" => "โชคอนันต์.png", "desc" => "มะม่วงที่นิยมปลูกเพื่อการค้า รสชาติอร่อย"],
                ["name" => "ตลับนาค", "eng_name" => "Talab Nak", "image" => "ตลับนาค.png", "desc" => "มีเปลือกสีทองสวย หอม หวานละมุน"]
            ];
            
            foreach ($mangoes as $index => $mango) {
                $imagePath = "image/{$mango['image']}";
                if (!file_exists($imagePath)) {
                    $imagePath = "image/default.png";
                }
            
                echo "<div class='col mango-item' data-bs-toggle='modal' data-bs-target='#mangoModal' 
                        data-name='{$mango['name']}' 
                        data-eng_name='{$mango['eng_name']}' 
                        data-image='{$imagePath}' 
                        data-desc='{$mango['desc']}'>
                        <div class='card mango-card'>
                            <img src='{$imagePath}' class='card-img-top' alt='{$mango['name']}'>
                            <div class='card-body'>
                                <h5 class='card-title'>{$mango['name']}</h5>
                                <p class='text-muted'>{$mango['eng_name']}</p>
                            </div>
                        </div>
                      </div>";
            }
            ?>
        </div>
    </div>

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="mangoModal" tabindex="-1" aria-labelledby="mangoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mangoModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img id="mangoModalImage" src="" class="img-fluid mb-3" alt="">
                    <p id="mangoModalDesc"></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('searchInput').addEventListener('input', function() {
            let filter = this.value.toLowerCase();
            let mangoItems = document.querySelectorAll('.mango-item');

            mangoItems.forEach(function(item) {
                let name = item.getAttribute('data-name').toLowerCase();
                if (name.includes(filter)) {
                    item.style.display = "block";
                } else {
                    item.style.display = "none";
                }
            });
        });

        // จัดการ Modal ให้แสดงข้อมูลของมะม่วงที่คลิก
        let mangoModal = document.getElementById('mangoModal');
        mangoModal.addEventListener('show.bs.modal', function (event) {
            let button = event.relatedTarget;
            let name = button.getAttribute('data-name');
            let engName = button.getAttribute('data-eng_name');
            let image = button.getAttribute('data-image');
            let desc = button.getAttribute('data-desc');

            document.getElementById('mangoModalLabel').textContent = name + " (" + engName + ")";
            document.getElementById('mangoModalImage').src = image;
            document.getElementById('mangoModalDesc').textContent = desc;
        });
    </script>
</body>
</html>
