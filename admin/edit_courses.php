<?php
require_once 'auth.php';
require_once 'db.php';

// ดึงชื่อ admin จาก session
$admin_name = $_SESSION['admin_name'] ?? '';
$admin_email = $_SESSION['admin_email'] ?? '';

// ดึงข้อมูลหลักสูตรทั้งหมด
$courses = [];
$result = $conn->query("SELECT * FROM courses ORDER BY id DESC");
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการหลักสูตร - สวนมะม่วงลุงเผือก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --light: #f8f9fa;
            --dark: #212529;
            --purple: #7209b7;
            --teal: #20c997;
            --pink: #e83e8c;
            --cyan: #0dcaf0;
            --mango: #FFC107;
            --mango-dark: #E6A000;
        }

        * {
            font-family: 'Kanit', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            min-height: 100vh;
        }

        .dashboard-header {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: white;
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            position: relative;
            overflow: hidden;
            z-index: 10;
            border-radius: 50px;
        }

        .dashboard-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .admin-profile:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .admin-profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .admin-profile span {
            font-weight: 500;
            color: white;
            font-size: 0.9rem;
        }


        .stats-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
        }

        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .stats-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .stats-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
        }

        .course-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 1.5rem;
            border: none;
            position: relative;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
        }

        .course-card::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .course-card-header {
            padding: 1rem 1.5rem;
            background: linear-gradient(90deg, rgba(67, 97, 238, 0.1), transparent);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .course-card-body {
            padding: 1.5rem;
        }

        .course-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .course-description {
            color: #6c757d;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .action-btn {
            border-radius: 50px;
            padding: 0.5rem 1.2rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn i {
            margin-right: 5px;
        }

        .btn-view {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .btn-view:hover {
            background: rgba(67, 97, 238, 0.2);
            color: var(--primary);
        }

        .btn-edit {
            background: rgba(246, 194, 62, 0.1);
            color: var(--warning);
        }

        .btn-edit:hover {
            background: rgba(246, 194, 62, 0.2);
            color: var(--warning);
        }

        .btn-delete {
            background: rgba(231, 74, 59, 0.1);
            color: var(--danger);
        }

        .btn-delete:hover {
            background: rgba(231, 74, 59, 0.2);
            color: var(--danger);
        }

        .search-box {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .search-box input {
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.2);
            border-color: var(--primary);
        }

        .search-box i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .course-modal .modal-content {
            border-radius: 16px;
            overflow: hidden;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .course-modal .modal-header {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: white;
            border-bottom: none;
        }

        .course-modal .btn-close {
            filter: invert(1);
        }

        .modal-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .admin-profile:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .admin-profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .admin-profile span {
            font-weight: 500;
            color: white;
            font-size: 0.9rem;
        }

        .btn-add-course {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-add-course:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
            color: white;
        }

        .btn-add-course i {
            margin-right: 5px;
        }

        @media (max-width: 768px) {
            .course-card-body {
                padding: 1rem;
            }

            .action-btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .btn-group-vertical {
                width: 100%;
            }

            .dashboard-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="p-4" style="margin-left: 250px; flex: 1;">
        <!-- Header -->
        <header class="dashboard-header pb-4 mb-4">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h2 class="dashboard-title mb-0">จัดการหลักสูตร</h2>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-2 mt-md-0">
                        <div class="position-relative">
                            <button class="btn btn-light rounded-circle p-2 shadow-sm position-relative" style="width:44px; height:44px;">
                                <i class="bi bi-bell fs-5"></i>
                                <span class="notification-badge position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger" style="font-size:0.75rem; min-width:20px; height:20px; display:flex; align-items:center; justify-content:center;">
                                    3
                                </span>
                            </button>
                        </div>
                        <div class="admin-profile">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=random&color=fff" alt="Admin">
                            <span><?= htmlspecialchars($admin_name) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Courses List -->
        <div class="row">
            <?php foreach ($courses as $course): ?>
            <div class="col-lg-4 col-md-6">
                <div class="course-card">
                    <div class="course-card-header">
                        <h5 class="mb-0"><?= htmlspecialchars($course['course_name']) ?></h5>
                    </div>
                    <div class="course-card-body">
                        <img src="<?= $course['image1'] ? '/mango/uploads/' . $course['image1'] : 'https://via.placeholder.com/400x200?text=No+Image' ?>"
                             class="course-image" alt="<?= htmlspecialchars($course['course_name']) ?>">
                        <p class="course-description"><?= htmlspecialchars($course['course_description']) ?></p>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn action-btn btn-view view-course-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#courseModal"
                                data-course='<?= htmlspecialchars(json_encode([
                                    'id' => $course['id'],
                                    'name' => $course['course_name'],
                                    'description' => $course['course_description'],
                                    'image1' => $course['image1'],
                                    'image2' => $course['image2'],
                                    'image3' => $course['image3']
                                ]), ENT_QUOTES, 'UTF-8') ?>'>
                                <i class="bi bi-info-circle"></i> ดูข้อมูล
                            </button>
                            <button class="btn action-btn btn-edit edit-course-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#editCourseModal"
                                data-course='<?= htmlspecialchars(json_encode([
                                    'id' => $course['id'],
                                    'name' => $course['course_name'],
                                    'description' => $course['course_description'],
                                    'image1' => $course['image1'],
                                    'image2' => $course['image2'],
                                    'image3' => $course['image3']
                                ]), ENT_QUOTES, 'UTF-8') ?>'>
                                <i class="bi bi-pencil"></i> แก้ไข
                            </button>
                            <button class="btn action-btn btn-delete" onclick="confirmDelete(<?= $course['id'] ?>)">
                                <i class="bi bi-trash"></i> ลบ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Add Course Button -->
        <div class="text-center mt-4">
            <button type="button" class="btn btn-add-course" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                <i class="bi bi-plus-circle"></i> เพิ่มหลักสูตรใหม่
            </button>
        </div>
    </div>
    <!-- Course Detail Modal -->
    <div class="modal fade course-modal" id="courseModal" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="courseModalLabel">รายละเอียดหลักสูตร</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="courseDetailTable">
                            <!-- ข้อมูลจะถูกเติมโดย JavaScript -->
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Course Modal -->
    <div class="modal fade course-modal" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCourseModalLabel">เพิ่มหลักสูตรใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="save_course.php" method="POST" enctype="multipart/form-data">
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
                        <div class="text-center">
                            <button type="submit" class="btn btn-success">บันทึก</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div class="modal fade course-modal" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCourseModalLabel">แก้ไขหลักสูตร</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="update_course.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" id="editCourseId" name="id">
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
                            <input type="file" class="form-control" id="editImage1" name="image1" accept="image/*" onchange="previewImage(event, 'previewEdit1')">
                            <img id="previewEdit1" src="#" alt="Preview Image 1" class="img-thumbnail mt-2" style="max-height: 150px;">
                        </div>
                        <div class="mb-3">
                            <label for="editImage2" class="form-label">รูปที่ 2</label>
                            <input type="file" class="form-control" id="editImage2" name="image2" accept="image/*" onchange="previewImage(event, 'previewEdit2')">
                            <img id="previewEdit2" src="#" alt="Preview Image 2" class="img-thumbnail mt-2" style="max-height: 150px;">
                        </div>
                        <div class="mb-3">
                            <label for="editImage3" class="form-label">รูปที่ 3</label>
                            <input type="file" class="form-control" id="editImage3" name="image3" accept="image/*" onchange="previewImage(event, 'previewEdit3')">
                            <img id="previewEdit3" src="#" alt="Preview Image 3" class="img-thumbnail mt-2" style="max-height: 150px;">
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-success">บันทึกการเปลี่ยนแปลง</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <a href="#" id="confirmDeleteButton" class="btn btn-danger">ลบ</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.view-course-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const course = JSON.parse(this.getAttribute('data-course'));
                let html = '';

                const fields = [{
                        key: 'name',
                        label: 'ชื่อหลักสูตร'
                    },
                    {
                        key: 'description',
                        label: 'คำอธิบาย'
                    },
                    {
                        key: 'image1',
                        label: 'รูปภาพ 1',
                        format: value => value ? `<img src="../uploads/${value}" class="modal-image" alt="Image 1">` : 'ไม่มีรูปภาพ'
                    },
                    {
                        key: 'image2',
                        label: 'รูปภาพ 2',
                        format: value => value ? `<img src="../uploads/${value}" class="modal-image" alt="Image 2">` : 'ไม่มีรูปภาพ'
                    },
                    {
                        key: 'image3',
                        label: 'รูปภาพ 3',
                        format: value => value ? `<img src="../uploads/${value}" class="modal-image" alt="Image 3">` : 'ไม่มีรูปภาพ'
                    }
                ];

                fields.forEach(field => {
                    let value = course[field.key] !== null ? course[field.key] : '';
                    if (field.format) {
                        value = field.format(value);
                    }

                    html += `<tr>
                    <th style="width:180px; background-color: #f8f9fa;">${field.label}</th>
                    <td>${value}</td>
                </tr>`;
                });

                document.getElementById('courseDetailTable').innerHTML = html;
            });
        });

        document.querySelectorAll('.edit-course-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const course = JSON.parse(this.getAttribute('data-course'));

                document.getElementById('editCourseId').value = course.id;
                document.getElementById('editCourseName').value = course.name;
                document.getElementById('editCourseDescription').value = course.description;

                // ตั้งค่าภาพ预览 (ถ้ามี)
                if (course.image1) {
                    document.getElementById('previewEdit1').src = '../uploads/' + course.image1;
                    document.getElementById('previewEdit1').style.display = 'block';
                }
                if (course.image2) {
                    document.getElementById('previewEdit2').src = '../uploads/' + course.image2;
                    document.getElementById('previewEdit2').style.display = 'block';
                }
                if (course.image3) {
                    document.getElementById('previewEdit3').src = '../uploads/' + course.image3;
                    document.getElementById('previewEdit3').style.display = 'block';
                }
            });
        });

        // ฟังก์ชันค้นหาหลักสูตร
        document.querySelector('.search-box input').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            document.querySelectorAll('.course-card').forEach(card => {
                const name = card.querySelector('h5').textContent.toLowerCase();
                if (name.includes(searchText)) {
                    card.parentElement.style.display = 'block';
                } else {
                    card.parentElement.style.display = 'none';
                }
            });
        });

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

        function confirmDelete(id) {
            const confirmDeleteButton = document.getElementById('confirmDeleteButton');
            confirmDeleteButton.href = 'delete_course.php?id=' + id;

            const deleteCourseModal = new bootstrap.Modal(document.getElementById('deleteCourseModal'));
            deleteCourseModal.show();
        }
    </script>
</body>

</html>