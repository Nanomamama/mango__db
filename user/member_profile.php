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
    <title>โปรไฟล์สมาชิก - สวนมะม่วงลุงเผือก</title>
    
    <!-- Ant Design CSS -->
    <link rel="stylesheet" href="https://unpkg.com/antd@5.0.0/dist/reset.css">
    <!-- Boxicons CSS -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <style>
        :root {
            --ant-primary-color: #1677ff;
            --ant-success-color: #52c41a;
            --ant-warning-color: #faad14;
            --ant-error-color: #ff4d4f;
            --ant-info-color: #1677ff;
            --ant-text-color: rgba(0, 0, 0, 0.88);
            --ant-text-color-secondary: rgba(0, 0, 0, 0.65);
            --ant-border-color: #d9d9d9;
            --ant-border-radius: 6px;
            --ant-box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03), 0 1px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px 0 rgba(0, 0, 0, 0.02);
            --ant-box-shadow-secondary: 0 6px 16px 0 rgba(0, 0, 0, 0.08), 0 3px 6px -4px rgba(0, 0, 0, 0.12), 0 9px 28px 8px rgba(0, 0, 0, 0.05);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
            background-color: #f5f5f5;
            color: var(--ant-text-color);
            line-height: 1.5715;
            min-height: 100vh;
            padding: 0;
        }
        
        .ant-layout {
            display: flex;
            flex: auto;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .ant-layout-header {
            height: 64px;
            padding: 0 50px;
            color: rgba(0, 0, 0, 0.88);
            line-height: 64px;
            background: #001529;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .ant-layout-content {
            flex: auto;
            min-height: 0;
            padding: 24px;
        }
        
        .ant-page-header {
            background-color: #fff;
            border-bottom: 1px solid var(--ant-border-color);
            padding: 16px 24px;
        }
        
        .ant-page-header-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .ant-page-header-heading-left {
            display: flex;
            align-items: center;
        }
        
        .ant-page-header-back {
            margin-right: 16px;
            font-size: 16px;
            line-height: 1;
            color: var(--ant-text-color);
        }
        
        .ant-page-header-heading-title {
            margin-right: 12px;
            margin-bottom: 0;
            color: var(--ant-text-color);
            font-weight: 600;
            font-size: 20px;
            line-height: 32px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        
        .ant-card {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            color: var(--ant-text-color);
            font-size: 14px;
            line-height: 1.5715;
            list-style: none;
            background: #fff;
            border-radius: var(--ant-border-radius);
            border: 1px solid var(--ant-border-color);
        }
        
        .ant-card-head {
            min-height: 48px;
            margin-bottom: -1px;
            padding: 0 24px;
            color: var(--ant-text-color);
            font-weight: 600;
            font-size: 16px;
            background: transparent;
            border-bottom: 1px solid var(--ant-border-color);
            border-radius: var(--ant-border-radius) var(--ant-border-radius) 0 0;
            display: flex;
            align-items: center;
        }
        
        .ant-card-body {
            padding: 24px;
        }
        
        .ant-card-grid {
            padding: 24px;
            box-shadow: 1px 0 0 0 var(--ant-border-color), 0 1px 0 0 var(--ant-border-color), 1px 1px 0 0 var(--ant-border-color), 1px 0 0 0 var(--ant-border-color) inset, 0 1px 0 0 var(--ant-border-color) inset;
        }
        
        .ant-row {
            display: flex;
            flex-flow: row wrap;
            min-width: 0;
        }
        
        .ant-col {
            position: relative;
            max-width: 100%;
            min-height: 1px;
        }
        
        .ant-col-24 {
            display: block;
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        .ant-col-12 {
            display: block;
            flex: 0 0 50%;
            max-width: 50%;
        }
        
        .ant-col-8 {
            display: block;
            flex: 0 0 33.33333333%;
            max-width: 33.33333333%;
        }
        
        .ant-col-6 {
            display: block;
            flex: 0 0 25%;
            max-width: 25%;
        }
        
        .ant-descriptions {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            color: var(--ant-text-color);
            font-size: 14px;
            line-height: 1.5715;
            list-style: none;
        }
        
        .ant-descriptions-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .ant-descriptions-title {
            flex: auto;
            overflow: hidden;
            color: var(--ant-text-color);
            font-weight: 600;
            font-size: 16px;
            line-height: 1.5;
        }
        
        .ant-descriptions-view {
            width: 100%;
            overflow: hidden;
            border-radius: var(--ant-border-radius);
        }
        
        .ant-descriptions-row {
            display: flex;
            border-bottom: 1px solid var(--ant-border-color);
        }
        
        .ant-descriptions-item {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            padding: 12px 0;
        }
        
        .ant-descriptions-item-label {
            flex: 0 0 150px;
            color: var(--ant-text-color-secondary);
            font-weight: 400;
            font-size: 14px;
            line-height: 1.5715;
        }
        
        .ant-descriptions-item-content {
            flex: 1;
            color: var(--ant-text-color);
            font-size: 14px;
            line-height: 1.5715;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        
        .ant-descriptions-item:last-child .ant-descriptions-item-content {
            flex: 1;
        }
        
        .ant-avatar {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            color: var(--ant-text-color);
            font-size: 14px;
            line-height: 1.5715;
            list-style: none;
            position: relative;
            display: inline-block;
            overflow: hidden;
            color: #fff;
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
            background: #ccc;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: var(--ant-box-shadow-secondary);
        }
        
        .ant-avatar-image {
            background: transparent;
        }
        
        .ant-avatar-image img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .ant-tag {
            display: inline-block;
            height: auto;
            margin: 0 8px 0 0;
            padding: 0 7px;
            font-size: 12px;
            line-height: 20px;
            white-space: nowrap;
            background: #fafafa;
            border: 1px solid #d9d9d9;
            border-radius: 4px;
            opacity: 1;
            transition: all 0.2s;
            box-sizing: border-box;
        }
        
        .ant-tag-green {
            color: #52c41a;
            background: #f6ffed;
            border-color: #b7eb8f;
        }
        
        .ant-btn {
            line-height: 1.5715;
            position: relative;
            display: inline-flex;
            align-items: center;
            font-weight: 400;
            white-space: nowrap;
            text-align: center;
            background-image: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.645, 0.045, 0.355, 1);
            user-select: none;
            touch-action: manipulation;
            height: 32px;
            padding: 4px 15px;
            font-size: 14px;
            border-radius: var(--ant-border-radius);
            color: var(--ant-text-color);
            border-color: var(--ant-border-color);
            background: #fff;
            box-shadow: 0 2px 0 rgba(0, 0, 0, 0.02);
            gap: 8px;
            text-decoration: none;
            justify-content: center;
        }
        
        .ant-btn-primary {
            color: #fff;
            border-color: var(--ant-primary-color);
            background: var(--ant-primary-color);
            box-shadow: 0 2px 0 rgba(5, 145, 255, 0.1);
        }
        
        .ant-btn-default {
            color: var(--ant-text-color);
            border-color: var(--ant-border-color);
            background: #fff;
        }
        
        .ant-btn-dashed {
            color: var(--ant-text-color);
            border-color: var(--ant-border-color);
            background: #fff;
            border-style: dashed;
        }
        
        .ant-divider {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            color: var(--ant-text-color);
            font-size: 14px;
            line-height: 1.5715;
            list-style: none;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
        }
        
        .ant-divider-horizontal {
            display: flex;
            clear: both;
            width: 100%;
            min-width: 100%;
            margin: 24px 0;
        }
        
        .ant-space {
            display: inline-flex;
            gap: 8px;
        }
        
        .ant-space-vertical {
            flex-direction: column;
        }
        
        .ant-space-horizontal {
            flex-direction: row;
        }
        
        .ant-statistic {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            color: var(--ant-text-color);
            font-size: 14px;
            line-height: 1.5715;
            list-style: none;
        }
        
        .ant-statistic-title {
            margin-bottom: 4px;
            color: var(--ant-text-color-secondary);
            font-size: 14px;
        }
        
        .ant-statistic-content {
            color: var(--ant-text-color);
            font-size: 24px;
            font-weight: 600;
        }
        
        .ant-list {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            color: var(--ant-text-color);
            font-size: 14px;
            line-height: 1.5715;
            list-style: none;
            position: relative;
        }
        
        .ant-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--ant-border-color);
        }
        
        .ant-list-item:last-child {
            border-bottom: none;
        }
        
        .ant-list-item-meta {
            display: flex;
            flex: 1;
            align-items: flex-start;
        }
        
        .ant-list-item-meta-avatar {
            margin-right: 16px;
        }
        
        .ant-list-item-meta-content {
            flex: 1 0;
        }
        
        .ant-list-item-meta-title {
            margin-bottom: 4px;
            color: var(--ant-text-color);
            font-size: 14px;
            line-height: 1.5715;
        }
        
        .ant-list-item-meta-description {
            color: var(--ant-text-color-secondary);
            font-size: 14px;
            line-height: 1.5715;
        }
        
        .ant-list-item-action {
            flex: 0 0 auto;
            margin-left: 48px;
            padding: 0;
            font-size: 0;
            list-style: none;
        }
        
        .ant-list-item-action > li {
            position: relative;
            display: inline-block;
            padding: 0 8px;
        }
        
        .ant-progress {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            color: var(--ant-text-color);
            font-size: 14px;
            line-height: 1.5715;
            list-style: none;
            display: inline-block;
        }
        
        .ant-progress-line {
            position: relative;
            width: 100%;
            font-size: 14px;
        }
        
        .ant-progress-outer {
            display: inline-block;
            width: 100%;
            margin-right: 0;
            padding-right: 0;
        }
        
        .ant-progress-inner {
            position: relative;
            display: inline-block;
            width: 100%;
            overflow: hidden;
            vertical-align: middle;
            background-color: rgba(0, 0, 0, 0.04);
            border-radius: 100px;
        }
        
        .ant-progress-bg {
            position: relative;
            background-color: var(--ant-primary-color);
            border-radius: 100px;
            transition: all 0.4s cubic-bezier(0.08, 0.82, 0.17, 1) 0s;
            height: 8px;
        }
        
        .ant-progress-text {
            display: inline-block;
            width: 2em;
            margin-left: 8px;
            color: var(--ant-text-color-secondary);
            font-size: 14px;
            line-height: 1;
            white-space: nowrap;
            text-align: left;
            vertical-align: middle;
            word-break: normal;
        }
        
        .ant-progress-success-bg {
            position: absolute;
            top: 0;
            left: 0;
            background-color: var(--ant-success-color);
            border-radius: 100px;
            transition: all 0.4s cubic-bezier(0.08, 0.82, 0.17, 1) 0s;
            height: 8px;
        }
        
        /* Custom Styles */
        .profile-header {
            background: linear-gradient(135deg, #016A70 0%, #018992 100%);
            padding: 40px;
            color: white;
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
        
        .profile-actions {
            position: absolute;
            top: 24px;
            right: 24px;
            z-index: 1;
        }
        
        .profile-info {
            position: relative;
            z-index: 1;
        }
        
        .profile-name {
            font-size: 28px;
            font-weight: 600;
            margin: 16px 0 8px;
        }
        
        .profile-description {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 16px;
        }
        
        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 24px;
        }
        
        .profile-stat-item {
            text-align: center;
        }
        
        .profile-stat-value {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .profile-stat-label {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .action-card {
            height: 100%;
            transition: all 0.3s;
        }
        
        .action-card:hover {
            box-shadow: var(--ant-box-shadow-secondary);
            transform: translateY(-4px);
        }
        
        .action-icon {
            font-size: 32px;
            margin-bottom: 16px;
            color: var(--ant-primary-color);
        }
        
        .action-title {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .action-description {
            color: var(--ant-text-color-secondary);
            font-size: 14px;
        }
        
        .full-address {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 16px;
            margin-top: 8px;
            border: 1px solid var(--ant-border-color);
            font-size: 14px;
            line-height: 1.6;
        }
        
        .gutter-row {
            padding: 12px;
        }
        
        /* Icon Styles */
        .bx {
            font-family: 'boxicons' !important;
            font-weight: normal;
            font-style: normal;
            font-variant: normal;
            line-height: 1;
            display: inline-block;
            text-transform: none;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .card-header-icon {
            margin-right: 8px;
            font-size: 18px;
        }
        
        .list-item-icon {
            font-size: 20px;
            color: var(--ant-primary-color);
        }
        
        @media (max-width: 768px) {
            .ant-col-12, .ant-col-8, .ant-col-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            .profile-stats {
                flex-direction: column;
                gap: 20px;
            }
            
            .profile-header {
                padding: 24px 16px;
            }
            
            .profile-actions {
                position: static;
                margin-bottom: 16px;
                display: flex;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="ant-layout">
        <!-- navbar -->
        <?php include 'navbar.php'; ?>
        <!-- Content -->
        <main class="ant-layout-content mt-5">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-actions">
                    <a href="edit_profile.php" class="ant-btn ant-btn-default" style="background: rgba(255,255,255,0.2); color: white; border-color: rgba(255,255,255,0.3);">
                        <i class='bx bx-edit'></i> แก้ไขโปรไฟล์
                    </a>
                </div>
                
                <div class="profile-info">
                    <div class="ant-avatar ant-avatar-image">
                        <img src="../user/image/profile.png" alt="โปรไฟล์">
                    </div>
                    
                    <h1 class="profile-name"><?php echo htmlspecialchars($fullname); ?></h1>
                    <p class="profile-description">สมาชิกสวนมะม่วงลุงเผือก</p>
                    
                    <div>
                        <span class="ant-tag ant-tag-green">
                            <i class='bx bx-check-circle'></i> สถานะ: ใช้งานได้ปกติ
                        </span>
                    </div>
                    
                    <div class="profile-stats">
                        <div class="profile-stat-item">
                            <div class="profile-stat-value">5</div>
                            <div class="profile-stat-label">การจองทั้งหมด</div>
                        </div>
                        <div class="profile-stat-item">
                            <div class="profile-stat-value">12</div>
                            <div class="profile-stat-label">การซื้อสินค้า</div>
                        </div>
                        <div class="profile-stat-item">
                            <div class="profile-stat-value"><?php echo thaiDate($created_at); ?></div>
                            <div class="profile-stat-label">วันที่สมัครสมาชิก</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Page Header -->
            <div class="ant-page-header">
                <div class="ant-page-header-heading">
                    <div class="ant-page-header-heading-left">
                        <a class="ant-page-header-back" href="index.php">
                            <i class='bx bx-arrow-back'></i>
                        </a>
                        <span class="ant-page-header-heading-title">ข้อมูลโปรไฟล์</span>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="ant-row" style="margin-top: 24px;">
                <!-- Left Column -->
                <div class="ant-col ant-col-12 gutter-row">
                    <!-- Personal Information Card -->
                    <div class="ant-card" style="margin-bottom: 24px;">
                        <div class="ant-card-head">
                            <i class='bx bx-id-card card-header-icon'></i>
                            <span>ข้อมูลส่วนตัว</span>
                        </div>
                        <div class="ant-card-body">
                            <div class="ant-descriptions">
                                <div class="ant-descriptions-row">
                                    <div class="ant-descriptions-item">
                                        <span class="ant-descriptions-item-label">
                                            <i class='bx bx-user'></i> ชื่อ-นามสกุล
                                        </span>
                                        <span class="ant-descriptions-item-content"><?php echo htmlspecialchars($fullname); ?></span>
                                    </div>
                                </div>
                                <div class="ant-descriptions-row">
                                    <div class="ant-descriptions-item">
                                        <span class="ant-descriptions-item-label">
                                            <i class='bx bx-phone'></i> เบอร์โทรศัพท์
                                        </span>
                                        <span class="ant-descriptions-item-content"><?php echo htmlspecialchars($phone); ?></span>
                                    </div>
                                </div>
                                <div class="ant-descriptions-row">
                                    <div class="ant-descriptions-item">
                                        <span class="ant-descriptions-item-label">
                                            <i class='bx bx-envelope'></i> อีเมล
                                        </span>
                                        <span class="ant-descriptions-item-content"><?php echo htmlspecialchars($email); ?></span>
                                    </div>
                                </div>
                                <div class="ant-descriptions-row">
                                    <div class="ant-descriptions-item">
                                        <span class="ant-descriptions-item-label">
                                            <i class='bx bx-calendar'></i> วันที่สมัครสมาชิก
                                        </span>
                                        <span class="ant-descriptions-item-content"><?php echo thaiDate($created_at); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Address Card -->
                    <div class="ant-card">
                        <div class="ant-card-head">
                            <i class='bx bx-home card-header-icon'></i>
                            <span>ที่อยู่</span>
                        </div>
                        <div class="ant-card-body">
                            <div class="ant-descriptions">
                                <div class="ant-descriptions-row">
                                    <div class="ant-descriptions-item">
                                        <span class="ant-descriptions-item-label">
                                            <i class='bx bx-map'></i> ที่อยู่ทั้งหมด
                                        </span>
                                        <span class="ant-descriptions-item-content">
                                            <div class="full-address">
                                                บ้าน <?php echo htmlspecialchars($address); ?> ตำบล <?php echo htmlspecialchars($subdistrict_name); ?> อำเภอ <?php echo htmlspecialchars($district_name); ?> จังหวัด <?php echo htmlspecialchars($province_name); ?> <?php echo htmlspecialchars($zipcode); ?>
                                            </div>
                                        </span>
                                    </div>
                                </div>
                                <div class="ant-descriptions-row">
                                    <div class="ant-descriptions-item">
                                        <span class="ant-descriptions-item-label">
                                            <i class='bx bx-map-pin'></i> จังหวัด
                                        </span>
                                        <span class="ant-descriptions-item-content"><?php echo htmlspecialchars($province_name); ?></span>
                                    </div>
                                </div>
                                <div class="ant-descriptions-row">
                                    <div class="ant-descriptions-item">
                                        <span class="ant-descriptions-item-label">
                                            <i class='bx bx-map-pin'></i> อำเภอ
                                        </span>
                                        <span class="ant-descriptions-item-content"><?php echo htmlspecialchars($district_name); ?></span>
                                    </div>
                                </div>
                                <div class="ant-descriptions-row">
                                    <div class="ant-descriptions-item">
                                        <span class="ant-descriptions-item-label">
                                            <i class='bx bx-map-pin'></i> ตำบล
                                        </span>
                                        <span class="ant-descriptions-item-content"><?php echo htmlspecialchars($subdistrict_name); ?></span>
                                    </div>
                                </div>
                                <div class="ant-descriptions-row">
                                    <div class="ant-descriptions-item">
                                        <span class="ant-descriptions-item-label">
                                            <i class='bx bx-mail-send'></i> รหัสไปรษณีย์
                                        </span>
                                        <span class="ant-descriptions-item-content"><?php echo htmlspecialchars($zipcode); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="ant-col ant-col-12 gutter-row">
                    <!-- Purchase Actions -->
                    <div class="ant-card" style="margin-bottom: 24px;">
                        <div class="ant-card-head">
                            <i class='bx bx-shopping-bag card-header-icon'></i>
                            <span>การซื้อสินค้า</span>
                        </div>
                        <div class="ant-card-body">
                            <div class="ant-row">
                                <div class="ant-col ant-col-12">
                                    <div class="ant-card action-card ant-card-bordered" style="text-align: center; padding: 24px;">
                                        <div class="action-icon"><i class='bx bx-history'></i></div>
                                        <div class="action-title">ประวัติการซื้อ</div>
                                        <div class="action-description">ดูรายการสั่งซื้อทั้งหมด</div>
                                        <a href="purchase_history.php" class="ant-btn ant-btn-default" style="margin-top: 16px;">
                                            <i class='bx bx-show'></i> ดูรายการ
                                        </a>
                                    </div>
                                </div>
                                <div class="ant-col ant-col-12">
                                    <div class="ant-card action-card ant-card-bordered" style="text-align: center; padding: 24px;">
                                        <div class="action-icon"><i class='bx bx-bar-chart-alt'></i></div>
                                        <div class="action-title">สถานะการสั่งซื้อ</div>
                                        <div class="action-description">ติดตามการจัดส่ง</div>
                                        <a href="order_status.php" class="ant-btn ant-btn-default" style="margin-top: 16px;">
                                            <i class='bx bx-search-alt'></i> ตรวจสอบ
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Booking Actions -->
                    <div class="ant-card">
                        <div class="ant-card-head">
                            <i class='bx bx-calendar-event card-header-icon'></i>
                            <span>การจองเข้าชมสวน</span>
                        </div>
                        <div class="ant-card-body">
                            <div class="ant-row">
                                <div class="ant-col ant-col-8">
                                    <div class="ant-card action-card ant-card-bordered" style="text-align: center; padding: 24px;">
                                        <div class="action-icon"><i class='bx bx-history'></i></div>
                                        <div class="action-title">ประวัติการจอง</div>
                                        <div class="action-description">ดูการจองทั้งหมด</div>
                                        <a href="booking_history.php" class="ant-btn ant-btn-default" style="margin-top: 16px;">
                                            <i class='bx bx-show'></i> ดูประวัติ
                                        </a>
                                    </div>
                                </div>
                                <div class="ant-col ant-col-8">
                                    <div class="ant-card action-card ant-card-bordered" style="text-align: center; padding: 24px;">
                                        <div class="action-icon"><i class='bx bx-check-circle'></i></div>
                                        <div class="action-title">สถานะการจอง</div>
                                        <div class="action-description">ตรวจสอบการอนุมัติ</div>
                                        <a href="booking_status.php" class="ant-btn ant-btn-default" style="margin-top: 16px;">
                                            <i class='bx bx-search-alt'></i> ตรวจสอบ
                                        </a>
                                    </div>
                                </div>
                                <div class="ant-col ant-col-8">
                                    <div class="ant-card action-card ant-card-bordered" style="text-align: center; padding: 24px;">
                                        <div class="action-icon"><i class='bx bx-plus-circle'></i></div>
                                        <div class="action-title">จองเข้าชมใหม่</div>
                                        <div class="action-description">จองรอบเข้าชมใหม่</div>
                                        <a href="activities.php" class="ant-btn ant-btn-primary" style="margin-top: 16px;">
                                            <i class='bx bx-calendar-plus'></i> จองเลย
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activities -->
                    <div class="ant-card" style="margin-top: 24px;">
                        <div class="ant-card-head">
                            <i class='bx bx-trending-up card-header-icon'></i>
                            <span>กิจกรรมล่าสุด</span>
                        </div>
                        <div class="ant-card-body">
                            <div class="ant-list">
                                <div class="ant-list-item">
                                    <div class="ant-list-item-meta">
                                        <div class="ant-list-item-meta-avatar">
                                            <i class='bx bx-calendar list-item-icon'></i>
                                        </div>
                                        <div class="ant-list-item-meta-content">
                                            <div class="ant-list-item-meta-title">จองเข้าชมสวน</div>
                                            <div class="ant-list-item-meta-description">วันที่ 15 มกราคม 2567 เวลา 10:00 น.</div>
                                        </div>
                                    </div>
                                    <div class="ant-list-item-action">
                                        <span class="ant-tag ant-tag-green">อนุมัติแล้ว</span>
                                    </div>
                                </div>
                                <div class="ant-list-item">
                                    <div class="ant-list-item-meta">
                                        <div class="ant-list-item-meta-avatar">
                                            <i class='bx bx-package list-item-icon'></i>
                                        </div>
                                        <div class="ant-list-item-meta-content">
                                            <div class="ant-list-item-meta-title">สั่งซื้อสินค้า</div>
                                            <div class="ant-list-item-meta-description">มะม่วงน้ำดอกไม้ 5 กิโลกรัม</div>
                                        </div>
                                    </div>
                                    <div class="ant-list-item-action">
                                        <span class="ant-tag">กำลังจัดส่ง</span>
                                    </div>
                                </div>
                                <div class="ant-list-item">
                                    <div class="ant-list-item-meta">
                                        <div class="ant-list-item-meta-avatar">
                                            <i class='bx bx-star list-item-icon'></i>
                                        </div>
                                        <div class="ant-list-item-meta-content">
                                            <div class="ant-list-item-meta-title">ให้คะแนนการเข้าชม</div>
                                            <div class="ant-list-item-meta-description">การเข้าชมวันที่ 10 มกราคม 2567</div>
                                        </div>
                                    </div>
                                    <div class="ant-list-item-action">
                                        <i class='bx bxs-star' style="color: #ffc107;"></i>
                                        <i class='bx bxs-star' style="color: #ffc107;"></i>
                                        <i class='bx bxs-star' style="color: #ffc107;"></i>
                                        <i class='bx bxs-star' style="color: #ffc107;"></i>
                                        <i class='bx bxs-star' style="color: #ffc107;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer Actions -->
            <div class="ant-row" style="margin-top: 24px; padding: 24px; background: #fff; border-radius: var(--ant-border-radius); border: 1px solid var(--ant-border-color);">
                <div class="ant-col ant-col-24" style="text-align: center;">
                    <div class="ant-space ant-space-horizontal" style="gap: 16px;">
                        <a href="index.php" class="ant-btn ant-btn-default" style="padding: 8px 24px;">
                            <i class='bx bx-home'></i> กลับหน้าหลัก
                        </a>
                        <a href="member_logout.php" class="ant-btn ant-btn-primary" style="padding: 8px 24px;">
                            <i class='bx bx-log-out'></i> ออกจากระบบ
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // เพิ่มเอฟเฟกต์การโหลดให้กับการ์ด
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.ant-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>