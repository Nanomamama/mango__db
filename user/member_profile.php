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
            --secondary: #f5b553;
            --accent: #e74a3b;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --gray: #6c757d;
            --border: #e1e5eb;
        }
        
        body {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            font-family: 'Kanit', sans-serif;
            min-height: 100vh;
            padding: 20px;
        }
        
        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.3);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            overflow: hidden;
        }
        
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .profile-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        
        .profile-subtitle {
            font-size: 18px;
            font-weight: 300;
            opacity: 0.9;
        }
        
        .profile-content {
            padding: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        
        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
        }
        
        .info-card {
            background: var(--light);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border);
            transition: transform 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        
        .card-header i {
            font-size: 24px;
            color: var(--primary);
            margin-right: 12px;
        }
        
        .card-header h3 {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
            color: var(--primary);
        }
        
        .info-item {
            display: flex;
            margin-bottom: 15px;
        }
        
        .info-label {
            min-width: 120px;
            font-weight: 500;
            color: var(--dark);
            display: flex;
            align-items: center;
        }
        
        .info-label i {
            margin-right: 8px;
            color: var(--gray);
        }
        
        .info-value {
            flex: 1;
            color: var(--dark);
            font-weight: 400;
            word-break: break-word;
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 25px;
        }
        
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px 15px;
            text-align: center;
            transition: all 0.3s ease;
            color: var(--dark);
            text-decoration: none;
        }
        
        .action-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(39, 120, 89, 0.2);
            border-color: var(--primary);
        }
        
        .action-btn:hover i {
            color: white;
        }
        
        .action-btn i {
            font-size: 32px;
            margin-bottom: 12px;
            color: var(--primary);
            transition: all 0.3s ease;
        }
        
        .action-btn span {
            font-size: 16px;
            font-weight: 500;
        }
        
        .logout-btn {
            background: linear-gradient(135deg, var(--accent) 0%, #f6c23e 100%);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 18px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 30px auto 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(231, 74, 59, 0.25);
        }
        
        .logout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(231, 74, 59, 0.35);
        }
        
        .logout-btn i {
            margin-right: 8px;
            font-size: 24px;
        }
        
        .edit-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 8px 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .edit-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .edit-btn i {
            margin-right: 5px;
        }
        
        .badge-status {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            background: #e8f5e9;
            color: var(--primary);
        }
        
        .full-address {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-top: 10px;
            border: 1px solid var(--border);
        }
         .main-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 20px;
            flex-wrap: wrap;
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
        }
        
        .full-address {
            background: white;
            border-radius: 12px;
            padding: 18px;
            margin-top: 15px;
            border: 1px solid var(--border);
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.05);
            font-size: 16px;
            line-height: 1.6;
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
        
        @media (max-width: 768px) {
            .profile-content {
                padding: 20px 15px;
            }
            
            .profile-header {
                padding: 30px 20px 20px;
            }
            
            .profile-avatar {
                width: 110px;
                height: 110px;
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
                padding: 15px;
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
        }

    </style>
</head>
<body>
    <div class="profile-container">
        <!-- ส่วนหัวโปรไฟล์ -->
        <div class="profile-header">
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
                        <div></div>
                        <div class="full-address">
                          บ้าน  <?php echo htmlspecialchars($address); ?> ตำบล <?php echo htmlspecialchars($subdistrict_name); ?> อำเภอ <?php echo htmlspecialchars($district_name); ?> จังหวัด <?php echo htmlspecialchars($province_name); ?> <?php echo htmlspecialchars($zipcode); ?>
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