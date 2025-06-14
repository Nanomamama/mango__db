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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="preload" href="https://unpkg.com/boxicons@2.1.4/fonts/boxicons.woff2" as="font" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet">
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
        --green-color: #016A70;
        --white-color: #fff;
        --Primary: #4e73df;
        --Success: #1cc88a;
        --Info: #36b9cc;
        --Warning: #f6c23e;
        --Danger: #e74a3b;
        --Secondary: #858796;
        --Light: #f8f9fc;
        --Dark: #5a5c69;
        --Darkss:#000;
    }

    .container h2 {
        margin-top: 2rem;
        font-weight: 600;
        color: var(--Darkss);
    }
    .container h4 {
        font-size: 18px;
    }

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

        <!-- ปุ่มแสดง Modal -->
        <button type="button" class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#howtoModal">
            ขั้นตอนการจอง →
        </button>

        <div id="calendar" class="calendar-container"></div>

        <!-- Modal แจ้งเตือนจองสำเร็จ -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-center">
                <div class="modal-body py-4">
                    <div class="mb-3">
                    <span style="font-size:2.5rem;color:#1cc88a;">
                        <i class="bi bi-check-circle-fill"></i>
                    </span>
                    </div>
                    <h5 class="mb-2" id="successModalLabel">จองสำเร็จ!</h5>
                    <p>กรุณารอการอนุมัติ</p>
                </div>
                </div>
            </div>
        </div>

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
                                <input type="text" class="form-control" id="visit_time" name="visit_time" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">จำนวนผู้เข้าชม</label>
                                <input type="number" class="form-control" name="number_of_people" required min="1">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">หมายเลขโทรศัพท์</label>
                                <input type="tel" class="form-control" name="phone_number" pattern="[0-9]{10}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">แนบเอกสาร (ถ้ามี)</label>
                                <input type="file" class="form-control" name="document">
                            </div>
                            <div class="mb-3" id="qrcode-section" style="display:none;">
                                <label class="form-label">QR พร้อมเพย์ (ชำระค่ามัดจำ 30%) <br>บัญชีธนาคาร นายหนึ่งเดียว
                                    เทียกสีบุญ</label>
                                <div id="qrcode"></div>
                                <div id="total-amount" class="mt-2"></div>
                                <div id="remain-amount" class="mt-2"></div>
                                <div id="deposit-amount" class="mt-2 text-primary"></div>
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

        <!-- Modal ขั้นตอนการจอง -->
        <div class="modal fade" id="howtoModal" tabindex="-1" aria-labelledby="howtoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="howtoModalLabel">ขั้นตอนการจองเข้าชมสวน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                </div>
                <div class="modal-body text-start">
                    <ol>
                    <li>เลือกวันที่ต้องการจองในปฏิทิน</li>
                    <li>กรอกข้อมูลการจองให้ครบถ้วน</li>
                    <li>แนบเอกสาร และ สลิป </li>
                    <li>ชำระค่ามัดจำ 30% ผ่าน QR พร้อมเพย์</li>
                    <li>รอการอนุมัติจากเจ้าหน้าที่</li>
                    </ol>
                    <div class="alert alert-info mt-3 mb-2">
                    กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนยืนยันการจอง
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-3">
                    <span>
                        <span style="display:inline-block;width:18px;height:18px;background:#dc3545;border-radius:20px;vertical-align:middle;"></span>
                        <span class="ms-1">สีแดง = วันไม่ว่าง</span>
                    </span>
                    <span>
                        <span style="display:inline-block;width:18px;height:18px;background:#198754;border-radius:20px;vertical-align:middle;"></span>
                        <span class="ms-1">สีเขียว = วันว่าง</span>
                    </span>
                    <span>
                        <span style="display:inline-block;width:18px;height:18px;background:#0dcaf0;border-radius:20px;vertical-align:middle;"></span>
                        <span class="ms-1">สีฟ้า = มีคนจองแล้ว</span>
                    </span>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        $(document).ready(function() {
            // รีเซ็ต QR ทุกครั้งที่เปิด modal
            $('#bookingModal').on('show.bs.modal', function() {
                $('#qrcode-section').hide();
                $('#qrcode').empty();
                $('#deposit-amount').empty();
                $('input[name="number_of_people"]').val('');
            });

            // สร้าง QR พร้อมเพย์ เมื่อกรอกจำนวนผู้เข้าชม
            $('input[name="number_of_people"]').on('input', function() {
                let people = parseInt($(this).val());
                if (isNaN(people) || people < 1) {
                    $('#qrcode-section').hide();
                    $('#qrcode').empty();
                    $('#deposit-amount').empty();
                    $('#total-amount').empty();
                    $('#remain-amount').empty();
                    return;
                }

                let total = people * 150;
                let deposit = Math.ceil(total * 0.3);
                let remain = total - deposit;
                let promptpayId = "0651078576";

                // วิธีที่ 1: ใช้ PromptPayQR ถ้าโหลดสำเร็จ
                if (typeof PromptPayQR !== 'undefined') {
                    let qrData = PromptPayQR.generate({
                        mobileNumber: promptpayId,
                        amount: deposit
                    });
                    $('#qrcode').empty();
                    new QRCode(document.getElementById("qrcode"), {
                        text: qrData,
                        width: 180,
                        height: 180
                    });
                }
                // วิธีที่ 2: fallback เป็นรูปภาพจาก promptpay.io
                else {
                    $('#qrcode').empty();
                    let depositFixed = deposit.toFixed(1); // สำคัญ!
                    let qrImg = $('<img>').attr('src',
                        `https://promptpay.io/${promptpayId}/${depositFixed}.png`).css({
                        width: 180,
                        height: 180
                    });
                    $('#qrcode').append(qrImg);
                }

                $('#deposit-amount').html(`ยอดค่ามัดจำ 30% = <b>${deposit.toLocaleString()} บาท</b>`);
                $('#total-amount').html(`ยอดรวมทั้งหมด = <b>${total.toLocaleString()} บาท</b>`);
                $('#remain-amount').html(`ยอดคงเหลือชำระวันเข้าชม = <b>${remain.toLocaleString()} บาท</b>`);
                $('#qrcode-section').show();
            });
        });
    </script>

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

            flatpickr("#visit_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i", // 24 ชั่วโมง
                time_24hr: true,
                minTime: "08:00",
                maxTime: "17:30"
            });
        });
    </script>

    <?php if (isset($_GET['success'])): ?>

    <script>
        $(document).ready(function(){
            var modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
            setTimeout(function(){
                modal.hide();
            }, 3000);
        });

    </script>

    <?php endif; ?>

    <script>
        $(document).ready(function(){
            var howtoModal = new bootstrap.Modal(document.getElementById('howtoModal'));
            howtoModal.show();
        });
    </script>

</body>
</html>