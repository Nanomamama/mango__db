<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../admin/db.php'; // ตรวจสอบให้แน่ใจว่ามีการเชื่อมต่อฐานข้อมูล

$member_id_session = $_SESSION['member_id'] ?? null;

$is_member = isset($_SESSION['member_id']);
$member_data = [
    'fullname' => '',
    'email' => '',
    'phone' => ''
];

if ($is_member) {
    $member_id = $_SESSION['member_id'];
    $stmt = $conn->prepare("SELECT fullname, email, phone FROM members WHERE member_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $member_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result) $member_data = $result; // กำหนดค่าเมื่อพบข้อมูลเท่านั้น
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จองคิวเข้าชมสวน</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome สำหรับไอคอน -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&family=Sarabun:wght@300;400;500&display=swap" rel="stylesheet">

    <!-- Flatpickr CSS สำหรับปฏิทิน -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        :root {
            --primary-color: #016A70;
            --primary-light: #dbeafe;
            --secondary-color: #018992;
            --accent-color: #3b82f6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --light-color: #f8fafc;
            --dark-color: #1e293b;
            --text-color: #334155;
            --text-light: #64748b;
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            font-family: 'Sarabun', sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: #ffffff;
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Kanit', sans-serif;
            font-weight: 600;
            color: var(--dark-color);
        }

        /* Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2.5rem 0;
            margin-bottom: 2.5rem;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
        }

        .page-header-content {
            position: relative;
            z-index: 1;
        }

        .page-header h2 {
            font-weight: 700;
            margin: 0;
            position: relative;
            padding-bottom: 15px;
            display: inline-block;
        }

        .page-header h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 70px;
            height: 4px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
        }

        /* Container */
        .container-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Card */
        .card-modern {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            margin-bottom: 2rem;
            background-color: #ffffff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-modern:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .card-header-modern {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
            padding: 1.75rem 2rem;
            border-bottom: none;
        }

        .card-body-modern {
            padding: 2.5rem;
        }

        @media (max-width: 768px) {
            .card-body-modern {
                padding: 1.5rem;
            }
        }

        /* Form elements */
        .form-label-modern {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
            font-size: 0.95rem;
        }

        .required::after {
            content: " *";
            color: var(--danger-color);
        }

        .form-control-modern,
        .form-select-modern {
            border-radius: var(--border-radius-sm);
            padding: 0.875rem 1rem;
            border: 1.5px solid #e2e8f0;
            transition: all 0.3s;
            font-size: 1rem;
            background-color: #ffffff;
        }

        .form-control-modern:focus,
        .form-select-modern:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .form-control-modern.is-invalid,
        .form-select-modern.is-invalid {
            border-color: var(--danger-color);
            background-image: none;
            padding-right: 1rem;
        }

        .invalid-feedback-modern {
            display: block;
            margin-top: 0.375rem;
            font-size: 0.875rem;
            color: var(--danger-color);
        }

        /* Button */
        .btn-modern {
            border-radius: var(--border-radius-sm);
            padding: 0.875rem 2rem;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(37, 99, 235, 0.3);
            color: white;
        }

        .btn-secondary-modern {
            background-color: var(--light-color);
            color: var(--text-color);
            border: 1.5px solid #e2e8f0;
        }

        .btn-secondary-modern:hover {
            background-color: #f1f5f9;
            color: var(--text-color);
        }

        .btn-success-modern {
            background: linear-gradient(135deg, var(--success-color) 0%, #34d399 100%);
            color: white;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);
        }

        .btn-success-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        /* Step Indicator */
        .step-indicator-modern {
            display: flex;
            justify-content: center;
            margin-bottom: 3rem;
        }

        .step-modern {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .step-number-modern {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: #f1f5f9;
            color: var(--text-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.125rem;
            margin-bottom: 0.75rem;
            border: 3px solid #ffffff;
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }

        .step-modern.active .step-number-modern {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            box-shadow: 0 6px 12px rgba(37, 99, 235, 0.3);
        }

        .step-modern.completed .step-number-modern {
            background-color: var(--success-color);
            color: white;
        }

        .step-label-modern {
            font-size: 0.95rem;
            color: var(--text-light);
            font-weight: 500;
            text-align: center;
        }

        .step-modern.active .step-label-modern {
            color: var(--primary-color);
            font-weight: 600;
        }

        .step-modern.completed .step-label-modern {
            color: var(--success-color);
        }

        .step-line-modern {
            height: 2px;
            width: 100px;
            background-color: #e2e8f0;
            align-self: center;
            margin-top: 24px;
            position: relative;
            z-index: 0;
        }

        .step-line-modern.completed {
            background-color: var(--success-color);
        }

        /* Calendar */
        .calendar-section-modern {
            padding: 1rem 0 2rem 0;
        }

        .calendar-title-modern {
            margin-bottom: 1.5rem;
            color: var(--dark-color);
            display: flex;
            align-items: center;
        }

        .calendar-table-modern {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border-radius: var(--border-radius-sm);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .calendar-table-modern th {
            background-color: #f8fafc;
            font-weight: 600;
            padding: 1rem 0.5rem;
            color: var(--dark-color);
            border-bottom: 2px solid #e2e8f0;
            text-align: center;
        }

        .calendar-table-modern td {
            border: 1px solid #f1f5f9;
            padding: 0.75rem 0.5rem;
            text-align: center;
            vertical-align: top;
            transition: all 0.2s;
            cursor: pointer;
            height: 90px;
            background-color: white;
        }

        .calendar-table-modern td:hover {
            background-color: var(--primary-light);
            transform: scale(1.02);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .calendar-day-number {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 4px;
            color: var(--dark-color);
        }

        .calendar-table-modern td.today .calendar-day-number {
            color: var(--primary-color);
            font-weight: 700;
        }

        .calendar-table-modern td.selected-date {
            background-color: rgba(37, 99, 235, 0.1);
            border: 2px solid var(--primary-color);
            position: relative;
        }

        .calendar-table-modern td.selected-date::after {
            content: '';
            position: absolute;
            top: 5px;
            right: 5px;
            width: 8px;
            height: 8px;
            background-color: var(--primary-color);
            border-radius: 50%;
        }

        .calendar-table-modern td.past-date {
            background-color: #f8fafc;
            color: #cbd5e1;
            cursor: not-allowed;
        }

        .calendar-table-modern td.past-date .calendar-day-number {
            color: #cbd5e1;
        }

        .calendar-table-modern td.past-date:hover {
            background-color: #f8fafc;
            transform: none;
            box-shadow: none;
        }

        .calendar-table-modern td.booked {
            background-color: rgba(245, 158, 11, 0.1);
        }

        .calendar-table-modern td.confirmed-booked {
            background-color: rgba(16, 185, 129, 0.1);
        }

        .status-dots-modern {
            display: flex;
            justify-content: center;
            gap: 3px;
            margin: 4px 0;
        }

        .status-dot-modern {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .status-dot-modern.pending {
            background: linear-gradient(135deg, var(--warning-color) 0%, #f97316 100%);
        }

        .status-dot-modern.confirmed {
            background: linear-gradient(135deg, var(--success-color) 0%, #34d399 100%);
        }

        /* Summary Box */
        .summary-box-modern {
            background-color: #f8fafc;
            border-radius: var(--border-radius-sm);
            padding: 1.75rem;
            border-left: 4px solid var(--primary-color);
            box-shadow: var(--shadow);
        }

        .summary-item-modern {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px dashed #e2e8f0;
        }

        .summary-item-modern:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .total-highlight-modern {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.25rem;
        }

        /* File Upload */
        .file-upload-area-modern {
            border: 2px dashed #cbd5e1;
            border-radius: var(--border-radius-sm);
            padding: 2.5rem;
            text-align: center;
            background-color: #f8fafc;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-upload-area-modern:hover {
            border-color: var(--primary-color);
            background-color: rgba(37, 99, 235, 0.05);
        }

        .file-upload-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        /* Alert */
        .alert-modern {
            border-radius: var(--border-radius-sm);
            border: none;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
        }

        .alert-info-modern {
            background-color: rgba(37, 99, 235, 0.1);
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
        }

        .alert-success-modern {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-error-modern {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        /* Modal */
        .modal-content-modern {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--shadow-xl);
        }

        .modal-header-modern {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            padding: 1.75rem;
            border-bottom: none;
        }

        .modal-body-modern {
            padding: 2rem;
        }

        /* Navigation */
        .calendar-navigation-modern {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 0.75rem 1rem;
            background-color: #f8fafc;
            border-radius: var(--border-radius-sm);
        }

        .calendar-navigation-modern h5 {
            margin: 0;
            color: var(--dark-color);
            font-weight: 600;
        }

        /* Badge */
        .badge-modern {
            border-radius: 50px;
            padding: 0.375rem 0.75rem;
            font-weight: 500;
        }

        /* Input Group */
        .input-group-modern {
            border-radius: var(--border-radius-sm);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .input-group-modern .btn {
            background-color: #f8fafc;
            border: 1.5px solid #e2e8f0;
            color: var(--text-color);
            font-weight: 600;
        }

        .input-group-modern .form-control {
            border: 1.5px solid #e2e8f0;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .step-line-modern {
                width: 40px;
            }
            
            .calendar-table-modern td {
                height: 70px;
                padding: 0.5rem 0.25rem;
            }
            
            .calendar-day-number {
                font-size: 1rem;
            }
            
            .card-body-modern {
                padding: 1.5rem;
            }
            
            .file-upload-area-modern {
                padding: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .step-indicator-modern {
                flex-wrap: wrap;
            }
            
            .step-modern {
                margin-bottom: 1rem;
            }
            
            .calendar-table-modern td {
                height: 60px;
                padding: 0.25rem;
            }
            
            .calendar-day-number {
                font-size: 0.9rem;
            }
            
            .page-header {
                padding: 2rem 0;
            }
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="page-header-content">
                        <h2>เลือกวันที่ต้องการเข้าชมสวน</h2>
                        <p class="mb-0 mt-3">กรุณาเลือกวันที่คุณต้องการจองเข้าชมสวนจากปฏิทินด้านล่าง</p>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-inline-block p-3 bg-white rounded shadow" style="color: var(--dark-color);">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock fa-2x me-3" style="color: var(--primary-color);"></i>
                            <div>
                                <div class="fw-bold">เวลาเปิดทำการ</div>
                                <div class="small">08:00 - 17:30 น.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container container-main">
        <div class="card-modern">
            <!-- สัญลักษณ์ขั้นตอนการจอง -->
            <div class="step-indicator-modern pt-4">
                <div class="step-modern active" id="step1">
                    <div class="step-number-modern">1</div>
                    <div class="step-label-modern">เลือกวันที่เข้าชม</div>
                </div>
                <div class="step-line-modern" id="stepLine1"></div>
                <div class="step-modern" id="step2">
                    <div class="step-number-modern">2</div>
                    <div class="step-label-modern">กรอกรายละเอียด</div>
                </div>
            </div>

            <!-- Alert สำหรับแสดงข้อความแจ้งเตือน -->
            <div id="formAlert" class="alert-modern d-none"></div>

            <div class="card-body-modern">
                <!-- ขั้นตอนที่ 1: เลือกวันที่จากปฏิทิน -->
                <div id="calendar-section">
                    <div class="calendar-section-modern">
                        <h3 class="calendar-title-modern">
                            <i class="far fa-calendar-alt me-3" style="color: var(--primary-color);"></i>
                            เลือกวันที่ต้องการเข้าชมสวน
                        </h3>
                        <p class="text-muted mb-4">กรุณาเลือกวันที่คุณต้องการจองเข้าชมสวนจากปฏิทินด้านล่าง</p>

                        <!-- ปฏิทิน (รูปแบบตาราง) และแถบสถานะซ้ายมือ -->
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="card-modern mb-4">
                                    <div class="card-header-modern py-3">
                                        <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>สถานะการจอง</h5>
                                    </div>
                                    <div class="card-body-modern p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                            <div>จองสำเร็จ</div>
                                            <span id="confirmedCount" class="badge-modern bg-success">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                            <div>รอยืนยันการจอง</div>
                                            <span id="pendingCount" class="badge-modern bg-warning text-dark">0</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-modern mb-4">
                                    <div class="card-header-modern py-3">
                                        <h6 class="mb-0"><i class="fas fa-clock me-2"></i>รออนุมัติ</h6>
                                    </div>
                                    <div class="card-body-modern p-3">
                                        <ul id="pendingList" class="list-group list-group-flush" style="max-height:200px; overflow:auto;"></ul>
                                    </div>
                                </div>

                                <div class="card-modern">
                                    <div class="card-header-modern py-3">
                                        <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>ยืนยันแล้ว</h6>
                                    </div>
                                    <div class="card-body-modern p-3">
                                        <ul id="confirmedList" class="list-group list-group-flush" style="max-height:200px; overflow:auto;"></ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-9">
                                <div class="card-modern">
                                    <div class="card-body-modern p-3">
                                        <div id="calendarTable" class="table-responsive"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- แสดงวันที่ที่เลือก -->
                        <div id="dateSelectedBox" class="card-modern mt-4 d-none">
                            <div class="card-body-modern">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="mb-0">
                                            <i class="far fa-calendar-check me-2" style="color: var(--primary-color);"></i>
                                            <span class="fw-bold">วันที่คุณเลือก:</span>
                                            <span id="selectedDateDisplay" class="fw-bold text-primary ms-2"></span>
                                        </h5>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <button type="button" class="btn btn-secondary-modern btn-modern" onclick="clearSelectedDate()">
                                            <i class="fas fa-times me-2"></i>เปลี่ยนวันที่
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ปุ่มดำเนินการต่อ -->
                        <div class="text-center mt-4">
                            <button type="button" id="continueBtn" class="btn btn-primary-modern btn-modern px-5" onclick="showFormSection()" disabled>
                                <i class="fas fa-arrow-right me-2"></i>ดำเนินการต่อ
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ขั้นตอนที่ 2: กรอกแบบฟอร์มจอง -->
                <div id="form-section" class="d-none">
                    <div class="card-modern p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="mb-0"><i class="fas fa-file-alt me-2" style="color: var(--primary-color);"></i>กรอกรายละเอียดการจอง</h4>
                                <small class="text-muted">กรอกข้อมูลให้ครบเพื่อยืนยันการจอง</small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary" onclick="showCalendarSection()">
                                    <i class="fas fa-arrow-left me-1"></i> เปลี่ยนวันที่
                                </button>
                            </div>
                        </div>

                        <div class="alert alert-info-modern mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            คุณกำลังจองเข้าชมสวนในวันที่ <strong id="formSelectedDate"></strong>
                        </div>

                        <form id="bookingForm" enctype="multipart/form-data" novalidate>
                            <input type="hidden" id="selected_date" name="selected_date">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="booking_type" class="form-label-modern required">ประเภทการเข้าชม</label>
                                    <select id="booking_type" name="booking_type" class="form-select" required onchange="toggleOrgSection()">
                                        <option value="private">บุคคลทั่วไป (ส่วนตัว)</option>
                                        <option value="organization">หน่วยงาน/องค์กร</option>
                                    </select>
                                    <div class="invalid-feedback-modern">กรุณาเลือกประเภทการเข้าชม</div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="booking_time" class="form-label-modern required">เวลาเข้าชม</label>
                                    <select id="booking_time" name="booking_time" class="form-select" required>
                                        <option value="">กรุณาเลือกเวลา</option>
                                        <option value="08:00">08:00 น.</option>
                                        <option value="09:00">09:00 น.</option>
                                        <option value="10:00">10:00 น.</option>
                                        <option value="11:00">11:00 น.</option>
                                        <option value="12:00">12:00 น.</option>
                                        <option value="13:00">13:00 น.</option>
                                        <option value="14:00">14:00 น.</option>
                                        <option value="15:00">15:00 น.</option>
                                        <option value="16:00">16:00 น.</option>
                                        <option value="17:00">17:00 น.</option>
                                    </select>
                                    <div class="invalid-feedback-modern">กรุณาเลือกเวลาเข้าชม</div>
                                    <small class="text-muted d-block mt-1">เวลาเปิดทำการ: 08:00 - 17:30 น.</small>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="name" class="form-label-modern required">ชื่อ-นามสกุล</label>
                                    <input type="text" id="name" name="name" class="form-control" placeholder="ระบุชื่อ" required <?= $is_member ? 'readonly' : '' ?>>
                                    <div class="invalid-feedback-modern">กรุณากรอกชื่อ-นามสกุลหรือชื่อหน่วยงาน</div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="phone" class="form-label-modern required">เบอร์โทรศัพท์</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="08X-XXXXXXX" pattern="[0-9]{10}" required <?= $is_member ? 'readonly' : '' ?>>
                                    <div class="invalid-feedback-modern">กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง (ตัวเลข 10 หลัก)</div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="email" class="form-label-modern required">อีเมล</label>
                                    <input type="email" id="email" name="email" class="form-control" placeholder="example@domain.com" required <?= $is_member ? 'readonly' : '' ?>>
                                    <div class="invalid-feedback-modern">กรุณากรอกอีเมลให้ถูกต้อง</div>
                                </div>

                                <div class="col-12 col-md-6 d-flex flex-column">
                                    <label for="visitor_count" class="form-label-modern required">จำนวนผู้เข้าชม</label>
                                    <div class="input-group">
                                        <button class="btn btn-outline-secondary" type="button" onclick="changeVisitorCount(-1)"><i class="fas fa-minus"></i></button>
                                        <input type="number" id="visitor_count" name="visitor_count" class="form-control text-center" min="1" value="1" oninput="calculatePrice()" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="changeVisitorCount(1)"><i class="fas fa-plus"></i></button>
                                    </div>
                                    <small class="text-muted mt-1">คนละ 150 บาท</small>
                                </div>

                                <div class="col-12">
                                    <div class="form-check d-flex align-items-center p-3 rounded" id="lunch_section">
                                        <input class="form-check-input me-2" type="checkbox" id="lunch_request" name="lunch_request" value="yes">
                                        <label class="form-check-label mb-0" for="lunch_request"><i class="fas fa-utensils me-2"></i>ต้องการอาหารกลางวัน (เฉพาะนัดก่อน 12:00 น.)</label>
                                    </div>
                                </div>

                                <div id="org_upload_section" class="col-12 d-none">
                                    <label class="form-label-modern required">แนบหลักฐานหน่วยงาน (PDF/JPG/PNG)</label>
                                    <div class="d-flex flex-column flex-sm-row gap-3 align-items-start">
                                        <div class="file-upload-area-modern flex-grow-1 p-3" style="cursor:pointer;" onclick="document.getElementById('document').click()">
                                            <div class="file-upload-icon mb-2"><i class="fas fa-cloud-upload-alt"></i></div>
                                            <div>ลากไฟล์มาวางที่นี่ หรือคลิกเพื่อเลือกรูป/เอกสาร</div>
                                            <div class="text-muted">รองรับไฟล์ PDF, JPG, JPEG, PNG (ไม่เกิน 10MB)</div>
                                            <input type="file" id="document" name="document" class="d-none" accept=".pdf,.jpg,.jpeg,.png" onchange="displayFileName()">
                                        </div>
                                        <div id="file-name" class="mt-2 mt-sm-0"> 
                                            <span class="badge-modern bg-primary"><i class="fas fa-file me-1"></i>ยังไม่มีไฟล์ที่เลือก</span>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback-modern mt-2">กรุณาแนบหลักฐานหน่วยงาน</div>
                                </div>

                                <div class="col-12">
                                    <div class="summary-box-modern">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="fw-medium">สรุปรายละเอียดค่าใช้จ่าย</div>
                                            <div class="text-muted">รวมและมัดจำ</div>
                                        </div>
                                        <div class="summary-item-modern">
                                            <span>จำนวนผู้เข้าชม:</span>
                                            <span><span id="display_visitor_count">1</span> คน</span>
                                        </div>
                                        <div class="summary-item-modern">
                                            <span>ราคาต่อคน:</span>
                                            <span>150 บาท</span>
                                        </div>
                                        <div class="summary-item-modern">
                                            <span>ยอดรวมทั้งหมด:</span>
                                            <span><span id="display_total">150</span> บาท</span>
                                        </div>
                                        <div class="summary-item-modern">
                                            <span>ยอดมัดจำ (30%):</span>
                                            <span><span id="display_deposit">45</span> บาท</span>
                                        </div>
                                        <hr>
                                        <div class="summary-item-modern total-highlight-modern">
                                            <span>ยอดคงเหลือชำระวันเข้าชม:</span>
                                            <span><span id="display_balance">105</span> บาท</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-grid">
                                        <button type="button" class="btn btn-success btn-lg" onclick="submitForm()" id="submitButton">
                                            <span id="buttonText"><i class="fas fa-calendar-check me-2"></i>ยืนยันการจองคิว</span>
                                            <span id="buttonLoading" class="d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>กำลังส่งข้อมูล...</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับแสดงสถานะการส่ง -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title" id="statusModalLabel">
                        <i class="fas fa-check-circle me-2"></i>สำเร็จ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-modern text-center">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10" style="width: 100px; height: 100px;">
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                    <h4 id="modalTitle" class="mb-3">การจองคิวสำเร็จ!</h4>
                    <p id="modalMessage" class="text-muted">ระบบได้ทำการส่งข้อมูลการจองคิวเข้าชมสวนของคุณเรียบร้อยแล้ว กรุณาตรวจสอบอีเมลสำหรับข้อมูลเพิ่มเติม</p>
                </div>
                <div class="modal-footer justify-content-center border-top-0 pt-0">
                    <button type="button" class="btn btn-primary-modern btn-modern px-5" data-bs-dismiss="modal">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับแสดงข้อผิดพลาด -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern bg-danger">
                    <h5 class="modal-title" id="errorModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>เกิดข้อผิดพลาด
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-modern text-center">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10" style="width: 100px; height: 100px;">
                            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                    <h4 id="errorModalTitle" class="mb-3">ไม่สามารถส่งข้อมูลได้</h4>
                    <p id="errorModalMessage" class="text-muted">เกิดข้อผิดพลาดในการส่งข้อมูล กรุณาลองใหม่อีกครั้งหรือติดต่อผู้ดูแลระบบ</p>
                </div>
                <div class="modal-footer justify-content-center border-top-0 pt-0">
                    <button type="button" class="btn btn-danger btn-modern px-5" data-bs-dismiss="modal">ลองอีกครั้ง</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับแจ้งเตือนให้เข้าสู่ระบบ -->
    <div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern bg-warning">
                    <h5 class="modal-title" id="loginRequiredModalLabel">
                        <i class="fas fa-exclamation-circle me-2"></i>เข้าสู่ระบบก่อน
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-modern text-center">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10" style="width: 100px; height: 100px;">
                            <i class="fas fa-user-lock text-warning" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                    <h4 class="mb-3">คุณต้องเป็นสมาชิกเพื่อทำการจอง</h4>
                    <p class="text-muted">กรุณาเข้าสู่ระบบหรือสมัครสมาชิกเพื่อดำเนินการต่อ</p>
                </div>
                <div class="modal-footer justify-content-center border-top-0 pt-0">
                    <button type="button" class="btn btn-warning btn-modern px-5" onclick="redirectToLogin()">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Flatpickr สำหรับปฏิทิน -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>

    <script>
        const MEMBER_ID_SESSION = <?php echo json_encode($member_id_session); ?>; // ส่ง member_id ไปยัง JavaScript
        const MEMBER_DATA = <?php echo json_encode($member_data); ?>;
        let loginRequiredModalObj = null; // ตัวแปรสำหรับเก็บ instance ของ Modal

        // ฟังก์ชันสำหรับแสดง Modal แจ้งเตือนให้เข้าสู่ระบบ
        function showLoginRequiredModal() {
            if (!loginRequiredModalObj) {
                loginRequiredModalObj = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
            }
            loginRequiredModalObj.show();
        }

        // ฟังก์ชันสำหรับนำทางไปยังหน้าเข้าสู่ระบบ
        function redirectToLogin() {
            window.location.href = 'member_login.php';
        }

        const IS_MEMBER = <?php echo json_encode($is_member); ?>; // ตัวแปรเก็บสถานะสมาชิก
        let selectedDate = null;
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();
        
        // ฟังก์ชันสำหรับเริ่มต้นปฏิทินแบบตาราง
        function initCalendar() {
            const today = new Date();
            window.currentMonth = today.getMonth();
            window.currentYear = today.getFullYear();

            // ดึงข้อมูลการจองจากเซิร์ฟเวอร์ (ถ้ามี) แล้วเรนเดอร์ปฏิทิน
            fetchBookings().then(() => {
                renderCalendarTable(window.currentMonth, window.currentYear);
            });
        }

        // ดึงรายการการจองจาก endpoint `getBookings.php`
        function fetchBookings() {
            return new Promise((resolve) => {
                // ช่วงวันที่: แสดงตั้งแต่วันที่ 1 ถึงวันสุดท้ายของเดือนที่ currentMonth/currentYear
                const start = toYMD(new Date(window.currentYear, window.currentMonth, 1));
                const end = toYMD(new Date(window.currentYear, window.currentMonth + 1, 0));

                fetch(`getBookings.php?start=${start}&end=${end}`)
                    .then(res => res.json())
                    .then(data => {
                        // ปกป้องข้อมูลหาก API ส่งข้อผิดพลาด
                        if (!Array.isArray(data)) data = [];

                        // แปลงข้อมูลให้เรียบง่ายสำหรับปฏิทิน
                        window.bookingData = data.map(b => ({
                            date: b.date || b.booking_date || b.bookingDate,
                            name: b.name || b.guest_name || b.booking_code || 'ไม่ระบุ',
                            status: (b.status || '').toLowerCase() || 'pending',
                            time: b.time || b.booking_time || b.bookingTime || '' ,
                            visitor_count: b.visitor_count ? parseInt(b.visitor_count) : (b.visitorCount ? parseInt(b.visitorCount) : 0)
                        }));

                        populateLists();
                        resolve();
                    })
                    .catch(err => {
                        console.error('fetchBookings error:', err);
                        window.bookingData = [];
                        populateLists();
                        resolve();
                    });
            });
        }

        function getISODateOffset(offsetDays) {
            const d = new Date();
            d.setDate(d.getDate() + offsetDays);
            return toYMD(d);
        }

        // Format Date object to YYYY-MM-DD
        function toYMD(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        }

        // Parse a YYYY-MM-DD string as local date
        function parseYMD(s) {
            return new Date(s + 'T00:00:00');
        }

        // Escape HTML to safely inject booking names into cell title/text
        function escapeHtml(unsafe) {
            return String(unsafe)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Format time string to HH:MM (drop seconds if present)
        function formatTime(timeStr) {
            if (!timeStr) return '';
            const m = String(timeStr).match(/^(\d{1,2}:\d{2})/);
            return m ? m[1] : String(timeStr).slice(0,5);
        }

        // สร้างตารางปฏิทินสำหรับเดือนและปีที่กำหนด
        function renderCalendarTable(month, year) {
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const startWeekDay = (firstDay.getDay() + 6) % 7; // เปลี่ยนให้เริ่มจันทร์=0
            const thaiMonths = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
            
            let html = '<div class="calendar-navigation-modern">' +
                '<button class="btn btn-secondary-modern btn-sm" onclick="prevMonth()"><i class="fas fa-chevron-left me-1"></i>เดือนก่อน</button>' +
                '<h5 class="mb-0">' + thaiMonths[month] + ' ' + (year + 543) + '</h5>' +
                '<button class="btn btn-secondary-modern btn-sm" onclick="nextMonth()">เดือนถัดไป<i class="fas fa-chevron-right ms-1"></i></button>' +
                '</div>';

            html += '<table class="calendar-table-modern">';
            html += '<thead><tr><th>จ</th><th>อ</th><th>พ</th><th>พฤ</th><th>ศ</th><th>ส</th><th>อา</th></tr></thead><tbody>';

            let day = 1;
            let cells = Math.ceil((startWeekDay + lastDay.getDate()) / 7) * 7;
            for (let i = 0; i < cells; i++) {
                if (i % 7 === 0) html += '<tr>';

                if (i < startWeekDay || day > lastDay.getDate()) {
                    html += '<td class="bg-light"></td>';
                } else {
                    const dateObj = new Date(year, month, day);
                    const dateStr = toYMD(dateObj);
                    const bookings = (window.bookingData || []).filter(b => b.date === dateStr);
                    const hasConfirmed = bookings.some(b => b.status === 'confirmed');
                    const hasPending = bookings.some(b => b.status === 'pending');

                    // กำหนดคลาสตามสถานะ
                    let cellClass = '';
                    if (dateObj.getTime() === today.getTime()) {
                        cellClass += ' today';
                    }

                    if (dateObj < today) {
                        cellClass += ' past-date';
                    }

                    if (hasConfirmed) {
                        cellClass += ' confirmed-booked';
                    } else if (hasPending) {
                        cellClass += ' booked';
                    }

                    if (selectedDate === dateStr) {
                        cellClass += ' selected-date';
                    }

                    html += `<td class="${cellClass.trim()}" data-date="${dateStr}">`;
                    html += '<div class="calendar-day-number">' + day + '</div>';

                    // แสดงจุดสถานะการจอง
                    if (hasConfirmed || hasPending) {
                        html += '<div class="status-dots-modern">';
                        if (hasConfirmed) {
                            html += '<span class="status-dot-modern confirmed" title="มีการจองยืนยันแล้ว"></span>';
                        }
                        if (hasPending) {
                            html += '<span class="status-dot-modern pending" title="รอการยืนยัน"></span>';
                        }
                        html += '</div>';
                    }

                    // แสดงชื่อและเวลาในเซลล์ (แสดงสูงสุด 2 รายการ แล้วบอกอีกกี่รายการถ้ามีมากกว่า)
                    if (bookings.length > 0) {
                        const maxShow = 2;
                        const showList = bookings.slice(0, maxShow);
                        html += '<div class="mt-2 text-start" style="font-size:0.8rem;">';
                        showList.forEach(bk => {
                            const ft = formatTime(bk.time);
                            const titleText = (bk.name ? bk.name : '') + (ft ? ' ' + ft : '');
                            html += `<div class="text-truncate" title="${escapeHtml(titleText)}">${escapeHtml(bk.name)}${ft ? ' • ' + escapeHtml(ft) : ''}</div>`;
                        });
                        if (bookings.length > maxShow) {
                            html += `<div class="text-muted">+${bookings.length - maxShow} รายการ</div>`;
                        }
                        html += '</div>';
                    }

                    html += '</td>';
                    day++;
                }

                if (i % 7 === 6) html += '</tr>';
            }

            html += '</tbody></table>';
            document.getElementById('calendarTable').innerHTML = html;

            // ผูกเหตุการณ์คลิกวันที่
            document.querySelectorAll('#calendarTable td[data-date]').forEach(td => {
                td.addEventListener('click', function() {
                    // ถ้าวันที่ผ่านมา ให้ไม่ทำอะไร
                    if (this.classList.contains('past-date')) return;

                    const d = this.getAttribute('data-date');
                    // ป้องกันการจองวันที่มีการยืนยันหรือรออนุมัติแล้ว
                    const bookingsForDate = (window.bookingData || []).filter(b => b.date === d);
                    if (bookingsForDate.some(b => b.status === 'confirmed' || b.status === 'pending')) {
                        showAlert('วันที่นี้มีการจองแล้ว ไม่สามารถจองเพิ่มได้', 'error');
                        return;
                    }

                    handleDateSelection(d);
                });
            });
        }

        function prevMonth() {
            window.currentMonth--;
            if (window.currentMonth < 0) {
                window.currentMonth = 11;
                window.currentYear--;
            }
            // รีเฟรชข้อมูลสำหรับเดือนใหม่แล้วเรนเดอร์
            fetchBookings().then(() => renderCalendarTable(window.currentMonth, window.currentYear));
        }

        function nextMonth() {
            window.currentMonth++;
            if (window.currentMonth > 11) {
                window.currentMonth = 0;
                window.currentYear++;
            }
            // รีเฟรชข้อมูลสำหรับเดือนใหม่แล้วเรนเดอร์
            fetchBookings().then(() => renderCalendarTable(window.currentMonth, window.currentYear));
        }

        function populateLists() {
            const pendingList = document.getElementById('pendingList');
            const confirmedList = document.getElementById('confirmedList');
            pendingList.innerHTML = '';
            confirmedList.innerHTML = '';

            const pending = (window.bookingData || []).filter(b => b.status === 'pending');
            const confirmed = (window.bookingData || []).filter(b => b.status === 'confirmed');

            document.getElementById('pendingCount').textContent = pending.length;
            document.getElementById('confirmedCount').textContent = confirmed.length;

            pending.forEach(b => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center py-2';
                li.innerHTML = `<span>${b.name}</span><span class="badge-modern bg-warning text-dark">รอ</span>`;
                pendingList.appendChild(li);
            });
            
            confirmed.forEach(b => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center py-2';
                li.innerHTML = `<span>${b.name}</span><span class="badge-modern bg-success">ยืนยัน</span>`;
                confirmedList.appendChild(li);
            });
        }

        // ฟังก์ชันจัดการเมื่อเลือกวันที่
        function handleDateSelection(dateStr) {
            if (!IS_MEMBER) { // ถ้าไม่ใช่สมาชิก
                showLoginRequiredModal(); // แสดง Modal แจ้งเตือนให้เข้าสู่ระบบ
                return;
            }
            // ลบการเลือกเดิม
            document.querySelectorAll('#calendarTable td.selected-date').forEach(td => {
                td.classList.remove('selected-date');
            });

            // เลือกวันที่ใหม่
            const selectedCell = document.querySelector(`#calendarTable td[data-date="${dateStr}"]`);
            if (selectedCell) {
                selectedCell.classList.add('selected-date');
            }

            // ป้องกันการจองวันที่มีการยืนยันหรือรออนุมัติแล้ว
            const bookingsForDate = (window.bookingData || []).filter(b => b.date === dateStr);
            if (bookingsForDate.some(b => b.status === 'confirmed' || b.status === 'pending')) {
                showAlert('วันที่นี้มีการจองแล้ว ไม่สามารถจองเพิ่มได้', 'error');
                return;
            }

            selectedDate = dateStr;

            // แปลงวันที่เป็นรูปแบบที่อ่านง่าย
            const dateObj = parseYMD(dateStr);
            const thaiMonths = [
                "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน",
                "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม",
                "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
            ];

            const day = dateObj.getDate();
            const month = thaiMonths[dateObj.getMonth()];
            const year = dateObj.getFullYear() + 543;
            const thaiDate = `${day} ${month} ${year}`;

            // แสดงวันที่ที่เลือก
            document.getElementById('selectedDateDisplay').textContent = thaiDate;
            document.getElementById('dateSelectedBox').classList.remove('d-none');

            // เปิดใช้งานปุ่มดำเนินการต่อ
            document.getElementById('continueBtn').disabled = false;

            // แสดงข้อความแจ้งเตือน
            showAlert(`คุณได้เลือกวันที่ ${thaiDate} แล้ว กรุณาดำเนินการต่อ`, 'success');
        }

        // ฟังก์ชันล้างวันที่ที่เลือก
        function clearSelectedDate() {
            selectedDate = null;
            document.getElementById('dateSelectedBox').classList.add('d-none');
            document.getElementById('continueBtn').disabled = true;
            document.querySelectorAll('#calendarTable td.selected-date').forEach(td => {
                td.classList.remove('selected-date');
            });
        }

        // ฟังก์ชันแสดงส่วนฟอร์มและซ่อนปฏิทิน
        function showFormSection() {
            if (!IS_MEMBER) { // ถ้าไม่ใช่สมาชิก
                showLoginRequiredModal(); // แสดง Modal แจ้งเตือนให้เข้าสู่ระบบ
                return;
            }
            if (!selectedDate) {
                showAlert('กรุณาเลือกวันที่เข้าชมก่อน', 'error');
                return;
            }

            // อัปเดตขั้นตอน
            document.getElementById('step1').classList.remove('active');
            document.getElementById('step1').classList.add('completed');
            document.getElementById('stepLine1').classList.add('completed');
            document.getElementById('step2').classList.add('active');

            // แปลงวันที่เป็นรูปแบบที่อ่านง่ายสำหรับฟอร์ม
            const dateObj = new Date(selectedDate);
            const thaiMonths = [
                "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน",
                "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม",
                "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
            ];

            const day = dateObj.getDate();
            const month = thaiMonths[dateObj.getMonth()];
            const year = dateObj.getFullYear() + 543;
            const thaiDate = `${day} ${month} ${year}`;

            // แสดงวันที่ที่เลือกในฟอร์ม
            document.getElementById('formSelectedDate').textContent = thaiDate;
            // เก็บวันที่ลง hidden field เพื่อส่งไปยังเซิร์ฟเวอร์
            const selectedDateInput = document.getElementById('selected_date');
            if (selectedDateInput) selectedDateInput.value = selectedDate;

            // แสดงฟอร์มและซ่อนปฏิทิน
            document.getElementById('calendar-section').classList.add('d-none');
            document.getElementById('form-section').classList.remove('d-none');

            // 1. รีเซ็ตฟอร์มก่อน
            document.getElementById('bookingForm').reset();

            // 2. เติมข้อมูลสมาชิก (ถ้ามี)
            // Pre-fill member data if logged in
            if (IS_MEMBER && MEMBER_DATA) {
                document.getElementById('name').value = MEMBER_DATA.fullname || '';
                document.getElementById('email').value = MEMBER_DATA.email || '';
                document.getElementById('phone').value = MEMBER_DATA.phone || '';
            }
            toggleOrgSection();
            calculatePrice();
        }

        // ฟังก์ชันแสดงปฏิทินและซ่อนฟอร์ม
        function showCalendarSection() {
            // อัปเดตขั้นตอน
            document.getElementById('step1').classList.add('active');
            document.getElementById('step1').classList.remove('completed');
            document.getElementById('stepLine1').classList.remove('completed');
            document.getElementById('step2').classList.remove('active');

            // แสดงปฏิทินและซ่อนฟอร์ม
            document.getElementById('calendar-section').classList.remove('d-none');
            document.getElementById('form-section').classList.add('d-none');
        }

        // ฟังก์ชันสำหรับเพิ่ม/ลดจำนวนผู้เข้าชม
        function changeVisitorCount(change) {
            const input = document.getElementById('visitor_count');
            let value = parseInt(input.value) + change;

            if (value < 1) value = 1;

            input.value = value;
            calculatePrice();
        }

        // ฟังก์ชันสลับแสดงส่วนอัปโหลดไฟล์สำหรับองค์กร
        function toggleOrgSection() {
            const bookingType = document.getElementById('booking_type').value;
            const orgSection = document.getElementById('org_upload_section');
            const fileInput = document.getElementById('document');

            if (bookingType === 'organization') {
                orgSection.classList.remove('d-none');
                fileInput.required = true;
            } else {
                orgSection.classList.add('d-none');
                fileInput.required = false;
                document.getElementById('file-name').innerHTML = '<span class="badge-modern bg-primary"><i class="fas fa-file me-1"></i>ยังไม่มีไฟล์ที่เลือก</span>';
            }
        }

        // ฟังก์ชันคำนวณราคา
        function calculatePrice() {
            const count = parseInt(document.getElementById('visitor_count').value) || 0;
            const pricePerPerson = 150;

            const total = count * pricePerPerson;
            const deposit = total * 0.3;
            const balance = total - deposit;

            // แสดงจำนวนผู้เข้าชมในสรุป
            document.getElementById('display_visitor_count').textContent = count.toLocaleString();

            // แสดงราคาในสรุป
            document.getElementById('display_total').textContent = total.toLocaleString();
            document.getElementById('display_deposit').textContent = deposit.toLocaleString();
            document.getElementById('display_balance').textContent = balance.toLocaleString();
        }

        // ฟังก์ชันแสดงชื่อไฟล์ที่เลือก
        function displayFileName() {
            const fileInput = document.getElementById('document');
            const fileNameDisplay = document.getElementById('file-name');

            if (fileInput.files.length > 0) {
                fileNameDisplay.innerHTML = `<span class="badge-modern bg-success"><i class="fas fa-file me-1"></i>${fileInput.files[0].name}</span>`;
            } else {
                fileNameDisplay.innerHTML = '<span class="badge-modern bg-primary"><i class="fas fa-file me-1"></i>ยังไม่มีไฟล์ที่เลือก</span>';
            }
        }

        // ฟังก์ชันแสดง Alert
        function showAlert(message, type = 'success') {
            const alertDiv = document.getElementById('formAlert');

            if (type === 'success') {
                alertDiv.className = 'alert-modern alert-success-modern';
                alertDiv.innerHTML = `<i class="fas fa-check-circle me-2"></i>${message}`;
            } else {
                alertDiv.className = 'alert-modern alert-error-modern';
                alertDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>${message}`;
            }

            alertDiv.classList.remove('d-none');

            // ซ่อน Alert หลังจาก 5 วินาที
            setTimeout(() => {
                alertDiv.classList.add('d-none');
            }, 5000);
        }

        // ฟังก์ชันตรวจสอบความถูกต้องของฟอร์ม
        function validateForm() {
            const form = document.getElementById('bookingForm');
            const fields = form.querySelectorAll('[required]');
            let isValid = true;

            // ลบ class invalid ทั้งหมดก่อน
            fields.forEach(field => {
                field.classList.remove('is-invalid');
            });

            // ตรวจสอบแต่ละฟิลด์
            fields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else if (field.type === 'email' && !isValidEmail(field.value)) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else if (field.id === 'phone' && !isValidPhone(field.value)) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });

            // ตรวจสอบเวลาเข้าชม
            const bookingTime = document.getElementById('booking_time').value;
            if (!bookingTime) {
                document.getElementById('booking_time').classList.add('is-invalid');
                showAlert('กรุณาเลือกเวลาเข้าชม', 'error');
                isValid = false;
            }

            return isValid;
        }

        // ฟังก์ชันตรวจสอบอีเมล
        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // ฟังก์ชันตรวจสอบเบอร์โทรศัพท์
        function isValidPhone(phone) {
            const re = /^[0-9]{10}$/;
            return re.test(phone);
        }

        // ฟังก์ชันส่งแบบฟอร์ม (ส่งจริงไปยัง saveBooking.php)
        function submitForm() {
            // ตรวจสอบความถูกต้องของฟอร์ม
            if (!validateForm()) {
                showAlert('กรุณากรอกข้อมูลในฟิลด์ที่จำเป็นให้ครบถ้วนและถูกต้อง', 'error');
                return;
            }

            // ตรวจสอบว่าเลือกวันที่แล้วหรือยัง
            if (!selectedDate) {
                showAlert('กรุณาเลือกวันที่เข้าชมก่อน', 'error');
                showCalendarSection();
                return;
            }

            const submitButton = document.getElementById('submitButton');
            const buttonText = document.getElementById('buttonText');
            const buttonLoading = document.getElementById('buttonLoading');

            submitButton.disabled = true;
            buttonText.classList.add('d-none');
            buttonLoading.classList.remove('d-none');

            // เตรียม FormData (รองรับไฟล์)
            const formEl = document.getElementById('bookingForm');
            const fd = new FormData(formEl);
            // แน่ใจว่าแนบวันที่ที่เลือก
            fd.set('selected_date', selectedDate);

            // คำนวณยอดถ้ายังไม่มีค่าจากฟอร์ม
            // เพิ่ม member_id เข้าไปใน FormData ถ้าผู้ใช้เป็นสมาชิก
            if (IS_MEMBER && MEMBER_ID_SESSION) {
                fd.set('member_id', MEMBER_ID_SESSION);
            }
            const count = parseInt(document.getElementById('visitor_count').value) || 1;
            const pricePerPerson = 150;
            const total = count * pricePerPerson;
            const deposit = Math.round(total * 0.3 * 100) / 100;
            const balance = Math.round((total - deposit) * 100) / 100;
            fd.set('price_total', total);
            fd.set('deposit_amount', deposit);
            fd.set('balance_amount', balance);

            fetch('saveBooking.php', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(json => {
                if (json && (json.status === 'success' || json.status === 'partial')) {
                    const code = json.booking_code || json.booking_id || '';
                    document.getElementById('modalTitle').textContent = 'ส่งคำขอจองเรียบร้อยแล้ว';
                    
                    let msg = `
                        <div class="text-start">
                            <p>ระบบได้รับข้อมูลการจองของคุณแล้ว ขณะนี้อยู่ระหว่าง <strong>รอเจ้าหน้าที่ตรวจสอบวันเวลา</strong> ที่ท่านเลือก</p>
                            <h6 class="fw-bold mt-3">ขั้นตอนถัดไป:</h6>
                            <ul class="ps-3">
                                <li>รอรับอีเมลยืนยันจากเจ้าหน้าที่</li>
                                <li>ชำระเงินผ่าน <strong>QR Code พร้อมเพย์</strong> ที่แนบไปในอีเมล</li>
                                <li>ส่งหลักฐานการโอนเงิน (Slip) กลับมาทางอีเมล</li>
                            </ul>
                            <p class="mb-0 text-muted small">*เมื่อเจ้าหน้าที่ตรวจสอบยอดเงินแล้ว จะดำเนินการยืนยันการจองให้ทันทีครับ</p>
                        </div>
                    `;

                    if (code) {
                        msg += `
                            <div class="mt-4 p-3 bg-light rounded border text-center">
                                <small class="text-secondary d-block">รหัสอ้างอิงการจองของคุณ</small>
                                <span style="font-size: 1.5rem; color: #059669; font-weight: bold;">${code}</span>
                            </div>
                        `;
                    }

                    if (json.sendEmail_dispatched !== undefined) {
                        msg += `<br><small class="text-muted d-block mt-2 text-center">สถานะการส่งเมล: ${json.sendEmail_dispatched}</small>`;
                    }

                    document.getElementById('modalMessage').innerHTML = msg;
                    const statusModalEl = document.getElementById('statusModal');
                    const modal = new bootstrap.Modal(statusModalEl);

                    // เพิ่ม Event Listener: เมื่อ Modal ปิด ให้รีโหลดหน้า
                    statusModalEl.addEventListener('hidden.bs.modal', function () {
                        location.reload();
                    }, { once: true });

                    modal.show();

                    // ไม่จำเป็นต้องรีเซ็ตฟอร์มหรือทำอย่างอื่นแล้ว เพราะหน้าจะรีโหลด
                }
            })
            .catch(err => {
                console.error('submitForm error:', err);
                document.getElementById('errorModalTitle').textContent = 'เกิดข้อผิดพลาดระหว่างส่งข้อมูล';
                document.getElementById('errorModalMessage').textContent = String(err);
                const errModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errModal.show();
            })
            .finally(() => {
                submitButton.disabled = false;
                buttonText.classList.remove('d-none');
                buttonLoading.classList.add('d-none');
            });
        }

        // เริ่มต้นเมื่อโหลดหน้าเว็บ
        document.addEventListener('DOMContentLoaded', function() {
            // เริ่มต้นปฏิทิน
            initCalendar();

            // คำนวณราคาเริ่มต้น
            calculatePrice();
        });
    </script>

    <?php include 'footer.php'; ?>
</body>

</html>