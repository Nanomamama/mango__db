<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสายพันธุ์มะม่วง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="d-flex">
    <div class="p-4" style="margin-left: 250px; flex: 1;">
        <h2>📋 จัดการสายพันธุ์มะม่วง</h2>
        <a href="add_mango.php" class="btn btn-primary mb-3">➕ เพิ่มสายพันธุ์</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>รูปผลมะม่วง</th>
                    <th>รูปต้นมะม่วง</th>
                    <th>รูปใบมะม่วง</th>
                    <th>รูปดอกมะม่วง</th>
                    <th>รูปกลิ่งมะม่วง</th>
                    <th>ชื่อสายพันธุ์</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><img src="https://media.thairath.co.th/image/fmQpvmjp1V2ZIs1a2hU4OGKwkdosTnm1j4VXg22TebXFCs1a2hPSxQe9vA1.jpg" width="100"></td>
                    <td><img src="https://via.placeholder.com/100" width="100"></td>
                    <td><img src="https://via.placeholder.com/100" width="100"></td>
                    <td><img src="https://via.placeholder.com/100" width="100"></td>
                    <td><img src="https://via.placeholder.com/100" width="100"></td>
                    <td>น้ำดอกไม้</td>
                    <td>
                        <a href="edit_mango.php" class="btn btn-warning btn-sm">✏️ แก้ไข</a>
                        <a href="#" class="btn btn-danger btn-sm">🗑️ ลบ</a>
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
        <h5 class="modal-title" id="mangoDetailsModalLabel">ข้อมูลรายละเอียดสายพันธุ์มะม่วง</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- เนื้อหาที่จะแสดงใน Modal -->
        <div class="row">
            <!-- คอลัมน์ 1: ข้อมูลทั่วไป -->
            <div class="col-md-6">
                <h5>ชื่อสายพันธุ์: น้ำดอกไม้</h5>
                <h5>ชื่อวิทยาศาสตร์: น้ำดอกไม้</h5>
                <h5>ชื่อท้องถิ่น: น้ำดอกไม้</h5>

                <h6>ลักษณะสัณฐานวิทยา</h6>
                <ul>
                    <li><strong>ลำต้น:</strong> ลำต้นสูงและตรง</li>
                    <li><strong>ผล:</strong> ผลทรงกลม ขนาดกลาง</li>
                    <li><strong>ใบ:</strong> ใบกว้าง สีเขียวเข้ม</li>
                </ul>

                <h6>การขยายพันธุ์:</h6>
                <p>การปักชำหรือการติดตา</p>

                <h6>ลักษณะดิน:</h6>
                <p>ดินร่วนปนทราย มีการระบายน้ำดี</p>

                <h6>ระยะเวลาเพาะปลูก:</h6>
                <p>ประมาณ 2 ปี</p>

                <h6>ช่วงฤดูกาลเกี้ยว:</h6>
                <p>ฤดูร้อนถึงฤดูฝน</p>

                <h6>การแปรรูป:</h6>
                <ul>
                    <li>กวน</li>
                    <li>ดอง</li>
                    <li>แช่อิ่ม</li>
                    <li>นิยมรับประทานสด</li>
                </ul>

                <h6>หมวดมะม่วง:</h6>
                <ul>
                    <li>เชิงพาณิชย์</li>
                </ul>
            </div>

            <!-- คอลัมน์ 2: รูปภาพ -->
            <div class="col-md-6">
                <h6>รูปภาพ:</h6>
                <div>
                    <strong>รูปผลมะม่วง</strong><br>
                    <img src="https://via.placeholder.com/100" width="100"><br>
                    <strong>รูปต้นมะม่วง</strong><br>
                    <img src="https://via.placeholder.com/100" width="100"><br>
                    <strong>รูปใบมะม่วง</strong><br>
                    <img src="https://via.placeholder.com/100" width="100"><br>
                    <strong>รูปดอกมะม่วง</strong><br>
                    <img src="https://via.placeholder.com/100" width="100"><br>
                    <strong>รูปกลิ่งมะม่วง</strong><br>
                    <img src="https://via.placeholder.com/100" width="100">
                </div>
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

</body>
</html>
