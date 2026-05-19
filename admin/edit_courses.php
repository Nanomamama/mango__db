<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';
require_once 'sidebar.php';

$admin_name = $_SESSION['admin_name'] ?? '';
$admin_email = $_SESSION['admin_email'] ?? '';

$courses = [];
$result = $conn->query("
    SELECT courses_id, course_name, course_description, image1, image2, image3, created_at, updated_at
    FROM courses
    ORDER BY courses_id DESC
");

if ($result instanceof mysqli_result) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = [
            'id' => (int)$row['courses_id'],
            'course_name' => $row['course_name'] ?? '',
            'course_description' => $row['course_description'] ?? '',
            'image1' => $row['image1'] ?? '',
            'image2' => $row['image2'] ?? '',
            'image3' => $row['image3'] ?? '',
            'created_at' => $row['created_at'] ?? '',
            'updated_at' => $row['updated_at'] ?? '',
        ];
    }
    $result->close();
}

$courseCount = count($courses);
$imageCount = 0;
foreach ($courses as $course) {
    foreach (['image1', 'image2', 'image3'] as $imageKey) {
        if (!empty($course[$imageKey])) {
            $imageCount++;
        }
    }
}

$courses_json = json_encode($courses, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

$adminPageExtraHead = <<<'HTML'
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    :root {
        --course-green: #016A70;
        --course-green-dark: #01545a;
        --course-green-soft: rgba(1, 106, 112, .09);
        --course-ink: #0f172a;
        --course-muted: #64748b;
        --course-line: #e2e8f0;
        --course-bg: #f8fafc;
        --course-panel: #ffffff;
        --course-yellow: #f59e0b;
        --course-red: #ef4444;
        --course-blue: #2563eb;
        --course-shadow: 0 16px 38px rgba(15, 23, 42, .08);
        --course-radius: 8px;
    }

    .page-content {
        width: 100%;
        max-width: 100%;
        padding: 0;
    }

    .courses-admin-page {
        width: 100%;
        max-width: 1420px;
        margin: 0 auto;
        padding: 28px;
        color: var(--course-ink);
    }

    .course-hero,
    .course-toolbar,
    .course-empty,
    .course-card,
    .course-modal .modal-content,
    .delete-dialog .modal-content {
        background: var(--course-panel);
        border: 1px solid var(--course-line);
        border-radius: var(--course-radius);
        box-shadow: var(--course-shadow);
    }

    .course-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 20px;
        padding: 24px;
        margin-bottom: 18px;
    }

    .course-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        color: var(--course-green);
        font-size: .82rem;
        font-weight: 700;
    }

    .course-title {
        margin: 0;
        font-size: clamp(1.45rem, 2vw, 2rem);
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: 0;
    }

    .course-subtitle {
        margin: 8px 0 0;
        color: var(--course-muted);
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    .admin-chip {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 220px;
        padding: 10px 14px;
        border: 1px solid var(--course-line);
        border-radius: var(--course-radius);
        background: linear-gradient(180deg, #ffffff, #f8fafc);
    }

    .admin-chip img {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        flex: 0 0 auto;
    }

    .admin-chip strong,
    .admin-chip span {
        display: block;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .admin-chip strong {
        font-size: .95rem;
    }

    .admin-chip span {
        color: var(--course-muted);
        font-size: .78rem;
    }

    .course-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .course-stat {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        background: #fff;
        border: 1px solid var(--course-line);
        border-radius: var(--course-radius);
    }

    .course-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 8px;
        color: var(--course-green);
        background: var(--course-green-soft);
        flex: 0 0 auto;
    }

    .course-stat-value {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .course-stat-label {
        color: var(--course-muted);
        font-size: .82rem;
    }

    .course-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .course-search {
        position: relative;
        flex: 1 1 360px;
        max-width: 560px;
    }

    .course-search i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--course-muted);
    }

    .course-search input {
        width: 100%;
        min-height: 46px;
        padding: 10px 16px 10px 42px;
        border: 1px solid var(--course-line);
        border-radius: var(--course-radius);
        outline: none;
        transition: .2s ease;
    }

    .course-search input:focus {
        border-color: var(--course-green);
        box-shadow: 0 0 0 4px rgba(1, 106, 112, .10);
    }

    .course-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .btn-course-primary,
    .btn-course-secondary,
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 42px;
        border-radius: var(--course-radius);
        font-weight: 700;
        white-space: normal;
        text-align: center;
    }

    .btn-course-primary {
        border: 1px solid var(--course-green);
        background: var(--course-green);
        color: #fff;
        padding: 10px 16px;
    }

    .btn-course-primary:hover {
        background: var(--course-green-dark);
        color: #fff;
    }

    .btn-course-secondary {
        border: 1px solid var(--course-line);
        background: #fff;
        color: var(--course-green);
        padding: 10px 16px;
        text-decoration: none;
    }

    .btn-course-secondary:hover {
        background: var(--course-green-soft);
        color: var(--course-green-dark);
    }

    .course-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .course-card {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        min-height: 100%;
    }

    .course-thumb-wrap {
        position: relative;
        aspect-ratio: 16 / 10;
        background: #eef2f7;
        overflow: hidden;
    }

    .course-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform .25s ease;
    }

    .course-card:hover .course-image {
        transform: scale(1.035);
    }

    .course-badge {
        position: absolute;
        left: 12px;
        bottom: 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        max-width: calc(100% - 24px);
        padding: 7px 10px;
        border-radius: 999px;
        background: rgba(15, 23, 42, .78);
        color: #fff;
        font-size: .75rem;
        font-weight: 700;
    }

    .course-card-body {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding: 16px;
    }

    .course-name {
        margin: 0 0 10px;
        font-size: 1.02rem;
        font-weight: 800;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .course-description {
        margin: 0 0 16px;
        color: var(--course-muted);
        line-height: 1.7;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .course-card-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-top: auto;
    }

    .action-btn {
        border: 1px solid transparent;
        padding: 9px 10px;
        font-size: .88rem;
    }

    .btn-view {
        background: rgba(37, 99, 235, .09);
        color: var(--course-blue);
        border-color: rgba(37, 99, 235, .16);
    }

    .btn-edit {
        background: rgba(245, 158, 11, .12);
        color: #b45309;
        border-color: rgba(245, 158, 11, .2);
    }

    .btn-delete {
        background: rgba(239, 68, 68, .10);
        color: var(--course-red);
        border-color: rgba(239, 68, 68, .16);
    }

    .course-empty {
        display: none;
        padding: 42px 18px;
        color: var(--course-muted);
        text-align: center;
    }

    .course-empty i {
        display: block;
        margin-bottom: 10px;
        color: var(--course-green);
        font-size: 2rem;
    }

    .course-modal .modal-header,
    .delete-dialog .modal-header {
        background: var(--course-green);
        color: #fff;
        border: 0;
    }

    .course-modal .btn-close,
    .delete-dialog .btn-close {
        filter: invert(1);
    }

    .modal-image {
        width: 100%;
        max-height: 260px;
        object-fit: cover;
        border-radius: var(--course-radius);
        border: 1px solid var(--course-line);
    }

    .detail-table th {
        width: 170px;
        color: var(--course-muted);
        background: var(--course-bg);
    }

    .detail-table td {
        overflow-wrap: anywhere;
    }

    .image-upload-card {
        height: 100%;
        padding: 12px;
        border: 1px dashed #cbd5e1;
        border-radius: var(--course-radius);
        background: #f8fafc;
    }

    .preview-image {
        display: none;
        width: 100%;
        height: 145px;
        margin-top: 10px;
        object-fit: cover;
        border-radius: var(--course-radius);
        border: 1px solid var(--course-line);
    }

    .preview-image.is-marked-remove {
        opacity: .35;
        filter: grayscale(1);
    }

    .remove-image-check {
        display: none;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        padding: 9px 10px;
        border: 1px solid rgba(239, 68, 68, .18);
        border-radius: var(--course-radius);
        background: rgba(239, 68, 68, .08);
        color: var(--course-red);
        font-size: .84rem;
        font-weight: 700;
        cursor: pointer;
    }

    .remove-image-check input {
        width: 1rem;
        height: 1rem;
        flex: 0 0 auto;
    }

    @media (max-width: 1180px) {
        .course-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .courses-admin-page {
            padding: 18px;
        }

        .course-hero,
        .course-toolbar {
            grid-template-columns: 1fr;
            align-items: stretch;
        }

        .course-hero {
            display: flex;
            flex-direction: column;
            padding: 18px;
        }

        .admin-chip,
        .course-search,
        .btn-course-primary,
        .btn-course-secondary {
            width: 100%;
            max-width: none;
        }

        .course-search {
            flex: 0 1 auto;
        }

        .course-stats {
            grid-template-columns: 1fr;
        }

        .course-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .course-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .course-grid {
            grid-template-columns: 1fr;
        }

        .course-card-actions {
            grid-template-columns: 1fr;
        }

        .modal-dialog {
            margin: .75rem;
        }

        .modal-body,
        .modal-footer {
            padding: 1rem !important;
        }

        .detail-table,
        .detail-table tbody,
        .detail-table tr,
        .detail-table th,
        .detail-table td {
            display: block;
            width: 100%;
        }

        .detail-table th {
            border-bottom: 0;
        }
    }

    @media (max-width: 480px) {
        .courses-admin-page {
            padding: 12px;
        }

        .course-title {
            font-size: 1.28rem;
        }

        .course-stat {
            align-items: flex-start;
        }
    }
</style>
HTML;

adminPageStart('จัดการกิจกรรมอบรม');
?>

<div class="courses-admin-page">
    <section class="course-hero">
        <div>
            <div class="course-kicker"><i class="bi bi-stars"></i> ระบบกิจกรรมอบรม</div>
            <h1 class="course-title">จัดการกิจกรรมอบรม</h1>
            <p class="course-subtitle">เพิ่ม แก้ไข ลบ และตรวจสอบรายละเอียดกิจกรรมทั้งหมด พร้อมทางลัดไปหน้าจัดการความคิดเห็น</p>
        </div>
       
    </section>

    <section class="course-stats" aria-label="สรุปข้อมูลกิจกรรม">
        <div class="course-stat">
            <span class="course-stat-icon"><i class="bi bi-calendar2-check"></i></span>
            <div>
                <p class="course-stat-value"><?= number_format($courseCount) ?></p>
                <div class="course-stat-label">กิจกรรมทั้งหมด</div>
            </div>
        </div>
        <div class="course-stat">
            <span class="course-stat-icon"><i class="bi bi-images"></i></span>
            <div>
                <p class="course-stat-value"><?= number_format($imageCount) ?></p>
                <div class="course-stat-label">รูปภาพประกอบ</div>
            </div>
        </div>
        <div class="course-stat">
            <span class="course-stat-icon"><i class="bi bi-chat-left-text"></i></span>
            <div>
                <p class="course-stat-value"><a class="text-decoration-none text-reset" href="manage_comments.php">คอมเมนต์</a></p>
                <div class="course-stat-label">ตรวจสอบความคิดเห็นผู้ใช้</div>
            </div>
        </div>
    </section>

    <section class="course-toolbar" aria-label="เครื่องมือจัดการกิจกรรม">
        <div class="course-search">
            <i class="bi bi-search"></i>
            <input type="search" id="courseSearch" placeholder="ค้นหาชื่อหรือรายละเอียดกิจกรรม..." autocomplete="off">
        </div>
        <div class="course-actions">
            <button type="button" class="btn btn-course-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                <i class="bi bi-plus-lg"></i> เพิ่มกิจกรรมใหม่
            </button>
            <a href="manage_comments.php" class="btn-course-secondary">
                <i class="bi bi-chat-dots"></i> จัดการความคิดเห็น
            </a>
        </div>
    </section>

    <div class="course-grid" id="courseGrid">
        <?php foreach ($courses as $course): ?>
            <?php
            $images = array_values(array_filter([$course['image1'], $course['image2'], $course['image3']]));
            $primaryImage = $course['image1'] ?: ($images[0] ?? '');
            ?>
            <article class="course-card course-col" data-search="<?= htmlspecialchars(mb_strtolower($course['course_name'] . ' ' . $course['course_description'], 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>">
                <div class="course-thumb-wrap">
                    <img
                        src="<?= $primaryImage ? '../uploads/' . htmlspecialchars($primaryImage, ENT_QUOTES, 'UTF-8') : 'https://placehold.co/800x500/f1f5f9/64748b?text=No+Image' ?>"
                        class="course-image"
                        alt="<?= htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8') ?>">
                    <span class="course-badge"><i class="bi bi-image"></i> <?= count($images) ?> รูป</span>
                </div>
                <div class="course-card-body">
                    <h2 class="course-name"><?= htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="course-description"><?= htmlspecialchars($course['course_description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="course-card-actions">
                        <button class="btn action-btn btn-view view-course-btn" type="button" data-bs-toggle="modal" data-bs-target="#courseModal" data-course-id="<?= (int)$course['id'] ?>">
                            <i class="bi bi-eye"></i> ดู
                        </button>
                        <button class="btn action-btn btn-edit edit-course-btn" type="button" data-bs-toggle="modal" data-bs-target="#editCourseModal" data-course-id="<?= (int)$course['id'] ?>">
                            <i class="bi bi-pencil-square"></i> แก้ไข
                        </button>
                        <button class="btn action-btn btn-delete" type="button" onclick="confirmDelete(<?= (int)$course['id'] ?>, <?= json_encode($course['course_name'], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)">
                            <i class="bi bi-trash"></i> ลบ
                        </button>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="course-empty" id="courseEmpty">
        <i class="bi bi-search"></i>
        <strong>ไม่พบกิจกรรมที่ค้นหา</strong>
        <div>ลองเปลี่ยนคำค้นหา หรือเพิ่มกิจกรรมใหม่เข้าสู่ระบบ</div>
    </div>
</div>

<div class="modal fade course-modal" id="courseModal" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courseModalLabel"><i class="bi bi-journal-text me-2"></i>รายละเอียดกิจกรรมอบรม</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered detail-table mb-0" id="courseDetailTable"></table>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade course-modal" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCourseModalLabel"><i class="bi bi-plus-circle me-2"></i>เพิ่มกิจกรรมอบรม</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body p-4">
                <form action="save_course.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="course_name" class="form-label fw-bold">ชื่อกิจกรรมอบรม</label>
                        <input type="text" class="form-control" id="course_name" name="course_name" required>
                    </div>
                    <div class="mb-4">
                        <label for="course_description" class="form-label fw-bold">คำอธิบาย</label>
                        <textarea class="form-control" id="course_description" name="course_description" rows="5" required></textarea>
                    </div>
                    <h6 class="fw-bold mb-3">รูปภาพประกอบ</h6>
                    <div class="row g-3">
                        <?php for ($i = 1; $i <= 3; $i++): ?>
                            <div class="col-md-4">
                                <div class="image-upload-card">
                                    <label for="image<?= $i ?>" class="form-label"><?= $i === 1 ? 'รูปที่ 1 (หลัก)' : 'รูปที่ ' . $i ?></label>
                                    <input type="file" class="form-control" id="image<?= $i ?>" name="image<?= $i ?>" accept="image/*" onchange="previewImage(event, 'preview<?= $i ?>')">
                                    <img id="preview<?= $i ?>" src="#" alt="ตัวอย่างรูปที่ <?= $i ?>" class="preview-image">
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top flex-wrap">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-save me-2"></i>บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade course-modal" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCourseModalLabel"><i class="bi bi-pencil-square me-2"></i>แก้ไขกิจกรรมอบรม</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body p-4">
                <form action="update_course.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="editCourseId" name="id">
                    <div class="mb-3">
                        <label for="editCourseName" class="form-label fw-bold">ชื่อกิจกรรมอบรม</label>
                        <input type="text" class="form-control" id="editCourseName" name="course_name" required>
                    </div>
                    <div class="mb-4">
                        <label for="editCourseDescription" class="form-label fw-bold">คำอธิบายกิจกรรมอบรม</label>
                        <textarea class="form-control" id="editCourseDescription" name="course_description" rows="5" required></textarea>
                    </div>
                    <h6 class="fw-bold mb-3">รูปภาพประกอบ (เลือกไฟล์ใหม่เพื่อแทนที่รูปเดิม)</h6>
                    <div class="row g-3">
                        <?php for ($i = 1; $i <= 3; $i++): ?>
                            <div class="col-md-4">
                                <div class="image-upload-card">
                                    <label for="editImage<?= $i ?>" class="form-label"><?= $i === 1 ? 'รูปที่ 1 (หลัก)' : 'รูปที่ ' . $i ?></label>
                                    <input type="file" class="form-control" id="editImage<?= $i ?>" name="image<?= $i ?>" accept="image/*" onchange="previewImage(event, 'previewEdit<?= $i ?>')">
                                    <img id="previewEdit<?= $i ?>" src="#" alt="ตัวอย่างรูปที่ <?= $i ?>" class="preview-image">
                                    <label class="remove-image-check" id="removeImageWrap<?= $i ?>">
                                        <input type="checkbox" name="remove_image<?= $i ?>" id="removeImage<?= $i ?>" value="1">
                                        ลบรูปนี้ออก
                                    </label>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top flex-wrap">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-save me-2"></i>บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade delete-dialog" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCourseModalLabel"><i class="bi bi-exclamation-triangle me-2"></i>ยืนยันการลบ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">ต้องการลบกิจกรรมนี้หรือไม่?</p>
                <strong id="deleteCourseName" class="d-block text-danger"></strong>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <form action="delete_course.php" method="post" id="deleteForm">
                    <input type="hidden" name="id" id="deleteCourseId">
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>ลบกิจกรรม</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="bi bi-check-circle-fill me-2 text-success" id="toastIcon"></i>
            <strong class="me-auto" id="toastTitle">สำเร็จ</strong>
            <small>เมื่อสักครู่</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="ปิด"></button>
        </div>
        <div class="toast-body" id="toast-body"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const coursesData = <?= $courses_json ?: '[]' ?>;
    const UPLOADS_PATH = '../uploads/';

    function escapeHTML(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[char]);
    }

    function escapeAttr(value) {
        return escapeHTML(value).replace(/`/g, '&#096;');
    }

    function findCourse(id) {
        return coursesData.find(course => Number(course.id) === Number(id));
    }

    document.querySelectorAll('.view-course-btn').forEach(button => {
        button.addEventListener('click', function() {
            const course = findCourse(this.dataset.courseId);
            if (!course) return;

            const fields = [
                { key: 'course_name', label: 'ชื่อกิจกรรม' },
                { key: 'course_description', label: 'คำอธิบาย', multiline: true },
                { key: 'image1', label: 'รูปภาพ 1', image: true },
                { key: 'image2', label: 'รูปภาพ 2', image: true },
                { key: 'image3', label: 'รูปภาพ 3', image: true },
                { key: 'updated_at', label: 'แก้ไขล่าสุด' }
            ];

            const html = fields.map(field => {
                const rawValue = course[field.key] ?? '';
                let value = '';

                if (field.image) {
                    value = rawValue
                        ? `<img src="${UPLOADS_PATH}${escapeAttr(rawValue)}" class="modal-image" alt="${escapeAttr(field.label)}">`
                        : '<span class="text-muted">ไม่มีรูปภาพ</span>';
                } else if (field.multiline) {
                    value = escapeHTML(rawValue).replace(/\n/g, '<br>');
                } else {
                    value = escapeHTML(rawValue || '-');
                }

                return `<tr><th>${field.label}</th><td>${value}</td></tr>`;
            }).join('');

            document.getElementById('courseDetailTable').innerHTML = html;
        });
    });

    document.querySelectorAll('.edit-course-btn').forEach(button => {
        button.addEventListener('click', function() {
            const course = findCourse(this.dataset.courseId);
            if (!course) return;

            document.getElementById('editCourseId').value = course.id;
            document.getElementById('editCourseName').value = course.course_name;
            document.getElementById('editCourseDescription').value = course.course_description;

            [1, 2, 3].forEach(number => {
                const preview = document.getElementById(`previewEdit${number}`);
                const fileInput = document.getElementById(`editImage${number}`);
                const removeWrap = document.getElementById(`removeImageWrap${number}`);
                const removeInput = document.getElementById(`removeImage${number}`);
                const image = course[`image${number}`];

                if (fileInput) {
                    fileInput.value = '';
                }

                if (removeInput) {
                    removeInput.checked = false;
                }

                if (image) {
                    preview.src = UPLOADS_PATH + image;
                    preview.style.display = 'block';
                    preview.classList.remove('is-marked-remove');
                    if (removeWrap) removeWrap.style.display = 'flex';
                } else {
                    preview.src = '#';
                    preview.style.display = 'none';
                    preview.classList.remove('is-marked-remove');
                    if (removeWrap) removeWrap.style.display = 'none';
                }
            });
        });
    });

    const searchInput = document.getElementById('courseSearch');
    const emptyState = document.getElementById('courseEmpty');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const keyword = this.value.trim().toLowerCase();
            let visibleCount = 0;

            document.querySelectorAll('.course-col').forEach(card => {
                const searchableText = (card.dataset.search || '').toLowerCase();
                const visible = searchableText.includes(keyword);
                card.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            if (emptyState) {
                emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        });
    }

    function previewImage(event, previewId) {
        const input = event.target;
        const preview = document.getElementById(previewId);
        if (!preview) return;

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = 'block';
                preview.classList.remove('is-marked-remove');
            };
            reader.readAsDataURL(input.files[0]);

            const editMatch = previewId.match(/^previewEdit([1-3])$/);
            if (editMatch) {
                const removeInput = document.getElementById(`removeImage${editMatch[1]}`);
                if (removeInput) removeInput.checked = false;
            }
        } else {
            preview.src = '#';
            preview.style.display = 'none';
            preview.classList.remove('is-marked-remove');
        }
    }

    document.querySelectorAll('[id^="removeImage"]').forEach(input => {
        input.addEventListener('change', function() {
            const number = this.id.replace('removeImage', '');
            const preview = document.getElementById(`previewEdit${number}`);
            if (preview) {
                preview.classList.toggle('is-marked-remove', this.checked);
            }
        });
    });

    function confirmDelete(id, name) {
        document.getElementById('deleteCourseId').value = id;
        document.getElementById('deleteCourseName').textContent = name || '';
        new bootstrap.Modal(document.getElementById('deleteCourseModal')).show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get('success');
        const error = urlParams.get('error');
        const toastEl = document.getElementById('liveToast');
        const toastBody = document.getElementById('toast-body');
        const toastTitle = document.getElementById('toastTitle');
        const toastIcon = document.getElementById('toastIcon');

        if (!toastEl || (!success && !error)) return;

        const successMessages = {
            update: 'อัปเดตข้อมูลกิจกรรมสำเร็จ',
            add: 'เพิ่มกิจกรรมใหม่สำเร็จ',
            delete: 'ลบกิจกรรมอบรมสำเร็จ'
        };

        if (error) {
            toastTitle.textContent = 'เกิดข้อผิดพลาด';
            toastIcon.className = 'bi bi-exclamation-circle-fill me-2 text-danger';
            toastBody.textContent = 'เกิดข้อผิดพลาด: ' + error;
        } else {
            toastTitle.textContent = 'สำเร็จ';
            toastBody.textContent = successMessages[success] || 'ดำเนินการสำเร็จ';
        }

        new bootstrap.Toast(toastEl).show();
    });
</script>

<?php adminPageEnd(); ?>
