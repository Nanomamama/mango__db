<?php
require_once 'db.php'; // เชื่อมต่อฐานข้อมูล
require_once 'auth.php';

// นับจำนวนออเดอร์ที่รอยืนยัน (pending)
$orderCount = 0;
$orderCountQuery = "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'";
$orderCountResult = $conn->query($orderCountQuery);
if ($orderCountResult) {
    $orderCountRow = $orderCountResult->fetch_assoc();
    $orderCount = $orderCountRow['cnt'];
}
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
    <style>
        table.table {
            border: 1px solid #ddd;
            border-collapse: collapse;
        }

        table.table th, table.table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }

        table.table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        table.table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table.table tr:hover {
            background-color: #f1f1f1;
        }

        .product-image {
            width: 100%; /* ปรับให้รูปภาพเต็มความกว้างของคอลัมน์ */
            height: auto; /* รักษาอัตราส่วนของรูปภาพ */
            object-fit: cover; /* ครอบรูปภาพให้พอดีกับพื้นที่ */
            border: 1px solid #ddd; /* เพิ่มเส้นขอบ */
            border-radius: 5px; /* เพิ่มมุมโค้งมน */
        }

        /* ตกแต่งช่องค้นหา DataTables ให้เข้ากับ Bootstrap */
        .dataTables_filter {
            margin-bottom: 1rem;
        }
        .dataTables_filter label {
            font-weight: bold;
            color: #0d6efd;
        }
        .dataTables_filter input[type="search"] {
            border-radius: 2rem;
            border: 1px solid #0d6efd;
            padding: 8px 18px;
            width: 250px;
            transition: box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(13,110,253,0.07);
            background: #fff;
            font-size: 1rem;
        }
        .dataTables_filter input[type="search"]:focus {
            outline: none;
            border-color: #6610f2;
            box-shadow: 0 0 0 2px #b6d4fe;
            background: #f8f9fa;
        }
    </style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="d-flex">
    <div class="p-4" style="margin-left: 250px; flex: 2;">
        <h2>📋 จัดการสินค้าผลิตภัณฑ์</h2>
        <a href="add_product.php" class="btn btn-primary mb-3">➕ เพิ่มสินค้า</a>
        <a href="order_product.php" class="btn btn-warning mb-3">
            คำสั่งซื้อ
            <?php if ($orderCount > 0): ?>
                <span class="badge bg-danger"><?= $orderCount ?></span>
            <?php endif; ?>
        </a>
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
                $query = "SELECT * FROM products ORDER BY id DESC";
                $result = $conn->query($query);

                while ($row = $result->fetch_assoc()):
                    $images = json_decode($row['images'], true); // แปลง JSON เป็น Array
                ?>
                <tr>
                    <td>
                        <img src="productsimage/<?= htmlspecialchars($images[0]) ?>" style="width: 150px; height: 150px; object-fit: cover; #ddd; border-radius: 5px;">
                    </td>
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
                            <div class="row">
                              <!-- รูปภาพสินค้า -->
                              <div class="col-md-6">
                                <div class="d-flex flex-wrap gap-2">
                                  <?php foreach ($images as $image): ?>
                                    <img src="productsimage/<?= htmlspecialchars($image) ?>" class="product-image mb-2" alt="รูปสินค้า">
                                  <?php endforeach; ?>
                                </div>
                              </div>
                              <!-- ข้อมูลสินค้า -->
                              <div class="col-md-6">
                                <h5><strong>ชื่อสินค้า:</strong> <?= htmlspecialchars($row['name']) ?></h5>
                                <h6><strong>ราคา:</strong> ฿<?= htmlspecialchars($row['price']) ?></h6>
                                <h6><strong>คงเหลือในคลัง:</strong> <?= htmlspecialchars($row['stock']) ?> ชิ้น</h6>
                                <h6><strong>รายละเอียดสินค้า:</strong></h6>
                                <p><?= htmlspecialchars($row['description']) ?></p>
                              </div>
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