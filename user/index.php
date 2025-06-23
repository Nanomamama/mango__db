<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าแรกผู้ใช้</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --Primary: #4e73df;
            --Success:rgb(20, 58, 44);
            --Info: #36b9cc;
            --Warning: #f6c23e;
            --Danger:  #e74a3b;;
            --Secondary: #858796;
            --Light: #f8f9fc;
            --Dark: #5a5c69;
            --Darkss:#000;
        }

        .hero {
            height: 100vh;
            background-image: url('./image/1-9.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            background-color: rgba(0, 0, 0, 0.5);
            background-blend-mode: darken;
        }

        .hero-contact {
            margin-top: 10rem;
        }

        .hero h1 {
            color: var(--Light);
            font-size: 60px;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .hero p {
            margin: 1rem;
            color: var(--Light);
            font-size: 18px;
        }

        .hero p samp {
             margin: 1rem;
            color: var(--Light);
            font-size: 18px;
            font-family: "Kanit", sans-serif;
        }

        .button-2 {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .button-2 a {
            border-radius: 20px;
            padding: 0.5rem 1.5rem;
            font-weight: bold;
            color: var(--Light);
            border: 1px solid var(--Light);
            background-color: transparent;
            transition: background-color 0.5s ease, color 0.5s ease;
        }

        .button-2 a:hover {
            background-color: var(--Light);
            color: var(--Success);
            transition: 0.5s;
        }

        .card-body {
            font-family: "Kanit", sans-serif;
        }

        .naw {
            font-family: "Kanit", sans-serif;
        }

        .aboutcontainer {
            margin-top: 7rem;
        }

        .aboutcontainer h1 {
            font-size: 40px;
            font-weight: 500;
            color: var(--Danger);

        }

        .aboutcontainer p {
            font-size: 18px;
            color: var(--Dark);

        }

        
        @media (max-width: 640px) {
            .hero h1 {
                color: var(--Light);
                font-size: 36px;
                font-weight: 700;
                margin-bottom: 1rem;
            }

            .hero p {
                color: var(--Light);
                font-size: 18px;
            }

            .hero p samp {
                color: var(--Light);
                font-size: 18px;
                font-family: "Kanit", sans-serif;
            }

            .aboutcontainer h1 {
                font-size: 24px;
                font-weight: 500;
                color: var(--Danger);

            }

            .aboutcontainer p {
                font-size: 18px;
                color: var(--Dark);

            }

        }

        .mango-card {
            border-radius: 12px;
            transition: box-shadow 0.3s;
            cursor: pointer;
            background-color: #f8f9fa;
            overflow: hidden;
        }

        .mango-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.13);
            /* ไม่มี transform: scale() */
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
            color: var(--Darks);
        }
            .link-underline-hover {
            position: relative;
            text-decoration: none;
            }
            .link-underline-hover::after {
            content: "";
            display: block;
            position: absolute;
            left: 0; right: 0; bottom: 0;
            height: 3px;
            background:var(--Success);
            transform: scaleX(0);
            transition: transform 0.2s;
            }
            .link-underline-hover:hover::after {
            transform: scaleX(1);
            }
            .mango-item {
                margin-bottom: 20px;
            }

            .mango-item a {
                text-decoration: none;
                color: inherit;
            }

            .mango-item p {
                margin: 0;
            }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="hero text-center">
        <div class="hero-contact">
            <h1>สวนมะม่วงลุงเผือก<br />จังหวัดเลย</h1>
            <p>เป็นฐานข้อมูลรวบรวมข้อมูลเกี่ยวกับมะม่วงในจังหวัดเลย ครอบคลุมข้อมูลด้านต่างๆ
                <br>
                <samp>กรณีศึกษา สวนลุงเผือก บ.บุฮม อ.เชียงคาน จ.เลย</samp>
            </p>
            <div class="button-2">
                <a href="../user/mango_varieties.php" class="btn cta-button bg-white"style="color:rgb(20, 58, 44);">ดูพันธุ์มะม่วง</a>
                <a href="../user/course.php" class="btn cta-button">เรียนรู้เพิ่ม →</a>
            </div>
        </div>
    </div>
    <div class="container mt-5">
        <br>
        <h2 class="text-center mb-4">สายพันธุ์มะม่วงที่น่าสนใจ</h2>
        <br>
        <div class="row">
            <div class="text-start mb-2">
                <a class="btn btn-dack fs-5 link-underline-hover" href="../user/mango_varieties.php">พันธุ์มะม่วงทั้งหมด</a>
            </div>
        </div>
        <div class="row row-cols-2 row-cols-lg-4 g-4" id="mangoList">
            <?php
            require_once '../admin/db.php';

            $query = "SELECT * FROM mango_varieties WHERE mango_category = 'เชิงอนุรักษ์' LIMIT 4";
            $result = $conn->query($query);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // ดึงชื่อไฟล์รูปผลมะม่วง
                    $img_file = isset($row['fruit_image']) ? basename($row['fruit_image']) : null;
                    // Path สำหรับแสดงผล (ปรับ path ให้ตรงกับที่เก็บไฟล์จริง)
                    $fruit_image = $img_file ? "../admin/uploads/{$img_file}" : null;
                    // Absolute path สำหรับตรวจสอบไฟล์
                    $abs_path = __DIR__ . "/../admin/uploads/" . $img_file;

                    $name = isset($row['mango_name']) ? $row['mango_name'] : "ไม่ทราบชื่อ";
                    $mango_category = isset($row['mango_category']) ? $row['mango_category'] : "ไม่ทราบประเภท";

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
                                    </div>
                                </div>
                            </a>
                        </div>";
                }
            } else {
                echo "<div class='col'><p>ไม่พบข้อมูลสายพันธุ์มะม่วง</p></div>";
            }
            ?>
        </div>
    </div>
    <br>
    <br>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>