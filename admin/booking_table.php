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
                    <a href="../uploads/<?php echo htmlspecialchars($booking['doc']); ?>" target="_blank" class="btn btn-primary btn-sm">ดูไฟล์</a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
            <td>
                <?php if (!empty($booking['slip'])): ?>
                    <a href="../uploads/<?php echo htmlspecialchars($booking['slip']); ?>" target="_blank" class="btn btn-primary btn-sm">ดูสลิป</a>
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
                <button type="button" class="btn btn-dark btn-sm" 
                    data-bs-toggle="modal" 
                    data-bs-target="#detailModal" 
                    data-booking='<?= htmlspecialchars(json_encode($booking), ENT_QUOTES, 'UTF-8') ?>'>
                    ดูข้อมูล
                </button>
                <!-- ปุ่มเสร็จสิ้นในแต่ละแถว -->
                <button type="button" class="btn btn-success btn-sm btn-complete">เสร็จสิ้น</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Modal แสดงรายละเอียดการจอง -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel">รายละเอียดการจอง</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tbody id="modalDetailBody">
            <!-- ข้อมูลจะถูกเติมด้วย JS -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var detailModal = document.getElementById('detailModal');
    detailModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var booking = JSON.parse(button.getAttribute('data-booking'));
        var body = detailModal.querySelector('#modalDetailBody');
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

    document.querySelectorAll('.btn-complete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            // ซ่อนแถวที่ปุ่มนี้อยู่
            this.closest('tr').style.display = 'none';
        });
    });
});
</script>