<?php
require_once 'db.php'; // เชื่อมต่อฐานข้อมูล
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="d-flex">
    <div class="p-4" style="margin-left: 250px; flex: 2;">
        <h2>📋 จัดการสินค้าผลิตภัณฑ์</h2>
        <a href="add_product.php" class="btn btn-primary mb-3">➕ เพิ่มสินค้า</a>
        <a href="order_product.php" class="btn btn-warning mb-3">คำสั่งซื้อ</a>
        <a href="sales_report.php" class="btn btn-warning mb-3">รายงานการขาย</a>

        <table id="productTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>รูปภาพ</th>
                    <th>ชื่อสินค้า</th>
                    <th>คงเหลือ</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM products";
                $result = $conn->query($query);

                while ($row = $result->fetch_assoc()):
                    $images = json_decode($row['images'], true); // แปลง JSON เป็น Array
                ?>
                <tr>
                    <td><img src="productsimage/<?= htmlspecialchars($images[0]) ?>" width="100"></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td>คงเหลือ: <?= htmlspecialchars($row['stock']) ?> ชิ้น</td>
                    <td>
                        <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">✏️ แก้ไข</a>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id'] ?>">🗑️ ลบ</button>
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#mangoDetailsModal<?= $row['id'] ?>">🔍 ดูข้อมูลทั้งหมด</button>
                    </td>

                    <!-- Modal สำหรับการลบสินค้า -->
                    <div class="modal fade" id="deleteModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $row['id'] ?>" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel<?= $row['id'] ?>">ยืนยันการลบสินค้า</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            คุณแน่ใจหรือไม่ว่าต้องการลบสินค้า <strong><?= htmlspecialchars($row['name']) ?></strong>?
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <a href="delete_product.php?id=<?= $row['id'] ?>" class="btn btn-danger">ยืนยันการลบ</a>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Modal สำหรับแสดงรายละเอียดสินค้า -->
                    <div class="modal fade" id="mangoDetailsModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="mangoDetailsModalLabel<?= $row['id'] ?>" aria-hidden="true">
                      <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="mangoDetailsModalLabel<?= $row['id'] ?>">ข้อมูลรายละเอียดสินค้า</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <div id="productImagesCarousel<?= $row['id'] ?>" class="carousel slide" data-bs-ride="carousel">
                              <div class="carousel-inner">
                                <?php foreach ($images as $index => $image): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                  <img src="productsimage/<?= htmlspecialchars($image) ?>" class="d-block w-100" alt="รูปที่ <?= $index + 1 ?>">
                                </div>
                                <?php endforeach; ?>
                              </div>
                              <button class="carousel-control-prev" type="button" data-bs-target="#productImagesCarousel<?= $row['id'] ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                                <span class="visually-hidden">Previous</span>
                              </button>
                              <button class="carousel-control-next" type="button" data-bs-target="#productImagesCarousel<?= $row['id'] ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                                <span class="visually-hidden">Next</span>
                              </button>
                            </div>
                            <div class="mt-4">
                              <h5>ชื่อสินค้า: <?= htmlspecialchars($row['name']) ?></h5>
                              <h5>รายละเอียดสินค้า:</h5>
                              <p><?= htmlspecialchars($row['description']) ?></p>
                              <h6><strong>ราคา:</strong> <?= htmlspecialchars($row['price']) ?> บาท</h6>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                          </div>
                        </div>
                      </div>
                    </div>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap 5 JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>
$(document).ready(function () {
    $("#productTable").DataTable();
});
</script>

</body>
</html>