<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>หลักสูตรการอบรมแบบมีฐานการเรียนรู้</title>

   <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <style>
    /* * {
      font-family: "Prompt", sans-serif;
    } */
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
    .hero h2,
    .hero p {
      text-align: center;
      font-weight: 600;
      color: var(--Danger);
    }
    .base-icon {
      font-size: 40px;
      color: #0d6efd;
    }
    .carousel-item img {
    height: 300px;
    object-fit: cover;
    }

    @media (max-width: 768px) {
      .carousel-item img {
        height: 350px;
      }
    }
    .card-body h5{
      color: var(--Danger);
      font-weight: 600;
    }
    .card-body a{
      padding: 0.5rem 1.5rem;
      border-radius: 20px;
      font-weight: bold;
      color:var(--Danger);
      border: 1px solid var(--Danger);
      background-color: transparent;
      transition:background-color 0.5s ease, color 0.5s ease;

    }
    .card-body a:hover{
      background-color: var(--Danger);
      color: var(--Light);
      transition: 0.5s;
    }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>

  <!-- Hero Section -->
  <br><br>
  <section class="hero">
    <div class="container mt-5 text-center">
      <br>
      <h2>ศูยน์การเรียนรู้เศรษฐกิจพอเพียง สวนลุงเผือก อ.เชียงคาน จ.เลย</h2>
      <p class="lead">เรียนรู้ผ่านการลงมือทำ เสริมสร้างทักษะที่ใช้ได้จริง</p>
    </div>
  </section>

  <!-- About Section -->
  <section>
    <div class="container text-center">
      <p>การอบรมให้ความรู้ เกี่ยวกับเศรษฐกิจพอเพียง และเกษตรอินทรีย์ ฉบับลุงเผือก </p>
    </div>
  </section>

  <!-- Course Structure -->
  <section class="bg-light py-5">
    <div class="container">
      <h2 class="mb-5">กิจกรรมการอบรม</h2>
      <div class="row justify-content-center">
       <!-- START ROW ของฐานทั้ง 5 -->
<div class="row">
  <!-- ฐานที่ 1 -->
  <div class="col-md-4 mb-4">
    <div class="card">
      <div id="carouselBase1" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="https://www.baanwin.com/_files/pictures/2563/7/001.jpg" class="d-block w-100" alt="รูปที่ 1">
          </div>
          <div class="carousel-item">
            <img src="https://www.doa.go.th/ac/nakhonsawan/wp-content/uploads/2021/09/241233872_1966184576864532_2288258166794160560_n.jpg" class="d-block w-100" alt="รูปที่ 2">
          </div>
          <div class="carousel-item active">
            <img src="https://orgweb.ldd.go.th/sources/img/part_executive_news/IMG_9936_666a573b58181.jpg" class="d-block w-100" alt="รูปที่ 1">
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselBase1" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselBase1" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
      </div>
      <div class="card-body text-center">
        <h5 class="card-title">ฐานที่ 1</h5>
        <p>ฟังบรรยายให้ความรู้</p>
        <a href="#demo-1" class="btn " data-bs-toggle="collapse">ดูรายละเอียด</a>
        <div id="demo-1" class="collapse">
           <p> Lorem ipsum dolor sit amet consectetur, adipisicing elit. Incidunt ad provident est, eaque exercitationem nobis pariatur, illo nam, similique animi ab eligendi consequuntur odio doloremque quam cupiditate blanditiis facere? Voluptas recusandae id fugiat officiis consequatur atque minus itaque dolores iste dolorem, tempore nostrum ab natus qui commodi fuga fugit nemo.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ฐานที่ 2 -->
  <div class="col-md-4 mb-4">
    <div class="card">
      <div id="carouselBase2" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="https://www.kasetkaoklai.com/home/wp-content/uploads/2020/08/10520.jpg" class="d-block w-100" alt="รูปที่ 1">
          </div>
          <div class="carousel-item">
            <img src="https://www.technologychaoban.com/wp-content/uploads/2023/01/410390-1024x768.jpg" class="d-block w-100" alt="รูปที่ 2">
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselBase2" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselBase2" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
      </div>
      <div class="card-body text-center">
      <h5 class="card-title">ฐานที่ 2</h5>
      <p>ความรู้สารชีวะภาพและสารเคมี</p>
        <a href="#demo-2" class="btn " data-bs-toggle="collapse">ดูรายละเอียด</a>
        <div id="demo-2" class="collapse">
           <p>ฐานนี้จะบรรยายให้ความรู้เกี่ยวกับเศรษฐกิจพอเพี่ยง การทำสวนมะม่วง เทคนิคการทำสวน</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ฐานที่ 3 -->
  <div class="col-md-4 mb-4">
    <div class="card">
      <div id="carouselBase3" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="https://thecitizen.plus/wp-content/uploads/2022/05/279117180_7345604058843335_7645027622622327390_n-1024x768.jpg" class="d-block w-100" alt="รูปที่ 1">
          </div>
          <div class="carousel-item">
            <img src="https://mpics.mgronline.com/pics/Images/563000004425701.JPEG" class="d-block w-100" alt="รูปที่ 2">
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselBase3" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselBase3" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
      </div>
      <div class="card-body text-center">
        <h5 class="card-title">ฐานที่ 3</h5>
        <p>ชมสวนมะม่วง</p>
        <a href="#demo-3" class="btn " data-bs-toggle="collapse">ดูรายละเอียด</a>
        <div id="demo-3" class="collapse">
           <p>ฐานนี้จะบรรยายให้ความรู้เกี่ยวกับเศรษฐกิจพอเพี่ยง การทำสวนมะม่วง เทคนิคการทำสวน</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ฐานที่ 4 -->
  <div class="col-md-6 mb-4">
    <div class="card">
      <div id="carouselBase4" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="https://thecitizen.plus/wp-content/uploads/2020/04/94360141_1304484809761739_3199230059713921024_n.jpg" class="d-block w-100" alt="รูปที่ 1">
          </div>
          <div class="carousel-item">
            <img src="https://thecitizen.plus/wp-content/uploads/2020/04/93169866_1299382463605307_3904226311169638400_n.jpg" class="d-block w-100" alt="รูปที่ 2">
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselBase4" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselBase4" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
      </div>
      <div class="card-body text-center">
        <h5 class="card-title">ฐานที่ 4</h5>
        <p>ชมล้งขายมะม่วง</p>
        <a href="#demo-4" class="btn " data-bs-toggle="collapse">ดูรายละเอียด</a>
        <div id="demo-4" class="collapse">
           <p>ฐานนี้จะบรรยายให้ความรู้เกี่ยวกับเศรษฐกิจพอเพี่ยง การทำสวนมะม่วง เทคนิคการทำสวน</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ฐานที่ 5 -->
  <div class="col-md-6 mb-4">
    <div class="card">
      <div id="carouselBase5" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="https://th.kku.ac.th/wp-content/uploads/2023/06/DSC09609-500x375.jpg" class="d-block w-100" alt="รูปที่ 1">
          </div>
          <div class="carousel-item">
            <img src="https://saraburi.doae.go.th/wihandaeng/wp-content/uploads/2024/05/474881_0-1024x768.jpg" class="d-block w-100" alt="รูปที่ 2">
          </div>
          <div class="carousel-item">
            <img src="https://rbkm.kmutt.ac.th/retrieve/16573" class="d-block w-100" alt="รูปที่ 2">
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselBase5" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselBase5" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
      </div>
      <div class="card-body text-center">
        <h5 class="card-title">ฐานที่ 5</h5>
        <p>สอนวิธีขยายพันธุ์มะม่วง</p>
        <a href="#demo-5" class="btn " data-bs-toggle="collapse">ดูรายละเอียด</a>
        <div id="demo-5" class="collapse">
           <p>ฐานนี้จะบรรยายให้ความรู้เกี่ยวกับเศรษฐกิจพอเพี่ยง การทำสวนมะม่วง เทคนิคการทำสวน</p>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- END ROW -->
    </div>
  </section>

  <!-- Suitable for -->
  <section class="py-5">
    <div class="container">
      <h2 class="mb-4">ใครเหมาะกับหลักสูตรนี้?</h2>
      <ul>
        <li>ผู้นำทีม / หัวหน้างาน</li>
        <li>ผู้ที่สนใจเกษตร</li>
        <li>--</li>
      </ul>
    </div>
  </section>

  <!-- Schedule and Register -->
  <section class="bg-light py-5" id="register">
    <div class="container">
      <h2 class="mb-4">ตารางเวลาและการสมัคร</h2>
      <p><strong>ระยะเวลาอบรม:</strong> 1 วัน (09:00 - 16:00)</p>
      <p><strong>รับจำนวนจำกัด:</strong> 25 คน</p>
      <p><strong>ค่าลงทะเบียน:</strong> 1,500 บาท</p>
      <a href="activities.php" class="btn btn-primary">สมัครอบรมออนไลน์</a>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="py-5">
    <div class="container">
      <h2 class="mb-4">--</h2>
     
    </div>
  </section>
  <?php include 'footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
