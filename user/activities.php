<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กิจกรรมและการจองวันเข้าชม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@3.2.0/dist/fullcalendar.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            height: 100%;
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
<br>
<br>
<div class="container py-5">
    <!-- ปฏิทิน -->
    <h2 class="mb-4">เลือกวันที่ต้องการจองเข้าชมสวนมะม่วงลุงเผือก</h2>
    <div id="calendar" class="calendar-container"></div>

    <!-- Modal สำหรับกรอกข้อมูลการจอง -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">กรอกข้อมูลการจอง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="process_booking.php">
                        <div class="mb-3">
                            <label for="user_name" class="form-label">ชื่อผู้จอง</label>
                            <input type="text" class="form-control" id="user_name" name="user_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">อีเมล์</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">หมายเลขโทรศัพท์</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="number_of_people" class="form-label">จำนวนผู้เข้าชม</label>
                            <input type="number" class="form-control" id="number_of_people" name="number_of_people" required>
                        </div>
                        <div class="mb-3">
                            <label for="visit_type" class="form-label">ประเภทการเข้าชม</label>
                            <select class="form-select" id="visit_type" name="visit_type" required>
                                <option value="ชมสวนมะม่วง">ชมสวนมะม่วง</option>
                                <option value="ชมการแปรรูปมะม่วง">ชมการแปรรูปมะม่วง</option>
                                <option value="ชมการผลิตมะม่วง">ชมการผลิตมะม่วง</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">หมายเหตุเพิ่มเติม</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                        <input type="hidden" id="selected_date" name="selected_date">
                        <button type="submit" class="btn btn-primary">จอง</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5">

</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.2.0/dist/fullcalendar.min.js"></script>

<script>
    $(document).ready(function() {
        // กำหนดค่าให้ FullCalendar
        $('#calendar').fullCalendar({
            selectable: true,
            selectHelper: true,
            select: function(startDate, endDate) {
                var selectedDate = moment(startDate).format('YYYY-MM-DD');
                // กำหนดค่าของวันที่เลือกใน hidden input
                $('#selected_date').val(selectedDate);
                // เปิด Modal เมื่อเลือกวัน
                $('#bookingModal').modal('show');
            },
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
