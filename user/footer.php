<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    
    <style>
        :root {
                    --green-color: #016A70;
                    --white-color: #fff;
                    --Primary: #4e73df;
                    --Success:rgb(27, 78, 59);
                    --Info: #36b9cc;
                    --Warning: #f6c23e;
                    --Danger: #e74a3b;
                    --Secondary: #858796;
                    --Light: #f8f9fc;
                    --Dark: #5a5c69;
            }


        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Kanit", sans-serif;
        }
        
        .footer {
            background-color: var(--Success);
            color: #ffffff;
            padding: 50px 20px 20px;
            font-size: 0.95rem;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }
        
        .footer-section {
            padding: 0 10px;
        }
        
        .footer-section h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 25px;
            color: var(--Light);
            position: relative;
            padding-bottom: 12px;
        }
        
        /* .footer-section h3::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--Light);
            border-radius: 3px;
        } */
        
        .footer-section p {
            margin-bottom: 15px;
            line-height: 1.7;
            color: var(--Light);
        }
        
        .contact-info {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .contact-info i {
            margin-right: 12px;
            color:var(--Light);
            min-width: 20px;
            font-size: 1.1rem;
            margin-top: 4px;
        }
        
        .contact-info a {
            color:var(--Light);
            transition: all 0.3s;
        }
        
        .contact-info a:hover {
            color: var(--Danger);
            text-decoration: underline;
        }
        
        .copyright {
            text-align: center;
            padding-top: 30px;
            margin-top: 40px;
            border-top: 2px solid rgb(162, 165, 165);
            font-size: 0.9rem;
            color:var(--Light);
        }
        
        .footer-links {
            display: flex;
            flex-direction: column;
        }
        
        .footer-links a {
            display: block;
            color:var(--Light);
            text-decoration: none;
            padding: 10px 0;
            transition: all 0.3s;
            position: relative;
            padding-left: 25px;
        }
        
        .footer-links a:hover {
            color:var(--Danger);
            transform: translateX(5px);
        }
        
        .footer-links a::before {
            content: "•";
            position: absolute;
            left: 0;
            top: 10px;
            color:var(--Light);
            font-size: 1.2rem;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #fff;
            color: #009688;
            font-size: 1.4rem;
            margin: 0 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: background 0.2s, color 0.2s, transform 0.2s;
            border: 2px solid #e0e0e0;
        }
        
        .social-links a:hover {
            color: #fff;
            transform: translateY(-4px) scale(1.08);
        }
        
        .social-fb { background: #1877f2; color: #fff; border-color: #1877f2; }
        .social-fb:hover { background: #145db2; }
        .social-line { background: #00c300; color: #fff; border-color: #00c300; }
        .social-line:hover { background: #009900; }
        .social-yt { background: #ff0000; color: #fff; border-color: #ff0000; }
        .social-yt:hover { background: #b20000; }
        .social-ig { 
            background: linear-gradient(135deg, #fdc468 0%, #df4996 100%);
            color: #fff; 
            border: none;
        }
        .social-ig:hover { 
            background: linear-gradient(135deg, #df4996 0%, #fdc468 100%);
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .footer-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 40px 20px;
            }
        }
        
        @media (max-width: 576px) {
            .footer-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .footer-section {
                margin-bottom: 15px;
            }
            
            .footer-section h3 {
                margin-bottom: 20px;
            }
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            background: var(--Danger);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .logo i {
            font-size: 2rem;
            color: var(--Light);
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--Danger);
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&family=Sarabun:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <div class="logo-section">
                    <div class="logo">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <div class="logo-text">การเกษตร</div>
                </div>
                <p>เราคือหน่วยงานวิจัยที่มุ่งเน้นการพัฒนาเทคโนโลยีทางการเกษตรและระบบสารสนเทศเพื่อยกระดับภาคการเกษตรไทย</p>
                <p>ดำเนินงานภายใต้ความร่วมมือระหว่างสถาบันการศึกษาและภาคเอกชน</p>
                
                <div class="social-links">
                    <a href="https://www.facebook.com/lungphuakgarden" target="_blank" class="social-fb" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://line.me/" target="_blank" class="social-line" title="Line">
                        <i class="fab fa-line"></i>
                    </a>
                    <a href="https://youtube.com/" target="_blank" class="social-yt" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://instagram.com/" target="_blank" class="social-ig" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>เมนู</h3>
                <div class="footer-links">
                    <a href="../user/index.php">หน้าแรก</a>
                    <a href="../user/mango_varieties.php">สายพันธุ์ทั้งหมด</a>
                    <a href="../user/course.php">หลักสูตรการเรียนรู้</a>
                    <a href="../user/products.php">สินค้าผลิตภัณฑ์</a>
                    <a href="../user/activities.php">จองวันเข้าดูงาน</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>ติดต่อเรา</h3>
                <div class="contact-info">
                    <i class="fas fa-map-marker-alt"></i>
                    <p>สวนลุงเผือก บ.บุฮม อ.เชียงคาน จ.เลย</p>
                </div>
                <div class="contact-info">
                    <i class="fas fa-phone-alt"></i>
                    <p><a href="tel:0621970420">062-197-0420</a></p>
                </div>
                <div class="contact-info">
                    <i class="fas fa-envelope"></i>
                    <p><a href="mailto:kwanhata.i@ku.th">psrimachan@gmail.com</a></p>
                </div>
                 <div class="contact-info">
                    <i class="fas fa-building"></i>
                    <p>สถาฐานที่ บ.บุฮม</p>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>ลิงก์ที่เกี่ยวข้อง</h3>
                <div class="footer-links">
                    <a href="#">งานวิจัยล่าสุด</a>
                    <a href="#">ข่าวสารและกิจกรรม</a>
                    <a href="#">เอกสารเผยแพร่</a>
                    <a href="#">ร่วมงานกับเรา</a>
                    <a href="#">ถามตอบ (Q&A)</a>
                    <a href="#">แผนผังเว็บไซต์</a>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>Developed and maintained by Nuengdiaw Thiaksiboon and Sukanda Somsiamg, Full-Stack Developer</p>
            <p>© 2025 Mango Database. คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏเลย</p>
        </div>
    </footer>
</body>
</html>