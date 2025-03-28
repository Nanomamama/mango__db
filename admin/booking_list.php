<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางรายการจอง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="d-flex">
        <div class="container mt-5"  style="margin-left: 250px; flex: 1;">
            <h2 class="mb-4">📋 ตารางรายการจอง</h2>
            <table class="table table-bordered table-hover text-center">
                <thead class="table-dark">
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
                <tbody>
                    <?php
                    $bookings = [
                        ["name" => "คณะ A", "date" => "2025-03-30", "time" => "08:00-12:00", "people" => 50, "status" => "รออนุมัติ", "doc" => "doc_a.pdf", "slip" => "slip_a.jpg"],
                        ["name" => "คณะ B", "date" => "2025-03-31", "time" => "13:00-17:00", "people" => 45, "status" => "อนุมัติแล้ว", "doc" => "doc_b.pdf", "slip" => "slip_b.jpg"],
                        ["name" => "คณะ C", "date" => "2025-04-01", "time" => "08:00-12:00", "people" => 60, "status" => "ถูกปฏิเสธ", "doc" => "doc_c.pdf", "slip" => "slip_c.jpg"]
                    ];
                    $i = 1;
                    foreach ($bookings as $booking) {
                        echo "<tr>";
                        echo "<td>{$i}</td>";
                        echo "<td>{$booking['name']}</td>";
                        echo "<td>{$booking['date']}</td>";
                        echo "<td>{$booking['time']}</td>";
                        echo "<td>{$booking['people']}</td>";
                        echo "<td><a href='uploads/{$booking['doc']}' class='btn btn-primary btn-sm' target='_blank'>📂 ดูไฟล์</a></td>";
                        echo "<td><a href='uploads/{$booking['slip']}' class='btn btn-info btn-sm' target='_blank'>📂 ดูสลิป</a></td>";
                        
                        // สถานะแสดงสี
                        $statusColor = ($booking['status'] == 'อนุมัติแล้ว') ? 'success' : (($booking['status'] == 'ถูกปฏิเสธ') ? 'danger' : 'warning');
                        echo "<td><span class='badge bg-{$statusColor}'>{$booking['status']}</span></td>";
                        
                        // ปุ่มจัดการ
                        echo "<td>";
                        echo "<button class='btn btn-success btn-sm me-1'>✔ อนุมัติ</button>";
                        echo "<button class='btn btn-danger btn-sm me-1'>❌ ปฏิเสธ</button>";
                        echo "<button class='btn btn-secondary btn-sm'>🗑 ลบ</button>";
                        echo "</td>";
                        
                        echo "</tr>";
                        $i++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
