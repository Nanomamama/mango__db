<?php
require_once 'auth.php';
require_once 'db.php';

// ดึงวันว่าง/ไม่ว่างจากฐานข้อมูล
$dates = [];
$res = $conn->query("SELECT date, status FROM calendar_dates");
while ($row = $res->fetch_assoc()) {
    $dates[] = $row;
}

// ดึงชื่อ admin จาก session
$admin_name = $_SESSION['admin_name'] ?? '';
$admin_email = $_SESSION['admin_email'] ?? '';

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>อัพเดทปฏิทินวันว่าง/ไม่ว่าง - ระบบจัดการ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #34495e;
            --secondary: #3f37c9;
            --light: #f8f9fa;
            --light-gray: #e9ecef;
            --dark: #2c3e50;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --border: #dee2e6;
        }


        * {
            font-family: "Kanit", sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: #f5f7fa;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            animation: fadeIn 0.6s ease-out;
        }

        .dashboard-header {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: white;
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            position: relative;
            overflow: hidden;
            z-index: 10;
            border-radius: 50px;
        }

        .dashboard-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .admin-profile:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .admin-profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .admin-profile span {
            font-weight: 500;
            color: white;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(-10px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.02);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes ripple {
            0% {
                transform: scale(0);
                opacity: 1;
            }

            100% {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* Header Card */
        .header-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            border: 1px solid var(--border);
            animation: slideIn 0.5s ease-out;
            position: relative;
            overflow: hidden;
        }

        .header-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background-color: var(--primary);
        }

        .header-title {
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1.8rem;
        }

        /* Button Styles */
        .btn-custom {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            min-width: 160px;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
            background-color: white;
            color: var(--dark);
        }

        .btn-custom::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(0, 0, 0, 0.1);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        .btn-custom:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-custom:active {
            transform: translateY(0);
        }

        .btn-available {
            border-color: var(--success);
            color: var(--success);
        }

        .btn-available:hover {
            background-color: rgba(39, 174, 96, 0.05);
        }

        .btn-unavailable {
            border-color: var(--danger);
            color: var(--danger);
        }

        .btn-unavailable:hover {
            background-color: rgba(231, 76, 60, 0.05);
        }

        .btn-clear {
            border-color: var(--warning);
            color: var(--warning);
        }

        .btn-clear:hover {
            background-color: rgba(243, 156, 18, 0.05);
        }

        .btn-week {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-week:hover {
            background-color: rgba(44, 62, 80, 0.05);
        }

        .btn-icon {
            font-size: 1.1rem;
            margin-right: 8px;
            transition: transform 0.3s ease;
        }

        .btn-custom:hover .btn-icon {
            transform: translateX(2px);
        }

        /* Calendar Container */
        .calendar-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            animation: fadeIn 0.8s ease-out 0.2s both;
            border: 1px solid var(--border);
        }

        #calendar a.fc-daygrid-day-number {
            text-decoration: none !important;
            color: #000 !important;
        }

        #calendar .fc-col-header-cell-cushion {
            text-decoration: none !important;
            color: #000 !important;
        }

        /* ป้องกันตอน hover */
        #calendar .fc-col-header-cell-cushion:hover {
            text-decoration: none !important;
            color: #000 !important;
        }
        .fc {
            --fc-border-color: var(--border);
            --fc-button-bg-color: white;
            --fc-button-border-color: var(--border);
            --fc-button-hover-bg-color: var(--light);
            --fc-button-hover-border-color: var(--border);
            --fc-button-active-bg-color: var(--light-gray);
            --fc-button-active-border-color: var(--border);
            --fc-today-bg-color: rgba(44, 62, 80, 0.05);
            --fc-event-bg-color: transparent;
            --fc-event-border-color: transparent;
        }

        .fc .fc-button {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            color: var(--dark);
            background-color: white;
        }

        .fc .fc-button:hover {
            background-color: var(--dark);
            transform: translateY(-1px);
        }

        .fc .fc-button:active {
            background-color: var(--dark);
        }

        .fc .fc-toolbar-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .fc-daygrid-day {
            transition: all 0.2s ease;
            border-radius: 6px;
            overflow: hidden;
        }

        .fc-daygrid-day:hover {
            background-color: var(--light);
        }

        .fc-day-today {
            background-color: rgba(44, 62, 80, 0.05) !important;
            border: 1px solid rgba(44, 62, 80, 0.1) !important;
        }

        .fc-event {
            border-radius: 4px;
            border: none;
            padding: 4px 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            background-color: transparent;
        }

        .fc-event:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .fc-event-available {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success);
            border-left: 3px solid var(--success);
        }

        .fc-event-unavailable {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger);
            border-left: 3px solid var(--danger);
        }

        .fc-highlight {
            background-color: rgba(44, 62, 80, 0.08) !important;
            border: 1px dashed rgba(44, 62, 80, 0.2) !important;
            border-radius: 6px;
        }

        /* Selected Dates */
        .selected-dates-box {
            background-color: var(--light);
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            border-left: 3px solid var(--primary);
            animation: slideIn 0.5s ease-out;
            border: 1px solid var(--border);
        }

        .date-badge {
            background-color: white;
            color: var(--dark);
            padding: 6px 12px;
            border-radius: 20px;
            margin: 4px;
            display: inline-block;
            font-size: 0.9em;
            font-weight: 500;
            animation: fadeIn 0.3s ease-out;
            border: 1px solid var(--border);
            transition: all 0.2s ease;
        }

        .date-badge:hover {
            background-color: var(--light-gray);
            transform: translateY(-1px);
        }

        .date-badge i {
            margin-left: 8px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
            font-size: 0.9em;
        }

        .date-badge i:hover {
            opacity: 1;
        }

        /* Status Indicators */
        .status-indicator {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            margin: 0 5px;
            background-color: white;
            border: 1px solid var(--border);
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }

        .status-available .status-dot {
            background-color: var(--success);
        }

        .status-unavailable .status-dot {
            background-color: var(--danger);
        }

        .status-selected .status-dot {
            background-color: var(--primary);
        }

        /* Stats Cards */
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 10px 0;
            /* border: 1px solid var(--border); */
            transition: all 0.3s ease;
            animation: fadeIn 0.6s ease-out;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .stats-card:hover {
            transform: translateY(-2px);

        }

        .stats-number {
            font-size: 2rem;
            font-weight: 600;
            margin: 8px 0;
            color: var(--dark);
        }

        .stats-label {
            font-size: 0.9rem;
            color: var(--secondary);
            font-weight: 500;
        }

        .stats-icon {
            margin-top: 10px;
            font-size: 1.5rem;
        }

        .stats-card-available .stats-number {
            color: var(--success);
        }

        .stats-card-unavailable .stats-number {
            color: var(--danger);
        }

        .stats-card-selected .stats-number {
            color: var(--primary);
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 14px 22px;
            border-radius: 8px;
            color: var(--dark);
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            animation: slideIn 0.3s ease-out, fadeOut 0.3s ease-out 2.7s forwards;
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: white;
            border: 1px solid var(--border);
            border-left: 4px solid;
        }

        .notification-success {
            border-left-color: var(--success);
        }

        .notification-error {
            border-left-color: var(--danger);
        }

        .notification-warning {
            border-left-color: var(--warning);
        }

        .notification-info {
            border-left-color: var(--primary);
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(100px);
            }
        }

        /* Subtle animations for interactions */
        .interaction-animation {
            transition: all 0.2s ease;
        }

        .interaction-animation:hover {
            transform: translateY(-1px);
        }

        /* Section divider */
        .section-divider {
            height: 1px;
            background-color: var(--border);
            margin: 25px 0;
            opacity: 0.5;
        }

        /* Calendar cell hover effect */
        .fc-daygrid-day-frame {
            transition: background-color 0.2s ease;
        }

        /* Button group spacing */
        .btn-group-spaced {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div id="notification" class="notification" style="display: none;">
            <i class="fas fa-info-circle"></i>
            <span id="notification-text"></span>
        </div>

        <!-- Header -->
        <header class="dashboard-header pb-4 mb-4">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h2 class="dashboard-title mb-0">อัพเดทปฏิทินวันว่าง/ไม่ว่าง</h2>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-2 mt-md-0">
                        <div class="admin-profile">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=random&color=fff" alt="Admin">
                            <span><?= htmlspecialchars($admin_name) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="header-card">
            <!-- <div class="row align-items-center mb-4">
                <div class="col-md-8">
                    <h1 class="header-title">อัพเดทปฏิทินวันว่าง/ไม่ว่าง</h1>
                    <p class="text-muted mb-0">จัดการวันที่ว่างและไม่ว่างสำหรับการจองคิว</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="d-flex justify-content-md-end gap-2 flex-wrap">
                        <div class="status-indicator status-available">
                            <div class="status-dot"></div>
                            <span>วันว่าง</span>
                        </div>
                        <div class="status-indicator status-unavailable">
                            <div class="status-dot"></div>
                            <span>วันไม่ว่าง</span>
                        </div>
                    </div>
                </div>
            </div> -->

            <div class="section-divider"></div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stats-card stats-card-available interaction-animation">
                        <div class="stats-number" id="available-count">0</div>
                        <div class="stats-label">วันว่างทั้งหมด</div>
                        <div class="stats-icon">
                            <i class="fas fa-calendar-check" style="color: var(--success);"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card stats-card-unavailable interaction-animation">
                        <div class="stats-number" id="unavailable-count">0</div>
                        <div class="stats-label">วันไม่ว่างทั้งหมด</div>
                        <div class="stats-icon">
                            <i class="fas fa-calendar-times" style="color: var(--danger);"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card stats-card-selected interaction-animation">
                        <div class="stats-number" id="selected-count">0</div>
                        <div class="stats-label">วันที่เลือกในปัจจุบัน</div>
                        <div class="stats-icon">
                            <i class="fas fa-calendar-star" style="color: var(--primary);"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-divider"></div>

            <h6 class="mb-3" style="font-weight: 600; color: var(--dark);">
                <i class="fas fa-tools me-2"></i>เครื่องมือจัดการวันที่
            </h6>

            <div class="btn-group-spaced mb-4">
                <button class="btn btn-custom btn-available interaction-animation" onclick="updateStatus('available')">
                    <i class="fas fa-calendar-check btn-icon"></i>อัพเดทเป็นวันว่าง
                </button>
                <button class="btn btn-custom btn-unavailable interaction-animation" onclick="updateStatus('unavailable')">
                    <i class="fas fa-calendar-times btn-icon"></i>อัพเดทเป็นวันไม่ว่าง
                </button>
                <button class="btn btn-custom btn-clear interaction-animation" onclick="clearSelectedDates()">
                    <i class="fas fa-trash-alt btn-icon"></i>ล้างวันที่เลือก
                </button>
                <button class="btn btn-custom btn-week interaction-animation" onclick="selectWeek()">
                    <i class="fas fa-calendar-week btn-icon"></i>เลือกทั้งสัปดาห์
                </button>
            </div>

            <div class="calendar-container">
                <div class="row">
                    <!-- ปฏิทินที่แสดงวันว่าง/ไม่ว่าง และวันที่ที่เลือก -->
                    <div class="calendar-1 col-md-8">
                        <div id="calendar"></div>
                    </div>

                    <div class="col-md-4">
                        <!-- รายการที่เลือก -->
                        <div id="selectedDatesContainer" class="selected-dates-box" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <strong style="font-weight: 600; color: var(--dark);">วันที่เลือก:</strong>
                                    <span class="badge ms-2" style="background-color: var(--primary); color: white; padding: 6px 12px; border-radius: 20px; font-weight: 500;">
                                        <span id="selectedCount">0</span> วันที่เลือก
                                    </span>
                                </div>
                                <button class="btn btn-sm" onclick="clearAllDates()" style="border: 1px solid var(--border); color: var(--dark);">
                                    <i class="fas fa-times me-1"></i>ล้างทั้งหมด
                                </button>
                            </div>
                            <div id="selectedDatesList" class="d-flex flex-wrap"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
        <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
        <script>
            let selectedDates = [];
            let calendar;
            const existingDates = <?php echo json_encode($dates); ?>;

            // Count stats
            let availableCount = 0;
            let unavailableCount = 0;

            existingDates.forEach(date => {
                if (date.status === 'available') availableCount++;
                if (date.status === 'unavailable') unavailableCount++;
            });

            document.getElementById('available-count').textContent = availableCount;
            document.getElementById('unavailable-count').textContent = unavailableCount;

            function showNotification(message, type = 'info') {
                const notification = document.getElementById('notification');
                const text = document.getElementById('notification-text');

                notification.className = `notification notification-${type}`;
                text.textContent = message;
                notification.style.display = 'flex';

                setTimeout(() => {
                    notification.style.display = 'none';
                }, 3000);
            }

            function updateSelectedDatesDisplay() {
                const container = document.getElementById('selectedDatesContainer');
                const list = document.getElementById('selectedDatesList');
                const count = document.getElementById('selectedCount');
                const selectedCountElement = document.getElementById('selected-count');

                if (selectedDates.length > 0) {
                    container.style.display = 'block';
                    count.textContent = selectedDates.length;
                    selectedCountElement.textContent = selectedDates.length;

                    list.innerHTML = '';
                    selectedDates.forEach((date, index) => {
                        const dateObj = new Date(date);
                        const formattedDate = dateObj.toLocaleDateString('th-TH', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });

                        const badge = document.createElement('span');
                        badge.className = 'date-badge interaction-animation';
                        badge.innerHTML = `${formattedDate} <i class="fas fa-times" onclick="removeDate('${date}', ${index})"></i>`;
                        badge.style.animationDelay = `${index * 0.05}s`;
                        list.appendChild(badge);
                    });
                } else {
                    container.style.display = 'none';
                    selectedCountElement.textContent = '0';
                }
            }

            function removeDate(date, index) {
                selectedDates.splice(index, 1);
                updateSelectedDatesDisplay();
                updateCalendarSelection();
                showNotification('ลบวันที่ออกจากรายการแล้ว', 'warning');
            }

            function clearAllDates() {
                selectedDates = [];
                updateSelectedDatesDisplay();
                updateCalendarSelection();
                showNotification('ล้างวันที่เลือกทั้งหมดแล้ว', 'warning');
            }

            function updateCalendarSelection() {
                // ลบ event ที่เป็น selection เก่าทั้งหมด
                calendar.getEvents().forEach(event => {
                    if (event.extendedProps?.isSelection) {
                        event.remove();
                    }
                });

                // เพิ่ม selection ใหม่
                selectedDates.forEach(date => {
                    calendar.addEvent({
                        start: date,
                        display: 'background',
                        color: 'rgba(56, 143, 229, 0.88)',
                        extendedProps: {
                            isSelection: true
                        },
                        allDay: true
                    });
                });
            }

            function selectWeek() {
                const today = new Date();
                const currentDate = calendar.getDate();
                const startOfWeek = new Date(currentDate);
                startOfWeek.setDate(currentDate.getDate() - currentDate.getDay());

                selectedDates = [];
                for (let i = 0; i < 7; i++) {
                    const date = new Date(startOfWeek);
                    date.setDate(startOfWeek.getDate() + i);
                    const dateStr = date.toISOString().slice(0, 10);
                    if (!selectedDates.includes(dateStr)) {
                        selectedDates.push(dateStr);
                    }
                }

                updateSelectedDatesDisplay();
                updateCalendarSelection();
                showNotification('เลือกทั้งสัปดาห์สำเร็จ', 'info');
            }

            document.addEventListener('DOMContentLoaded', function() {
                const calendarEl = document.getElementById('calendar');
                const events = existingDates.map(d => ({
                    title: d.status === 'available' ? 'ว่าง' : 'ไม่วาง',
                    start: d.date,
                    color: d.status === 'available' ? '#27ae60' : '#e74c3c',
                    allDay: true,
                    className: d.status === 'available' ? 'fc-event-available' : 'fc-event-unavailable',
                    extendedProps: {
                        status: d.status,
                        originalEvent: true
                    }
                }));

                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'th',
                    selectable: true,
                    selectMirror: true,
                    dayMaxEvents: 3,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,dayGridWeek,dayGridDay'
                    },
                    buttonText: {
                        today: 'วันนี้',
                        month: 'เดือน',
                        week: 'สัปดาห์',
                        day: 'วัน'
                    },
                    dayHeaderFormat: {
                        weekday: 'long'
                    },
                    firstDay: 0, // เริ่มจากวันอาทิตย์
                    select: function(info) {
                        // เลือกหลายวัน
                        let start = info.startStr;
                        let end = info.endStr;
                        let current = new Date(start);
                        let last = new Date(end);
                        last.setDate(last.getDate() - 1);

                        const newDates = [];
                        while (current <= last) {
                            const dateStr = current.toISOString().slice(0, 10);
                            if (!selectedDates.includes(dateStr)) {
                                newDates.push(dateStr);
                            }
                            current.setDate(current.getDate() + 1);
                        }

                        selectedDates = [...selectedDates, ...newDates];

                        updateSelectedDatesDisplay();
                        updateCalendarSelection();

                        if (newDates.length > 0) {
                            showNotification(`เลือก ${newDates.length} วันใหม่แล้ว`, 'info');
                        }
                    },
                    eventClick: function(info) {
                        const dateStr = info.event.startStr;
                        const status = info.event.extendedProps.status;

                        if (confirm(`ต้องการลบสถานะวันที่ ${dateStr} ใช่หรือไม่?`)) {
                            // ลบจากฐานข้อมูล
                            fetch('clear_update_date.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        dates: [dateStr]
                                    })
                                })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.success) {
                                        info.event.remove();
                                        // อัพเดทสถิติ
                                        if (status === 'available') {
                                            availableCount--;
                                            document.getElementById('available-count').textContent = availableCount;
                                        } else {
                                            unavailableCount--;
                                            document.getElementById('unavailable-count').textContent = unavailableCount;
                                        }
                                        showNotification('ลบสถานะวันที่สำเร็จ', 'info');
                                    }
                                });
                        }
                    },
                    events: events,
                    eventContent: function(arg) {
                        return {
                            html: `<div class="fc-event-title d-flex align-items-center">
                            <i class="fas ${arg.event.extendedProps.status === 'available' ? 'fa-check' : 'fa-times'} me-1"></i>
                            <span>${arg.event.title}</span>
                        </div>`
                        };
                    },
                    datesSet: function(info) {
                        // เมื่อเปลี่ยนเดือน/สัปดาห์
                        calendar.getEvents().forEach(event => {
                            if (event.extendedProps?.isSelection) {
                                event.setProp('display', 'background');
                            }
                        });
                    },
                    dayCellDidMount: function(arg) {
                        // เพิ่ม subtle hover effect
                        arg.el.addEventListener('mouseenter', function() {
                            this.style.backgroundColor = 'rgba(0,0,0,0.02)';
                        });
                        arg.el.addEventListener('mouseleave', function() {
                            this.style.backgroundColor = '';
                        });
                    }
                });

                calendar.render();
            });

            function updateStatus(status) {
                if (selectedDates.length === 0) {
                    showNotification('กรุณาเลือกวันที่ก่อนดำเนินการ', 'warning');
                    return;
                }

                const action = status === 'available' ? 'อัพเดทเป็นวันว่าง' : 'อัพเดทเป็นวันไม่ว่าง';
                const color = status === 'available' ? '#27ae60' : '#e74c3c';
                const icon = status === 'available' ? 'fa-calendar-check' : 'fa-calendar-times';

                // แสดงอนิเมชันก่อนยืนยัน
                const buttons = document.querySelectorAll('.btn-custom');
                buttons.forEach(btn => {
                    if (btn.classList.contains(status === 'available' ? 'btn-available' : 'btn-unavailable')) {
                        btn.style.animation = 'pulse 0.5s 2';
                        setTimeout(() => {
                            btn.style.animation = '';
                        }, 1000);
                    }
                });

                if (confirm(`ยืนยันที่จะ${action} ${selectedDates.length} วันที่เลือก?`)) {
                    fetch('update_calendar.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                dates: selectedDates,
                                status: status
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // อัพเดทปฏิทินทันที
                                selectedDates.forEach(dateStr => {
                                    // ลบ event เก่าหากมี
                                    calendar.getEvents().forEach(event => {
                                        if (event.startStr === dateStr && event.extendedProps?.originalEvent) {
                                            event.remove();
                                        }
                                    });

                                    // เพิ่ม event ใหม่
                                    calendar.addEvent({
                                        title: status === 'available' ? 'ว่าง' : 'ไม่ว่าง',
                                        start: dateStr,
                                        color: color,
                                        allDay: true,
                                        className: status === 'available' ? 'fc-event-available' : 'fc-event-unavailable',
                                        extendedProps: {
                                            status: status,
                                            originalEvent: true
                                        }
                                    });
                                });

                                // อัพเดทสถิติ
                                if (status === 'available') {
                                    availableCount += selectedDates.length;
                                    document.getElementById('available-count').textContent = availableCount;
                                } else {
                                    unavailableCount += selectedDates.length;
                                    document.getElementById('unavailable-count').textContent = unavailableCount;
                                }

                                showNotification(`${action} ${selectedDates.length} วันสำเร็จ`, 'success');
                                selectedDates = [];
                                updateSelectedDatesDisplay();
                                updateCalendarSelection();
                            } else {
                                showNotification('เกิดข้อผิดพลาดในการอัพเดท', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                        });
                }
            }

            function clearSelectedDates() {
                if (selectedDates.length === 0) {
                    showNotification('ไม่มีวันที่ที่เลือกไว้', 'warning');
                    return;
                }

                if (!confirm(`ยืนยันการลบสถานะ ${selectedDates.length} วันที่เลือก?`)) return;

                fetch('clear_update_date.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            dates: selectedDates
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // ลบจากปฏิทิน
                            selectedDates.forEach(dateStr => {
                                calendar.getEvents().forEach(event => {
                                    if (event.startStr === dateStr && event.extendedProps?.originalEvent) {
                                        // อัพเดทสถิติ
                                        if (event.extendedProps.status === 'available') {
                                            availableCount--;
                                            document.getElementById('available-count').textContent = availableCount;
                                        } else {
                                            unavailableCount--;
                                            document.getElementById('unavailable-count').textContent = unavailableCount;
                                        }
                                        event.remove();
                                    }
                                });
                            });

                            showNotification(`ลบสถานะ ${selectedDates.length} วันสำเร็จ`, 'success');
                            selectedDates = [];
                            updateSelectedDatesDisplay();
                            updateCalendarSelection();
                        } else {
                            showNotification('เกิดข้อผิดพลาดในการลบ', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                    });
            }
        </script>
</body>

</html>