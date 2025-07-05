<?php
require_once 'auth.php';

// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_mango";

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// เช็คการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// คำสั่ง SQL สำหรับดึงข้อมูลจากฐานข้อมูล
$sql = "SELECT * FROM mango_varieties";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสายพันธุ์มะม่วง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: "Kanit", sans-serif;
        }
        body{
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
        }

        .btn {
            transition: transform 0.3s ease;
            transition: 0.3s;
        }

        .btn:hover {
            transform: translateY(-5px);
        }
    </style>
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ลบข้อมูลเรียบร้อยแล้ว
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>


    <div class="d-flex">
        <div class="p-4" style="margin-left: 250px; flex: 1;">
            <h2><i class='bx bx-detail'></i> จัดการสายพันธุ์มะม่วง</h2>
            <a href="add_mango.php" class="btn btn-primary mb-3"><i class='bx bx-plus'></i> เพิ่มสายพันธุ์</a>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr style="text-align: center;">
                        <th>ชื่อสายพันธุ์</th>
                        <th>รูปผลมะม่วง</th>
                        <th>รูปต้นมะม่วง</th>
                        <th>รูปใบมะม่วง</th>
                        <th>รูปดอกมะม่วง</th>
                        <th>รูปกิ่งมะม่วง</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($mango = $result->fetch_assoc()): ?>
                        <tr>
                            <td 
                                style="text-align: center;">
                                <?= $mango['mango_name']; ?>
                            </td>
                            
                            <td style="text-align: center;">
                                <?php if (!empty($mango['fruit_image'])): ?>
                                    <img src="<?= $mango['fruit_image']; ?>" style="width: 110px; height: 110px; object-fit: cover;">
                                <?php else: ?>
                                    <span>ไม่มีข้อมูล</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($mango['tree_image'])): ?>
                                    <img src="<?= $mango['tree_image']; ?>" style="width: 150px; height: 110px; object-fit: cover;">
                                <?php else: ?>
                                    <span>ไม่มีข้อมูล</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($mango['leaf_image'])): ?>
                                    <img src="<?= $mango['leaf_image']; ?>" style="width: 150px; height: 110px; object-fit: cover;">
                                <?php else: ?>
                                    <span>ไม่มีข้อมูล</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($mango['flower_image'])): ?>
                                    <img src="<?= $mango['flower_image']; ?>" style="width: 150px; height: 110px; object-fit: cover;">
                                <?php else: ?>
                                    <span>ไม่มีข้อมูล</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($mango['branch_image'])): ?>
                                    <img src="<?= $mango['branch_image']; ?>" style="width: 150px; height: 110px; object-fit: cover;">
                                <?php else: ?>
                                    <span>ไม่มีข้อมูล</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <a href="edit_mango.php?id=<?php echo $mango['id']; ?>" class="btn btn-warning btn-sm">✏️ แก้ไข</a>
                                <!-- ปุ่มลบที่เรียก Modal -->
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $mango['id']; ?>" data-name="<?= htmlspecialchars($mango['mango_name']); ?>">
                                    🗑️ ลบ
                                </button>
                                <button type="button" class="btn btn-info btn-sm text-light" data-bs-toggle="modal" data-bs-target="#mangoDetailsModal"
                                    data-id="<?= $mango['id']; ?>"
                                    data-name="<?= htmlspecialchars($mango['mango_name']); ?>"
                                    data-scientific-name="<?= htmlspecialchars($mango['scientific_name']); ?>"
                                    data-local-name="<?= htmlspecialchars($mango['local_name']); ?>"
                                    data-stem="<?= htmlspecialchars($mango['morphology_stem']); ?>"
                                    data-fruit="<?= htmlspecialchars($mango['morphology_fruit']); ?>"
                                    data-leaf="<?= htmlspecialchars($mango['morphology_leaf']); ?>"
                                    data-propagation="<?= htmlspecialchars($mango['propagation_method']); ?>"
                                    data-soil="<?= htmlspecialchars($mango['soil_characteristics']); ?>"
                                    data-planting-duration="<?= htmlspecialchars($mango['planting_period']); ?>"
                                    data-harvest-season="<?= htmlspecialchars($mango['harvest_season']); ?>"
                                    data-category="<?= htmlspecialchars($mango['mango_category']); ?>"
                                    data-description="<?= htmlspecialchars($mango['processing_methods']); ?>"
                                    data-fruit-img="<?= htmlspecialchars($mango['fruit_image']); ?>"
                                    data-tree-img="<?= htmlspecialchars($mango['tree_image']); ?>"
                                    data-leaf-img="<?= htmlspecialchars($mango['leaf_image']); ?>"
                                    data-flower-img="<?= htmlspecialchars($mango['flower_image']); ?>"
                                    data-branch-img="<?= htmlspecialchars($mango['branch_image']); ?>">
                                    ดูรายละเอียด</button>
                            </td>

                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal ดูข้อมูล -->
    <div class="modal fade" id="mangoDetailsModal" tabindex="-1" aria-labelledby="mangoDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mangoDetailsModalLabel">ข้อมูลรายละเอียดสายพันธุ์มะม่วง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- ข้อมูลจะถูกอัปเดตโดย JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>
    <!-- JavaScript ดูข้อมูล -->
    <script>
        var mangoDetailsModal = document.getElementById('mangoDetailsModal');
        mangoDetailsModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var scientificName = button.getAttribute('data-scientific-name');
            var localName = button.getAttribute('data-local-name');
            var stem = button.getAttribute('data-stem');
            var fruit = button.getAttribute('data-fruit');
            var leaf = button.getAttribute('data-leaf');
            var propagation = button.getAttribute('data-propagation');
            var soil = button.getAttribute('data-soil');
            var plantingDuration = button.getAttribute('data-planting-duration');
            var harvestSeason = button.getAttribute('data-harvest-season');
            var preserved = button.getAttribute('data-preserved');
            var category = button.getAttribute('data-category');
            var description = button.getAttribute('data-description');
            var fruitImg = button.getAttribute('data-fruit-img');
            var treeImg = button.getAttribute('data-tree-img');
            var leafImg = button.getAttribute('data-leaf-img');
            var flowerImg = button.getAttribute('data-flower-img');
            var branchImg = button.getAttribute('data-branch-img');

            var modalTitle = mangoDetailsModal.querySelector('.modal-title');
            var modalBody = mangoDetailsModal.querySelector('.modal-body');
            modalTitle.textContent = 'ข้อมูลรายละเอียดสายพันธุ์มะม่วง: ' + name;
            modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h5>ข้อมูลทั่วไป</h5>
                <p><strong>ชื่อวิทยาศาสตร์:</strong> ${scientificName}</p>
                <p><strong>ชื่อท้องถิ่น:</strong> ${localName}</p>
                <p><strong>หมวดหมู่:</strong> ${category}</p>

                <h5>ลักษณะทางสัณฐานวิทยา</h5>
                <p><strong>ลำต้น:</strong> ${stem}</p>
                <p><strong>ผล:</strong> ${fruit}</p>
                <p><strong>ใบ:</strong> ${leaf}</p>
            </div>
            <div class="col-md-6">
                <h5>การปลูกและการดูแล</h5>
                <p><strong>การขยายพันธุ์:</strong> ${propagation}</p>
                <p><strong>ลักษณะดิน:</strong> ${soil}</p>
                <p><strong>ระยะเวลาการปลูก:</strong> ${plantingDuration}</p>
                <p><strong>ฤดูกาลเก็บเกี่ยว:</strong> ${harvestSeason}</p>

                <h5>วิธีการแปรรูป</h5>
                <p><strong>แปรรูป:</strong> ${description}</p>
            </div>
        </div>
        <hr>
        <h5>รูปภาพ</h5>
        <div class="row">
            <div class="col-md-4">
                <strong>ผลมะม่วง</strong><br>
                <img src="${fruitImg}" style="width: 100%; height: 250px; object-fit: cover;">
            </div>
            <div class="col-md-4">
                <strong>ต้นมะม่วง</strong><br>
                <img src="${treeImg}" style="width: 100%; height: 250px; object-fit: cover;">
            </div>
            <div class="col-md-4">
                <strong>ใบมะม่วง</strong><br>
                <img src="${leafImg}" style="width: 100%; height: 250px; object-fit: cover;">
            </div>
            <div class="col-md-4">
                <strong>ดอกมะม่วง</strong><br>
                <img src="${flowerImg}" style="width: 100%; height: 250px; object-fit: cover;">
            </div>
            <div class="col-md-4">
                <strong>กิ่งมะม่วง</strong><br>
                <img src="${branchImg}" style="width: 100%; height: 250px; object-fit: cover;">
            </div>
        </div>
        `;
        });
    </script>


    <!-- Modal ยืนยันการลบ -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="delete_mango.php">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteModalLabel">ยืนยันการลบ</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        คุณแน่ใจหรือไม่ว่าต้องการลบสายพันธุ์ <strong id="deleteMangoName"></strong>?
                        <input type="hidden" name="delete_id" id="deleteMangoId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ลบ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- JavaScript ที่ใช้ในการลบ -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');

                // ใส่ค่าลงใน Modal
                document.getElementById('deleteMangoId').value = id;
                document.getElementById('deleteMangoName').textContent = name;
            });
        });
    </script>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

</body>
</html>