<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';
require_once 'sidebar.php';

$admin_name = $_SESSION['admin_name'] ?? '';
$admin_email = $_SESSION['admin_email'] ?? '';

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod === 'POST' && isset($_POST['delete_comment_id'])) {
    $id = (int)$_POST['delete_comment_id'];
    if ($id > 0) {
        $del = $conn->prepare("DELETE FROM course_comments WHERE comment_id = ?");
        if ($del) {
            $del->bind_param("i", $id);
            $del->execute();
            $del->close();
        }
    }
    header("Location: manage_comments.php?success=delete");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $del = $conn->prepare("DELETE FROM course_comments WHERE comment_id = ?");
        if ($del) {
            $del->bind_param("i", $id);
            $del->execute();
            $del->close();
        }
    }
    header("Location: manage_comments.php?success=delete");
    exit;
}

$comments = [];
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

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $comments[] = [
            'comment_id' => (int)$row['comment_id'],
            'name' => $row['name'] ?: 'ไม่ระบุชื่อ',
            'comment_text' => $row['comment_text'] ?? '',
            'created_at' => $row['created_at'] ?? '',
            'course_name' => $row['course_name'] ?? '',
        ];
    }
    $stmt->close();
}

$courseNames = [];
foreach ($comments as $comment) {
    $courseNames[$comment['course_name']] = true;
}

$comments_json = json_encode($comments, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

$adminPageExtraHead = <<<'HTML'
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    :root {
        --comment-green: #016A70;
        --comment-green-dark: #01545a;
        --comment-green-soft: rgba(1, 106, 112, .09);
        --comment-ink: #0f172a;
        --comment-muted: #64748b;
        --comment-line: #e2e8f0;
        --comment-bg: #f8fafc;
        --comment-panel: #ffffff;
        --comment-red: #ef4444;
        --comment-shadow: 0 16px 38px rgba(15, 23, 42, .08);
        --comment-radius: 8px;
    }

    .page-content {
        width: 100%;
        max-width: 100%;
        padding: 0;
    }

    .comments-admin-page {
        width: 100%;
        max-width: 1420px;
        margin: 0 auto;
        padding: 28px;
        color: var(--comment-ink);
    }

    .comment-hero,
    .comment-toolbar,
    .comment-panel,
    .comment-empty,
    .comment-modal .modal-content {
        background: var(--comment-panel);
        border: 1px solid var(--comment-line);
        border-radius: var(--comment-radius);
        box-shadow: var(--comment-shadow);
    }

    .comment-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 20px;
        padding: 24px;
        margin-bottom: 18px;
    }

    .comment-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        color: var(--comment-green);
        font-size: .82rem;
        font-weight: 700;
    }

    .comment-title {
        margin: 0;
        font-size: clamp(1.45rem, 2vw, 2rem);
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: 0;
    }

    .comment-subtitle {
        margin: 8px 0 0;
        color: var(--comment-muted);
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    .admin-chip {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 220px;
        padding: 10px 14px;
        border: 1px solid var(--comment-line);
        border-radius: var(--comment-radius);
        background: linear-gradient(180deg, #ffffff, #f8fafc);
    }

    .admin-chip img {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
    }

    .admin-chip strong,
    .admin-chip span {
        display: block;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .admin-chip span {
        color: var(--comment-muted);
        font-size: .78rem;
    }

    .comment-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .comment-stat {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        background: #fff;
        border: 1px solid var(--comment-line);
        border-radius: var(--comment-radius);
    }

    .comment-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 8px;
        color: var(--comment-green);
        background: var(--comment-green-soft);
        flex: 0 0 auto;
    }

    .comment-stat-value {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .comment-stat-label {
        color: var(--comment-muted);
        font-size: .82rem;
    }

    .comment-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 16px;
        margin-bottom: 18px;
    }

    .comment-search {
        position: relative;
        flex: 1 1 360px;
        max-width: 620px;
    }

    .comment-search i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--comment-muted);
    }

    .comment-search input {
        width: 100%;
        min-height: 46px;
        padding: 10px 16px 10px 42px;
        border: 1px solid var(--comment-line);
        border-radius: var(--comment-radius);
        outline: none;
        transition: .2s ease;
    }

    .comment-search input:focus {
        border-color: var(--comment-green);
        box-shadow: 0 0 0 4px rgba(1, 106, 112, .10);
    }

    .btn-comment-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 10px 16px;
        border: 1px solid var(--comment-line);
        border-radius: var(--comment-radius);
        background: #fff;
        color: var(--comment-green);
        font-weight: 700;
        text-decoration: none;
    }

    .btn-comment-back:hover {
        background: var(--comment-green-soft);
        color: var(--comment-green-dark);
    }

    .comment-panel {
        overflow: hidden;
    }

    .comment-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px;
        border-bottom: 1px solid var(--comment-line);
    }

    .comment-panel-head h2 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
    }

    .comment-count-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        background: var(--comment-green-soft);
        color: var(--comment-green);
        font-size: .78rem;
        font-weight: 800;
    }

    .comment-table {
        margin: 0;
    }

    .comment-table thead th {
        background: #f1f5f9;
        color: #334155;
        border-bottom: 1px solid var(--comment-line);
        font-size: .82rem;
        white-space: nowrap;
    }

    .comment-table td {
        vertical-align: middle;
        color: #334155;
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .course-cell {
        max-width: 260px;
        font-weight: 700;
        color: var(--comment-ink);
    }

    .comment-preview {
        max-width: 520px;
        color: #475569;
        line-height: 1.65;
    }

    .muted-small {
        color: var(--comment-muted);
        font-size: .82rem;
        white-space: nowrap;
    }

    .btn-comment-view,
    .btn-comment-delete {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 36px;
        padding: 7px 10px;
        border-radius: var(--comment-radius);
        font-size: .82rem;
        font-weight: 700;
    }

    .btn-comment-view {
        border: 1px solid rgba(1, 106, 112, .2);
        background: var(--comment-green-soft);
        color: var(--comment-green);
    }

    .btn-comment-delete {
        border: 1px solid rgba(239, 68, 68, .18);
        background: rgba(239, 68, 68, .09);
        color: var(--comment-red);
    }

    .comment-full-text {
        max-height: 58vh;
        overflow-y: auto;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        line-height: 1.8;
    }

    .comment-empty {
        display: none;
        margin-top: 18px;
        padding: 42px 18px;
        color: var(--comment-muted);
        text-align: center;
    }

    .comment-empty i {
        display: block;
        margin-bottom: 10px;
        color: var(--comment-green);
        font-size: 2rem;
    }

    .comment-modal .modal-header {
        background: var(--comment-green);
        color: #fff;
        border: 0;
    }

    .comment-modal .btn-close {
        filter: invert(1);
    }

    @media (max-width: 900px) {
        .comment-table thead {
            display: none;
        }

        .comment-table tbody,
        .comment-table tr,
        .comment-table td {
            display: block;
            width: 100%;
        }

        .comment-table tr {
            margin: 14px;
            border: 1px solid var(--comment-line);
            border-radius: var(--comment-radius);
            background: #fff;
            overflow: hidden;
        }

        .comment-table td {
            display: grid;
            grid-template-columns: 108px minmax(0, 1fr);
            gap: 12px;
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        .comment-table td:last-child {
            border-bottom: 0;
        }

        .comment-table td::before {
            content: attr(data-label);
            color: var(--comment-muted);
            font-size: .78rem;
            font-weight: 800;
        }

        .course-cell,
        .comment-preview {
            max-width: none;
        }
    }

    @media (max-width: 768px) {
        .comments-admin-page {
            padding: 18px;
        }

        .comment-hero {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            padding: 18px;
        }

        .admin-chip,
        .comment-search,
        .btn-comment-back {
            width: 100%;
            max-width: none;
        }

        .comment-search {
            flex: 0 1 auto;
        }

        .comment-stats {
            grid-template-columns: 1fr;
        }

        .comment-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .comment-panel-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .modal-dialog {
            margin: .75rem;
        }
    }

    @media (max-width: 480px) {
        .comments-admin-page {
            padding: 12px;
        }

        .comment-title {
            font-size: 1.28rem;
        }

        .comment-table td {
            grid-template-columns: 1fr;
            gap: 6px;
        }
    }
</style>
HTML;

adminPageStart('จัดการความคิดเห็น');
?>

<div class="comments-admin-page">
    <section class="comment-hero">
        <div>
            <div class="comment-kicker"><i class="bi bi-chat-left-dots"></i> ความคิดเห็นกิจกรรม</div>
            <h1 class="comment-title">จัดการความคิดเห็น</h1>
            <p class="comment-subtitle">ตรวจสอบความคิดเห็นจากผู้ใช้ ดูรายละเอียดข้อความเต็ม และลบความคิดเห็นที่ไม่เหมาะสม</p>
        </div>
     
    </section>

    <section class="comment-stats" aria-label="สรุปความคิดเห็น">
        <div class="comment-stat">
            <span class="comment-stat-icon"><i class="bi bi-chat-square-text"></i></span>
            <div>
                <p class="comment-stat-value"><?= number_format(count($comments)) ?></p>
                <div class="comment-stat-label">ความคิดเห็นทั้งหมด</div>
            </div>
        </div>
        <div class="comment-stat">
            <span class="comment-stat-icon"><i class="bi bi-journal-check"></i></span>
            <div>
                <p class="comment-stat-value"><?= number_format(count($courseNames)) ?></p>
                <div class="comment-stat-label">กิจกรรมที่มีความคิดเห็น</div>
            </div>
        </div>
    </section>

    <section class="comment-toolbar" aria-label="เครื่องมือจัดการความคิดเห็น">
        <div class="comment-search">
            <i class="bi bi-search"></i>
            <input type="search" id="commentSearch" placeholder="ค้นหากิจกรรม ชื่อผู้แสดงความคิดเห็น หรือข้อความ..." autocomplete="off">
        </div>
        <a href="edit_courses.php" class="btn-comment-back">
            <i class="bi bi-arrow-left"></i> กลับหน้ากิจกรรม
        </a>
    </section>

    <section class="comment-panel">
        <div class="comment-panel-head">
            <h2>รายการความคิดเห็น</h2>
            <span class="comment-count-pill"><i class="bi bi-list-check"></i> <span id="visibleCount"><?= number_format(count($comments)) ?></span> รายการ</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle comment-table">
                <thead>
                    <tr>
                        <th>กิจกรรม</th>
                        <th>ชื่อ</th>
                        <th>ความคิดเห็น</th>
                        <th>วันที่</th>
                        <th class="text-end">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment): ?>
                        <?php
                        $fullText = $comment['comment_text'];
                        $shortText = mb_strimwidth($fullText, 0, 110, '...', 'UTF-8');
                        ?>
                        <tr class="comment-row" data-search="<?= htmlspecialchars(mb_strtolower($comment['course_name'] . ' ' . $comment['name'] . ' ' . $comment['comment_text'], 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>">
                            <td class="course-cell" data-label="กิจกรรม"><?= htmlspecialchars($comment['course_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td data-label="ชื่อ"><?= htmlspecialchars($comment['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td data-label="ความคิดเห็น">
                                <div class="comment-preview"><?= nl2br(htmlspecialchars($shortText, ENT_QUOTES, 'UTF-8')) ?></div>
                                <?php if (mb_strlen($fullText, 'UTF-8') > 90): ?>
                                    <button class="btn btn-comment-view mt-2 view-comment-btn" type="button" data-bs-toggle="modal" data-bs-target="#viewCommentModal" data-comment-id="<?= (int)$comment['comment_id'] ?>">
                                        <i class="bi bi-eye"></i> ดูเพิ่มเติม
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td data-label="วันที่"><span class="muted-small"><?= htmlspecialchars($comment['created_at'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td class="text-end" data-label="จัดการ">
                                <button class="btn btn-comment-delete delete-comment-btn" type="button" data-bs-toggle="modal" data-bs-target="#deleteCommentModal" data-comment-id="<?= (int)$comment['comment_id'] ?>" data-comment-name="<?= htmlspecialchars($comment['name'], ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="bi bi-trash"></i> ลบ
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <div class="comment-empty" id="commentEmpty">
        <i class="bi bi-search"></i>
        <strong>ไม่พบความคิดเห็นที่ค้นหา</strong>
        <div>ลองเปลี่ยนคำค้นหาเพื่อแสดงรายการอื่น</div>
    </div>
</div>

<div class="modal fade comment-modal" id="viewCommentModal" tabindex="-1" aria-labelledby="viewCommentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewCommentModalLabel"><i class="bi bi-chat-left-text me-2"></i>รายละเอียดความคิดเห็น</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <div class="muted-small">กิจกรรม</div>
                    <h6 class="fw-bold mb-0" id="modalCourseName"></h6>
                </div>
                <div class="mb-3">
                    <div class="muted-small">ผู้แสดงความคิดเห็น</div>
                    <h6 class="fw-bold mb-0" id="modalCommentName"></h6>
                </div>
                <div class="comment-full-text" id="fullCommentText"></div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade comment-modal" id="deleteCommentModal" tabindex="-1" aria-labelledby="deleteCommentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCommentModalLabel"><i class="bi bi-exclamation-triangle me-2"></i>ยืนยันการลบ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">ต้องการลบความคิดเห็นนี้หรือไม่?</p>
                <strong class="text-danger" id="deleteCommentName"></strong>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <form method="POST" action="manage_comments.php">
                    <input type="hidden" name="delete_comment_id" id="deleteCommentId">
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>ลบความคิดเห็น</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="bi bi-check-circle-fill me-2 text-success"></i>
            <strong class="me-auto">สำเร็จ</strong>
            <small>เมื่อสักครู่</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="ปิด"></button>
        </div>
        <div class="toast-body">ลบความคิดเห็นสำเร็จ</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const commentsData = <?= $comments_json ?: '[]' ?>;

    function findComment(id) {
        return commentsData.find(comment => Number(comment.comment_id) === Number(id));
    }

    document.querySelectorAll('.view-comment-btn').forEach(button => {
        button.addEventListener('click', function() {
            const comment = findComment(this.dataset.commentId);
            if (!comment) return;

            document.getElementById('modalCourseName').textContent = comment.course_name || '-';
            document.getElementById('modalCommentName').textContent = comment.name || '-';
            document.getElementById('fullCommentText').textContent = comment.comment_text || '-';
        });
    });

    document.querySelectorAll('.delete-comment-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('deleteCommentId').value = this.dataset.commentId || '';
            document.getElementById('deleteCommentName').textContent = this.dataset.commentName || '';
        });
    });

    const commentSearch = document.getElementById('commentSearch');
    const commentEmpty = document.getElementById('commentEmpty');
    const visibleCount = document.getElementById('visibleCount');

    if (commentSearch) {
        commentSearch.addEventListener('input', function() {
            const keyword = this.value.trim().toLowerCase();
            let count = 0;

            document.querySelectorAll('.comment-row').forEach(row => {
                const visible = (row.dataset.search || '').toLowerCase().includes(keyword);
                row.style.display = visible ? '' : 'none';
                if (visible) count++;
            });

            if (visibleCount) visibleCount.textContent = count.toLocaleString();
            if (commentEmpty) commentEmpty.style.display = count === 0 ? 'block' : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const toastEl = document.getElementById('liveToast');
        if (toastEl && params.get('success') === 'delete') {
            new bootstrap.Toast(toastEl).show();
        }
    });
</script>

<?php adminPageEnd(); ?>
