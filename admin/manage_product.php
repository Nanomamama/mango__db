<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- ✅ โหลด jQuery ก่อนใช้งาน -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="d-flex">
    <div class="p-4" style="margin-left: 250px; flex: 2;">
        <h2>📋 จัดการสินค้าผลิตภัณฑ์</h2>
        <a href="add_product.php" class="btn btn-primary mb-3">➕ เพิ่มสินค้า</a>
        <a href="order_product.php" class="btn btn-warning mb-3 "> คำสั่งซื้อ</a>
        <a href="sales_report.php" class="btn btn-warning mb-3 "> รายงานการขาย</a>
        <input type="text" id="searchInput" class="form-control mb-3" placeholder="🔍 ค้นหาสินค้า...">
        
        <table id="productTable" class="table table-bordered">
            <tbody>
                <tr>
                    <td><img src="https://ตลาดเกษตรกรออนไลน์.com/uploads/products/212.jpg" width="100"></td>
                    <td>กล้วยทอดอบเนยสมุนไพร</td>
                    <td>คงเหลือ: 20 ชิ้น</td>
                    <td>
                        <a href="edit_product.php" class="btn btn-warning btn-sm">✏️ แก้ไข</a>
                        <a href="#" class="btn btn-danger btn-sm" onclick="confirmDelete()">🗑️ ลบ</a>
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#mangoDetailsModal">🔍 ดูข้อมูลทั้งหมด</button>
                    </td>
                </tr>
                <!-- เพิ่มแถวอื่นๆ ตามต้องการ -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="mangoDetailsModal" tabindex="-1" aria-labelledby="mangoDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mangoDetailsModalLabel">ข้อมูลรายละเอียดสินค้า</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <img src="https://ตลาดเกษตรกรออนไลน์.com/uploads/products/212.jpg" width="350" height="200"><br>
                <h5>ชื่อสินค้า: กล้วยทอดอบเนยสมุนไพร</h5>
                <h5>รายละเอียดสินค้า:</h5>
                <h6><strong>หมวดหมู่สินค้าแปรรูป:</strong></h6>
                <p>กล้วยทอด</p>
                <h6>ราคา:</h6>
                <p>50</p>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap 5 JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>
// ✅ ยืนยันการลบ
function confirmDelete() {
    if (confirm("คุณแน่ใจหรือไม่ว่าต้องการลบสินค้านี้?")) {
        alert("สินค้าถูกลบเรียบร้อยแล้ว!");
    }
}

// ✅ ค้นหาสินค้า
document.addEventListener("DOMContentLoaded", function () {
    let searchInput = document.getElementById("searchInput");

    if (searchInput) {
        searchInput.addEventListener("keyup", function () {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll("tbody tr");

            rows.forEach(row => {
                let productName = row.cells[1].innerText.toLowerCase();
                row.style.display = productName.includes(filter) ? "" : "none";
            });
        });
    }
});

// ✅ Pagination สำหรับตาราง
$(document).ready(function () {
    $("#productTable").DataTable();
});
</script>

</body>
</html>
