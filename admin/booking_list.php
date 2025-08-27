<?php
require_once 'auth.php';
require_once 'db.php';

// อัปเดตสถานะการดูเมื่อโหลดหน้านี้
// if (!isset($_SESSION['viewed_updated'])) {
//     $conn->query("UPDATE bookings SET viewed = 1 WHERE viewed = 0");
//     $_SESSION['viewed_updated'] = true;
// }

// อัปเดตสถานะการดูเมื่อโหลดหน้านี้
$conn->query("UPDATE bookings SET viewed = 1 WHERE viewed = 0");

// ดึงข้อมูลจองจากฐานข้อมูล
$bookings = [];
$result = $conn->query("SELECT * FROM bookings ORDER BY date ASC");
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
// แยกข้อมูลตามสถานะ
$approved = array_filter($bookings, fn($b) => $b['status'] === 'อนุมัติแล้ว');
$rejected = array_filter($bookings, fn($b) => $b['status'] === 'ถูกปฏิเสธ');
$pending = array_filter($bookings, fn($b) => $b['status'] === 'รออนุมัติ');
?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางรายการจอง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
                * {
            font-family: "Kanit", sans-serif;
        }
        body{
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="d-flex">
        <div class="container mt-5" style="margin-left: 250px; flex: 1;">
        <h2 class="mb-4">📅 ตารางรายการจองวันเข้าชมสวนมะม่วงลุงเผือก</h2>
        <ul class="nav nav-tabs mb-3" id="bookingTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                    ทั้งหมด <span class="badge bg-secondary"><?= count($bookings) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                    รออนุมัติ <span class="badge bg-warning"><?= count($pending) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                    อนุมัติแล้ว <span class="badge bg-success"><?= count($approved) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
                    ถูกปฏิเสธ <span class="badge bg-danger"><?= count($rejected) ?></span>
                </button>
            </li>
        </ul>
        <div class="tab-content" id="bookingTabContent">
            <!-- แท็บทั้งหมด -->
            <div class="tab-pane fade show active" id="all" role="tabpanel">
                <?php $bookings_show = $bookings; include 'booking_table.php'; ?>
            </div>
            <!-- แท็บยังไม่อนุมัติ'); -->
            <div class="tab-pane fade" id="pending" role="tabpanel">
                <?php $bookings_show = $pending; include 'booking_table.php'; ?>
            </div>
            <!-- แท็บอนุมัติแล้ว -->
            <div class="tab-pane fade" id="approved" role="tabpanel">
                <?php $bookings_show = $approved; include 'booking_table.php'; ?>
            </div>
            <!-- แท็บถูกปฏิเสธ -->
            <div class="tab-pane fade" id="rejected" role="tabpanel">
                <?php $bookings_show = $rejected; include 'booking_table.php'; ?>
            </div>
        </div> <!-- ปิด .tab-content -->

    </div>

    <script>
        function changeStatus(id, newStatus) {
            if (!confirm('คุณต้องการเปลี่ยนสถานะเป็น "' + newStatus + '" ใช่หรือไม่?')) return;
            fetch('update_booking_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id,
                        status: newStatus
                    })
                })
                .then(res => res.json())
                .then(data => { // <-- แก้ไขตรงนี้
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('เกิดข้อผิดพลาดในการอัปเดตสถานะ');
                    }
                });
        }

        function deleteBooking(id) {
            if (!confirm('คุณต้องการลบการจองนี้ใช่หรือไม่?')) return;
            fetch('delete_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('เกิดข้อผิดพลาดในการลบการจอง');
                    }
                });
        }

        // ฟังก์ชันสำหรับแสดงรายละเอียดการจองในโมดัล
        document.addEventListener('DOMContentLoaded', function() {
            var detailModal = document.getElementById('detailModal');
            detailModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                if (!button) return;
                var booking = JSON.parse(button.getAttribute('data-booking'));
                var body = detailModal.querySelector('#modalDetailBody');
                if (!body) return;
                body.innerHTML = `
                    <tr><th>ชื่อคณะ</th><td>${booking.name || '-'}</td></tr>
                    <tr><th>วันที่จอง</th><td>${booking.date || '-'}</td></tr>
                    <tr><th>เวลา</th><td>${booking.time || '-'}</td></tr>
                    <tr><th>จำนวนผู้เข้าชม</th><td>${booking.people || '-'}</td></tr>
                    <tr><th>สถานะ</th><td>${booking.status || '-'}</td></tr>
                    <tr><th>ยอดรวม</th><td>${Number(booking.total_amount).toLocaleString()} บาท</td></tr>
                    <tr><th>ยอดมัดจำ</th><td>${Number(booking.deposit_amount).toLocaleString()} บาท</td></tr>
                    <tr><th>ยอดคงเหลือ</th><td>${Number(booking.remain_amount).toLocaleString()} บาท</td></tr>
                    <tr><th>เบอร์โทร</th><td>${booking.phone || '-'}</td></tr>
                    <tr><th>เอกสาร</th><td>${booking.doc ? `<a href="../uploads/${booking.doc}" target="_blank">ดูไฟล์</a>` : '-'}</td></tr>
                    <tr><th>สลิป</th><td>${booking.slip ? `<a href="../uploads/${booking.slip}" target="_blank">ดูไฟล์</a>` : '-'}</td></tr>
                `;
            });
        });
    </script>

    <!-- booking_list.php -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel">รายละเอียดการจอง</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tbody id="modalDetailBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>

</html>