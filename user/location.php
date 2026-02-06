<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location Section - For Inclusion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

        /* Location Section Styles */
        .location-section {
            font-family: 'Noto Sans Thai', sans-serif;
            padding: 60px 0;
            /* background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); */
            background-color: #f8f9fc;
            border-radius: 16px;
            margin: 40px 0;
            /* box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); */
        }

        .location-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .location-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .location-header h2 {
            font-size: 2.5rem;
            color: #1a2a3a;
            margin-bottom: 15px;
            font-weight: 600;
            position: relative;
            display: inline-block;
        }

        .location-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, #5ae6ab, #016A70);
            border-radius: 2px;
        }

        .location-header p {
            font-size: 1.1rem;
            color: #666;
            max-width: 700px;
            margin: 25px auto 0;
            line-height: 1.7;
        }

        .location-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        /* Location Info Styles */
        .location-info-card {
            background-color: white;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .location-info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .location-info-card h3 {
            font-size: 1.8rem;
            color: #1a2a3a;
            margin-bottom: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .location-info-card h3 i {
            color: #016A70;
        }

        .info-item {
            display: flex;
            margin-bottom: 25px;
            align-items: flex-start;
        }

        .info-icon {
            background: linear-gradient(135deg, #5ae6ab 0%, #016A70 100%);
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 18px;
            color: white;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .info-text h4 {
            font-size: 1.2rem;
            color: #1a2a3a;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .info-text p {
            color: #666;
            line-height: 1.6;
        }

        /* Map Styles */
        .map-card {
            background-color: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .map-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .map-header {
            padding: 20px 30px;
            background: linear-gradient(135deg, #2c3e50 0%, #1a2a3a 100%);
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .map-header h3 {
            font-size: 1.5rem;
            font-weight: 500;
        }

        .map-header i {
            color: #016A70;
        }

        .map-responsive {
            position: relative;
            padding-bottom: 60%;
            /* Aspect ratio 5:3 */
            height: 0;
            overflow: hidden;
        }

        .map-responsive iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        /* Operating Hours */
        .operating-hours {
            background-color: white;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            max-width: 1000px;
            margin: 0 auto;
        }

        .hours-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .hours-header h3 {
            font-size: 1.8rem;
            color: #1a2a3a;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .hours-header i {
            color: #016A70;
        }

        .hours-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .day-time {
            display: flex;
            justify-content: space-between;
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            transition: background-color 0.3s;
        }

        .day-time:hover {
            background-color: #e9ecef;
        }

        .day {
            font-weight: 600;
            color: #1a2a3a;
        }

        .time {
            color: #016A70;
            font-weight: 600;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .location-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .location-header h2 {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 768px) {
            .location-section {
                padding: 40px 0;
            }

            .location-header h2 {
                font-size: 1.9rem;
            }

            .location-info-card,
            .operating-hours {
                padding: 25px;
            }

            .info-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-icon {
                margin-bottom: 12px;
            }

            .hours-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .location-header h2 {
                font-size: 1.7rem;
            }

            .location-header p {
                font-size: 1rem;
            }

            .map-header {
                padding: 15px 20px;
            }

            .map-header h3 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>

<body>
    <!-- ส่วนนี้สามารถ copy ไป include ในหน้าเว็บหลักได้ -->
    <section class="location-section" id="location">
        <div class="location-container">
            <div class="location-header">
                <h2>ที่ตั้งศูนย์เรียนรู้</h2>
                <p>สนใจสั่งสินค้า หรือ พรีออร์เดอร์ได้ในระบบ มีบริการส่งแค่ในพื้นที่เฉพาะบ้านบุฮม และรับหน้าร้าน สินค้าผลไม้ตามฤดูกาลและสินค้าแปรรูป สนใจติดต่อสอบถามเพิ่มเติมได้ที่เบอร์โทรศัพท์</p>
            </div>

            <div class="location-content">
                <!-- ส่วนข้อมูลที่อยู่และติดต่อ -->
                <div class="location-info-card">
                    <h3><i class="fas fa-store"></i> ข้อมูลร้านค้า</h3>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-text">
                            <h4>ที่อยู่ร้าน</h4>
                            <p>ร้านอิ่มเลย(สวนลุงเผือก)
                                26 4 ตำบล บุฮม อำเภอ เชียงคาน เลย 42110</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="info-text">
                            <h4>เบอร์โทรศัพท์</h4>
                            <p>062-197-0420 , 089-898-0821</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-text">
                            <h4>อีเมล</h4>
                            <p>contact@siamcafe.com</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <div class="info-text">
                            <h4>บริการ</h4>
                            <p> ที่จอดรถ, รับจองล่วงหน้า, บริการส่งสินค้าภายในพื้นที่</p>
                        </div>
                    </div>
                </div>

                <!-- ส่วนแผนที่ -->
                <div class="map-card">
                    <div class="map-header">
                        <i class="fas fa-map"></i>
                        <h3>ค้นหาเส้นทาง</h3>
                    </div>
                    <div class="map-responsive">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d583.2269561143113!2d101.73779659421837!3d17.93598673381639!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3126b932695c11e9%3A0x663ac49e3dd2b5bc!2z4Lij4LmJ4Liy4LiZ4Lit4Li04LmI4Lih4LmA4Lil4LiiKOC4quC4p-C4meC4peC4uOC4h-C5gOC4nOC4t-C4reC4gSk!5e1!3m2!1sth!2sth!4v1769708605758!5m2!1sth!2sth"
                            width="100%"
                            height="450"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            title="แผนที่ร้าน Siam Cafe">
                        </iframe>
                    </div>
                </div>
            </div>

            <!-- ส่วนเวลาทำการ -->
            <div class="operating-hours">
                <div class="hours-header">
                    <h3><i class="fas fa-clock"></i> เวลาเปิด-ปิดทำการ</h3>
                </div>
                <div class="hours-grid">
                    <div class="day-time">
                        <span class="day">จันทร์ - ศุกร์</span>
                        <span class="time">08:00 - 22:00 น.</span>
                    </div>
                    <div class="day-time">
                        <span class="day">เสาร์ - อาทิตย์</span>
                        <span class="time">08:00 - 23:00 น.</span>
                    </div>
                    <div class="day-time">
                        <span class="day">วันหยุดนักขัตฤกษ์</span>
                        <span class="time">09:00 - 21:00 น.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // เพิ่มเอฟเฟกต์เมื่อโหลดหน้าเว็บ
        document.addEventListener('DOMContentLoaded', function() {
            // เอฟเฟกต์แสดงผลแบบ fade in สำหรับการ์ด
            const cards = document.querySelectorAll('.location-info-card, .map-card, .operating-hours');

            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 * index);
            });

            // เอฟเฟกต์ hover สำหรับไอคอนข้อมูล
            const infoIcons = document.querySelectorAll('.info-icon');
            infoIcons.forEach(icon => {
                icon.addEventListener('mouseenter', function() {
                    this.style.transform = 'rotate(10deg) scale(1.1)';
                });

                icon.addEventListener('mouseleave', function() {
                    this.style.transform = 'rotate(0) scale(1)';
                });
            });
        });
    </script>
</body>

</html>