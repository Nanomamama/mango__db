<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';

// ดึงชื่อ admin จาก session
$admin_name = $_SESSION['admin_name'] ?? '';

// ลบคอมเม้น
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $del = $conn->prepare("DELETE FROM course_comments WHERE comment_id = ?");
    $del->bind_param("i", $id);
    $del->execute();
    $del->close();
    header("Location: manage_comments.php");
    exit;
}

// ดึงคอมเม้นทั้งหมด
$stmt = $conn->prepare("
    SELECT 
        cc.comment_id,
        cc.name,
        cc.comment_text,
        cc.created_at,
        c.course_name
    FROM course_comments cc
    JOIN courses c ON cc.courses_id = c.courses_id
    ORDER BY cc.created_at DESC
");

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการความคิดเห็น</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Kanit', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            min-height: 100vh;
        }

        .dashboard-header {
            background: linear-gradient(120deg, #4361ee, #3f37c9);
            color: white;
            padding: 1rem;
            border-radius: 50px;
            box-shadow: 0 4px 12px rgba(67, 97, 238, .3);
        }

        .admin-profile {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, .2);
            padding: .5rem 1rem;
            border-radius: 50px;
        }

        .admin-profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .card-box {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .05);
            padding: 1.5rem;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="p-4" style="margin-left:250px;">

        <!-- HEADER -->
        <header class="dashboard-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">จัดการความคิดเห็น</h4>
                <div class="admin-profile">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=random&color=fff">
                    <span><?= htmlspecialchars($admin_name) ?></span>
                </div>
            </div>
        </header>

        <!-- CONTENT -->
        <div class="card-box">
            <div class="d-flex justify-content-between mb-3">
                <h5>รายการความคิดเห็น</h5>
                <a href="edit_courses.php" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> กลับ
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>กิจกรรม</th>
                            <th>ชื่อ</th>
                            <th>ความคิดเห็น</th>
                            <th>วันที่</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                             
                                <td><?= htmlspecialchars($row['course_name']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td>
                                    <?php
                                    $full = htmlspecialchars($row['comment_text']);
                                    $short = mb_strimwidth($full, 0, 80, '...');
                                    ?>
                                    <span><?= nl2br($short) ?></span>

                                    <?php if (mb_strlen($full) > 80): ?>
                                        <br>
                                        <button class="btn btn-link p-0"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewCommentModal"
                                            onclick="showComment(`<?= addslashes($full) ?>`)">
                                            ดูเพิ่มเติม
                                        </button>
                                        <div class="modal fade" id="viewCommentModal" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">ความคิดเห็น</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p id="fullCommentText"
                                                            style="
                                                                    white-space: pre-wrap;
                                                                    word-wrap: break-word;
                                                                    word-break: break-word;
                                                                    max-height: 60vh;
                                                                    overflow-y: auto;
                                                                ">
                                                        </p>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endif; ?>
                                </td>

                                <td><?= $row['created_at'] ?></td>
                                <td>
                                    <a href="?delete=<?= $row['comment_id'] ?>"
                                        onclick="return confirm('ลบความคิดเห็นนี้หรือไม่?')"
                                        class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> ลบ
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function showComment(text) {
            document.getElementById('fullCommentText').innerText = text;
        }
    </script>

</body>

</html>