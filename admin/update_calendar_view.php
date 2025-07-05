<?php
// filepath: c:\xampp\htdocs\mango\admin\update_calendar_view.php
require_once 'auth.php';
require_once 'db.php';

// ดึงวันว่าง/ไม่ว่างจากฐานข้อมูล
$dates = [];
$res = $conn->query("SELECT date, status FROM calendar_dates");
while ($row = $res->fetch_assoc()) {
    $dates[] = $row;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>อัพเดทปฏิทินวันว่าง/ไม่ว่าง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
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
            <h2 class="mb-4">อัพเดทปฏิทินวันว่าง/ไม่ว่าง</h2>
            <div id="calendar"></div>
            <div class="mt-3">
                <button class="btn btn-success" onclick="updateStatus('available')">อัพเดทเป็นวันว่าง</button>
                <button class="btn btn-danger" onclick="updateStatus('unavailable')">อัพเดทเป็นวันไม่ว่าง</button>
                <button class="btn btn-warning" onclick="clearSelectedDates()">ล้างวันที่เลือก</button>
            </div>
            <br>
        </div>
        <script>
            let selectedDates = [];
            const existingDates = <?php echo json_encode($dates); ?>;

            document.addEventListener('DOMContentLoaded', function() {
                const calendarEl = document.getElementById('calendar');
                const events = existingDates.map(d => ({
                    title: d.status === 'available' ? 'ว่าง' : 'ไม่ว่าง',
                    start: d.date,
                    color: d.status === 'available' ? '#198754' : '#dc3545',
                    allDay: true
                }));

                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'th',
                    selectable: true,
                    select: function(info) {
                        // เลือกหลายวัน
                        let start = info.startStr;
                        let end = info.endStr;
                        let current = new Date(start);
                        let last = new Date(end);
                        last.setDate(last.getDate() - 1);
                        selectedDates = [];
                        while (current <= last) {
                            selectedDates.push(current.toISOString().slice(0, 10));
                            current.setDate(current.getDate() + 1);
                        }
                        alert('เลือกวันที่: ' + selectedDates.join(', '));
                    },
                    events: events
                });
                calendar.render();

                // สมมุติ selectedDates คือ array ของวันที่ที่ต้องการลบ (เช่น ['2025-06-20', '2025-06-21'])
                selectedDates.forEach(function(dateStr) {
                    calendar.getEvents().forEach(function(event) {
                        if (event.startStr === dateStr) {
                            event.remove();
                        }
                    });
                });
            });

            function updateStatus(status) {
                if (selectedDates.length === 0) {
                    alert('กรุณาเลือกวันที่');
                    return;
                }
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
                            alert('อัพเดทสำเร็จ');
                            location.reload();
                        } else {
                            alert('เกิดข้อผิดพลาด');
                        }
                    });
            }

            function clearSelectedDates() {
                if (selectedDates.length === 0) {
                    alert('กรุณาเลือกวันที่');
                    return;
                }
                if (!confirm('ยืนยันการลบวันที่อัปเดตเหล่านี้?')) return;
                fetch('clear_update_date.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({dates: selectedDates})
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('ลบวันที่อัปเดตสำเร็จ');
                        location.reload(); // รีเฟรชหน้าทันทีหลังยืนยัน
                    } else {
                        alert('เกิดข้อผิดพลาด');
                    }
                });
            }
        </script>
    </div>
</body>

</html>