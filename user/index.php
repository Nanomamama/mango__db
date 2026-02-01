<?php
session_start();
?>

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

        body{
            background-color: #f8f9fc;
        }

        .hero {
            height: 100vh;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .hero-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }

        .hero-contact {
            margin-top: 1rem;
            text-align: center;
            position: relative;
            z-index: 1;
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
            background-color: #fff;
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
        <!-- วิดีโอพื้นหลัง -->
        <video class="hero-video" autoplay muted loop playsinline>
            <source src="/video/background-video.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-contact">
            <h1>ศูนย์เรียนรู้เศรษฐกิจพอเพียง<br />สวนลุงเผือก</h1>
            <p>เว็บไซต์ศูนย์การเรียนรู้เศรษฐกิจพอเพียง และเปิดให้เข้าศึกษาดูงาน
                <br>
                <samp> สวนลุงเผือก บ.บุฮม อ.เชียงคาน จ.เลย</samp>
            </p>
            <div class="button-2">
                <a href="../user/booking.php" class="btn cta-button bg-white"style="color:rgb(20, 58, 44);">จองคิวออนไลน์</a>
                <a href="../user/course.php" class="btn cta-button">เรียนรู้เพิ่ม →</a>
            </div>
        </div>
    </div>
    <?php include 'location.php'; ?>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>