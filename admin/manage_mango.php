<?php
// ข้อมูลตัวอย่างที่จำลองจากฐานข้อมูล
$mango_varieties = [
    [
        'id' => 1,
        'name' => 'น้ำดอกไม้',
        'scientific_name' => 'Mangifera indica',
        'local_name' => 'น้ำดอกไม้',
        'morphological_characteristics' => [
            'stem' => 'ลำต้นสูงและตรง',
            'fruit' => 'ผลทรงกลม ขนาดกลาง',
            'leaf' => 'ใบกว้าง สีเขียวเข้ม',
        ],
        'propagation' => 'การขยายพันธุ์โดยการตอนกิ่ง และการเพาะเมล็ด',
        'soil_characteristics' => 'ดินร่วนระบายน้ำดี',
        'planting_duration' => 'ระยะเวลาเพาะปลูกประมาณ 4-6 เดือน',
        'harvest_season' => 'ช่วงฤดูกาลเก็บเกี่ยวคือช่วงเดือนเมษายน-พฤษภาคม',
        'processing_methods' => [
            'preserved' => 'นิยมทำเป็นมะม่วงดองหรือมะม่วงแช่อิ่ม',
            'fresh' => 'นิยมรับประทานสด',
        ],
        'mango_category' => 'หมวดมะม่วงเชิงพาณิชย์',
        'description' => 'ลำต้นสูงและตรง ผลทรงกลม ขนาดกลาง ใบกว้าง สีเขียวเข้ม',
        'images' => [
            'fruit' => 'https://image.makewebeasy.net/makeweb/m_1920x0/vYbyNLJY1/Fruit/bcfad4f70f02b816bea05818b0b40fe0.jpg',
            'tree' => 'https://www.palangkaset.com/wp-content/uploads/2018/05/4.%E0%B8%81%E0%B8%A3%E0%B8%B0%E0%B8%95%E0%B8%B8%E0%B9%89%E0%B8%99%E0%B9%83%E0%B8%AB%E0%B9%89%E0%B8%A3%E0%B8%B2%E0%B8%81%E0%B8%94%E0%B8%B9%E0%B8%94%E0%B8%8B%E0%B8%B6%E0%B8%A1%E0%B8%AA%E0%B8%B2%E0%B8%A3%E0%B8%AD%E0%B8%B2%E0%B8%AB%E0%B8%B2%E0%B8%A3%E0%B8%A1%E0%B8%B2%E0%B9%83%E0%B8%8A%E0%B9%89%E0%B9%80%E0%B8%AD%E0%B8%87%E0%B8%88%E0%B8%B2%E0%B8%81%E0%B8%94%E0%B8%B4%E0%B8%99.jpg',
            'leaf' => 'https://inwfile.com/s-cl/aawzer.jpg',
            'flower' => 'https://inwfile.com/s-ds/n49cdc.jpg',
            'branch' => 'https://www.technologychaoban.com/wp-content/uploads/2019/01/7-13.jpg'
        ]
    ],
    [
        'id' => 2,
        'name' => 'เขียวเสวย',
        'scientific_name' => 'Mangifera indica',
        'local_name' => 'เขียวเสวย',
        'morphological_characteristics' => [
            'stem' => 'ลำต้นแข็งแรงและสูงตรง',
            'fruit' => 'ผลทรงกระบอก ขนาดใหญ่ สีเขียวอมเหลืองเมื่อสุก',
            'leaf' => 'ใบกว้าง รูปรี สีเขียวเข้ม',
        ],
        'propagation' => 'การขยายพันธุ์โดยการเพาะเมล็ดและการตอนกิ่ง',
        'soil_characteristics' => 'ดินร่วนซุย มีการระบายน้ำดี',
        'planting_duration' => 'ระยะเวลาเพาะปลูกประมาณ 5-7 เดือน',
        'harvest_season' => 'ช่วงฤดูกาลเก็บเกี่ยวคือช่วงเดือนมีนาคม-เมษายน',
        'processing_methods' => [
            'preserved' => 'นิยมทำเป็นมะม่วงดอง มะม่วงแช่อิ่ม',
            'fresh' => 'นิยมรับประทานสด',
        ],
        'mango_category' => 'หมวดมะม่วงเชิงพาณิชย์',
        'description' => 'ลำต้นแข็งแรง ผลขนาดใหญ่ สีเขียวอมเหลืองเมื่อสุก ใบสีเขียวเข้ม',
        'images' => [
            'fruit' => 'https://onniorganicfarm.com/wp-content/uploads/2021/03/IMG_0343.jpg',
            'tree' => 'https://www.technologychaoban.com/wp-content/uploads/2017/04/089.jpg',
            'leaf' => 'https://www.kasettambon.com/wp-content/uploads/2021/03/%E0%B9%83%E0%B8%9A%E0%B8%A1%E0%B8%B0%E0%B8%A1%E0%B9%88%E0%B8%A7%E0%B8%87-600x398.jpg',
            'flower' => 'https://www.parichfertilizer.com/wp-content/uploads/mango.jpg',
            'branch' => 'https://www.technologychaoban.com/wp-content/uploads/2019/01/7-13.jpg'
        ]
    ],
    // เพิ่มข้อมูลตัวอย่างอื่นๆ ตามต้องการ
];
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสายพันธุ์มะม่วง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: "Kanit", sans-serif;
        }

        .btn {
            transition: transform 0.3s ease;
            transition: 0.3s;
        }

        .btn:hover {
            transform: translateY(-5px);
        }
    </style>
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <div class="d-flex">
        <div class="p-4" style="margin-left: 250px; flex: 1;">
            <h2><i class='bx bx-detail'></i> จัดการสายพันธุ์มะม่วง</h2>
            <a href="add_mango.php" class="btn btn-primary mb-3"><i class='bx bx-plus'></i> เพิ่มสายพันธุ์</a>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr style="text-align: center;">
                        <th>รูปผลมะม่วง</th>
                        <th>รูปต้นมะม่วง</th>
                        <th>รูปใบมะม่วง</th>
                        <th>รูปดอกมะม่วง</th>
                        <th>รูปกลิ่งมะม่วง</th>
                        <th>ชื่อสายพันธุ์</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mango_varieties as $mango): ?>
                        <tr>
                            <td style="text-align: center;">
                                <img src="<?= $mango['images']['fruit']; ?>" style="width: 110px; height: 70px; object-fit: cover;">
                            </td>
                            <td style="text-align: center;">
                                <img src="<?= $mango['images']['tree']; ?>" style="width: 110px; height: 70px; object-fit: cover;">
                            </td>
                            <td style="text-align: center;">
                                <img src="<?= $mango['images']['leaf']; ?>" style="width: 110px; height: 70px; object-fit: cover;">
                            </td>
                            <td style="text-align: center;">
                                <img src="<?= $mango['images']['flower']; ?>" style="width: 110px; height: 70px; object-fit: cover;">
                            </td>
                            <td style="text-align: center;">
                                <img src="<?= $mango['images']['branch']; ?>" style="width: 110px; height: 70px; object-fit: cover;">
                            </td>

                            <td style="text-align: center;"><?= $mango['name']; ?></td>

                            <td style="text-align: center;">
                                <a href="edit_mango.php?id=<?= $mango['id']; ?>" class="btn btn-warning btn-sm text-light"><i class='bx bxs-edit'></i> แก้ไข</a>
                                <a href="#" class="btn btn-secondary btn-sm"><i class='bx bx-trash-alt'></i> ลบ</a>
                                <button type="button" class="btn btn-info btn-sm text-light" data-bs-toggle="modal" data-bs-target="#mangoDetailsModal"
                                    data-id="<?= $mango['id']; ?>"
                                    data-name="<?= $mango['name']; ?>"
                                    data-scientific-name="<?= $mango['scientific_name']; ?>"
                                    data-local-name="<?= $mango['local_name']; ?>"
                                    data-stem="<?= $mango['morphological_characteristics']['stem']; ?>"
                                    data-fruit="<?= $mango['morphological_characteristics']['fruit']; ?>"
                                    data-leaf="<?= $mango['morphological_characteristics']['leaf']; ?>"
                                    data-propagation="<?= $mango['propagation']; ?>"
                                    data-soil="<?= $mango['soil_characteristics']; ?>"
                                    data-planting-duration="<?= $mango['planting_duration']; ?>"
                                    data-harvest-season="<?= $mango['harvest_season']; ?>"
                                    data-preserved="<?= $mango['processing_methods']['preserved']; ?>"
                                    data-fresh="<?= $mango['processing_methods']['fresh']; ?>"
                                    data-category="<?= $mango['mango_category']; ?>"
                                    data-description="<?= $mango['description']; ?>"
                                    data-fruit-img="<?= $mango['images']['fruit']; ?>"
                                    data-tree-img="<?= $mango['images']['tree']; ?>"
                                    data-leaf-img="<?= $mango['images']['leaf']; ?>"
                                    data-flower-img="<?= $mango['images']['flower']; ?>"
                                    data-branch-img="<?= $mango['images']['branch']; ?>"><i class='bx bx-search'></i> ดูข้อมูลทั้งหมด</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="mangoDetailsModal" tabindex="-1" aria-labelledby="mangoDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mangoDetailsModalLabel">ข้อมูลรายละเอียดสายพันธุ์มะม่วง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- ข้อมูลจะถูกอัปเดตโดย JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        var mangoDetailsModal = document.getElementById('mangoDetailsModal');
        mangoDetailsModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var scientificName = button.getAttribute('data-scientific-name');
            var localName = button.getAttribute('data-local-name');
            var stem = button.getAttribute('data-stem');
            var fruit = button.getAttribute('data-fruit');
            var leaf = button.getAttribute('data-leaf');
            var propagation = button.getAttribute('data-propagation');
            var soil = button.getAttribute('data-soil');
            var plantingDuration = button.getAttribute('data-planting-duration');
            var harvestSeason = button.getAttribute('data-harvest-season');
            var preserved = button.getAttribute('data-preserved');
            var fresh = button.getAttribute('data-fresh');
            var category = button.getAttribute('data-category');
            var description = button.getAttribute('data-description');
            var fruitImg = button.getAttribute('data-fruit-img');
            var treeImg = button.getAttribute('data-tree-img');
            var leafImg = button.getAttribute('data-leaf-img');
            var flowerImg = button.getAttribute('data-flower-img');
            var branchImg = button.getAttribute('data-branch-img');

            var modalTitle = mangoDetailsModal.querySelector('.modal-title');
            var modalBody = mangoDetailsModal.querySelector('.modal-body');
            modalTitle.textContent = 'ข้อมูลรายละเอียดสายพันธุ์มะม่วง: ' + name;
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h5>ข้อมูลทั่วไป</h5>
                        <p><strong>ชื่อวิทยาศาสตร์:</strong> ${scientificName}</p>
                        <p><strong>ชื่อท้องถิ่น:</strong> ${localName}</p>
                        <p><strong>หมวดหมู่:</strong> ${category}</p>
                        <p><strong>คำอธิบาย:</strong> ${description}</p>
                        
                        <h5>ลักษณะทางสัณฐานวิทยา</h5>
                        <p><strong>ลำต้น:</strong> ${stem}</p>
                        <p><strong>ผล:</strong> ${fruit}</p>
                        <p><strong>ใบ:</strong> ${leaf}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>การปลูกและการดูแล</h5>
                        <p><strong>การขยายพันธุ์:</strong> ${propagation}</p>
                        <p><strong>ลักษณะดิน:</strong> ${soil}</p>
                        <p><strong>ระยะเวลาการปลูก:</strong> ${plantingDuration}</p>
                        <p><strong>ฤดูกาลเก็บเกี่ยว:</strong> ${harvestSeason}</p>
                        
                        <h5>วิธีการแปรรูป</h5>
                        <p><strong>แปรรูป:</strong> ${preserved}</p>
                        <p><strong>รับประทานสด:</strong> ${fresh}</p>
                    </div>
                </div>
                <hr>
                <h5>รูปภาพ</h5>
<div class="row">
    <div class="col-md-4">
        <strong>ผลมะม่วง</strong><br>
        <img src="${fruitImg}" style="width: 100%; height: 150px; object-fit: cover;">
    </div>
    <div class="col-md-4">
        <strong>ต้นมะม่วง</strong><br>
        <img src="${treeImg}" style="width: 100%; height: 150px; object-fit: cover;">
    </div>
    <div class="col-md-4">
        <strong>ใบมะม่วง</strong><br>
        <img src="${leafImg}" style="width: 100%; height: 150px; object-fit: cover;">
    </div>
    <div class="col-md-4">
        <strong>ดอกมะม่วง</strong><br>
        <img src="${flowerImg}" style="width: 100%; height: 150px; object-fit: cover;">
    </div>
    <div class="col-md-4">
        <strong>กิ่งมะม่วง</strong><br>
        <img src="${branchImg}" style="width: 100%; height: 150px; object-fit: cover;">
    </div>
</div>

            `;
        });
    </script>
    <a href="add_mango.php" class="btn btn-primary mb-3"><i class='bx bx-plus'></i> เพิ่มสายพันธุ์</a>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

</body>

</html>