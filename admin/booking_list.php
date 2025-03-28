<?php
// ตัวอย่างข้อมูลจำลองสำหรับการจอง
$bookings = [
    [
        'id' => 1,
        'name' => 'คณะวิทยาศาสตร์',
        'date' => '2025-04-01',
        'time' => '10:00 AM',
        'people' => 30,
        'doc' => 'confirm_doc_1.pdf',
        'slip' => 'deposit_slip_1.jpg',
        'status' => 'รออนุมัติ'
    ],
    [
        'id' => 2,
        'name' => 'คณะวิศวกรรมศาสตร์',
        'date' => '2025-04-02',
        'time' => '02:00 PM',
        'people' => 50,
        'doc' => 'confirm_doc_2.pdf',
        'slip' => 'deposit_slip_2.jpg',
        'status' => 'อนุมัติแล้ว'
    ],
    [
        'id' => 3,
        'name' => 'คณะเศรษฐศาสตร์',
        'date' => '2025-04-03',
        'time' => '09:00 AM',
        'people' => 20,
        'doc' => 'confirm_doc_3.pdf',
        'slip' => 'deposit_slip_3.jpg',
        'status' => 'ถูกปฏิเสธ'
    ],
    [
        'id' => 4,
        'name' => 'คณะมนุษยศาสตร์',
        'date' => '2025-04-04',
        'time' => '01:00 PM',
        'people' => 15,
        'doc' => 'confirm_doc_4.pdf',
        'slip' => 'deposit_slip_4.jpg',
        'status' => 'รออนุมัติ'
    ],
    [
        'id' => 5,
        'name' => 'คณะสังคมศาสตร์',
        'date' => '2025-04-05',
        'time' => '11:00 AM',
        'people' => 40,
        'doc' => 'confirm_doc_5.pdf',
        'slip' => 'deposit_slip_5.jpg',
        'status' => 'อนุมัติแล้ว'
    ]
];
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
            <h2 class="mb-4">📋 ตารางรายการจอง</h2>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="bookingTabs">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all">ทั้งหมด</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pending">รออนุมัติ</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#approved">อนุมัติแล้ว</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rejected">ถูกปฏิเสธ</button>
                </li>
            </ul>

            <div class="tab-content mt-3">
                <?php
                $tabs = ["all" => "ทั้งหมด", "approved" => "อนุมัติแล้ว", "rejected" => "ถูกปฏิเสธ", "pending" => "รออนุมัติ"];
                foreach ($tabs as $key => $title) {
                    echo "<div class='tab-pane fade" . ($key == 'all' ? " show active" : "") . "' id='$key'>";
                    echo "<table class='table table-bordered table-hover text-center'>";
                    echo "<thead class='table-dark'>
                            <tr>
                                <th>#</th>
                                <th>ชื่อคณะ</th>
                                <th>วันที่จอง</th>
                                <th>เวลาเข้าชม</th>
                                <th>จำนวนคน</th>
                                <th>เอกสารยืนยัน</th>
                                <th>สลิปค่ามัดจำ</th>
                                <th>สถานะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id='tbody-$key'></tbody>
                        </table></div>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fileModalLabel">ดูไฟล์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <iframe id="fileViewer" src="" width="100%" height="500px" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ข้อมูลเริ่มต้นจาก PHP
        let bookingsData = <?php echo json_encode($bookings); ?>;

        function showModal(fileUrl, title) {
            document.getElementById('fileViewer').src = fileUrl;
            document.getElementById('fileModalLabel').innerText = title;
            var modal = new bootstrap.Modal(document.getElementById('fileModal'));
            modal.show();
        }

        function renderTables() {
            const tabs = {
                'all': 'ทั้งหมด',
                'approved': 'อนุมัติแล้ว',
                'rejected': 'ถูกปฏิเสธ',
                'pending': 'รออนุมัติ'
            };

            Object.keys(tabs).forEach(tab => {
                const tbody = document.getElementById(`tbody-${tab}`);
                tbody.innerHTML = ''; // ล้างข้อมูลเก่า
                let i = 1;

                bookingsData.forEach(booking => {
                    if (tab === 'all' || 
                        (tab === 'approved' && booking.status === 'อนุมัติแล้ว') ||
                        (tab === 'rejected' && booking.status === 'ถูกปฏิเสธ') ||
                        (tab === 'pending' && booking.status === 'รออนุมัติ')) {
                        
                        const row = document.createElement('tr');
                        const statusColor = booking.status === 'อนุมัติแล้ว' ? 'success' : 
                                        (booking.status === 'ถูกปฏิเสธ' ? 'danger' : 'warning');

                        row.innerHTML = `
                            <td>${i}</td>
                            <td>${booking.name}</td>
                            <td>${booking.date}</td>
                            <td>${booking.time}</td>
                            <td>${booking.people}</td>
                            <td><button class='btn btn-primary btn-sm' onclick='showModal("uploads/${booking.doc}", "เอกสารยืนยัน")'>📂 ดูไฟล์</button></td>
                            <td><button class='btn btn-info btn-sm' onclick='showModal("uploads/${booking.slip}", "สลิปค่ามัดจำ")'>📂 ดูสลิป</button></td>
                            <td><span class='badge bg-${statusColor}'>${booking.status}</span></td>
                            <td>
                                <button class='btn btn-success btn-sm me-1' onclick='changeStatus(${booking.id}, "อนุมัติแล้ว")'>✔ อนุมัติ</button>
                                <button class='btn btn-danger btn-sm me-1' onclick='changeStatus(${booking.id}, "ถูกปฏิเสธ")'>❌ ปฏิเสธ</button>
                                <button class='btn btn-secondary btn-sm' onclick='deleteBooking(${booking.id})'>🗑 ลบ</button>
                            </td>
                        `;
                        tbody.appendChild(row);
                        i++;
                    }
                });
            });
        }

        function changeStatus(bookingId, newStatus) {
            if (confirm(`คุณต้องการเปลี่ยนสถานะการจองเป็น "${newStatus}" หรือไม่?`)) {
                const booking = bookingsData.find(b => b.id === bookingId);
                if (booking) {
                    booking.status = newStatus;
                    console.log(`Booking ID: ${bookingId} Status changed to: ${newStatus}`);
                    renderTables();
                    alert("สถานะการจองถูกอัปเดตแล้ว!");
                }
            }
        }

        function deleteBooking(bookingId) {
            if (confirm("คุณต้องการลบการจองนี้หรือไม่?")) {
                bookingsData = bookingsData.filter(b => b.id !== bookingId);
                console.log(`Booking ID: ${bookingId} has been deleted.`);
                renderTables();
                alert("การจองถูกลบแล้ว!");
            }
        }

        // รันครั้งแรกเมื่อโหลดหน้า
        document.addEventListener('DOMContentLoaded', renderTables);
    </script>
</body>

</html>