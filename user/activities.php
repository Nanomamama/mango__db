<?php
session_start();
require_once '../admin/db.php';

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
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กิจกรรมและการจองวันเข้าชม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@3.2.0/dist/fullcalendar.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/locale/th.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.2.0/dist/fullcalendar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
        }

        .calendar-container {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <br>
        <br>
        <h2 class="mb-4">เลือกวันที่ต้องการจองเข้าชมสวนมะม่วงลุงเผือก</h2>
        <div id="calendar" class="calendar-container"></div>

        <!-- Modal สำหรับกรอกข้อมูลการจอง -->
        <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">กรอกข้อมูลการจอง</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="process_booking.php" enctype="multipart/form-data">
                            <input type="hidden" id="booking_date" name="booking_date">
                            <div class="mb-3">
                                <label class="form-label">ชื่อคณะ</label>
                                <input type="text" class="form-control" name="group_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">เวลาเข้าชม</label>
                                <input type="time" class="form-control" name="visit_time" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">จำนวนผู้เข้าชม</label>
                                <input type="number" class="form-control" name="number_of_people" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">หมายเลขโทรศัพท์</label>
                                <input type="tel" class="form-control" name="phone_number" pattern="[0-9]{10}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">แนบเอกสาร (ถ้ามี)</label>
                                <input type="file" class="form-control" name="document">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">แนบสลิป</label>
                                <input type="file" class="form-control" name="slip">
                            </div>
                            <button type="submit" class="btn btn-success">จอง</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">จองสำเร็จ! กรุณารอการอนุมัติ</div>
        <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger">เกิดข้อผิดพลาดในการจอง</div>
        <?php endif; ?>
    </div>

<?php include 'footer.php'; ?>


    <script>
        $(document).ready(function() {
            moment.locale('th');
            var calendarDates = <?php echo json_encode($dates); ?>;
            var approvedDates = <?php echo json_encode($approved); ?>;
            var events = calendarDates.map(function(d) {
                if (approvedDates.includes(d.date)) {
                    return {
                        title: 'จองแล้ว',
                        start: d.date,
                        color: '#2196f3',
                        allDay: true
                    };
                }
                return {
                    title: d.status === 'available' ? 'ว่าง' : 'ไม่ว่าง',
                    start: d.date,
                    color: d.status === 'available' ? 'green' : 'red',
                    allDay: true
                };
            });

            $('#calendar').fullCalendar({
                locale: 'th',
                selectable: true,
                selectHelper: true,
                dayRender: function(date, cell) {
                    var found = calendarDates.find(d => d.date === date.format('YYYY-MM-DD'));
                    if (approvedDates.includes(date.format('YYYY-MM-DD'))) {
                        cell.css('background-color', '#bbdefb');
                    } else if (found && found.status === 'unavailable') {
                        cell.css('background-color', '#ffd6d6');
                    }
                },
                select: function(startDate) {
                    var selectedDate = moment(startDate).format('YYYY-MM-DD');
                    var found = calendarDates.find(d => d.date === selectedDate);
                    if (approvedDates.includes(selectedDate)) {
                        alert("วันนี้มีผู้จองแล้ว กรุณาเลือกวันอื่น");
                    } else if (found && found.status === 'unavailable') {
                        alert("วันที่เลือกนี้ไม่ว่าง กรุณาเลือกวันอื่น");
                    } else {
                        $('#booking_date').val(selectedDate);
                        $('#bookingModal').modal('show');
                    }
                },
                events: events
            });
        });
    </script>

</body>

</html>