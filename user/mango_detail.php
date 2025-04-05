<?php
// mango_detail.php
if (!isset($_GET['name'])) {
    header('Location: mango_varieties.php');
    exit;
}

$name = $_GET['name'];

$mangoes = [
    "กะล่อนทอง" => [
        "eng_name" => "Kalon Thong",
        "scientific_name" => "Mangifera indica 'Kalon Thong'",
        "local_name" => "มะม่วงกะล่อนทอง",
        "image" => "กะล่อนทอง.png",
        "desc" => "มะม่วงที่มีสีเหลืองทอง รสชาติหวานอร่อย",
        "morphology" => [
            "trunk" => "ลำต้นขนาดกลาง เปลือกเรียบ สีน้ำตาล",
            "fruit" => "ผลขนาดปานกลาง สีเหลืองทอง เนื้อหวานฉ่ำ",
            "leaf" => "ใบเรียวยาว สีเขียวเข้ม"
        ],
        "propagation" => "การตอนกิ่ง และการทาบกิ่ง",
        "soil" => "ดินร่วนปนทราย มีการระบายน้ำดี",
        "growing_period" => "ประมาณ 3-5 ปี ถึงให้ผลผลิต",
        "flowering_season" => "ระหว่างเดือนพฤศจิกายน - กุมภาพันธ์",
        "processing" => [
            "preserved" => "นิยมกวนเป็นมะม่วงกวน",
            "pickled" => "สามารถดองได้",
            "candied" => "นิยมแช่อิ่ม",
            "fresh" => "นิยมรับประทานสด"
        ],
        "category" => "เชิงพาณิชย์",
        "images" => [
            "tree" => "ต้น.jpg",
            "leaf" => "ใบ.jpg",
            "branch" => "กิ่ง.jpg",
            "flower" => "ดอก.jpg"
        ]
    ],
    "แก้วขมิ้น" => [
        "eng_name" => "Kaew Khamin",
        "scientific_name" => "Mangifera indica 'Kaew Khamin'",
        "local_name" => "แก้วขมิ้น",
        "image" => "แก้วขมิ้น.png",
        "desc" => "มะม่วงที่มีสีเหลืองทอง รสชาติหวานอร่อย",
        "morphology" => [
            "trunk" => "ลำต้นขนาดกลาง เปลือกเรียบ สีน้ำตาล",
            "fruit" => "ผลขนาดปานกลาง สีเหลืองทอง เนื้อหวานฉ่ำ",
            "leaf" => "ใบเรียวยาว สีเขียวเข้ม"
        ],
        "propagation" => "การตอนกิ่ง และการทาบกิ่ง",
        "soil" => "ดินร่วนปนทราย มีการระบายน้ำดี",
        "growing_period" => "ประมาณ 3-5 ปี ถึงให้ผลผลิต",
        "flowering_season" => "ระหว่างเดือนพฤศจิกายน - กุมภาพันธ์",
        "processing" => [
            "preserved" => "นิยมกวนเป็นมะม่วงกวน",
            "pickled" => "สามารถดองได้",
            "candied" => "นิยมแช่อิ่ม",
            "fresh" => "นิยมรับประทานสด"
        ],
        "category" => "เชิงพาณิชย์",
        "images" => [
            "tree" => "ต้น.jpg",
            "leaf" => "ใบ.jpg",
            "branch" => "กิ่ง.jpg",
            "flower" => "ดอก.jpg"
        ]
        ],
    "แก้วขาว" => [
        "eng_name" => "Kaew Khao",
        "scientific_name" => "Mangifera indica 'Kaew Khamin'",
        "local_name" => "แก้วขาว",
        "image" => "แก้วขาว.png",
        "desc" => "มะม่วงที่มีสีเหลืองทอง รสชาติหวานอร่อย",
        "morphology" => [
            "trunk" => "ลำต้นขนาดกลาง เปลือกเรียบ สีน้ำตาล",
            "fruit" => "ผลขนาดปานกลาง สีเหลืองทอง เนื้อหวานฉ่ำ",
            "leaf" => "ใบเรียวยาว สีเขียวเข้ม"
        ],
        "propagation" => "การตอนกิ่ง และการทาบกิ่ง",
        "soil" => "ดินร่วนปนทราย มีการระบายน้ำดี",
        "growing_period" => "ประมาณ 3-5 ปี ถึงให้ผลผลิต",
        "flowering_season" => "ระหว่างเดือนพฤศจิกายน - กุมภาพันธ์",
        "processing" => [
            "preserved" => "นิยมกวนเป็นมะม่วงกวน",
            "pickled" => "สามารถดองได้",
            "candied" => "นิยมแช่อิ่ม",
            "fresh" => "นิยมรับประทานสด"
        ],
        "category" => "เชิงพาณิชย์",
        "images" => [
            "tree" => "ต้น.jpg",
            "leaf" => "ใบ.jpg",
            "branch" => "กิ่ง.jpg",
            "flower" => "ดอก.jpg"
        ]
        ],
    "เขียวเสวย" => [
        "eng_name" => "Kheaw Swei",
        "scientific_name" => "Mangifera indica 'Kheaw Swei'",
        "local_name" => "เขียวเสวย",
        "image" => "เขียวเสวย.png",
        "desc" => "มะม่วงที่มีสีเหลืองทอง รสชาติหวานอร่อย",
        "morphology" => [
            "trunk" => "ลำต้นขนาดกลาง เปลือกเรียบ สีน้ำตาล",
            "fruit" => "ผลขนาดปานกลาง สีเหลืองทอง เนื้อหวานฉ่ำ",
            "leaf" => "ใบเรียวยาว สีเขียวเข้ม"
        ],
        "propagation" => "การตอนกิ่ง และการทาบกิ่ง",
        "soil" => "ดินร่วนปนทราย มีการระบายน้ำดี",
        "growing_period" => "ประมาณ 3-5 ปี ถึงให้ผลผลิต",
        "flowering_season" => "ระหว่างเดือนพฤศจิกายน - กุมภาพันธ์",
        "processing" => [
            "preserved" => "นิยมกวนเป็นมะม่วงกวน",
            "pickled" => "สามารถดองได้",
            "candied" => "นิยมแช่อิ่ม",
            "fresh" => "นิยมรับประทานสด"
        ],
        "category" => "เชิงพาณิชย์",
        "images" => [
            "tree" => "ต้น.jpg",
            "leaf" => "ใบ.jpg",
            "branch" => "กิ่ง.jpg",
            "flower" => "ดอก.jpg"
        ]
    ]
];

$mango = $mangoes[$name] ?? null;

if (!$mango) {
    echo "ไม่พบข้อมูลสายพันธุ์";
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title><?= $name ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    
    <style>
        :root {
        --Primary: #4e73df;
        --Success: #1cc88a;
        --Info: #36b9cc;
        --Warning: #f6c23e;
        --Danger:rgb(246, 49, 31);
        --Secondary: #858796;
        --Light: #f8f9fc;
        --Dark: #5a5c69;
        }

        .col-6 img {
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .col-6:hover img {
            transform: scale(1.1);
        }
        .row h2 {
            font-weight: 600;
        }
        .row h4 {
            font-weight: 600;
        }
        p strong {
            font-weight: 400;
        }
        .container h4 {
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <br>
    <div class="container py-5 mt-5">
        <div class="row">
            <!-- คอลัมน์ซ้าย -->
            <div class="col-md-4">
                <h2 class="mb-4"><?= $name ?> (<?= $mango['eng_name'] ?>)</h2>
                <img src="image/<?= $mango['image'] ?>" class="img-fluid mb-3" alt="<?= $name ?>" style="max-height: 400px;">

            </div>

            <!-- คอลัมน์ขวา -->
            <div class="col-md-4">
                <h4>ข้อมูลทั่วไป</h4>
                <p><strong>ชื่อวิทยาศาสตร์:</strong> <?= $mango['scientific_name'] ?></p>
                <p><strong>ชื่อท้องถิ่น:</strong> <?= $mango['local_name'] ?></p>
                <p><strong>รายละเอียด:</strong> <?= $mango['desc'] ?></p>

                <h4 class="mt-4">ลักษณะสัณฐานวิทยา</h4>
                <p><strong>ลำต้น:</strong> <?= $mango['morphology']['trunk'] ?></p>
                <p><strong>ผล:</strong> <?= $mango['morphology']['fruit'] ?></p>
                <p><strong>ใบ:</strong> <?= $mango['morphology']['leaf'] ?></p>

                <h4>การเพาะปลูก</h4>
                <p><strong>การขยายพันธุ์:</strong> <?= $mango['propagation'] ?></p>
                <p><strong>ลักษณะดิน:</strong> <?= $mango['soil'] ?></p>
                <p><strong>ระยะเวลาเพาะปลูก:</strong> <?= $mango['growing_period'] ?></p>
                <p><strong>ช่วงฤดูกาลออกดอก:</strong> <?= $mango['flowering_season'] ?></p>

            </div>
            <div class="col-md-4">
                <h4>การแปรรูป</h4>
                <p><strong>กวน:</strong> <?= $mango['processing']['preserved'] ?></p>
                <p><strong>ดอง:</strong> <?= $mango['processing']['pickled'] ?></p>
                <p><strong>แช่อิ่ม:</strong> <?= $mango['processing']['candied'] ?></p>
                <p><strong>นิยมรับประทานสด:</strong> <?= $mango['processing']['fresh'] ?></p>

                <h4 class="mt-4">หมวดหมู่มะม่วง</h4>
                <p><strong>ประเภท:</strong> <?= $mango['category'] ?></p>

            </div>
        </div>

        <h4 class="mt-5">รูปภาพ</h4>

      <div class="row text-center">
    <div class="col-6 col-md-3 mb-3">
        <h6>ต้น</h6>
        <img src="image/<?= $mango['images']['tree'] ?>" class="img-fluid mb-3" alt="ต้นมะม่วง <?= $name ?>" style="object-fit: cover; width: 100%; height: 200px;">
    </div>
    <div class="col-6 col-md-3 mb-3">
        <h6>ใบ</h6>
        <img src="image/<?= $mango['images']['leaf'] ?>" class="img-fluid mb-3" alt="ใบมะม่วง <?= $name ?>" style="object-fit: cover; width: 100%; height: 200px;">
    </div>
    <div class="col-6 col-md-3 mb-3">
        <h6>กิ่ง</h6>
        <img src="image/<?= $mango['images']['branch'] ?>" class="img-fluid mb-3" alt="กิ่งมะม่วง <?= $name ?>" style="object-fit: cover; width: 100%; height: 200px;">
    </div>
    <div class="col-6 col-md-3 mb-3">
        <h6>ดอก</h6>
        <img src="image/<?= $mango['images']['flower'] ?>" class="img-fluid mb-3" alt="ดอกมะม่วง <?= $name ?>" style="object-fit: cover; width: 100%; height: 200px;">
    </div>
</div>
        <a href="mango_varieties.php" class="btn btn-secondary mt-4">← กลับหน้ารวม</a>
    </div>

<?php include 'footer.php'; ?>

</body>

</html>