<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการหลักสูตร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: "Kanit", sans-serif;
        }
        .modal-image {
        width: 100%; /* ให้รูปภาพเต็มความกว้างของคอลัมน์ */
        height: 200px; /* กำหนดความสูงคงที่ */
        object-fit: cover; /* ทำให้รูปภาพสมดุลโดยไม่บิดเบี้ยว */
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>

        <div class="container mt-4" style="margin-left: 250px; flex: 1;">

            <h2>📚 จัดการหลักสูตร</h2>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ชื่อหลักสูตร</th>
                        <th>รูป</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // เชื่อมต่อฐานข้อมูล
                    $conn = new mysqli("localhost", "root", "", "db_mango");

                    // ตรวจสอบการเชื่อมต่อ
                    if ($conn->connect_error) {
                        die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
                    }

                    // ดึงข้อมูลจากตาราง courses
                    $sql = "SELECT id, course_name, course_description, image1, image2, image3 FROM courses";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['course_name'] . "</td>";
                            echo "<td class='text-center'><img src='../uploads/" . $row['image1'] . "' alt='Image 1' class='img-thumbnail' style='width: 180px; height: 110px; object-fit: cover;'></td>";
                            echo "<td>";
                            echo "<button class='btn btn-info btn-sm' onclick='viewCourse(\"" . $row['course_name'] . "\", \"" . $row['course_description'] . "\", \"" . $row['image1'] . "\", \"" . $row['image2'] . "\", \"" . $row['image3'] . "\")'>ดูข้อมูลทั้งหมด</button> ";
                            echo "<button class='btn btn-warning btn-sm' onclick='editCourse(\"" . $row['id'] . "\", \"" . $row['course_name'] . "\", \"" . $row['course_description'] . "\", \"" . $row['image1'] . "\", \"" . $row['image2'] . "\", \"" . $row['image3'] . "\")'>แก้ไข</button> ";
                            echo "<button class='btn btn-danger btn-sm' onclick='confirmDelete(\"" . $row['id'] . "\")'>ลบ</button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>ไม่มีข้อมูล</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>

            <!-- ปุ่มเปิด Modal -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                ➕ เพิ่มหลักสูตร
            </button>

            <!-- Modal สำหรับเพิ่มหลักสูตร -->
            <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="save_course.php" method="POST" class="modal-content" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addCourseModalLabel">เพิ่มหลักสูตรใหม่</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="course_name" class="form-label">ชื่อหลักสูตร</label>
                                <input type="text" class="form-control" id="course_name" name="course_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="course_description" class="form-label">คำอธิบายหลักสูตร</label>
                                <textarea class="form-control" id="course_description" name="course_description" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="image1" class="form-label">รูปที่ 1</label>
                                <input type="file" class="form-control" id="image1" name="image1" accept="image/*" onchange="previewImage(event, 'preview1')">
                                <img id="preview1" src="#" alt="Preview Image 1" class="img-thumbnail mt-2" style="display: none; max-height: 150px;">
                            </div>
                            <div class="mb-3">
                                <label for="image2" class="form-label">รูปที่ 2</label>
                                <input type="file" class="form-control" id="image2" name="image2" accept="image/*" onchange="previewImage(event, 'preview2')">
                                <img id="preview2" src="#" alt="Preview Image 2" class="img-thumbnail mt-2" style="display: none; max-height: 150px;">
                            </div>
                            <div class="mb-3">
                                <label for="image3" class="form-label">รูปที่ 3</label>
                                <input type="file" class="form-control" id="image3" name="image3" accept="image/*" onchange="previewImage(event, 'preview3')">
                                <img id="preview3" src="#" alt="Preview Image 3" class="img-thumbnail mt-2" style="display: none; max-height: 150px;">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">💾 บันทึก</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">❌ ยกเลิก</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal สำหรับแสดงข้อมูลทั้งหมด -->
            <div class="modal fade" id="viewCourseModal" tabindex="-1" aria-labelledby="viewCourseModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg"> <!-- เพิ่ม class modal-lg เพื่อขยายความกว้าง -->
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewCourseModalLabel">ข้อมูลหลักสูตร</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>ชื่อหลักสูตร:</strong> <span id="modalCourseName"></span></p>
                            <p><strong>คำอธิบาย:</strong> <span id="modalCourseDescription"></span></p>
                            <p><strong>รูปภาพ:</strong></p>
                            <div class="row text-center"> <!-- ใช้ row และ col เพื่อจัดรูปภาพ -->
                                <div class="col-md-4">
                                    <img id="modalImage1" src="#" alt="Image 1" class="img-thumbnail mb-2 modal-image">
                                </div>
                                <div class="col-md-4">
                                    <img id="modalImage2" src="#" alt="Image 2" class="img-thumbnail mb-2 modal-image">
                                </div>
                                <div class="col-md-4">
                                    <img id="modalImage3" src="#" alt="Image 3" class="img-thumbnail mb-2 modal-image">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal สำหรับแก้ไขข้อมูล -->
            <div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="update_course.php" method="POST" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editCourseModalLabel">แก้ไขข้อมูลหลักสูตร</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="editCourseId" name="id"> <!-- ซ่อน ID หลักสูตร -->
                                <div class="mb-3">
                                    <label for="editCourseName" class="form-label">ชื่อหลักสูตร</label>
                                    <input type="text" class="form-control" id="editCourseName" name="course_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editCourseDescription" class="form-label">คำอธิบายหลักสูตร</label>
                                    <textarea class="form-control" id="editCourseDescription" name="course_description" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="editImage1" class="form-label">รูปที่ 1</label>
                                    <input type="file" class="form-control" id="editImage1" name="image1" accept="image/*">
                                    <img id="previewEditImage1" src="#" alt="Preview Image 1" class="img-thumbnail mt-2" style="max-height: 150px;">
                                </div>
                                <div class="mb-3">
                                    <label for="editImage2" class="form-label">รูปที่ 2</label>
                                    <input type="file" class="form-control" id="editImage2" name="image2" accept="image/*">
                                    <img id="previewEditImage2" src="#" alt="Preview Image 2" class="img-thumbnail mt-2" style="max-height: 150px;">
                                </div>
                                <div class="mb-3">
                                    <label for="editImage3" class="form-label">รูปที่ 3</label>
                                    <input type="file" class="form-control" id="editImage3" name="image3" accept="image/*">
                                    <img id="previewEditImage3" src="#" alt="Preview Image 3" class="img-thumbnail mt-2" style="max-height: 150px;">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">💾 บันทึก</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">❌ ยกเลิก</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal ยืนยันการลบ -->
            <div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteCourseModalLabel">ยืนยันการลบ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                        </div>
                        <div class="modal-body">
                            <p>คุณแน่ใจหรือไม่ว่าต้องการลบหลักสูตรนี้?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">❌ ยกเลิก</button>
                            <a href="#" id="confirmDeleteButton" class="btn btn-danger">🗑️ ลบ</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(event, previewId) {
            const input = event.target;
            const preview = document.getElementById(previewId);

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        }

        function showCourseDetails(courseName, courseDescription, image1) {
            document.getElementById('modalCourseName').textContent = courseName;
            document.getElementById('modalCourseDescription').textContent = courseDescription;
            document.getElementById('modalImage1').src = '../uploads/' + image1;
        }

        function viewCourse(courseName, courseDescription, image1, image2, image3) {
            // ตั้งค่าข้อมูลใน Modal
            document.getElementById('modalCourseName').textContent = courseName;
            document.getElementById('modalCourseDescription').textContent = courseDescription;
            document.getElementById('modalImage1').src = '../uploads/' + image1;
            document.getElementById('modalImage2').src = '../uploads/' + image2;
            document.getElementById('modalImage3').src = '../uploads/' + image3;

            // เปิด Modal
            const viewCourseModal = new bootstrap.Modal(document.getElementById('viewCourseModal'));
            viewCourseModal.show();
        }

        function editCourse(id, courseName, courseDescription, image1, image2, image3) {
            // ตั้งค่าข้อมูลใน Modal
            document.getElementById('editCourseId').value = id;
            document.getElementById('editCourseName').value = courseName;
            document.getElementById('editCourseDescription').value = courseDescription;
            document.getElementById('previewEditImage1').src = '../uploads/' + image1;
            document.getElementById('previewEditImage2').src = '../uploads/' + image2;
            document.getElementById('previewEditImage3').src = '../uploads/' + image3;

            // เปิด Modal
            const editCourseModal = new bootstrap.Modal(document.getElementById('editCourseModal'));
            editCourseModal.show();
        }

        function confirmDelete(id) {
            const confirmDeleteButton = document.getElementById('confirmDeleteButton');
            confirmDeleteButton.href = 'delete_course.php?id=' + id;

            const deleteCourseModal = new bootstrap.Modal(document.getElementById('deleteCourseModal'));
            deleteCourseModal.show();
        }
    </script>

</body>

</html>