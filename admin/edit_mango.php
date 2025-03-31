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
    ]
    
];

// รับค่า id จาก URL ผ่าน $_GET
$mango_id = isset($_GET['id']) ? $_GET['id'] : null;
$selected_mango = null;

if ($mango_id !== null) {
    foreach ($mango_varieties as $mango) {
        if ($mango['id'] == $mango_id) {
            $selected_mango = $mango;
            break;
        }
    }
}

if ($selected_mango === null) {
    die("ไม่พบข้อมูลที่ต้องการแก้ไข");
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แก้ไขสายพันธุ์มะม่วง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: "Kanit", sans-serif;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h2>✏️ แก้ไขสายพันธุ์มะม่วง</h2>
        <form action="#" method="POST" enctype="multipart/form-data">
            <div class="row">
                <!-- คอลัมน์ซ้าย -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">ชื่อสายพันธุ์</label>
                        <input type="text" class="form-control" name="mango_name" value="<?php echo $selected_mango['name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อวิทยาศาสตร์</label>
                        <input type="text" class="form-control" name="scientific_name" value="<?php echo $selected_mango['scientific_name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อท้องถิ่น</label>
                        <input type="text" class="form-control" name="local_name" value="<?php echo $selected_mango['local_name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ลักษณะสัณฐานวิทยา</label>
                        <input type="text" class="form-control" name="morphology_stem" value="<?php echo $selected_mango['morphological_characteristics']['stem']; ?>" required>
                        <input type="text" class="form-control mt-2" name="morphology_fruit" value="<?php echo $selected_mango['morphological_characteristics']['fruit']; ?>" required>
                        <input type="text" class="form-control mt-2" name="morphology_leaf" value="<?php echo $selected_mango['morphological_characteristics']['leaf']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ลักษณะดิน</label>
                        <input type="text" class="form-control" name="soil_characteristics" value="<?php echo $selected_mango['soil_characteristics']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ระยะเวลาเพาะปลูก</label>
                        <input type="text" class="form-control" name="planting_period" value="<?php echo $selected_mango['planting_duration']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ช่วงฤดูกาลเก็บเกี่ยว</label>
                        <input type="text" class="form-control" name="harvest_season" value="<?php echo $selected_mango['harvest_season']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">การแปรรูป</label><br>
                        <?php
                        $processing_methods = ['กวน', 'ดอง', 'แช่อิ่ม', 'นิยมรับประทานสด'];
                        foreach ($processing_methods as $method) {
                            $checked = (in_array($method, array_values($selected_mango['processing_methods'])) || strpos($selected_mango['processing_methods']['preserved'], $method) !== false || $selected_mango['processing_methods']['fresh'] == $method) ? 'checked' : '';
                            echo '<div class="form-check">';
                            echo '<input class="form-check-input" type="checkbox" name="processing_methods[]" value="' . $method . '" ' . $checked . '>';
                            echo '<label class="form-check-label">' . $method . '</label>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">หมวดมะม่วง</label>
                        <select class="form-select" name="mango_category" required>
                            <?php
                            $categories = ['เชิงพาณิชย์', 'เชิงอนุรักษ์', 'บริโภคในครัวเรือน'];
                            foreach ($categories as $category) {
                                $selected = (strpos($selected_mango['mango_category'], $category) !== false) ? 'selected' : '';
                                echo '<option value="' . $category . '" ' . $selected . '>' . $category . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- คอลัมน์ขวา -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">รูปผลมะม่วง</label><br>
                        <img src="<?php echo $selected_mango['images']['fruit']; ?>" width="100">
                        <input type="file" class="form-control mt-2" name="image_fruit">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รูปต้นมะม่วง</label><br>
                        <img src="<?php echo $selected_mango['images']['tree']; ?>" width="100">
                        <input type="file" class="form-control mt-2" name="image_tree">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รูปใบมะม่วง</label><br>
                        <img src="<?php echo $selected_mango['images']['leaf']; ?>" width="100">
                        <input type="file" class="form-control mt-2" name="image_leaf">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รูปดอกมะม่วง</label><br>
                        <img src="<?php echo $selected_mango['images']['flower']; ?>" width="100">
                        <input type="file" class="form-control mt-2" name="image_flower">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รูปกิ่งมะม่วง</label><br>
                        <img src="<?php echo $selected_mango['images']['branch']; ?>" width="100">
                        <input type="file" class="form-control mt-2" name="image_branch">
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-success">💾 บันทึก</button>
                <a href="manage_mango.php" class="btn btn-secondary">🔙 กลับ</a>
            </div>
        </form>
    </div>
</body>

</html>