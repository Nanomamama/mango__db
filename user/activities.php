<?php
session_start();
require_once '../admin/db.php';

// ตรวจสอบว่า login แล้วหรือยัง
$loggedIn = false;
$memberName = '';
$memberPhone = '';
if (isset($_SESSION['member_id']) && !empty($_SESSION['member_id'])) {
    $loggedIn = true;
    // ดึงชื่อและเบอร์โทรสมาชิก
    $member_id = $_SESSION['member_id'];
    $stmt = $conn->prepare("SELECT fullname, phone FROM members WHERE member_id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $stmt->bind_result($memberName, $memberPhone);
    $stmt->fetch();
    $stmt->close();
}

// ดึงข้อมูลวันว่าง/ไม่ว่างจากฐานข้อมูล (เช่น calendar_dates)
$dates = [];
$res = $conn->query("SELECT date, status FROM calendar_dates");
while ($row = $res->fetch_assoc()) {
    $dates[] = $row;
}

// ดึงวันจองที่อนุมัติแล้ว
$approved = [];
$res2 = $conn->query("SELECT date FROM bookings WHERE status='อนุมัติแล้ว'");
while ($row = $res2->fetch_assoc()) {
    $approved[] = $row['date'];
}

// ดึงชื่อผู้จองในแต่ละวัน
$sql = "SELECT date, name FROM bookings";
$result = $conn->query($sql);

$booking_names = [];
while ($row = $result->fetch_assoc()) {
    $booking_names[$row['date']][] = $row['name'];
}

// สร้างข้อมูลสำหรับแสดงในคอลัมน์ขวา
$approved_bookings = array();
$total_approved_bookings = 0;
foreach ($approved as $date) {
    if (isset($booking_names[$date])) {
        $approved_bookings[$date] = $booking_names[$date];
        $total_approved_bookings += count($booking_names[$date]);
    }
}

// เรียงจากวันที่ล่าสุดไปเก่าสุด
krsort($approved_bookings);
// แสดงเพียง 6 รายการ
$approved_bookings_display = array_slice($approved_bookings, 0, 6, true);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจองเข้าดูงานสวนมะม่วงลุงเผือก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@3.2.0/dist/fullcalendar.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="preload" href="https://unpkg.com/boxicons@2.1.4/fonts/boxicons.woff2" as="font" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/locale/th.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.2.0/dist/fullcalendar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="https://unpkg.com/promptpay-qr@1.2.0/dist/promptpay-qr.min.js"></script>

    <style>
        :root {
            --primary: #016A70;
            --primary-light: #018992;
            --primary-gradient: linear-gradient(135deg, #016A70 0%, #018992 100%);
            --secondary: #4e73df;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --light: #f8f9fc;
            --dark: #5a5c69;
            --darker: #000;
            --card-shadow: 0 10px 20px rgba(0,0,0,0.08);
            --card-hover-shadow: 0 15px 30px rgba(0,0,0,0.12);
        }

        * {
            font-family: 'Kanit', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            color: #444;
        }



        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .page-header h2 {
            font-weight: 700;
            margin: 0;
            position: relative;
            padding-bottom: 15px;
        }

        .page-header h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 70px;
            height: 4px;
            background: rgba(255,255,255,0.7);
            border-radius: 10px;
        }

        .container {
            max-width: 1400px;
        }

        .calendar-container {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            padding: 25px;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .calendar-container:hover {
            box-shadow: var(--card-hover-shadow);
        }
        
        
        
        .fc-day {
            border-radius: 8px !important;
            margin: 3px;
            transition: all 0.3s ease;
        }
        
        .fc-day:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075) !important;
        }
        
        .fc-event {
            border-radius: 6px !important;
            padding: 3px 6px;
            margin: 3px;
            font-size: 0.85em;
            cursor: pointer;
        }
        
        .fc-event.available {
            background-color: var(--success);
        }
        
        .fc-event.unavailable {
            background-color: var(--danger);
        }
        
        .fc-event.booked {
            background-color: var(--secondary);
        }
        
        .fc-toolbar {
            margin-bottom: 1.5rem !important;
        }
        
        .fc-button {
            background-color: var(--primary) !important;
            border: none !important;
            text-transform: capitalize !important;
        }
        
        .fc-button:hover {
            background-color: var(--primary-light) !important;
        }
        
        .modal-content {
            border-radius: 16px;
            overflow: hidden;
            border: none;
        }
        
        .modal-header {
            background: var(--primary-gradient);
            color: white;
            padding: 20px;
        }
        
        .modal-title {
            font-weight: 600;
            font-size: 1.4rem;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(1, 106, 112, 0.25);
        }
        
         
        
        .payment-card {
            background: var(--light);
            border-radius: 12px;
            padding: 20px;
            margin-top: 15px;
            border: 1px solid #eaeaea;
            transition: all 0.3s;
        }
        
        .payment-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .payment-card h6 {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .qrcode-container {
            display: flex;
            justify-content: center;
            margin: 15px 0;
        }
        
        .step-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary);
            transition: all 0.3s;
        }
        
        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .step-number {
            background: var(--primary);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 10px;
        }
        
        .step-card h5 {
            font-weight: 600;
            color: var(--darker);
            display: flex;
            align-items: center;
        }
        
        .info-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s;
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-hover-shadow);
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .booking-item {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-left: 4px solid transparent;
            transition: all 0.3s;
            cursor: pointer;
        }

        .booking-item:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .booking-item.active {
            background: #e3f2fd;
            border-left-color: var(--primary);
        }

        .booking-date {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .booking-names {
            font-size: 0.9em;
            color: var(--dark);
        }
        
        .sidebar-section {
            margin-bottom: 25px;
        }
        
        .calendar-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            margin-right: 15px;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem 0;
            }
            
            .page-header h2 {
                font-size: 1.8rem;
            }
            
            .calendar-container {
                padding: 15px;
            }
            
            .fc-event {
                font-size: 0.7em;
            }
            
            .header-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .header-actions .btn {
                width: 100%;
                margin-top: 10px;
            }
        }

        .is-invalid {
            border-color: #e74a3b !important;
        }

        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #e74a3b;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="page-header mt-5">
        <div class="container">
            <div class="row align-items-center mt-5">
                <div class="col-md-8">
                    <h2>ระบบจองเข้าดูงานสวนมะม่วงลุงเผือก</h2>
                    <p class="mb-0 mt-2">เลือกวันที่ต้องการจองเข้าดูงานได้จากปฏิทินด้านล่าง</p>
                </div>
                <div class="col-md-4">
                    <div class="header-actions">
                        <button type="button" class="btn btn-light text-dark" data-bs-toggle="modal" data-bs-target="#howtoModal">
                            <i class="bi bi-info-circle me-2"></i>ขั้นตอน
                        </button>
                        <?php if (!$loggedIn): ?>
                            <a href="member_login.php" class="btn btn-outline-light">
                                <i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ
                            </a>
                        <?php else: ?>
                            <div class="text-white">
                                <i class="bi bi-person-circle me-2"></i>ยินดีต้อนรับ <?php echo htmlspecialchars($memberName); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container py-4">
        <?php
        if (isset($_GET['error'])) {
            $err = $_GET['error'];
            $msg = '';
            switch ($err) {
                case 'invalid_doc_type':
                    $msg = 'ไฟล์แนบต้องเป็นไฟล์ PDF เท่านั้น';
                    break;
                case 'doc_too_large':
                    $msg = 'ไฟล์แนบมีขนาดเกิน 5MB กรุณาเลือกไฟล์ที่มีขนาดเล็กกว่า 5MB';
                    break;
                case 'doc_move_failed':
                    $msg = 'เกิดข้อผิดพลาดขณะบันทึกไฟล์ กรุณาลองอีกครั้ง'; 
                    break;
                case 'invalid_slip_type':
                    $msg = 'ไฟล์สลิปต้องเป็นรูปภาพ (JPG/PNG) หรือ PDF เท่านั้น';
                    break;
                case 'slip_too_large':
                    $msg = 'สลิปมีขนาดเกิน 5MB กรุณาเลือกไฟล์ที่มีขนาดเล็กกว่า 5MB';
                    break;
                default:
                    $msg = 'เกิดข้อผิดพลาดในการอัพโหลดไฟล์ กรุณาลองอีกครั้ง';
            }
            echo '<div class="alert alert-danger">' . htmlspecialchars($msg) . '</div>';
        }
        ?>
        <div class="row">
            <!-- คอลัมน์ซ้ายสำหรับปฏิทิน -->
            <div class="col-lg-8 mb-4">
                <div class="calendar-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0"><i class="bi bi-calendar-week me-2"></i>ปฏิทินการจอง</h5>
                        <div class="calendar-legend">
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #1cc88a;"></div>
                                <span>ว่าง</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #e74a3b;"></div>
                                <span>ไม่ว่าง</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #4e73df;"></div>
                                <span>จองแล้ว</span>
                            </div>
                        </div>
                    </div>
                    <div id="calendar"></div>
                </div>
            </div>
            
            <!-- คอลัมน์ขวาสำหรับข้อมูลการจอง -->
            <div class="col-lg-4">
                <div class="sidebar-section">
                    <div class="info-card">
                        <h5 class="mb-3"><i class="bi bi-graph-up me-2"></i>สรุปการจอง</h5>
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="fs-4 fw-bold text-primary"><?php echo $total_approved_bookings; ?></div>
                                <div class="small">การจองทั้งหมด</div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="fs-4 fw-bold text-success"><?php echo count($approved); ?></div>
                                <div class="small">วันที่มีการจอง</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p class="mb-2"><i class="bi bi-currency-dollar me-2"></i>ราคาต่อคน: 150 บาท</p>
                            <p class="mb-2"><i class="bi bi-credit-card me-2"></i>มัดจำ: 30% ของยอดรวม</p>
                            <p class="mb-0"><i class="bi bi-clock me-2"></i>เวลาเปิด: 08:00 - 17:30 น.</p>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <div class="info-card">
                        <h5 class="mb-3"><i class="bi bi-calendar-check me-2"></i>การจองล่าสุด</h5>
                        <div id="booking-list">
                            <?php
                            $count = 0;
                            foreach ($approved_bookings_display as $date => $names) {
                                $count++;
                                $thaiDate = date('d/m/Y', strtotime($date));
                                echo '<div class="booking-item" data-date="'.$date.'">';
                                echo '<div class="booking-date">'.$thaiDate.'</div>';
                                echo '<div class="booking-names">'.implode(', ', $names).'</div>';
                                echo '</div>';
                            }
                            if ($count == 0) {
                                echo '<div class="text-center text-muted py-3">ไม่มีข้อมูลการจอง</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal แจ้งเตือนจองสำเร็จ -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-center">
                    <div class="modal-body py-5">
                        <div class="mb-4">
                            <span style="font-size:4rem;color:#1cc88a;">
                                <i class="bi bi-check-circle-fill"></i>
                            </span>
                        </div>
                        <h3 class="mb-3" id="successModalLabel">จองสำเร็จ!</h3>
                        <p class="fs-5">กรุณารอการอนุมัติจากเจ้าหน้าที่</p>
                        <button type="button" class="btn btn-primary mt-3" id="successModalOkBtn">ตกลง</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- แทนที่ Modal สำหรับกรอกข้อมูลการจองด้วยโค้ดใหม่นี้ -->
        <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-calendar-check me-2"></i>กรอกข้อมูลการจอง</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="process_booking.php" enctype="multipart/form-data" id="bookingForm">
                            <input type="hidden" id="booking_date" name="booking_date">
                            <input type="hidden" id="deposit_amount" name="deposit_amount">
                            <input type="hidden" id="total_amount" name="total_amount">
                            
                            <!-- ขั้นตอนที่ 1: ข้อมูลพื้นฐาน -->
                            <div id="step1">
                                <div class="row mb-4">
                                    <div class="col-md-12 mb-3">
                                        <div class="alert alert-primary d-flex align-items-center">
                                            <i class="bi bi-calendar-event me-2 fs-4"></i>
                                            <div>
                                                <strong>วันที่จอง:</strong> <span id="display-booking-date"></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ชื่อผู้จอง</label>
                                        <input type="text" class="form-control" name="group_name" value="<?php echo htmlspecialchars($memberName); ?>" <?php echo $loggedIn ? 'readonly' : ''; ?> required placeholder="ชื่อ-นามสกุล">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">หมายเลขโทรศัพท์</label>
                                        <input type="tel" class="form-control" name="phone_number" value="<?php echo htmlspecialchars($memberPhone); ?>" <?php echo $loggedIn ? 'readonly' : ''; ?> pattern="[0-9]{10}" required placeholder="เช่น 0812345678">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">เวลาเข้าชม</label>
                                        <input type="text" class="form-control" id="visit_time" name="visit_time" required placeholder="เลือกเวลา">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">จำนวนผู้เข้าชม</label>
                                        <input type="number" class="form-control" id="number_of_people" name="number_of_people" required min="1" placeholder="เช่น 30">
                                    </div>
                                </div>
                                
                                <!-- แสดงการคำนวณราคาในขั้นตอนที่ 1 -->
                                <div class="payment-card" id="calculation-section" style="display:none;">
                                    <h6><i class="bi bi-calculator me-2"></i>สรุปราคา</h6>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-2">
                                                <strong>ยอดรวมทั้งหมด:</strong>
                                                <div id="total-amount-preview" class="fw-bold fs-5 text-success"></div>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <strong>ยอดมัดจำ (30%):</strong>
                                                <div id="deposit-amount-preview" class="fw-bold fs-5 text-primary"></div>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <strong>ยอดคงเหลือชำระวันเข้าชม:</strong>
                                                <div id="remain-amount-preview" class="fw-bold fs-5 text-danger"></div>
                                            </div>
                                            
                                            <div class="alert alert-info small mt-3">
                                                <i class="bi bi-info-circle me-2"></i>ในขั้นตอนถัดไป จะมี QR Code สำหรับชำระค่ามัดจำ
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end mt-4">
                                    <!-- ปุ่มกลับ -->
                                    <button type="button" id="nextBtn" class="ant-btn ant-btn-default" style="
                                        border-radius: 10px;
                                        padding: 10px 25px;
                                        background: rgba(255,255,255,0.7);
                                        backdrop-filter: blur(12px);
                                        border: 1px solid rgba(200,200,200,0.5);
                                        display: flex;
                                        align-items: center;
                                        gap: 8px;
                                    ">
                                        <span class="anticon">
                                            <i class="bi bi-arrow-right"></i>
                                        </span>
                                        ถัดไป
                                    </button>
                                </div>
                            </div>
                            
                            <!-- ขั้นตอนที่ 2: อัพโหลดไฟล์และชำระเงิน -->
                            <div id="step2" style="display:none;">
                                <div class="row mb-4">
                                    <div class="col-md-12 mb-3">
                                        <div class="alert alert-info d-flex align-items-center">
                                            <i class="bi bi-info-circle me-2 fs-4"></i>
                                            <div>
                                                <strong>ขั้นตอนที่ 2:</strong> ชำระค่ามัดจำและอัพโหลดไฟล์
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- QR พร้อมเพย์ในขั้นตอนที่ 2 -->
                                    <div class="payment-card" id="qrcode-section">
                                        <h6><i class="bi bi-qr-code me-2"></i>ชำระค่ามัดจำ 30%</h6>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-center">
                                                        <div id="qrcode"></div>
                                                    </div>
                                                </div>
                                                
                                                <div class="alert alert-light">
                                                    <small>บัญชีธนาคาร: นายหนึ่งเดียว เทียกสีบุญ<br>เลขบัญชี: 065-107-8576</small>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <strong>ยอดรวมทั้งหมด:</strong>
                                                    <div id="total-amount-display" class="fw-bold fs-5 text-success"></div>
                                                </div>
                                                
                                                <div class="mb-2">
                                                    <strong>ยอดมัดจำ (30%):</strong>
                                                    <div id="deposit-amount-display" class="fw-bold fs-5 text-primary"></div>
                                                </div>
                                                
                                                <div class="mb-2">
                                                    <strong>ยอดคงเหลือชำระวันเข้าชม:</strong>
                                                    <div id="remain-amount-display" class="fw-bold fs-5 text-danger"></div>
                                                </div>
                                                
                                                <div class="alert alert-warning small mt-3">
                                                    <i class="bi bi-info-circle me-2"></i>กรุณาชำระค่ามัดจำเพื่อยืนยันการจอง
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">แนบสลิปการชำระเงิน <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" name="slip" required>
                                        <div class="form-text">กรุณาแนบสลิปการชำระเงินค่ามัดจำ 30%</div>
                                    </div>
                                    
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">แนบเอกสารเพิ่มเติม (ถ้ามี) ต้องเป็นไฟล์ PDF เท่านั้น</label>
                                        <input type="file" class="form-control" name="document">
                                        <div class="form-text">เช่น เอกสารการขอเข้าศึกษาดูงานจากสถาบันการศึกษา</div>
                                    </div>
                                </div>
                                
                                <div style="display: flex; justify-content:space-between; margin-top: 20px;">

                                    <!-- ปุ่มกลับ -->
                                    <button type="button" id="backBtn" class="ant-btn ant-btn-default" style="
                                        border-radius: 10px;
                                        padding: 10px 25px;
                                        background: rgba(255,255,255,0.7);
                                        backdrop-filter: blur(12px);
                                        border: 1px solid rgba(200,200,200,0.5);
                                        display: flex;
                                        align-items: center;
                                        gap: 8px;
                                    ">
                                        <span class="anticon">
                                            <i class="bi bi-arrow-left"></i>
                                        </span>
                                        กลับ
                                    </button>

                                    <!-- ปุ่มยืนยัน -->
                                    <button type="submit" class="ant-btn ant-btn-primary" style="
                                        border-radius: 10px;
                                        padding: 10px 25px;
                                        background: linear-gradient(135deg, #2ecc71, #27ae60);
                                        border: none;
                                        display: flex;
                                        align-items: center;
                                        gap: 8px;
                                    ">
                                        <span class="anticon">
                                            <i class="bi bi-check-circle"></i>
                                        </span>
                                        ยืนยันการจอง
                                    </button>

                            </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal ขั้นตอนการจอง -->
        <div class="modal fade" id="howtoModal" tabindex="-1" aria-labelledby="howtoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="howtoModalLabel"><i class="bi bi-list-check me-2"></i>ขั้นตอนการจองเข้าชมสวน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="step-card">
                            <h5><span class="step-number">1</span>เลือกวันที่ต้องการจองในปฏิทิน</h5>
                            <p class="mb-0">เลือกวันที่แสดงด้วยสีเขียวซึ่งหมายถึงวันว่าง</p>
                        </div>
                        
                        <div class="step-card">
                            <h5><span class="step-number">2</span>กรอกข้อมูลการจองให้ครบถ้วน</h5>
                            <p class="mb-0">ระบุชื่อคณะ จำนวนคน และข้อมูลที่จำเป็นทั้งหมด</p>
                        </div>
                        
                        <div class="step-card">
                            <h5><span class="step-number">3</span>ชำระค่ามัดจำ</h5>
                            <p class="mb-0">ชำระค่ามัดจำ 30% ผ่าน QR พร้อมเพย์ที่ระบบแสดงให้</p>
                        </div>

                        <div class="step-card">
                            <h5><span class="step-number">4</span>แนบสลิปการชำระเงิน</h5>
                            <p class="mb-0">อัพโหลดสลิปการโอนเงินเพื่อยืนยันการจอง</p>
                        </div>
                        
                        <div class="step-card">
                            <h5><span class="step-number">5</span>รอการอนุมัติจากเจ้าหน้าที่</h5>
                            <p class="mb-0">เจ้าหน้าที่จะตรวจสอบและอนุมัติการจองของคุณภายใน 24 ชั่วโมง</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">เข้าใจแล้ว</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
<script>
    $(document).ready(function() {
        // ตัวแปร global
        let isLoggedIn = <?php echo json_encode($loggedIn); ?>;
        let bookingData = { total: 0, deposit: 0, remain: 0 };
        
        console.log("isLoggedIn =", isLoggedIn); // debug ดูค่า

        // ตั้งค่า Calendar
        initializeCalendar();
        
        // ตั้งค่า Time Picker
        initializeTimePicker();
        
        // จัดการ Modal Booking
        initializeBookingModal();

        // จัดการ Success Modal
        initializeSuccessModal();

        // จัดการ Form Submission
        initializeFormValidation();

        function initializeCalendar() {
            moment.locale('th');
            var calendarDates = <?php echo json_encode($dates); ?>;
            var approvedDates = <?php echo json_encode($approved); ?>;
            var bookingNames = <?php echo json_encode($booking_names); ?>;

            var events = calendarDates.map(function(d) {
                var date = d.date;
                var title = '';
                var className = '';
                
                if (approvedDates.includes(date)) {
                    title = bookingNames[date] ? bookingNames[date].join(', ') : 'จองแล้ว';
                    className = 'booked';
                } else {
                    className = d.status === 'available' ? 'available' : 'unavailable';
                    title = d.status === 'available' ? 'ว่าง' : 'ไม่ว่าง';
                }
                
                return {
                    title: title,
                    start: date,
                    className: className,
                    allDay: true
                };
            });

            $('#calendar').fullCalendar({
                locale: 'th',
                selectable: true,
                selectHelper: true,
                dayRender: function(date, cell) {
                    var dateStr = date.format('YYYY-MM-DD');
                    var found = calendarDates.find(d => d.date === dateStr);
                    
                    if (approvedDates.includes(dateStr)) {
                        cell.css('background-color', '#e3f2fd');
                    } else if (found && found.status === 'unavailable') {
                        cell.css('background-color', '#fde8e8');
                    } else if (!found) {
                        cell.css('background-color', '#f8f9fa');
                        cell.css('opacity', '0.5');
                        cell.css('cursor', 'not-allowed');
                    }
                },
                select: function(startDate) {
                    handleDateSelection(startDate, calendarDates, approvedDates);
                },
                events: events,
                eventAfterRender: function(event, element) {
                    if (event.className.includes('booked')) {
                        element.attr('title', event.title);
                        element.tooltip({ container: 'body' });
                    }
                }
            });
        }

        function initializeTimePicker() {
            flatpickr("#visit_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                minTime: "08:00",
                maxTime: "17:30",
                minuteIncrement: 30
            });
        }

        function initializeBookingModal() {
            // รีเซ็ตฟอร์มทุกครั้งที่เปิด modal
            $('#bookingModal').on('show.bs.modal', function(e) {
                resetBookingForm();
            });

            // ไปขั้นตอนที่ 2
            $('#nextBtn').on('click', function() {
                if (validateStep1()) {
                    generateQRCode();
                    $('#step1').hide();
                    $('#step2').show();
                }
            });

            // กลับไปขั้นตอนที่ 1
            $('#backBtn').on('click', function() {
                $('#step2').hide();
                $('#step1').show();
            });

            // คำนวณราคา
            $('#number_of_people').on('input', calculatePrice);
        }

        function initializeSuccessModal() {
            <?php if (isset($_GET['success'])): ?>
            (function() {
                var modalEl = document.getElementById('successModal');
                if (modalEl) {
                    // เก็บ id ก่อนจะลบ query string
                    const urlParams = new URLSearchParams(window.location.search);
                    const bookingId = urlParams.get('id');

                    var modal = new bootstrap.Modal(modalEl);
                    modal.show();

                    var okBtn = document.getElementById('successModalOkBtn');
                    if (okBtn) {
                        okBtn.addEventListener('click', function() {
                            window.location.href = bookingId ? 'receipt.php?id=' + bookingId : 'receipt.php';
                        });
                    }

                    // ลบ query string เพื่อไม่ให้ modal โผล่อีกเมื่อ refresh
                    if (window.history && history.replaceState) {
                        const newUrl = window.location.pathname;
                        history.replaceState(null, '', newUrl);
                    }
                }
            })();
            <?php endif; ?>
        }

        function initializeFormValidation() {
            document.querySelector('form').addEventListener('submit', function(e) {
                const slipInput = document.querySelector('input[name="slip"]');
                if (!slipInput.value) {
                    showWarningModal('กรุณาแนบสลิป', 'กรุณาแนบสลิปการชำระเงินก่อนยืนยันการจอง', 'error');
                    e.preventDefault();
                }
            });
        }

        function handleDateSelection(startDate, calendarDates, approvedDates) {
            var selectedDate = moment(startDate).format('YYYY-MM-DD');
            var found = calendarDates.find(d => d.date === selectedDate);

            if (!isLoggedIn) {
                showLoginModal({
                    title: 'กรุณาเข้าสู่ระบบ',
                    content: 'คุณต้องเข้าสู่ระบบก่อนทำการจอง',
                    okText: 'เข้าสู่ระบบ',
                    cancelText: 'ยกเลิก',
                    onOk() { window.location.href = "member_login.php"; },
                    onCancel() { console.log('Cancel'); }
                });
                return;
            }

            // ตรวจสอบเงื่อนไขทั้งหมดที่ทำให้ไม่สามารถจองได้
            if (!found) {
                showWarningModal('ไม่สามารถจองได้', 'วันที่เลือกนี้ยังไม่เปิดให้จอง กรุณาเลือกวันอื่น', 'error');
            } else if (approvedDates.includes(selectedDate)) {
                showWarningModal('วันนี้มีผู้จองแล้ว', 'กรุณาเลือกวันอื่น', 'warning');
            } else if (found.status === 'unavailable') {
                showWarningModal('วันที่เลือกนี้ไม่ว่าง', 'กรุณาเลือกวันอื่น', 'warning');
            } else if (found.status !== 'available') {
                showWarningModal('ไม่สามารถจองได้', 'สถานะวันที่เลือกไม่ถูกต้อง กรุณาเลือกวันอื่น', 'error');
            } else {
                // เฉพาะกรณีที่ available เท่านั้น
                $('#booking_date').val(selectedDate);
                $('#bookingModal').modal('show');
            }
            
            // ไฮไลต์ booking-item ที่ถูกเลือก
            $('.booking-item').removeClass('active');
            $(`.booking-item[data-date="${selectedDate}"]`).addClass('active');
        }

        // ฟังก์ชันแสดง Modal Login
        function showLoginModal(config) {
            const modalHtml = `
                <div class="ant-modal-mask" style="position: fixed; inset: 0px; z-index: 1050; background: rgba(0, 0, 0, 0.45);">
                    <div class="ant-modal-wrap" style="position: fixed; inset: 0px; overflow: auto; outline: 0px;">
                        <div class="ant-modal" style="width: 400px; margin: 100px auto; padding-bottom: 24px; pointer-events: auto;">
                            <div class="ant-modal-content" style="box-shadow: 0 6px 16px 0 rgba(0, 0, 0, 0.08), 0 3px 6px -4px rgba(0, 0, 0, 0.12), 0 9px 28px 8px rgba(0, 0, 0, 0.05); border-radius: 8px; background: #fff;">
                                <div class="ant-modal-header" style="padding: 16px 24px; border-bottom: 1px solid #f0f0f0; border-radius: 8px 8px 0 0; background: #fff;">
                                    <div class="ant-modal-title" style="margin: 0; color: rgba(0, 0, 0, 0.88); font-weight: 600; font-size: 16px; line-height: 1.5;">${config.title}</div>
                                </div>
                                <div class="ant-modal-body" style="padding: 24px; font-size: 14px; line-height: 1.5714285714285714; word-wrap: break-word;">${config.content}</div>
                                <div class="ant-modal-footer" style="padding: 16px 24px; text-align: right; background: 0 0; border-top: 1px solid #f0f0f0; border-radius: 0 0 8px 8px;">
                                    <button class="ant-btn ant-btn-default" style="position: relative; display: inline-flex; align-items: center; font-weight: 400; white-space: nowrap; text-align: center; background-image: none; border: 1px solid transparent; cursor: pointer; transition: all 0.2s cubic-bezier(0.645, 0.045, 0.355, 1); user-select: none; touch-action: manipulation; line-height: 1.5714285714285714; height: 32px; padding: 4px 15px; font-size: 14px; border-radius: 6px; color: rgba(0, 0, 0, 0.88); border-color: #d9d9d9; background: #fff; margin-right: 8px;" id="cancelBtn">
                                        <span>${config.cancelText}</span>
                                    </button>
                                    <button class="ant-btn ant-btn-primary" style="position: relative; display: inline-flex; align-items: center; font-weight: 400; white-space: nowrap; text-align: center; background-image: none; border: 1px solid transparent; cursor: pointer; transition: all 0.2s cubic-bezier(0.645, 0.045, 0.355, 1); user-select: none; touch-action: manipulation; line-height: 1.5714285714285714; height: 32px; padding: 4px 15px; font-size: 14px; border-radius: 6px; color: #fff; border-color: #1677ff; background: #1677ff; box-shadow: 0 2px 0 rgba(5, 145, 255, 0.1);" id="okBtn">
                                        <span>${config.okText}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
            
            // Event handlers
            $('#okBtn').on('click', function() {
                config.onOk();
                $('.ant-modal-mask').remove();
            });
            
            $('#cancelBtn').on('click', function() {
                config.onCancel();
                $('.ant-modal-mask').remove();
            });
            
            // Close when clicking on mask
            $('.ant-modal-mask').on('click', function(e) {
                if (e.target === this) {
                    config.onCancel();
                    $(this).remove();
                }
            });
        }

        // ฟังก์ชันแสดง Warning Modal
        function showWarningModal(title, content, type = 'warning') {
            const icons = {
                warning: { icon: '⚠', color: '#faad14' },
                error: { icon: '❌', color: '#ff4d4f' },
                info: { icon: 'ℹ️', color: '#1677ff' }
            };
            
            const selectedIcon = icons[type] || icons.warning;

            const modalHtml = `
                <div class="ant-modal-mask" style="position: fixed; inset: 0px; z-index: 1050; background: rgba(0, 0, 0, 0.45);">
                    <div class="ant-modal-wrap" style="position: fixed; inset: 0px; overflow: auto; outline: 0px;">
                        <div class="ant-modal" style="width: 400px; margin: 100px auto; padding-bottom: 24px; pointer-events: auto;">
                            <div class="ant-modal-content" style="box-shadow: 0 6px 16px 0 rgba(0, 0, 0, 0.08), 0 3px 6px -4px rgba(0, 0, 0, 0.12), 0 9px 28px 8px rgba(0, 0, 0, 0.05); border-radius: 8px; background: #fff;">
                                <div class="ant-modal-header" style="padding: 16px 24px; border-bottom: 1px solid #f0f0f0; border-radius: 8px 8px 0 0; background: #fff;">
                                    <div class="ant-modal-title" style="margin: 0; color: rgba(0, 0, 0, 0.88); font-weight: 600; font-size: 16px; line-height: 1.5; display: flex; align-items: center; gap: 8px;">
                                        <span style="color: ${selectedIcon.color}; font-size: 18px;">${selectedIcon.icon}</span>
                                        ${title}
                                    </div>
                                </div>
                                <div class="ant-modal-body" style="padding: 24px; font-size: 14px; line-height: 1.5714285714285714; word-wrap: break-word;">${content}</div>
                                <div class="ant-modal-footer" style="padding: 16px 24px; text-align: right; background: 0 0; border-top: 1px solid #f0f0f0; border-radius: 0 0 8px 8px;">
                                    <button class="ant-btn ant-btn-primary" style="position: relative; display: inline-flex; align-items: center; font-weight: 400; white-space: nowrap; text-align: center; background-image: none; border: 1px solid transparent; cursor: pointer; transition: all 0.2s cubic-bezier(0.645, 0.045, 0.355, 1); user-select: none; touch-action: manipulation; line-height: 1.5714285714285714; height: 32px; padding: 4px 15px; font-size: 14px; border-radius: 6px; color: #fff; border-color: #1677ff; background: #1677ff; box-shadow: 0 2px 0 rgba(5, 145, 255, 0.1);" id="confirmBtn">
                                        <span>ตกลง</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
            
            $('#confirmBtn').on('click', function() {
                $('.ant-modal-mask').remove();
            });
            
            $('.ant-modal-mask').on('click', function(e) {
                if (e.target === this) {
                    $(this).remove();
                }
            });
        }

        function resetBookingForm() {
            $('#step1').show();
            $('#step2').hide();
            $('#calculation-section').hide();
            $('#qrcode').empty();
            $('#number_of_people').val('');
            
            // แสดงวันที่เลือก
            var selectedDate = $('#booking_date').val();
            var thaiDate = moment(selectedDate).locale('th').format('LL');
            $('#display-booking-date').text(thaiDate);
        }

        function validateStep1() {
            let isValid = true;
            
            $('#step1 input[required]').each(function() {
                if ($(this).val() === '') {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            
            const peopleCount = parseInt($('#number_of_people').val());
            if (isNaN(peopleCount) || peopleCount < 1) {
                isValid = false;
                $('#number_of_people').addClass('is-invalid');
            }
            
            if (!isValid) {
                showWarningModal('กรุณากรอกข้อมูลให้ครบถ้วน', 'กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้องก่อนไปขั้นตอนต่อไป', 'error');
            }
            
            return isValid;
        }

        function calculatePrice() {
            let people = parseInt($(this).val());
            
            if (isNaN(people) || people < 1) {
                $('#calculation-section').hide();
                return;
            }

            // คำนวณราคา
            bookingData.total = people * 150;
            bookingData.deposit = Math.ceil(bookingData.total * 0.3);
            bookingData.remain = bookingData.total - bookingData.deposit;

            // แสดงการคำนวณ
            $('#total-amount-preview').html(`${bookingData.total.toLocaleString()} บาท`);
            $('#deposit-amount-preview').html(`${bookingData.deposit.toLocaleString()} บาท`);
            $('#remain-amount-preview').html(`${bookingData.remain.toLocaleString()} บาท`);
            $('#calculation-section').show();
        }

        function generateQRCode() {
            const promptpayId = "0651078576";
            
            // แสดงการคำนวณในขั้นตอนที่ 2
            $('#total-amount-display').html(`${bookingData.total.toLocaleString()} บาท`);
            $('#deposit-amount-display').html(`${bookingData.deposit.toLocaleString()} บาท`);
            $('#remain-amount-display').html(`${bookingData.remain.toLocaleString()} บาท`);
            
            // ตั้งค่า hidden inputs
            $('#total_amount').val(bookingData.total);
            $('#deposit_amount').val(bookingData.deposit);

            // สร้าง QR Code
            $('#qrcode').empty();
            
            if (typeof PromptPayQR !== 'undefined') {
                let qrData = PromptPayQR.generate({
                    mobileNumber: promptpayId,
                    amount: bookingData.deposit
                });
                new QRCode(document.getElementById("qrcode"), {
                    text: qrData,
                    width: 180,
                    height: 180
                });
            } else {
                // fallback
                let depositFixed = bookingData.deposit.toFixed(1);
                let qrImg = $('<img>').attr('src',
                    `https://promptpay.io/${promptpayId}/${depositFixed}.png`).css({
                    width: 180,
                    height: 180
                });
                $('#qrcode').append(qrImg);
            }
        }
    });
</script>
</body>
</html>