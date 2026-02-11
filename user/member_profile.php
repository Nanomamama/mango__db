<?php
session_start();
require_once __DIR__ . '/../db/db.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['member_id'])) {
    header("Location: member_login.php");
    exit;
}

// ดึงข้อมูลสมาชิก
$member_id = $_SESSION['member_id'];
$stmt = $conn->prepare("SELECT fullname, address, province_id, district_id, subdistrict_id, zipcode, phone, email, created_at, status FROM members WHERE member_id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$stmt->bind_result($fullname, $address, $province_id, $district_id, $subdistrict_id, $zipcode, $phone, $email, $created_at, $member_status);
$stmt->fetch();
$stmt->close();

// Initialize variables to prevent null errors
$fullname = $fullname ?? '';
$address = $address ?? '';
$province_id = $province_id ?? 0;
$district_id = $district_id ?? 0;
$subdistrict_id = $subdistrict_id ?? 0;
$zipcode = $zipcode ?? '';
$phone = $phone ?? '';
$email = $email ?? '';
$created_at = $created_at ?? date('Y-m-d H:i:s');
$member_status = $member_status ?? 0;

// ตรวจสอบสถานะผู้ใช้ที่เข้าสู่ระบบ
    if (isset($_SESSION['member_id'])) {
        $member_id_for_status_check = $_SESSION['member_id'];
        $stmt_status = $conn->prepare("SELECT status FROM members WHERE member_id = ?");
        if ($stmt_status) {
            $stmt_status->bind_param("i", $member_id_for_status_check);
            $stmt_status->execute();
            $result_status = $stmt_status->get_result();
            if ($row_status = $result_status->fetch_assoc()) {
                if ((int)$row_status['status'] === 0) {
                    // บัญชีถูกปิดใช้งาน, ทำลาย session และ redirect
                    session_unset();
                    session_destroy();
                    header('Location: index.php?login_error=disabled');
                    exit;
                }
            }
            $stmt_status->close();
        }
    }

// นับการจองทั้งหมดสำหรับสมาชิก
$booking_count = 0;
$stmtCount = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE member_id = ?");
if ($stmtCount) {
    $stmtCount->bind_param('i', $member_id);
    if ($stmtCount->execute()) {
        $res = $stmtCount->get_result();
        if ($row = $res->fetch_row()) $booking_count = (int)$row[0];
    }
    $stmtCount->close();
}

// นับคำสั่งซื้อที่อาจเชื่อมโยงกับสมาชิก (matching by phone or fullname)
// $purchase_count = 0;
// $stmtOrder = $conn->prepare("SELECT COUNT(*) FROM orders WHERE customer_phone = ? OR customer_name = ?");
// if ($stmtOrder) {
//     $stmtOrder->bind_param('ss', $phone, $fullname);
//     if ($stmtOrder->execute()) {
//         $res2 = $stmtOrder->get_result();
//         if ($row2 = $res2->fetch_row()) $purchase_count = (int)$row2[0];
//     }
//     $stmtOrder->close();
// }

// ดึงชื่อจังหวัด/อำเภอ/ตำบล
function getNameById($file, $id)
{
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
function thaiDate($datetime)
{
    $months = [
        "",
        "มกราคม",
        "กุมภาพันธ์",
        "มีนาคม",
        "เมษายน",
        "พฤษภาคม",
        "มิถุนายน",
        "กรกฎาคม",
        "สิงหาคม",
        "กันยายน",
        "ตุลาคม",
        "พฤศจิกายน",
        "ธันวาคม"
    ];
    $ts = strtotime($datetime);
    $d = date("j", $ts);
    $m = $months[(int)date("n", $ts)];
    $y = date("Y", $ts) + 543;
    return "$d $m $y";
}

// ฟอร์แมตรายการจอง (วันที่ + เวลา)
function formatBookingDate($date, $time)
{
    $months = [
        "",
        "มกราคม",
        "กุมภาพันธ์",
        "มีนาคม",
        "เมษายน",
        "พฤษภาคม",
        "มิถุนายน",
        "กรกฎาคม",
        "สิงหาคม",
        "กันยายน",
        "ตุลาคม",
        "พฤศจิกายน",
        "ธันวาคม"
    ];
    $ts = strtotime($date);
    $d = date("j", $ts);
    $m = $months[(int)date("n", $ts)];
    $y = date("Y", $ts) + 543;
    $t = '';
    if (!empty($time)) {
        $t = ' เวลา ' . htmlspecialchars($time) . ' น.';
    }
    return "$d $m $y" . $t;
}

// ดึงรายการการจองล่าสุด 5 รายการ (สถานะ: อนุมัติแล้ว, รออนุมัติ, ถูกปฏิเสธ)
$recent_bookings = [];
// Order by submission (bookings_id) so the most recently created booking appears first,
// independent of the booked date.
// $stmt2 = $conn->prepare("SELECT bookings_id, date, time, name, status FROM bookings WHERE member_id = ? AND status IN ('อนุมัติแล้ว','รออนุมัติ','ถูกปฏิเสธ') ORDER BY bookings_id DESC LIMIT 5");
// $stmt2->bind_param("i", $member_id);
// if ($stmt2->execute()) {
//     $res2 = $stmt2->get_result();
//     while ($row = $res2->fetch_assoc()) {
//         $recent_bookings[] = $row;
//     }
// }
// $stmt2->close();
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

        .ant-list-item-action>li {
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

            .ant-col-12,
            .ant-col-8,
            .ant-col-6 {
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
        <main class="ant-layout-content">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-actions">
                    <button id="editProfileBtn" class="ant-btn ant-btn-default" style="background: rgba(255,255,255,0.2); color: white; border-color: rgba(255,255,255,0.3);" type="button">
                        <i class='bx bx-edit'></i> แก้ไขข้อมูลส่วนตัว
                    </button>
                </div>

                <div class="profile-info">
                    <div class="ant-avatar ant-avatar-image">
                        <img src="../user/image/profile.png" alt="ข้อมูลส่วนตัว">
                    </div>

                    <h1 class="profile-name"><?php echo htmlspecialchars($fullname); ?></h1>
                    <p class="profile-description">สมาชิกศูนย์การเรียนรู้สวนมะม่วงลุงเผือก</p>

                    <div>
                        <?php if (isset($member_status) && (int)$member_status === 1): ?>
                            <span class="ant-tag ant-tag-green">
                                <i class='bx bx-check-circle'></i> สถานะ: ใช้งานได้ปกติ
                            </span>
                        <?php else: ?>
                            <span class="ant-tag" style="background:#fff2f0;border-color:#ffd8d8;color:#e74a3b;">
                                <i class='bx bx-error-circle'></i> สถานะ: ถูกปิดใช้งาน
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="profile-stats">
                        <div class="profile-stat-item">
                            <div class="profile-stat-value"><?php echo htmlspecialchars((int)$booking_count); ?></div>
                            <div class="profile-stat-label">การจองทั้งหมด</div>
                        </div>
                        <!-- <div class="profile-stat-item">
                            <div class="profile-stat-value"><?php echo htmlspecialchars((int)$purchase_count); ?></div>
                            <div class="profile-stat-label">การซื้อสินค้า</div>
                        </div> -->
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
                <!-- Edit Profile Modal -->
                <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">แก้ไขข้อมูลส่วนตัว</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editProfileForm">
                                    <div class="ant-row" style="gap:12px;">
                                        <div class="ant-col ant-col-12">
                                            <label class="ant-descriptions-item-label">ชื่อ-นามสกุล</label>
                                            <input type="text" name="fullname" id="fullname" class="ant-input" style="width:100%;padding:8px;margin-top:6px;" value="<?php echo htmlspecialchars($fullname, ENT_QUOTES); ?>" required>
                                        </div>
                                        <div class="ant-col ant-col-12">
                                            <label class="ant-descriptions-item-label">เบอร์โทรศัพท์</label>
                                            <input type="text" name="phone" id="phone" class="ant-input" style="width:100%;padding:8px;margin-top:6px;" value="<?php echo htmlspecialchars($phone ?? '', ENT_QUOTES); ?>">
                                        </div>
                                        <div class="ant-col ant-col-12" style="margin-top:12px;">
                                            <label class="ant-descriptions-item-label">อีเมล</label>
                                            <input type="email" name="email" id="email" class="ant-input" style="width:100%;padding:8px;margin-top:6px;" value="<?php echo htmlspecialchars($email ?? '', ENT_QUOTES); ?>">
                                        </div>

                                        <div class="ant-col ant-col-24" style="margin-top:12px;">
                                            <label class="ant-descriptions-item-label">ที่อยู่</label>
                                            <textarea name="address" id="address" class="ant-input" style="width:100%;padding:8px;margin-top:6px;" rows="2"><?php echo htmlspecialchars($address, ENT_QUOTES); ?></textarea>
                                        </div>

                                        <div class="ant-col ant-col-8" style="margin-top:12px;">
                                            <label class="ant-descriptions-item-label">จังหวัด</label>
                                            <select id="province" name="province_id" class="ant-input" style="width:100%;padding:8px;margin-top:6px;"></select>
                                        </div>
                                        <div class="ant-col ant-col-8" style="margin-top:12px;">
                                            <label class="ant-descriptions-item-label">อำเภอ</label>
                                            <select id="district" name="district_id" class="ant-input" style="width:100%;padding:8px;margin-top:6px;"></select>
                                        </div>
                                        <div class="ant-col ant-col-8" style="margin-top:12px;">
                                            <label class="ant-descriptions-item-label">ตำบล</label>
                                            <select id="subdistrict" name="subdistrict_id" class="ant-input" style="width:100%;padding:8px;margin-top:6px;"></select>
                                        </div>

                                        <div class="ant-col ant-col-6" style="margin-top:12px;">
                                            <label class="ant-descriptions-item-label">รหัสไปรษณีย์</label>
                                            <input type="text" name="zipcode" id="zipcode" class="ant-input" style="width:100%;padding:8px;margin-top:6px;" value="<?php echo htmlspecialchars($zipcode, ENT_QUOTES); ?>">
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="ant-btn ant-btn-default" data-bs-dismiss="modal">ยกเลิก</button>
                                <button id="saveProfileBtn" type="button" class="ant-btn ant-btn-primary">บันทึกการเปลี่ยนแปลง</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success Modal -->
                <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-sm modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body" style="text-align:center;padding:24px;">
                                <div style="font-size:48px;color:#52c41a;"><i class='bx bx-check-circle'></i></div>
                                <h5 style="margin-top:12px;">บันทึกข้อมูลเรียบร้อยแล้ว</h5>
                                <p class="text-muted">ข้อมูลส่วนตัวของคุณถูกอัปเดตแล้ว</p>
                                <div style="margin-top:8px;"><button type="button" class="ant-btn ant-btn-primary" data-bs-dismiss="modal">ตกลง</button></div>
                            </div>
                        </div>
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
                            <!-- <span>การซื้อสินค้า</span> -->
                        </div>
                        <!-- <div class="ant-card-body">
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
                        </div> -->
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
                                        <button id="viewBookingHistoryBtn" class="ant-btn ant-btn-default" style="margin-top: 16px;" type="button">
                                            <i class='bx bx-show'></i> ดูประวัติ
                                        </button>
                                    </div>
                                </div>
                                <div class="ant-col ant-col-8">
                                    <div class="ant-card action-card ant-card-bordered" style="text-align: center; padding: 24px;">
                                        <div class="action-icon"><i class='bx bx-check-circle'></i></div>
                                        <div class="action-title">สถานะการจองล่าสุด</div>
                                        <div class="action-description">ตรวจสอบการอนุมัติ</div>
                                        <button id="checkBookingBtn" class="ant-btn ant-btn-default" style="margin-top: 16px;" type="button">
                                            <i class='bx bx-search-alt'></i> ตรวจสอบ
                                        </button>
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
                                <?php if (!empty($recent_bookings)): ?>
                                    <?php foreach ($recent_bookings as $rb): ?>
                                        <div class="ant-list-item">
                                            <div class="ant-list-item-meta">
                                                <div class="ant-list-item-meta-avatar">
                                                    <i class='bx bx-calendar list-item-icon'></i>
                                                </div>
                                                <div class="ant-list-item-meta-content">
                                                    <div class="ant-list-item-meta-title"><?= htmlspecialchars($rb['name']) ?></div>
                                                    <div class="ant-list-item-meta-description"><?= htmlspecialchars(formatBookingDate($rb['date'], $rb['time'])) ?></div>
                                                </div>
                                            </div>
                                            <div class="ant-list-item-action">
                                                <?php
                                                $s = trim($rb['status'] ?? '');
                                                if ($s === 'อนุมัติแล้ว') {
                                                    echo '<span class="ant-tag ant-tag-green">อนุมัติแล้ว</span>';
                                                } elseif ($s === 'ถูกปฏิเสธ') {
                                                    echo '<span class="ant-tag" style="background:#fff2f0;border-color:#ffd8d8;color:#e74a3b;">ถูกปฏิเสธ</span>';
                                                } else {
                                                    echo '<span class="ant-tag">รออนุมัติ</span>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted">ไม่มีการจองที่ได้รับการอนุมัติ</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Status Modal -->
                    <div class="modal fade" id="bookingStatusModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">สถานะการจองของฉัน</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="bookingTimelineContainer">
                                        <p class="text-muted">กำลังโหลดข้อมูล...</p>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="ant-btn ant-btn-default" data-bs-dismiss="modal">ปิด</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking History Modal -->
                    <div class="modal fade" id="bookingHistoryModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">ประวัติการจองของฉัน</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="bookingHistoryContainer">
                                        <p class="text-muted">กำลังโหลดประวัติ...</p>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="ant-btn ant-btn-default" data-bs-dismiss="modal">ปิด</button>
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
        // Profile edit modal logic
        (function() {
            const editBtn = document.getElementById('editProfileBtn');
            const editModalEl = document.getElementById('editProfileModal');
            const successModalEl = document.getElementById('successModal');
            let editModalObj = null;
            let successModalObj = null;

            // initial data from server
            const initial = {
                province_id: <?php echo json_encode((int)$province_id); ?>,
                district_id: <?php echo json_encode((int)$district_id); ?>,
                subdistrict_id: <?php echo json_encode((int)$subdistrict_id); ?>
            };

            if (editBtn) {
                editBtn.addEventListener('click', function() {
                    if (!editModalObj) editModalObj = new bootstrap.Modal(editModalEl);
                    if (!successModalObj) successModalObj = new bootstrap.Modal(successModalEl);
                    populateProvinces().then(() => {
                        editModalObj.show();
                    });
                });
            }

            // Save profile
            const saveBtn = document.getElementById('saveProfileBtn');
            if (saveBtn) {
                saveBtn.addEventListener('click', function() {
                    saveBtn.disabled = true;
                    const form = document.getElementById('editProfileForm');
                    const fd = new FormData(form);
                    fetch('update_profile.php', {
                            method: 'POST',
                            body: fd
                        }).then(res => res.json())
                        .then(json => {
                            saveBtn.disabled = false;
                            if (json && json.success) {
                                // update UI fields in page
                                const nameEl = document.querySelector('.profile-name');
                                if (nameEl) nameEl.textContent = fd.get('fullname');
                                const phoneEls = document.querySelectorAll('.ant-descriptions-item-content');
                                // update phone and email in the first card; best-effort replace
                                const phoneSpan = Array.from(document.querySelectorAll('.ant-descriptions-item-content')).find(el => el.textContent.trim() === '<?php echo htmlspecialchars($phone); ?>');
                                if (phoneSpan) phoneSpan.textContent = fd.get('phone');
                                const emailSpan = Array.from(document.querySelectorAll('.ant-descriptions-item-content')).find(el => el.textContent.trim() === '<?php echo htmlspecialchars($email); ?>');
                                if (emailSpan) emailSpan.textContent = fd.get('email');

                                // update full address block
                                const fullAddress = document.querySelector('.full-address');
                                if (fullAddress) {
                                    // try to assemble a friendly address using selected texts
                                    const addr = fd.get('address') || '';
                                    const provinceText = document.getElementById('province') ? document.getElementById('province').selectedOptions[0].text : '';
                                    const districtText = document.getElementById('district') ? document.getElementById('district').selectedOptions[0].text : '';
                                    const subText = document.getElementById('subdistrict') ? document.getElementById('subdistrict').selectedOptions[0].text : '';
                                    const zip = fd.get('zipcode') || '';
                                    fullAddress.textContent = 'บ้าน ' + addr + ' ตำบล ' + subText + ' อำเภอ ' + districtText + ' จังหวัด ' + provinceText + ' ' + zip;
                                }

                                editModalObj.hide();
                                successModalObj.show();
                            } else {
                                alert((json && json.error) ? json.error : 'ไม่สามารถบันทึกข้อมูลได้');
                            }
                        }).catch(err => {
                            saveBtn.disabled = false;
                            console.error(err);
                            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                        });
                });
            }

            // Helper: populate provinces/districts/subdistricts
            async function populateProvinces() {
                try {
                    const provRes = await fetch('../data/api_province.json');
                    const provinces = await provRes.json();
                    const provSelect = document.getElementById('province');
                    provSelect.innerHTML = '';
                    provinces.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.id;
                        opt.text = p.name_th || p.name;
                        provSelect.appendChild(opt);
                    });
                    if (initial.province_id) provSelect.value = initial.province_id;
                    // trigger districts population
                    await populateDistricts(provSelect.value);
                    return true;
                } catch (e) {
                    console.error(e);
                    return false;
                }
            }

            async function populateDistricts(provinceId) {
                try {
                    const ampRes = await fetch('../data/thai_amphures.json');
                    const amphures = await ampRes.json();
                    // amphures file likely contains all amphures with province_id field
                    const districts = amphures.filter(a => String(a.province_id) === String(provinceId));
                    const distSelect = document.getElementById('district');
                    distSelect.innerHTML = '';
                    districts.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id;
                        opt.text = d.name_th || d.name;
                        distSelect.appendChild(opt);
                    });
                    if (initial.district_id) distSelect.value = initial.district_id;
                    await populateSubdistricts(distSelect.value);
                } catch (e) {
                    console.error(e);
                }
            }

            async function populateSubdistricts(districtId) {
                try {
                    const subRes = await fetch('../data/thai_tambons.json');
                    const tambons = await subRes.json();
                    const subs = tambons.filter(t => String(t.amphure_id) === String(districtId));
                    const subSelect = document.getElementById('subdistrict');
                    subSelect.innerHTML = '';
                    subs.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.text = s.name_th || s.name;
                        // store zip code on option for autofill
                        if (s.zip_code !== undefined) opt.dataset.zip = s.zip_code;
                        subSelect.appendChild(opt);
                    });
                    if (initial.subdistrict_id) {
                        subSelect.value = initial.subdistrict_id;
                        // set zipcode input based on selected option
                        const sel = subSelect.selectedOptions[0];
                        if (sel && sel.dataset && sel.dataset.zip) {
                            const zipEl = document.getElementById('zipcode');
                            if (zipEl) zipEl.value = sel.dataset.zip;
                        }
                    }
                } catch (e) {
                    console.error(e);
                }
            }

            // cascade change events
            document.addEventListener('change', function(e) {
                if (!e.target) return;
                if (e.target.id === 'province') {
                    populateDistricts(e.target.value);
                } else if (e.target.id === 'district') {
                    populateSubdistricts(e.target.value);
                } else if (e.target.id === 'subdistrict') {
                    // autofill zipcode when subdistrict selected
                    const sel = e.target.selectedOptions ? e.target.selectedOptions[0] : null;
                    if (sel && sel.dataset && sel.dataset.zip) {
                        const zipEl = document.getElementById('zipcode');
                        if (zipEl) zipEl.value = sel.dataset.zip;
                    }
                }
            });
        })();
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
    <script>
        (function() {
            const btn = document.getElementById('checkBookingBtn');
            if (!btn) return;
            const modalEl = document.getElementById('bookingStatusModal');
            let modalObj = null;
            btn.addEventListener('click', function() {
                // show bootstrap modal
                if (!modalObj) modalObj = new bootstrap.Modal(modalEl);
                // fetch bookings
                fetch('api_member_bookings.php')
                    .then(res => {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(json => {
                        const list = json.data || [];
                        // only show the latest booking
                        if (!list || list.length === 0) {
                            const container = document.getElementById('bookingTimelineContainer');
                            container.innerHTML = '<div class="text-muted">ยังไม่มีการจองล่าสุด</div>';
                        } else {
                            renderTimeline([list[0]]);
                        }
                        modalObj.show();
                    })
                    .catch(err => {
                        const container = document.getElementById('bookingTimelineContainer');
                        container.innerHTML = '<div class="text-danger">ไม่สามารถโหลดข้อมูลได้</div>';
                        modalObj.show();
                        console.error(err);
                    });
            });

            // Booking history modal: show full list
            const historyBtn = document.getElementById('viewBookingHistoryBtn');
            const historyModalEl = document.getElementById('bookingHistoryModal');
            let historyModalObj = null;
            if (historyBtn) {
                historyBtn.addEventListener('click', function() {
                    if (!historyModalObj) historyModalObj = new bootstrap.Modal(historyModalEl);
                    const container = document.getElementById('bookingHistoryContainer');
                    container.innerHTML = '<p class="text-muted">กำลังโหลดประวัติ...</p>';
                    fetch('api_member_bookings.php')
                        .then(res => {
                            if (!res.ok) throw new Error('HTTP ' + res.status);
                            return res.json();
                        })
                        .then(json => {
                            const list = (json.data || []).filter(b => {
                                const s = (b.status || '').trim();
                                return s === 'อนุมัติแล้ว' || s === 'ถูกปฏิเสธ';
                            });
                            renderHistory(list);
                            historyModalObj.show();
                        })
                        .catch(err => {
                            container.innerHTML = '<div class="text-danger">ไม่สามารถโหลดข้อมูลได้</div>';
                            historyModalObj.show();
                            console.error(err);
                        });
                });
            }

            function formatThaiDateTime(datetimeStr) {
                if (!datetimeStr) return '';
                const d = new Date(datetimeStr);
                if (isNaN(d)) return datetimeStr;
                const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
                const day = d.getDate();
                const month = months[d.getMonth()];
                const year = d.getFullYear() + 543;
                const hh = String(d.getHours()).padStart(2, '0');
                const mm = String(d.getMinutes()).padStart(2, '0');
                return `${day} ${month} ${year} เวลา ${hh}:${mm} น.`;
            }

            function renderTimeline(items) {
                const container = document.getElementById('bookingTimelineContainer');
                if (!container) return;
                if (!items || items.length === 0) {
                    container.innerHTML = '<div class="text-muted">ไม่มีการจอง</div>';
                    return;
                }
                // Only the latest booking (first item) is used
                const b = items[0];

                // determine step index: 0=submitted,1=in progress,2=completed
                let step = 0;
                if (b.status === 'รออนุมัติ') step = 1; // in progress
                else if (b.status === 'อนุมัติแล้ว') step = 2; // completed
                else if (b.status === 'ถูกปฏิเสธ') step = 1; // treat as in progress but show rejection

                let html = '';

                // Booking header
                html += `<div style="margin-bottom:12px;padding:8px;border-radius:8px;background:#fff;">`;
                html += `<div style="font-weight:700; font-size:1.05rem;">${escapeHtml(b.name)}</div>`;
                html += `<div class="text-muted">วันที่ ${escapeHtml(b.date)} ${b.time ? 'เวลา ' + escapeHtml(b.time) + ' น.' : ''}</div>`;
                html += `</div>`;

                // 3-step horizontal timeline
                html += `<div style="display:flex;gap:12px;align-items:flex-start;justify-content:space-between;padding:12px;border-radius:8px;border:1px solid #eee;background:#fafafa;">`;
                const steps = ['ยื่นคำขอ', 'กำลังดำเนินการ', 'จองสำเร็จ'];
                steps.forEach((label, idx) => {
                    const active = idx <= step;
                    const isCurrent = idx === step;
                    const bg = isCurrent ? '#1677ff' : (active ? '#f0f7ff' : '#fff');
                    const color = isCurrent ? '#fff' : (active ? '#0b6ed1' : '#777');
                    html += `<div style="flex:1;text-align:center;">`;
                    html += `<div style="margin:0 auto;width:44px;height:44px;border-radius:22px;background:${bg};color:${color};display:flex;align-items:center;justify-content:center;font-weight:700;">${idx+1}</div>`;
                    html += `<div style="margin-top:8px;color:${isCurrent ? '#111' : '#666'};font-weight:${isCurrent?600:500};">${label}</div>`;
                    html += `</div>`;
                    if (idx < steps.length - 1) {
                        html += `<div style="width:24px;flex:0 0 24px;display:flex;align-items:center;justify-content:center;"><div style="height:4px;width:100%;background:${idx < step ? '#1677ff' : '#e9ecef'};border-radius:2px;"></div></div>`;
                    }
                });
                html += `</div>`;

                // show rejection reason if rejected
                if (b.status === 'ถูกปฏิเสธ') {
                    html += `<div style="margin-top:12px;padding:10px;border-radius:8px;background:#fff2f0;border:1px solid #ffd8d8;color:#bf2e2e;">`;
                    html += `<strong>ปฏิเสธการจอง</strong>`;
                    if (b.rejection_reason) html += `<div style="margin-top:6px;">สาเหตุ: ${escapeHtml(b.rejection_reason)}</div>`;
                    html += `</div>`;
                }

                // payment summary
                try {
                    const total = b.total_amount != null ? Number(b.total_amount) : null;
                    const deposit = b.paid_amount != null ? Number(b.paid_amount) : (b.deposit_amount != null ? Number(b.deposit_amount) : null);
                    const remain = b.remain_amount != null ? Number(b.remain_amount) : (total != null && deposit != null ? total - deposit : null);
                    if (total != null) {
                        html += '<div style="margin-top:12px;display:flex;gap:12px;flex-wrap:wrap;">';
                        html += `<div style="padding:8px;border-radius:8px;background:#fff;border:1px solid #eee;">ยอดรวม: <strong>${Number(total).toLocaleString()} บาท</strong></div>`;
                        if (deposit != null) html += `<div style="padding:8px;border-radius:8px;background:#f6ffed;border:1px solid #d9f7be;color:#237804;">จ่ายแล้ว: <strong>${Number(deposit).toLocaleString()} บาท</strong></div>`;
                        if (remain != null) html += `<div style="padding:8px;border-radius:8px;background:#fff7e6;border:1px solid #ffe7ba;color:#b35a00;">คงเหลือ: <strong>${Number(remain).toLocaleString()} บาท</strong></div>`;
                        html += '</div>';
                    }
                } catch (e) {
                    console.error(e);
                }

                // slip link
                if (b.slip) {
                    html += `<div style="margin-top:12px;"><a class="ant-btn ant-btn-default" href="download.php?type=slip&file=${encodeURIComponent(b.slip)}" target="_blank">ดูสลิปการชำระ</a></div>`;
                }

                container.innerHTML = html;
            }

            function renderHistory(items) {
                const container = document.getElementById('bookingHistoryContainer');
                if (!container) return;
                if (!items || items.length === 0) {
                    container.innerHTML = '<div class="text-muted">ยังไม่มีประวัติการจอง</div>';
                    return;
                }

                let html = '<div class="list-group">';
                items.forEach(b => {
                    html += '<div class="list-group-item" style="border-radius:8px;margin-bottom:8px;">';
                    html += `<div style="display:flex;justify-content:space-between;align-items:center;">`;
                    html += `<div style="font-weight:600;">${escapeHtml(b.name)}</div>`;
                    let statusTag = '';
                    if (b.status === 'อนุมัติแล้ว') statusTag = '<span class="ant-tag ant-tag-green">อนุมัติแล้ว</span>';
                    else if (b.status === 'ถูกปฏิเสธ') statusTag = '<span class="ant-tag" style="background:#fff2f0;border-color:#ffd8d8;color:#e74a3b;">ถูกปฏิเสธ</span>';
                    else statusTag = '<span class="ant-tag">รออนุมัติ</span>';
                    html += `<div>${statusTag}</div>`;
                    html += `</div>`;
                    html += `<div class="text-muted" style="margin-top:6px;">วันที่ ${escapeHtml(b.date)} ${b.time ? 'เวลา ' + escapeHtml(b.time) + ' น.' : ''}</div>`;
                    // payment summary for history item
                    try {
                        const total = b.total_amount != null ? Number(b.total_amount) : null;
                        const paid = b.paid_amount != null ? Number(b.paid_amount) : (b.deposit_amount != null ? Number(b.deposit_amount) : null);
                        const remain = b.remain_amount != null ? Number(b.remain_amount) : (total != null && paid != null ? total - paid : null);
                        if (total != null) {
                            html += '<div style="display:flex;gap:10px;margin-top:8px;flex-wrap:wrap;">';
                            html += `<div style="padding:8px;border-radius:8px;background:#fff;border:1px solid #eee;">ยอดรวม: <strong>${Number(total).toLocaleString()} บาท</strong></div>`;
                            if (paid != null) html += `<div style="padding:8px;border-radius:8px;background:#f6ffed;border:1px solid #d9f7be;color:#237804;">จ่ายแล้ว: <strong>${Number(paid).toLocaleString()} บาท</strong></div>`;
                            if (remain != null) html += `<div style="padding:8px;border-radius:8px;background:#fff7e6;border:1px solid #ffe7ba;color:#b35a00;">คงเหลือ: <strong>${Number(remain).toLocaleString()} บาท</strong></div>`;
                            html += '</div>';
                        }
                    } catch (e) {
                        console.error(e);
                    }
                    if (b.status === 'อนุมัติแล้ว') {
                        if (b.approved_at_display) html += `<div class="text-muted">อนุมัติเมื่อ ${escapeHtml(b.approved_at_display)}</div>`;
                        else if (b.approved_at) html += `<div class="text-muted">อนุมัติเมื่อ ${formatThaiDateTime(b.approved_at)}</div>`;
                        if (b.approved_by) html += `<div class="text-muted">อนุมัติโดย: ${escapeHtml(b.approved_by)}</div>`;
                    } else if (b.status === 'ถูกปฏิเสธ') {
                        if (b.rejection_reason) html += `<div class="text-danger">สาเหตุ: ${escapeHtml(b.rejection_reason)}</div>`;
                        else html += `<div class="text-muted">สาเหตุ: ไม่ระบุ</div>`;
                    }
                    // show slip link if available
                    if (b.slip) {
                        html += `<div style="margin-top:8px;"><a class="ant-btn ant-btn-default" href="download.php?type=slip&file=${encodeURIComponent(b.slip)}" target="_blank">ดูสลิปการชำระ</a></div>`;
                    }
                    html += '</div>';
                });
                html += '</div>';
                container.innerHTML = html;
            }

            function escapeHtml(s) {
                if (!s) return '';
                return String(s).replace(/[&<>"'`]/g, function(ch) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": "&#39;",
                        '`': '&#96;'
                    } [ch];
                });
            }
        })();
    </script>
</body>

</html>