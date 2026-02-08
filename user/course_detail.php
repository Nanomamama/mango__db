<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// กำหนดค่าเริ่มต้นของชื่อผู้ใช้
$loggedInUserName = '';

// ตรวจสอบว่ามีข้อมูลผู้ใช้ใน Session หรือไม่
if (isset($_SESSION['member_id']) && !empty($_SESSION['member_id'])) {
    require_once __DIR__ . '/../db/db.php';

    // ดึงชื่อจากฐานข้อมูล
    $memberId = (int)$_SESSION['member_id'];
    $userStmt = $conn->prepare("SELECT fullname FROM members WHERE member_id = ?");

    if ($userStmt) {
        $userStmt->bind_param('i', $memberId);
        $userStmt->execute();
        $userResult = $userStmt->get_result();

        if ($userRow = $userResult->fetch_assoc()) {
            // รวมชื่อ-นามสกุล
            $loggedInUserName = $userRow['fullname'];
        }
        $userStmt->close();
    }
}

// ถ้ายังไม่มีชื่อ ลอง fallback จาก session username
if (empty($loggedInUserName) && isset($_SESSION['username'])) {
    $loggedInUserName = htmlspecialchars($_SESSION['username']);
}

// หากใช้ $_SESSION['user_id'] คุณอาจต้องทำการ Query เพื่อดึงชื่อผู้ใช้จากฐานข้อมูลอีกครั้ง
require_once __DIR__ . '/../db/db.php';

// validate id (must be integer >= 1)
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if ($id === false || $id === null) {
    // กรณีต้องการ redirect กลับไปหน้าหลักสูตร
    header('Location: course.php');
    exit;

    // หรือถ้าต้องการแสดงข้อความ 404 ให้ใช้โค้ดนี้แทน:
    // http_response_code(404);
    // echo '<div class="container mt-5"><h3>ไม่พบหลักสูตร</h3><p>ไม่พบหลักสูตรที่ร้องขอ</p><a href="course.php">กลับไปหน้าหลักสูตร</a></div>';
    // exit;
}

// เตรียม statement ดึงหลักสูตร
$stmt = $conn->prepare("SELECT courses_id, course_name, course_description, image1, image2, image3 FROM courses WHERE courses_id = ?");
if (!$stmt) {
    echo "Database error: " . htmlspecialchars($conn->error);
    exit;
}
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course) {
    header('Location: course.php');
    exit;
}

// ดึงข้อมูลคะแนนเฉลี่ยและจำนวนโหวต
$ratingStmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM course_rating WHERE courses_id = ?");
if (!$ratingStmt) {
    throw new Exception('DB prepare error: ' . $conn->error);
}

$courseId = (int)$course['courses_id'];  // ใช้ courses_id จากตาราง courses
$ratingStmt->bind_param('i', $courseId);
$ratingStmt->execute();
$ratingRes = $ratingStmt->get_result()->fetch_assoc();
$avg_rating = $ratingRes['avg_rating'] ? round((float)$ratingRes['avg_rating'], 1) : 0;
$rating_count = (int)$ratingRes['cnt'];
$ratingStmt->close();

// เตรียมพาธ uploads และ placeholder (user page อยู่ใน /user/ ดังนั้น URL ../uploads/)
$uploadsDir = __DIR__ . '/../uploads/';
$placeholderFilePath = $uploadsDir . 'placeholder.jpg';
if (is_file($placeholderFilePath)) {
    $placeholderSrc = '../uploads/placeholder.jpg';
} else {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 450"><rect width="100%" height="100%" fill="#e9ecef"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#999" font-family="Kanit, Arial, sans-serif" font-size="28">No image</text></svg>';
    $placeholderSrc = 'data:image/svg+xml;base64,' . base64_encode($svg);
}

// รวมรูปจากฐานข้อมูล
$images = array_filter([
    $course['image1'] ?? '',
    $course['image2'] ?? '',
    $course['image3'] ?? ''
]);


// ===== Access Code Config =====


if (!isset($_SESSION['course_access'])) {
    $_SESSION['course_access'] = [];
}

// เช็คว่าคอร์สนี้ผ่านรหัสหรือยัง
// ===== Access Code Config =====

// เช็คว่ามี token ที่ valid หรือไม่
$hasAccess = false;

if (isset($_SESSION['temp_access_token']) && 
    isset($_SESSION['temp_access_time']) && 
    isset($_SESSION['temp_booking_id'])) {
    
    // Token หมดอายุภายใน 24 ชั่วโมง
    $tokenAge = time() - $_SESSION['temp_access_time'];
    if ($tokenAge < 86400) { // 86400 = 24 hours
        $hasAccess = true;
    } else {
        // Token หมดอายุแล้ว - เคลียร์ session
        unset($_SESSION['temp_access_token']);
        unset($_SESSION['temp_access_time']);
        unset($_SESSION['temp_booking_id']);
    }
}

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($course['course_name']); ?> - รายละเอียดหลักสูตร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1f7a6b;
            --primary-light: #e8f5f2;
            --secondary-color: #4a6572;
            --light-gray: #f9fafb;
            --medium-gray: #e5e7eb;
            --dark-gray: #6b7280;
            --border-radius: 12px;
            --box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            --box-shadow-hover: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: #ffffff;
            color: #333;
            line-height: 1.6;
        }

        .container-custom {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Section */
        .page-header {
            padding: 1.5rem 0;
            background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
            border-bottom: 1px solid var(--medium-gray);
            margin-bottom: 2rem;
        }

        .back-link {
            color: var(--dark-gray);
            text-decoration: none;
            font-weight: 400;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-link:hover {
            color: var(--primary-color);
        }

        .course-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-top: 0.5rem;
            font-size: 2.2rem;
            line-height: 1.2;
        }

        /* Course Content */
        .course-content-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        @media (max-width: 992px) {
            .course-content-section {
                grid-template-columns: 1fr;
            }
        }

        /* Image Gallery */
        .image-gallery {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            background-color: white;
            height: fit-content;
        }

        .image-placeholder {
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark-gray);
            font-size: 1.2rem;
        }

        /* New Modern Gallery Styles */
        .modern-gallery {
            display: grid;
            gap: 0.5rem;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease, filter 0.4s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
            filter: brightness(0.9);
        }

        .gallery-item::after {
            content: '\f00e'; /* Font Awesome search-plus icon */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            color: white;
            font-size: 2.5rem;
            background: rgba(31, 122, 107, 0.5);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.4s ease;
        }

        .gallery-item:hover::after {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        /* Grid layouts based on image count */
        .gallery-count-1 { grid-template-columns: 1fr; }
        .gallery-count-2 { grid-template-columns: 1fr 1fr; }
        .gallery-count-3 {
            grid-template-columns: 2fr 1fr;
            grid-template-rows: repeat(2, 225px);
        }
        .gallery-count-3 .gallery-item:first-child {
            grid-row: span 2;
        }

        /* Lightbox Modal */
        .lightbox {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            z-index: 1055;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .lightbox.show {
            opacity: 1;
            visibility: visible;
        }
        .lightbox-content {
            max-width: 90vw;
            max-height: 90vh;
            position: relative;
        }
        .lightbox-content img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            max-width: 85vw;
            max-height: 85vh;
        }
        .lightbox-close, .lightbox-prev, .lightbox-next {
            position: absolute;
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            padding: 0.5rem 1rem;
            z-index: 1056;
        }
        .lightbox-close { top: 10px; right: 15px; }
        .lightbox-prev { top: 50%; left: 10px; transform: translateY(-50%); }
        .lightbox-next { top: 50%; right: 10px; transform: translateY(-50%); }

        /* Course Info Card */
        .course-info-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--box-shadow);
            height: fit-content;
        }

        .course-meta {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--medium-gray);
        }

        .rating-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .rating-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .rating-stars {
            display: flex;
            gap: 3px;
        }

        .rating-star {
            color: #ffc107;
            font-size: 1.2rem;
        }

        .rating-count {
            color: var(--dark-gray);
            font-size: 0.9rem;
        }

        .course-description {
            margin-bottom: 2rem;
        }

        .section-title {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.3rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 3px;
        }

        /* Access Code Section */
        .access-code-section {
            background: var(--light-gray);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--medium-gray);
        }

        .access-code-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .access-code-icon {
            background-color: var(--primary-color);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .access-code-input-container {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .access-code-input {
            flex: 1;
            min-width: 200px;
            padding: 0.8rem 1.2rem;
            border: 2px solid var(--medium-gray);
            border-radius: 8px;
            font-size: 1.2rem;
            text-align: center;
            letter-spacing: 0.5rem;
            transition: border-color 0.2s;
        }

        .access-code-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(31, 122, 107, 0.1);
        }

        .access-code-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            white-space: nowrap;
        }

        .access-code-btn:hover:not(:disabled) {
            background-color: #166457;
        }

        .access-code-btn:disabled {
            background-color: var(--medium-gray);
            cursor: not-allowed;
        }

        .access-success {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #10b981;
            font-weight: 600;
        }

        .access-error {
            color: #ef4444;
            margin-top: 0.5rem;
            display: none;
        }

        /* Comments Section */
        .comments-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 3rem;
        }

        .comment-form-card {
            background: var(--light-gray);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2.5rem;
            border: 1px solid var(--medium-gray);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--secondary-color);
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--medium-gray);
            border-radius: 8px;
            font-family: 'Kanit', sans-serif;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(31, 122, 107, 0.1);
        }

        .char-count {
            text-align: right;
            color: var(--dark-gray);
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .comment-rating-stars {
            display: flex;
            gap: 5px;
            margin-top: 0.5rem;
        }

        .comment-star {
            font-size: 1.8rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }

        .comment-star.active, .comment-star:hover {
            color: #ffc107;
        }

        .submit-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .submit-btn:hover {
            background-color: #166457;
        }

        /* Comments List */
        .comment-item {
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--medium-gray);
        }

        .comment-item:last-child {
            border-bottom: none;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.8rem;
        }

        .comment-author {
            font-weight: 600;
            color: var(--primary-color);
        }

        .comment-date {
            color: var(--dark-gray);
            font-size: 0.9rem;
        }

        .comment-rating {
            margin: 0.5rem 0;
        }

        .comment-text {
            color: #333;
            line-height: 1.7;
        }

        .no-comments {
            text-align: center;
            padding: 3rem;
            color: var(--dark-gray);
        }

        /* Footer */
        .page-footer {
            background-color: var(--light-gray);
            padding: 2rem 0;
            border-top: 1px solid var(--medium-gray);
            margin-top: 3rem;
        }

        /* Utility Classes */
        .text-success {
            color: #10b981;
        }

        .text-error {
            color: #ef4444;
        }

        .d-none {
            display: none;
        }

        .d-flex {
            display: flex;
        }

        .align-items-center {
            align-items: center;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-custom">
            <a href="course.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                กลับไปหน้าหลักสูตร
            </a>
            <h1 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-custom">
        <!-- Course Content Section -->
        <div class="course-content-section">
            <!-- Image Gallery -->
            <div class="modern-gallery gallery-count-<?php echo count($images); ?>">
                <?php if (empty($images)): ?>
                    <div class="image-placeholder">
                        <i class="fas fa-image fa-3x mb-3"></i>
                        <p>ไม่มีภาพประกอบ</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($images as $index => $img): ?>
                        <div class="gallery-item" data-index="<?php echo $index; ?>">
                            <img src="../uploads/<?php echo htmlspecialchars($img); ?>" 
                                 alt="ภาพประกอบ <?php echo $index + 1; ?>"
                                 onerror="this.src='<?php echo $placeholderSrc; ?>'">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Course Info Card -->
            <div class="course-info-card">
                <div class="course-meta">
                    <div class="rating-container">
                        <div class="rating-value"><?php echo $avg_rating > 0 ? number_format($avg_rating, 1) : '0.0'; ?></div>
                        <div class="rating-stars">
                            <?php
                            $rounded = (int) round($avg_rating);
                            for ($i = 1; $i <= 5; $i++):
                                $starClass = $i <= $rounded ? 'fas fa-star rating-star' : 'far fa-star rating-star';
                            ?>
                            <i class="<?php echo $starClass; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="rating-count">(<?php echo $rating_count; ?> คะแนน)</div>
                    </div>
                </div>

                <div class="course-description">
                    <h3 class="section-title">รายละเอียดหลักสูตร</h3>
                    <p><?php echo nl2br(htmlspecialchars($course['course_description'] ?? 'ไม่มีรายละเอียดเพิ่มเติม')); ?></p>
                </div>
            </div>
        </div>

        <!-- Access Code Section -->
        <div class="access-code-section">
            <div class="access-code-header">
                <div class="access-code-icon">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h3 class="section-title">รหัสยืนยันการเข้าร่วมกิจกรรม</h3>
                    <p>กรอกรหัส 4 หลักที่ได้รับจากทางสวนเพื่อแสดงความคิดเห็น</p>
                </div>
            </div>

            <div class="access-code-input-container">
                <?php if ($hasAccess): ?>
                <div class="access-success">
                    <i class="fas fa-check-circle fa-2x"></i>
                    <div>
                        <div>ยืนยันการเข้าร่วมกิจกรรมเรียบร้อยแล้ว</div>
                        <small>คุณสามารถแสดงความคิดเห็นได้ทันที</small>
                    </div>
                </div>
                <?php else: ?>
                <input type="password" 
                       id="accessCodeInput" 
                       class="access-code-input" 
                       maxlength="4" 
                       placeholder="XXXX" 
                       autocomplete="off"
                       aria-label="รหัสยืนยันการเข้าร่วมกิจกรรม">
                <button id="submitAccessCode" class="access-code-btn">ยืนยันรหัส</button>
                <?php endif; ?>
            </div>
            <div id="accessCodeError" class="access-error d-none">รหัสไม่ถูกต้อง กรุณาลองอีกครั้ง</div>
        </div>

        <!-- Comments Section -->
        <div class="comments-section">
            <h3 class="section-title mb-4">ความคิดเห็นและข้อเสนอแนะ</h3>

            <!-- Comment Form -->
            <div id="commentFormContainer" class="comment-form-card" style="<?= $hasAccess ? '' : 'opacity: 0.6; pointer-events: none;' ?>">
                <form id="commentForm">
                    <input type="hidden" name="courses_id" value="<?php echo (int)$course['courses_id']; ?>">
                    
                    <div class="form-group">
                        <label for="userName" class="form-label">ชื่อผู้แสดงความคิดเห็น</label>
                        <input type="text" 
                               class="form-control" 
                               id="userName" 
                               placeholder="กรุณากรอกชื่อ"
                               value="<?php echo htmlspecialchars($loggedInUserName); ?>"
                               <?php echo !empty($loggedInUserName) ? 'readonly' : ''; ?>
                               required>
                    </div>

                    <div class="form-group">
                        <label for="commentText" class="form-label">ความคิดเห็น</label>
                        <textarea class="form-control" 
                                  id="commentText" 
                                  rows="4" 
                                  placeholder="กรุณากรอกความคิดเห็นของคุณเกี่ยวกับหลักสูตรนี้"
                                  maxlength="1000" 
                                  required></textarea>
                        <div class="char-count"><span id="charCount">0</span>/1000</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">ให้คะแนนความพึงพอใจ</label>
                        <div class="comment-rating-stars" id="commentStars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="far fa-star comment-star" data-value="<?php echo $i; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" id="commentRating" name="rating" value="0">
                        <small id="commentRatingText" class="text-muted">ยังไม่ได้ให้คะแนน</small>
                    </div>

                    <button type="submit" class="submit-btn">ส่งความคิดเห็น</button>
                    
                    <div id="commentFeedback" class="text-success mt-3 d-none">
                        <i class="fas fa-check-circle"></i> ขอบคุณสำหรับความคิดเห็นของคุณ
                    </div>
                    <div id="commentError" class="text-error mt-3 d-none"></div>
                </form>
            </div>

            <!-- Comments List -->
            <div id="commentsList">
                <?php if ($commentsResult->num_rows === 0): ?>
                <div class="no-comments">
                    <i class="far fa-comment-dots fa-3x mb-3"></i>
                    <h4>ยังไม่มีความคิดเห็น</h4>
                    <p>เป็นคนแรกที่แสดงความคิดเห็นเกี่ยวกับหลักสูตรนี้</p>
                </div>
                <?php else: ?>
                    <?php while ($comment = $commentsResult->fetch_assoc()): ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <div class="comment-author">คุณ<?php echo htmlspecialchars($comment['name'] ?? 'ผู้เข้าร่วมกิจกรรมอบรม'); ?></div>
                            <div class="comment-date"><?php echo date('j M Y H:i', strtotime($comment['created_at'])); ?></div>
                        </div>
                        
                        <?php if ((int)($comment['rating'] ?? 0) > 0): ?>
                        <div class="comment-rating">
                            <?php 
                            $rating = (int)$comment['rating'];
                            for ($i = 1; $i <= 5; $i++):
                                $starClass = $i <= $rating ? 'fas fa-star' : 'far fa-star';
                            ?>
                            <i class="<?php echo $starClass; ?>" style="color: #ffc107;"></i>
                            <?php endfor; ?>
                            <small class="text-muted">(<?php echo $rating; ?>/5)</small>
                        </div>
                        <?php endif; ?>
                        
                        <div class="comment-text"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Lightbox Modal -->
    <div class="lightbox" id="lightbox">
        <button class="lightbox-close">&times;</button>
        <button class="lightbox-prev">&lt;</button>
        <button class="lightbox-next">&gt;</button>
        <div class="lightbox-content">
            <img src="" alt="Enlarged image" id="lightboxImage">
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image Gallery Functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Image thumbnail selection
            const commentText = document.getElementById('commentText');
            const charCount = document.getElementById('charCount');
            
            if (commentText && charCount) {
                commentText.addEventListener('input', function() {
                    charCount.textContent = this.value.length;
                });
            }

            // New Lightbox Functionality
            const galleryItems = document.querySelectorAll('.gallery-item');
            const lightbox = document.getElementById('lightbox');
            const lightboxImage = document.getElementById('lightboxImage');
            const lightboxClose = document.querySelector('.lightbox-close');
            const lightboxPrev = document.querySelector('.lightbox-prev');
            const lightboxNext = document.querySelector('.lightbox-next');
            const images = <?php echo json_encode(array_values($images)); ?>;
            let currentIndex = 0;

            function showImage(index) {
                if (index >= 0 && index < images.length) {
                    currentIndex = index;
                    lightboxImage.src = '../uploads/' + images[currentIndex];
                    lightbox.classList.add('show');
                }
            }

            function closeLightbox() {
                lightbox.classList.remove('show');
            }

            galleryItems.forEach(item => {
                item.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    showImage(index);
                });
            });

            if (lightboxClose) {
                lightboxClose.addEventListener('click', closeLightbox);
            }

            if (lightbox) {
                lightbox.addEventListener('click', function(e) {
                    if (e.target === lightbox) {
                        closeLightbox();
                    }
                });
            }

            if (lightboxPrev) {
                lightboxPrev.addEventListener('click', () => {
                    showImage((currentIndex - 1 + images.length) % images.length);
                });
            }

            if (lightboxNext) {
                lightboxNext.addEventListener('click', () => {
                    showImage((currentIndex + 1) % images.length);
                });
            }

            // Comment rating stars
            const commentStars = document.querySelectorAll('#commentStars .comment-star');
            const commentRatingInput = document.getElementById('commentRating');
            const commentRatingText = document.getElementById('commentRatingText');
            
            if (commentStars.length > 0) {
                commentStars.forEach(star => {
                    star.addEventListener('mouseenter', function() {
                        const value = parseInt(this.getAttribute('data-value'));
                        updateCommentStars(value, false);
                    });
                    
                    star.addEventListener('click', function() {
                        const value = parseInt(this.getAttribute('data-value'));
                        commentRatingInput.value = value;
                        updateCommentStars(value, true);
                        commentRatingText.textContent = value + ' / 5';
                    });
                });
                
                // Reset stars on mouse leave if no rating selected
                document.getElementById('commentStars').addEventListener('mouseleave', function() {
                    const currentRating = parseInt(commentRatingInput.value) || 0;
                    updateCommentStars(currentRating, true);
                });
            }
            
            function updateCommentStars(rating, permanent) {
                commentStars.forEach(star => {
                    const value = parseInt(star.getAttribute('data-value'));
                    
                    if (value <= rating) {
                        star.classList.remove('far');
                        star.classList.add('fas');
                        if (permanent) {
                            star.style.color = '#ffc107';
                        }
                    } else {
                        star.classList.remove('fas');
                        star.classList.add('far');
                        if (permanent) {
                            star.style.color = '#ddd';
                        }
                    }
                });
            }
            
            // Load saved user name for guests
            const userNameInput = document.getElementById('userName');
            const loggedInName = '<?php echo $loggedInUserName; ?>';
            
            if (!loggedInName && userNameInput) {
                const savedName = localStorage.getItem('courseCommentUserName');
                if (savedName) {
                    userNameInput.value = savedName;
                }
            }
            
            // Access code functionality
            const accessCodeInput = document.getElementById('accessCodeInput');
            const submitAccessCodeBtn = document.getElementById('submitAccessCode');
            const accessCodeError = document.getElementById('accessCodeError');
            
            if (accessCodeInput && submitAccessCodeBtn) {
                // Format input as user types (4 digits only)
                accessCodeInput.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '').slice(0, 4);
                    accessCodeError.classList.add('d-none');
                });
                
                // Submit on Enter
                accessCodeInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        submitAccessCodeBtn.click();
                    }
                });
                
                // Submit button click
                submitAccessCodeBtn.addEventListener('click', async function() {
                    const code = accessCodeInput.value.trim();
                    
                    if (!code || code.length !== 4) {
                        accessCodeError.textContent = 'กรุณากรอกรหัส 4 หลักให้ถูกต้อง';
                        accessCodeError.classList.remove('d-none');
                        return;
                    }
                    
                    // Disable button and show loading
                    const originalText = this.textContent;
                    this.textContent = 'กำลังตรวจสอบ...';
                    this.disabled = true;
                    
                    try {
                        const response = await fetch('verify_access_code.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ code: code })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            // Success - reload page to update UI
                            location.reload();
                        } else {
                            // Error
                            accessCodeError.textContent = result.error || 'รหัสไม่ถูกต้อง';
                            accessCodeError.classList.remove('d-none');
                            accessCodeInput.value = '';
                            accessCodeInput.focus();
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        accessCodeError.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
                        accessCodeError.classList.remove('d-none');
                    } finally {
                        // Restore button
                        this.textContent = originalText;
                        this.disabled = false;
                    }
                });
            }
            
            // Comment form submission
            const commentForm = document.getElementById('commentForm');
            const commentFeedback = document.getElementById('commentFeedback');
            const commentError = document.getElementById('commentError');
            
            if (commentForm) {
                commentForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const courseId = this.querySelector('input[name="courses_id"]').value;
                    const userName = document.getElementById('userName').value.trim();
                    const commentText = document.getElementById('commentText').value.trim();
                    const rating = parseInt(document.getElementById('commentRating').value || 0, 10);
                    
                    // Validation
                    if (!userName || !commentText) {
                        showCommentError('กรุณากรอกข้อมูลให้ครบถ้วน');
                        return;
                    }
                    
                    // Hide previous messages
                    commentFeedback.classList.add('d-none');
                    commentError.classList.add('d-none');
                    
                    try {
                        // If rating is provided, submit it first
                        let guestIdentifier = null;
                        
                        if (rating > 0) {
                            const ratingResponse = await fetch('rate_course.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    courses_id: parseInt(courseId, 10),
                                    rating: rating
                                })
                            });
                            
                            const ratingResult = await ratingResponse.json();
                            
                            if (ratingResult && ratingResult.success) {
                                guestIdentifier = ratingResult.guest_identifier;
                                
                                // Update overall rating display
                                if (typeof ratingResult.avg !== 'undefined') {
                                    document.querySelector('.rating-value').textContent = 
                                        parseFloat(ratingResult.avg).toFixed(1);
                                }
                                if (typeof ratingResult.count !== 'undefined') {
                                    document.querySelector('.rating-count').textContent = 
                                        '(' + ratingResult.count + ' คะแนน)';
                                }
                            }
                        }
                        
                        // Submit comment
                        const commentData = {
                            courses_id: parseInt(courseId, 10),
                            user_name: userName,
                            comment_text: commentText
                        };
                        
                        if (guestIdentifier) {
                            commentData.guest_identifier = guestIdentifier;
                        }
                        
                        const commentResponse = await fetch('save_comment.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(commentData)
                        });
                        
                        const commentResult = await commentResponse.json();
                        
                        if (commentResult && commentResult.success) {
                            // Save user name for guests
                            if (!loggedInName) {
                                localStorage.setItem('courseCommentUserName', userName);
                            }
                            
                            // Add new comment to the list
                            addNewCommentToUI(userName, commentText, rating);
                            
                            // Reset form
                            commentForm.reset();
                            document.getElementById('charCount').textContent = '0';
                            document.getElementById('commentRating').value = '0';
                            commentRatingText.textContent = 'ยังไม่ได้ให้คะแนน';
                            updateCommentStars(0, true);
                            
                            // Keep user name if logged in
                            if (loggedInName) {
                                document.getElementById('userName').value = loggedInName;
                            }
                            
                            // Show success message
                            showCommentSuccess();
                        } else {
                            showCommentError(commentResult.error || 'เกิดข้อผิดพลาดในการส่งความคิดเห็น');
                        }
                    } catch (error) {
                        console.error('Submit error:', error);
                        showCommentError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                    }
                });
            }
            
            function showCommentError(message) {
                commentError.textContent = message;
                commentError.classList.remove('d-none');
                commentFeedback.classList.add('d-none');
            }
            
            function showCommentSuccess() {
                commentFeedback.classList.remove('d-none');
                commentError.classList.add('d-none');
                
                // Hide success message after 3 seconds
                setTimeout(() => {
                    commentFeedback.classList.add('d-none');
                }, 3000);
            }
            
            function addNewCommentToUI(userName, commentText, rating) {
                // Remove "no comments" message if exists
                const noComments = document.querySelector('.no-comments');
                if (noComments) {
                    noComments.remove();
                }
                
                // Create new comment element
                const commentsList = document.getElementById('commentsList');
                const now = new Date();
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const dateStr = now.getDate() + ' ' + monthNames[now.getMonth()] + ' ' + now.getFullYear() + ' ' +
                               String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                
                const newComment = document.createElement('div');
                newComment.className = 'comment-item';
                
                let ratingHTML = '';
                if (rating > 0) {
                    ratingHTML = '<div class="comment-rating">';
                    for (let i = 1; i <= 5; i++) {
                        ratingHTML += i <= rating ? 
                            '<i class="fas fa-star" style="color: #ffc107;"></i>' : 
                            '<i class="far fa-star" style="color: #ffc107;"></i>';
                    }
                    ratingHTML += '<small class="text-muted">(' + rating + '/5)</small></div>';
                }
                
                newComment.innerHTML = `
                    <div class="comment-header">
                        <div class="comment-author">คุณ${userName}</div>
                        <div class="comment-date">${dateStr}</div>
                    </div>
                    ${ratingHTML}
                    <div class="comment-text">${commentText.replace(/\n/g, '<br>')}</div>
                `;
                
                // Add to the top of comments list
                commentsList.insertBefore(newComment, commentsList.firstChild);
            }
        });
    </script>
</body>

</html>