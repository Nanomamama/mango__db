<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าแรกผู้ใช้</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
 
    body  {
        height: 500vh;
        position: relative; 
    }
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
      font-weight: 900;
      margin-bottom: 1rem;
    }
    .hero p {
      color: var(--Light);
      font-size: 18px;
    }
    .hero p samp {
      color: var(--Danger);
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
      border: 1px solid var(--Danger);
      background-color: transparent;
      transition: background-color 0.5s ease, color 0.5s ease;
    }

    .button-2 a:hover {
      background-color: var(--Danger);
      color: var(--Light);
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

    .image-container {
        overflow: hidden;
        height: 300px;
    }

    .image-container img {
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .image-container:hover img {
        transform: scale(1.2);
    }

    .card-body h2 {
        font-size: 24px;
        font-weight: 500;
        color: var(--Danger);
    }
    .card-body p {
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
            color: var(--Danger);
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
<body data-bs-spy="scroll" data-bs-target=".navbar" data-bs-offset="50">


<?php include 'navbar.php'; ?>

<div class="hero text-center">
  <div class="hero-contact">
    <h1>ฐานข้อมูลมะม่วงใน<br/>จังหวัดเลย</h1>
    <!-- <p>กรณีศึกษา สวนลุงเผือก บ.บุฮม อ.เชียงคาน จ.เลย</p> -->
    <p>เป็นฐานข้อมูลรวบรวมข้อมูลเกี่ยวกับมะม่วงในจังหวัดเลย ครอบคลุมข้อมูลด้านต่างๆ
        <br>
        <samp>กรณีศึกษา สวนลุงเผือก บ.บุฮม อ.เชียงคาน จ.เลย</samp></p>
    <div class="button-2">
        <a href="../user/mango_varieties.php" class="btn cta-button">ดูพันธุ์มะม่วง</a>
    </div>
  </div>
</div>

    <!-- About Us Section -->
    <section id="activity" class="container my-5">
        <div class="aboutcontainer">
            <h1 class="text-center mb-4">กิจกรรมที่สวนลุงเผือก</h1>
            <div class="activities text-center">
                <p>เพลิดเพลินกับกิจกรรมหลากหลาย ที่ให้ทั้งความสนุกและความรู้ เกี่ยวกับเกษตรอินทรีย์เก็บผลไม้สดจากสวน เลือกเก็บมะม่วงสดๆ จากต้น พร้อมชิมรสชาติที่หวานฉ่ำเรียนรู้<br>
                    การทำเกษตรอินทรีย์ เรียนรู้เทคนิคการเพาะปลูกแบบปลอดสารเคมีและการทำปุ๋ยหมักปั่นจักรยานชมสวน สัมผัสบรรยากาศธรรมชาติอันร่มรื่นระหว่างทาง <br>
                    กิจกรรม DIY งานฝีมือ ทดลองทำของที่ระลึกจากวัสดุธรรมชาติ เช่น สบู่สมุนไพรให้อาหารสัตว์ในฟาร์ม <br>
                    สนุกกับการให้อาหารไก่ เป็ด และแพะ พร้อมเรียนรู้เกี่ยวกับวิถีชีวิตสัตว์เลี้ยง
                </p>
            </div>

        </div>
    </section>
    <section id="gallery" class="container my-5">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="image-container">
                        <img src="./image/image.jpg" class="card-img-top zoom" alt="Cafe Aroma">
                    </div>
                </div>
                <br>
                <div class="card-body">
                    <h2 class="card-title">โครงการอบรมศึกษาดูงาน</h2>
                    <p class="card-text">โครงการอบรมศึกษาดูงาน”ศูนย์การเรียนรู้เศรษฐกิจพอเพียงสวนลุงเผือก และกลุ่มมะพร้าวแก้วเคียงเลยแม่น้อย</p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="image-container">
                        <img src="./image/activity2.jpg" class="card-img-top zoom" alt="โครงการอบรมศึกษาดูงาน">
                    </div>
                </div>
                <br>
                <div class="card-body">
                    <h2 class="card-title">โครงการอบรมศึกษาดูงาน</h2>
                    <p class="card-text">โครงการอบรมศึกษาดูงาน”ศูนย์การเรียนรู้เศรษฐกิจพอเพียงสวนลุงเผือก และกลุ่มมะพร้าวแก้วเคียงเลยแม่น้อย</p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="image-container">
                        <img src="./image/activity1.jpg" class="card-img-top zoom" alt="โครงการอบรมศึกษาดูงาน">
                    </div>
                </div>
                <br>
                <div class="card-body">
                    <h2 class="card-title">โครงการอบรมศึกษาดูงาน</h2>
                    <p class="card-text">โครงการอบรมศึกษาดูงาน”ศูนย์การเรียนรู้เศรษฐกิจพอเพียงสวนลุงเผือก และกลุ่มมะพร้าวแก้วเคียงเลยแม่น้อย</p>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <h2 class="text-center mb-4 mt-5">สายพันธุ์มะม่วงที่น่าสนใจ</h2>
        <br>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4" id="mangoList">
            <?php 
            $mangoes = [
                ["name" => "กะล่อนทอง", "eng_name" => "Kalon Thong", "image" => "กะล่อนทอง.png", "desc" => "มะม่วงที่มีสีเหลืองทอง รสชาติหวานอร่อย"],
                ["name" => "แก้วขมิ้น", "eng_name" => "Kaew Khamin", "image" => "แก้วขมิ้น.png", "desc" => "มะม่วงพันธุ์โบราณ เปลือกสีเหลืองเข้ม"],
                ["name" => "แก้วขาว", "eng_name" => "Kaew Khao", "image" => "แก้วขาว.png", "desc" => "มีเนื้อสีขาวใส รสชาติเปรี้ยวอมหวาน"],
                ["name" => "เขียวเสวย", "eng_name" => "Kheaw Swei", "image" => "เขียวเสวย.png", "desc" => "มะม่วงยอดนิยม รสชาติหวานมัน"]              
            ];
            
            foreach ($mangoes as $mango) {
                $imagePath = "image/{$mango['image']}";
                if (!file_exists($imagePath)) {
                    $imagePath = "image/default.png";
                }

                echo "<div class='col mango-item'>
                        <a href='mango_detail.php?name=" . urlencode($mango['name']) . "' class='text-decoration-none text-dark'>
                            <div class='card mango-card'>
                                <img src='{$imagePath}' class='card-img-top' alt='{$mango['name']}'>
                                <div class='card-body'>
                                    <h5 class='card-title'>{$mango['name']}</h5>
                                    <p class='text-muted'>{$mango['eng_name']}</p>
                                </div>
                            </div>
                        </a>
                      </div>";
            }
            ?>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>