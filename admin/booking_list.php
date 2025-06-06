<?php
require_once 'auth.php';
require_once 'db.php';

// ดึงข้อมูลจองจากฐานข้อมูล
$bookings = [];
$result = $conn->query("SELECT * FROM bookings ORDER BY date ASC");
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

// แยกข้อมูลตามสถานะ
$approved = array_filter($bookings, fn($b) => $b['status'] === 'อนุมัติแล้ว');
$rejected = array_filter($bookings, fn($b) => $b['status'] === 'ถูกปฏิเสธ');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางรายการจอง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="d-flex">
        <div class="container mt-5" style="margin-left: 250px; flex: 1;">
        <h2 class="mb-4">📅 ตารางรายการจองวันเข้าชมสวนมะม่วงลุงเผือก</h2>
        <ul class="nav nav-tabs mb-3" id="bookingTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">ทั้งหมด</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">อนุมัติแล้ว</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">ถูกปฏิเสธ</button>
            </li>
        </ul>
        <div class="tab-content" id="bookingTabContent">
            <!-- แท็บทั้งหมด -->
            <div class="tab-pane fade show active" id="all" role="tabpanel">
                <?php $bookings_show = $bookings; include 'booking_table.php'; ?>
            </div>
            <!-- แท็บอนุมัติแล้ว -->
            <div class="tab-pane fade" id="approved" role="tabpanel">
                <?php $bookings_show = $approved; include 'booking_table.php'; ?>
            </div>
            <!-- แท็บถูกปฏิเสธ -->
            <div class="tab-pane fade" id="rejected" role="tabpanel">
                <?php $bookings_show = $rejected; include 'booking_table.php'; ?>
            </div>
        </div>
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
                .then(data => {
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
    </script>
</body>

</html>