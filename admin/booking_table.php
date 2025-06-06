<?php
// filepath: c:\xampp\htdocs\mango\admin\booking_table.php
if (!isset($bookings_show)) $bookings_show = [];
?>
<table class="table table-bordered table-hover text-center align-middle">
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
        <?php $i=1; foreach($bookings_show as $booking): ?>
        <tr id="row-<?php echo $booking['id']; ?>">
            <td><?php echo $i++; ?></td>
            <td><?php echo htmlspecialchars($booking['name']); ?></td>
            <td><?php echo htmlspecialchars($booking['date']); ?></td>
            <td><?php echo htmlspecialchars($booking['time']); ?></td>
            <td><?php echo htmlspecialchars($booking['people']); ?></td>
            <td>
                <?php if (!empty($booking['doc'])): ?>
                    <a href="uploads/<?php echo htmlspecialchars($booking['doc']); ?>" target="_blank" class="btn btn-primary btn-sm">ดูไฟล์</a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
            <td>
                <?php if (!empty($booking['slip'])): ?>
                    <a href="uploads/<?php echo htmlspecialchars($booking['slip']); ?>" target="_blank" class="btn btn-primary btn-sm">ดูสลิป</a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
            <td>
                <span class="badge bg-<?php
                    echo $booking['status'] === 'อนุมัติแล้ว' ? 'success' :
                         ($booking['status'] === 'ถูกปฏิเสธ' ? 'danger' : 'warning');
                ?>">
                    <?php echo htmlspecialchars($booking['status']); ?>
                </span>
            </td>
            <td>
                <button class="btn btn-success btn-sm me-1" onclick="changeStatus(<?php echo $booking['id']; ?>, 'อนุมัติแล้ว')">อนุมัติ</button>
                <button class="btn btn-danger btn-sm me-1" onclick="changeStatus(<?php echo $booking['id']; ?>, 'ถูกปฏิเสธ')">ปฏิเสธ</button>
                <button class="btn btn-secondary btn-sm" onclick="deleteBooking(<?php echo $booking['id']; ?>)">ลบ</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>