<?php
session_start();
require_once '../admin/db.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['member_id'])) {
    header("Location: member_login.php");
    exit;
}

// ดึงข้อมูลสมาชิก
$member_id = $_SESSION['member_id'];
$stmt = $conn->prepare("SELECT fullname, address, province_id, district_id, subdistrict_id, zipcode, phone, email, created_at FROM members WHERE id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$stmt->bind_result($fullname, $address, $province_id, $district_id, $subdistrict_id, $zipcode, $phone, $email, $created_at);
$stmt->fetch();
$stmt->close();

// ดึงชื่อจังหวัด/อำเภอ/ตำบล
function getNameById($file, $id) {
    $data = json_decode(file_get_contents($file), true);
    foreach ($data as $item) {
        if ($item['id'] == $id) return $item['name_th'];
    }
    return '';
}
$province_name = getNameById('../data/api_province.json', $province_id);
$district_name = getNameById('../data/thai_amphures.json', $district_id);
$subdistrict_name = getNameById('../data/thai_tambons.json', $subdistrict_id);

// แปลงวันที่สมัคร
function thaiDate($datetime) {
    $months = [
        "", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
    ];
    $ts = strtotime($datetime);
    $d = date("j", $ts);
    $m = $months[(int)date("n", $ts)];
    $y = date("Y", $ts) + 543;
    return "$d $m $y";
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์สมาชิก - สวนพฤกษชาติไทย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #277859;
            --primary-light: #3d9e78;
            --primary-dark: #1a5c43;
            --secondary: #f5b553;
            --accent: #e74a3b;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --gray: #6c757d;
            --border: #e1e5eb;
            --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            font-family: 'Kanit', sans-serif;
            min-height: 100vh;
            padding: 20px;
            color: var(--dark);
        }
        
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 40px 30px 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,128C960,128,1056,192,1152,192C1248,192,1344,128,1392,96L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: center;
            opacity: 0.1;
        }
        
        .profile-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.3);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 2;
        }
        
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .profile-title {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 2;
        }
        
        .profile-subtitle {
            font-size: 18px;
            font-weight: 300;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        
        .profile-content {
            padding: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            position: relative;
            z-index: 1;
        }
        
        @media (max-width: 992px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
        }
        
        .info-card {
            background: white;
            border-radius: 18px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .info-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--secondary));
        }
        
        .info-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        
        .card-header i {
            font-size: 26px;
            color: var(--primary);
            margin-right: 15px;
            background: rgba(39, 120, 89, 0.1);
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card-header h3 {
            font-size: 22px;
            font-weight: 600;
            margin: 0;
            color: var(--dark);
        }
        
        .info-item {
            display: flex;
            margin-bottom: 18px;
            align-items: flex-start;
        }
        
        .info-label {
            min-width: 140px;
            font-weight: 500;
            color: var(--dark);
            display: flex;
            align-items: center;
            font-size: 16px;
        }
        
        .info-label i {
            margin-right: 10px;
            color: var(--primary);
            font-size: 20px;
        }
        
        .info-value {
            flex: 1;
            color: var(--dark);
            font-weight: 400;
            word-break: break-word;
            font-size: 16px;
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px 12px;
            text-align: center;
            transition: all 0.3s ease;
            color: var(--dark);
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.03);
        }
        
        .action-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(39, 120, 89, 0.2);
            border-color: var(--primary);
        }
        
        .action-btn:hover i {
            color: white;
            transform: scale(1.1);
        }
        
        .action-btn i {
            font-size: 32px;
            margin-bottom: 12px;
            color: var(--primary);
            transition: all 0.3s ease;
        }
        
        .action-btn span {
            font-size: 15px;
            font-weight: 500;
        }
        
        .main-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 30px;
            flex-wrap: wrap;
            background: rgba(248, 249, 250, 0.8);
            border-top: 1px solid var(--border);
        }
        
        .main-btn {
            min-width: 220px;
            padding: 16px 30px;
            font-size: 18px;
            font-weight: 500;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .main-btn::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            z-index: -1;
            transition: transform 0.3s ease;
            transform: scaleX(0);
            transform-origin: right;
        }
        
        .main-btn:hover::before {
            transform: scaleX(1);
            transform-origin: left;
        }
        
        .home-btn {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .home-btn:hover {
            color: white;
            border-color: var(--primary);
        }
        
        .logout-btn {
            background: linear-gradient(135deg, var(--accent), #f6c23e);
            color: white;
            border: none;
        }
        
        .logout-btn:hover {
            color: white;
            box-shadow: 0 8px 25px rgba(231, 74, 59, 0.4);
        }
        
        .edit-btn {
            position: absolute;
            top: 25px;
            right: 25px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(4px);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            font-size: 16px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }
        
        .edit-btn:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: translateY(-2px);
        }
        
        .edit-btn i {
            margin-right: 8px;
            font-size: 20px;
        }
        
        .badge-status {
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(4px);
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-top: 15px;
            position: relative;
            z-index: 2;
        }
        
        .full-address {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 18px;
            margin-top: 15px;
            border: 1px solid var(--border);
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.03);
            font-size: 16px;
            line-height: 1.6;
            position: relative;
        }
        
        .full-address::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
            border-radius: 4px 0 0 4px;
        }
        
        .header-decoration {
            position: absolute;
            bottom: -30px;
            left: 0;
            width: 100%;
            height: 60px;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120"><path fill="%23ffffff" fill-opacity="1" d="M0,64L80,58.7C160,53,320,43,480,48C640,53,800,75,960,74.7C1120,75,1280,53,1360,42.7L1440,32L1440,120L1360,120C1280,120,1120,120,960,120C800,120,640,120,480,120C320,120,160,120,80,120L0,120Z"></path></svg>');
            background-size: cover;
            z-index: 2;
        }
        
        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        
        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .shape-1 {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 5%;
            animation: float 15s infinite ease-in-out;
        }
        
        .shape-2 {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation: float 20s infinite ease-in-out reverse;
        }
        
        .shape-3 {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 15%;
            animation: float 12s infinite ease-in-out;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(10deg);
            }
        }
        
        @media (max-width: 768px) {
            .profile-content {
                padding: 20px 15px;
            }
            
            .profile-header {
                padding: 30px 20px 20px;
            }
            
            .profile-avatar {
                width: 120px;
                height: 120px;
            }
            
            .profile-title {
                font-size: 26px;
            }
            
            .info-item {
                flex-direction: column;
                gap: 8px;
            }
            
            .info-label {
                min-width: auto;
            }
            
            .main-buttons {
                flex-direction: column;
                gap: 15px;
                padding: 20px;
            }
            
            .main-btn {
                width: 100%;
                min-width: auto;
            }
            
            .edit-btn {
                top: 15px;
                right: 15px;
                padding: 8px 16px;
                font-size: 14px;
            }
            
            .card-header i {
                width: 40px;
                height: 40px;
                font-size: 22px;
            }
            
            .card-header h3 {
                font-size: 20px;
            }
        }

    </style>
</head>
<body>
    <div class="profile-container">
        <!-- ส่วนหัวโปรไฟล์ -->
        <div class="profile-header">
            <div class="floating-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>
            
            <button class="edit-btn">
                <i class='bx bx-edit'></i> แก้ไขโปรไฟล์
            </button>
            
            <div class="profile-avatar">
                <img src="../user/image/profile.png" alt="โปรไฟล์">
            </div>
            
            <h1 class="profile-title"><?php echo htmlspecialchars($fullname); ?></h1>
            <p class="profile-subtitle">สมาชิกสวนลุงเผือก</p>
            
            <div class="mt-3">
                <span class="badge-status">
                    <i class='bx bx-check-circle'></i> สถานะ: ใช้งานได้ปกติ
                </span>
            </div>
            
            <div class="header-decoration"></div>
        </div>
        
        <!-- เนื้อหาโปรไฟล์ -->
        <div class="profile-content">
            <!-- ข้อมูลส่วนตัว -->
            <div class="info-card">
                <div class="card-header">
                    <i class='bx bx-id-card'></i>
                    <h3>ข้อมูลส่วนตัว</h3>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class='bx bx-user'></i> ชื่อ-นามสกุล:
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($fullname); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class='bx bx-phone'></i> เบอร์โทร:
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($phone); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class='bx bx-envelope'></i> อีเมล์:
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($email); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class='bx bx-calendar'></i> วันที่สมัคร:
                    </div>
                    <div class="info-value"><?php echo thaiDate($created_at); ?></div>
                </div>
            </div>
            
            <!-- ที่อยู่ -->
            <div class="info-card">
                <div class="card-header">
                    <i class='bx bx-home'></i>
                    <h3>ที่อยู่</h3>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class='bx bx-map'></i> ที่อยู่:
                    </div>
                    <div class="info-value">
                        <div class="full-address">
                          บ้าน <?php echo htmlspecialchars($address); ?> ตำบล <?php echo htmlspecialchars($subdistrict_name); ?> อำเภอ <?php echo htmlspecialchars($district_name); ?> จังหวัด <?php echo htmlspecialchars($province_name); ?> <?php echo htmlspecialchars($zipcode); ?>
                        </div>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class='bx bx-map-pin'></i> จังหวัด:
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($province_name); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class='bx bx-map-pin'></i> อำเภอ:
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($district_name); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class='bx bx-map-pin'></i> ตำบล:
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($subdistrict_name); ?></div>
                </div>
            </div>
            
            <!-- เมนูหลัก -->
            <div class="info-card">
                <div class="card-header">
                    <i class='bx bx-shopping-bag'></i>
                    <h3>การซื้อสินค้า</h3>
                </div>
                
                <div class="action-buttons">
                    <a href="purchase_history.php" class="action-btn">
                        <i class='bx bx-history'></i>
                        <span>ประวัติการซื้อ</span>
                    </a>
                    
                    <a href="order_status.php" class="action-btn">
                        <i class='bx bx-list-check'></i>
                        <span>สถานะการสั่งซื้อ</span>
                    </a>
                </div>
            </div>
            
            <!-- การจองเข้าชม -->
            <div class="info-card">
                <div class="card-header">
                    <i class='bx bx-calendar-event'></i>
                    <h3>การจองเข้าชมสวน</h3>
                </div>
                
                <div class="action-buttons">
                    <a href="booking_history.php" class="action-btn">
                        <i class='bx bx-history'></i>
                        <span>ประวัติการจอง</span>
                    </a>
                    
                    <a href="booking_status.php" class="action-btn">
                        <i class='bx bx-check-circle'></i>
                        <span>สถานะการจอง</span>
                    </a>
                    
                    <a href="activities.php" class="action-btn">
                        <i class='bx bx-plus-circle'></i>
                        <span>จองเข้าชมใหม่</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- ปุ่มหลัก -->
        <div class="main-buttons">
            <a href="index.php" class="main-btn home-btn">
                <i class='bx bx-home-alt'></i> กลับหน้าหลัก
            </a>
            <a href="member_logout.php" class="main-btn logout-btn">
                <i class='bx bx-log-out'></i> ออกจากระบบ
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // เพิ่มเอฟเฟ็กต์เมื่อโหลดหน้าเว็บเสร็จ
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.info-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });
        });
    </script>
</body>
</html>