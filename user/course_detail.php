<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['member_id']) && isset($member_status) && (int)$member_status === 0) {
    session_unset();
    session_destroy();
    header('Location: index.php?login_error=disabled');
    exit;
}

$loggedInUserName = '';

if (isset($_SESSION['member_id']) && !empty($_SESSION['member_id'])) {
    require_once __DIR__ . '/../db/db.php';
    $memberId = (int)$_SESSION['member_id'];
    $userStmt = $conn->prepare("SELECT fullname FROM members WHERE member_id = ?");
    if ($userStmt) {
        $userStmt->bind_param('i', $memberId);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        if ($userRow = $userResult->fetch_assoc()) {
            $loggedInUserName = $userRow['fullname'];
        }
        $userStmt->close();
    }
}

if (empty($loggedInUserName) && isset($_SESSION['username'])) {
    $loggedInUserName = htmlspecialchars($_SESSION['username']);
}

require_once __DIR__ . '/../db/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if ($id === false || $id === null) {
    header('Location: course.php');
    exit;
}

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

$ratingStmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM course_rating WHERE courses_id = ?");
if (!$ratingStmt) {
    throw new Exception('DB prepare error: ' . $conn->error);
}

$courseId = (int)$course['courses_id'];
$ratingStmt->bind_param('i', $courseId);
$ratingStmt->execute();
$ratingRes = $ratingStmt->get_result()->fetch_assoc();
$avg_rating = $ratingRes['avg_rating'] ? round((float)$ratingRes['avg_rating'], 1) : 0;
$rating_count = (int)$ratingRes['cnt'];
$ratingStmt->close();

$uploadsDir = __DIR__ . '/../uploads/';
$placeholderFilePath = $uploadsDir . 'placeholder.jpg';
if (is_file($placeholderFilePath)) {
    $placeholderSrc = '../uploads/placeholder.jpg';
} else {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 450"><rect width="100%" height="100%" fill="#e9ecef"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#999" font-family="Kanit, Arial, sans-serif" font-size="28">No image</text></svg>';
    $placeholderSrc = 'data:image/svg+xml;base64,' . base64_encode($svg);
}

$images = array_filter([
    $course['image1'] ?? '',
    $course['image2'] ?? '',
    $course['image3'] ?? ''
]);

if (!isset($_SESSION['course_access'])) {
    $_SESSION['course_access'] = [];
}

$hasAccess = false;

if (
    isset($_SESSION['temp_access_token']) &&
    isset($_SESSION['temp_access_time']) &&
    isset($_SESSION['temp_booking_id'])
) {
    $tokenAge = time() - $_SESSION['temp_access_time'];
    if ($tokenAge < 86400) {
        $hasAccess = true;
    } else {
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
    <link rel="apple-touch-icon" sizes="180x180" href="../logo/logo_01.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../logo/logo_01.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($course['course_name']); ?> สวนลุงเผือก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #016A70;
            --secondary-color: #2ad3bc;
            --primary-dark: #014e53;
            --primary-light: #e8f8f6;
            --text-dark: #0f172a;
            --text-gray: #64748b;
            --bg-color: #f4f7fb;
            --white: #ffffff;
            --border-color: #dbe4ea;
            --gradient-primary: linear-gradient(135deg, #016A70 0%, #02939c 100%);
            --shadow-sm: 0 4px 12px rgba(0, 0, 0, .04);
            --shadow-md: 0 12px 30px rgba(1, 106, 112, .08);
            --shadow-lg: 0 25px 60px rgba(1, 106, 112, .14);
            --radius-lg: 28px;
            --radius-md: 18px;
            --transition: .3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(180deg, #f8fbfc 0%, #eef4f6 100%);
            color: var(--text-dark);
            min-height: 100vh;
            line-height: 1.7;
        }

        /* ========== PAGE HEADER ========== */
        .page-header {
            position: relative;
            overflow: hidden;
            padding: 1rem  ;
            background: linear-gradient(135deg, #016A70 0%, #028c94 100%);
        }

        .page-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(255,255,255,.18), transparent 35%),
                radial-gradient(circle at bottom left, rgba(255,255,255,.08), transparent 30%);
        }

        .back-link {
            color: white;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.18);
            transition: var(--transition);
        }

        .back-link:hover {
            color: white;
            transform: translateY(-2px);
            background: rgba(255,255,255,.2);
        }

        .course-title {
            color: white;
            font-weight: 800;
            margin-top: 20px;
            font-size: clamp(1.8rem, 4vw, 3rem);
            line-height: 1.2;
            max-width: 900px;
            text-shadow: 0 4px 20px rgba(0,0,0,.2);
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            padding: 2.5rem 0 3rem;
        }

        /* ========== TOP ROW: Gallery + Sidebar ========== */
        .top-row {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 28px;
            align-items: start;
            margin-bottom: 28px;
        }

        /* ========== GALLERY ========== */
        .gallery-sticky {
            position: sticky;
            top: 24px;
        }

        .modern-gallery {
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            background: white;
            transition: var(--transition);
        }

        .modern-gallery:hover {
            transform: translateY(-4px);
            box-shadow: 0 30px 70px rgba(1, 106, 112, .18);
        }

        .main-image-container {
            aspect-ratio: 16/10;
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }

        .main-image-container::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,.28), transparent 45%);
            pointer-events: none;
        }

        .main-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .7s ease;
        }

        .modern-gallery:hover .main-image {
            transform: scale(1.02);
        }

        .thumbnail-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            background: #f0f0f0;
        }

        .thumbnail-item {
            aspect-ratio: 4/3;
            overflow: hidden;
            cursor: pointer;
            position: relative;
            transition: var(--transition);
        }

        .thumbnail-item.active::before {
            content: '';
            position: absolute;
            inset: 0;
            border: 3px solid var(--primary-color);
            z-index: 1;
            pointer-events: none;
        }

        .thumbnail-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .thumbnail-item:hover img { transform: scale(1.1); }

        .view-all-btn {
            position: absolute;
            bottom: 18px;
            right: 18px;
            background: rgba(0,0,0,.65);
            color: white;
            border: none;
            padding: 9px 18px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(5px);
            transition: var(--transition);
            z-index: 3;
            font-family: 'Kanit', sans-serif;
            font-size: 0.9rem;
        }

        .view-all-btn:hover {
            background: rgba(0,0,0,.85);
            transform: translateY(-2px);
        }

        /* ========== SIDEBAR (right of gallery) ========== */
        .sidebar-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Course Info Card */
        .course-info-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 28px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255,255,255,.7);
            position: relative;
            overflow: hidden;
        }

        .course-info-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient-primary);
        }

        .rating-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid #f0f5f5;
        }

        .rating-value {
            font-size: 2rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .rating-stars { display: flex; gap: 4px; }
        .rating-star { font-size: 1.2rem; color: #FFD700; }
        .rating-count { color: var(--text-gray); font-size: 0.9rem; }

        .section-title {
            color: var(--text-dark);
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 0.75rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 36px; height: 3px;
            background: var(--gradient-primary);
            border-radius: 3px;
        }

        .course-description {
            color: var(--text-dark);
            line-height: 1.7;
            font-size: 0.95rem;
        }

        /* Access Code Card */
        .access-code-card {
            background: linear-gradient(135deg, #ffffff, #f2fffd);
            border-radius: var(--radius-lg);
            padding: 28px;
            box-shadow: var(--shadow-md);
            border: 2px solid #d7f7f2;
            transition: var(--transition);
        }

        .access-code-card:hover { border-color: var(--primary-color); }

        .access-code-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .access-code-icon {
            width: 48px; height: 48px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.2rem;
            flex-shrink: 0;
        }

        .access-code-form {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .access-code-input {
            flex: 1;
            padding: 0.85rem 0.3rem;
            border: 2px solid #e2ecec;
            border-radius: var(--radius-md);
            font-size: 1.2rem;
            text-align: center;
            letter-spacing: 0.5rem;
            transition: var(--transition);
            font-family: 'Kanit', sans-serif;
        }

        .access-code-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(1,106,112,.1);
        }

        .access-code-btn {
            background: var(--gradient-primary);
            color: white; border: none;
            padding: 0.85rem 1.3rem;
            border-radius: var(--radius-md);
            font-weight: 600; cursor: pointer;
            transition: var(--transition);
            display: flex; align-items: center; gap: 1px;
            white-space: nowrap;
            font-family: 'Kanit', sans-serif;
        }

        .access-code-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(1,106,112,.25);
        }

        .access-code-btn:disabled { background: #aaa; cursor: not-allowed; }

        .access-success {
            display: flex; align-items: center; gap: 1rem;
            color: #10B981; font-weight: 600;
            padding: 1rem;
            background: rgba(16,185,129,.1);
            border-radius: var(--radius-md);
            border: 1px solid #10B981;
        }

        .access-error {
            color: #EF4444;
            margin-top: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(239,68,68,.1);
            border-radius: var(--radius-md);
        }

        /* ========== COMMENTS SECTION (full-width bottom) ========== */
        .comments-section {
            background: white;
            border-radius: var(--radius-lg);
            padding: 36px;
            box-shadow: var(--shadow-md);
        }

        .comments-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            padding-bottom: 18px;
            border-bottom: 2px solid #f0f5f5;
        }

        .comments-section-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .comments-section-title i {
            width: 40px; height: 40px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1rem;
        }

        .comments-count-badge {
            background: var(--primary-light);
            color: var(--primary-color);
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* 2-column layout for comment form + list */
        .comments-inner {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 32px;
            align-items: start;
        }

        /* Comment Form */
        .comment-form-panel {
            position: sticky;
            top: 24px;
            background: linear-gradient(135deg, #f8fffd, #f0faf9);
            border-radius: var(--radius-md);
            padding: 24px;
            border: 1px solid #d7f0ec;
        }

        .comment-form-panel-title {
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--primary-color);
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group { margin-bottom: 1.2rem; }

        .form-label {
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text-dark);
            display: block;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 2px solid #e2ecec;
            border-radius: var(--radius-md);
            font-family: 'Kanit', sans-serif;
            font-size: 0.95rem;
            transition: var(--transition);
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(1,106,112,.1);
        }

        .char-count {
            text-align: right;
            color: var(--text-gray);
            font-size: 0.8rem;
            margin-top: 4px;
        }

        .comment-rating-stars {
            display: flex;
            gap: 6px;
            margin-top: 6px;
        }

        .comment-star {
            font-size: 1.8rem;
            color: #E5E7EB;
            cursor: pointer;
            transition: var(--transition);
        }

        .comment-star:hover,
        .comment-star.active {
            color: #FFD700;
            transform: scale(1.2);
        }

        .submit-btn {
            background: var(--gradient-primary);
            color: white; border: none;
            padding: 0.9rem 1.5rem;
            border-radius: var(--radius-md);
            font-weight: 600; cursor: pointer;
            transition: var(--transition);
            width: 100%;
            font-family: 'Kanit', sans-serif;
            font-size: 1rem;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }

        .submit-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(1,106,112,.2);
        }

        .submit-btn:disabled { opacity: 0.5; cursor: not-allowed; }

        .comment-feedback {
            margin-top: 1rem;
            padding: 0.85rem 1rem;
            border-radius: var(--radius-md);
            display: none;
        }

        .comment-success {
            background: rgba(16,185,129,.1);
            color: #10B981;
            border: 1px solid #10B981;
        }

        .comment-error {
            background: rgba(239,68,68,.1);
            color: #EF4444;
            border: 1px solid #EF4444;
        }

        /* Comments List */
        .comments-list-panel { min-height: 200px; }

        .comments-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        .comment-item {
            background: white;
            border-radius: var(--radius-md);
            padding: 18px 20px;
            border: 1px solid #eaf2f2;
            transition: var(--transition);
        }

        .comment-item:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            gap: 8px;
        }

        .comment-author {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1rem;
        }

        .comment-date {
            color: var(--text-gray);
            font-size: 0.8rem;
            white-space: nowrap;
        }

        .comment-rating {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
        }

        .comment-stars { display: flex; gap: 2px; }
        .comment-star-rating { font-size: 0.9rem; color: #FFD700; }

        .comment-text {
            color: var(--text-dark);
            line-height: 1.6;
            font-size: 0.95rem;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .comment-text.expanded {
            display: block;
            -webkit-line-clamp: unset;
        }

        .read-more {
            color: var(--primary-color);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
            margin-top: 4px;
        }

        .read-more:hover { text-decoration: underline; }

        .no-comments {
            text-align: center;
            padding: 3rem;
            color: var(--text-gray);
            grid-column: 1/-1;
        }

        /* Locked overlay for comment form */
        .form-locked-overlay {
            opacity: 0.6;
            pointer-events: none;
            position: relative;
        }

        /* ========== LIGHTBOX ========== */
        .lightbox {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.95);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .lightbox.show { display: flex; }

        .lightbox-content {
            max-width: 90vw;
            max-height: 90vh;
            position: relative;
        }

        .lightbox-img {
            max-width: 100%;
            max-height: 85vh;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 2rem;
            transform: translateY(-50%);
        }

        .lightbox-btn {
            background: rgba(255,255,255,.2);
            color: white; border: none;
            width: 56px; height: 56px;
            border-radius: 50%;
            font-size: 1.4rem; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: var(--transition);
            backdrop-filter: blur(5px);
        }

        .lightbox-btn:hover {
            background: rgba(255,255,255,.3);
            transform: scale(1.1);
        }

        .lightbox-close {
            position: absolute;
            top: 1.5rem; right: 1.5rem;
            background: rgba(255,255,255,.2);
            color: white; border: none;
            width: 46px; height: 46px;
            border-radius: 50%;
            font-size: 1.4rem; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: var(--transition);
            backdrop-filter: blur(5px);
        }

        .lightbox-close:hover {
            background: rgba(255,255,255,.3);
            transform: rotate(90deg);
        }

        .lightbox-counter {
            position: absolute;
            bottom: 1.5rem; left: 50%;
            transform: translateX(-50%);
            color: white;
            background: rgba(0,0,0,.5);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            backdrop-filter: blur(5px);
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 1100px) {
            .top-row {
                grid-template-columns: 1fr 360px;
            }

            .comments-inner {
                grid-template-columns: 320px 1fr;
            }
        }

        @media (max-width: 992px) {
            .top-row {
                grid-template-columns: 1fr;
            }

            .gallery-sticky { position: static; }

            .comments-inner {
                grid-template-columns: 1fr;
            }

            .comment-form-panel { position: static; }
        }

        @media (max-width: 768px) {
            .page-header { padding: 1.5rem 0 2rem; }
            .course-title { font-size: 1.6rem; }
            .thumbnail-grid { grid-template-columns: repeat(2, 1fr); }
            .comments-section { padding: 24px 18px; }
            .comments-list { grid-template-columns: 1fr; }
            .lightbox-nav { padding: 0 1rem; }
            .access-code-form { flex-direction: column; }
            .access-code-btn { width: 100%; justify-content: center; }
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <?php include __DIR__ . '/fb_chat_button.php'; ?>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <a href="course.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                กลับไปหน้ากิจกรรม
            </a>
            <h1 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">

            <!-- ===== TOP ROW: Gallery (left) + Info/Access (right) ===== -->
            <div class="top-row">

                <!-- Gallery -->
                <div class="gallery-sticky">
                    <div class="modern-gallery">
                        <div class="main-image-container">
                            <img id="mainImage"
                                src="<?php echo !empty($images) ? '../uploads/' . htmlspecialchars($images[0]) : $placeholderSrc; ?>"
                                alt="<?php echo htmlspecialchars($course['course_name']); ?>"
                                class="main-image"
                                onerror="this.src='<?php echo $placeholderSrc; ?>'">

                            <?php if (count($images) > 0): ?>
                                <button class="view-all-btn" id="viewAllBtn">
                                    <i class="fas fa-expand"></i>
                                    ดูภาพทั้งหมด (<?php echo count($images); ?>)
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if (count($images) > 1): ?>
                            <div class="thumbnail-grid">
                                <?php foreach ($images as $index => $img): ?>
                                    <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>"
                                        data-index="<?php echo $index; ?>">
                                        <img src="../uploads/<?php echo htmlspecialchars($img); ?>"
                                            alt="ภาพประกอบ <?php echo $index + 1; ?>"
                                            onerror="this.src='<?php echo $placeholderSrc; ?>'">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sidebar: Info + Access Code -->
                <div class="sidebar-column">

                    <!-- Course Info Card -->
                    <div class="course-info-card">
                        <div class="rating-container">
                            <div class="rating-value"><?php echo number_format($avg_rating, 1); ?></div>
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= round($avg_rating)): ?>
                                        <i class="fas fa-star rating-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star rating-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <div class="rating-count">(<?php echo $rating_count; ?> คะแนน)</div>
                        </div>

                        <h3 class="section-title">รายละเอียดกิจกรรม</h3>
                        <div class="course-description">
                            <?php echo nl2br(htmlspecialchars($course['course_description'] ?? 'ไม่มีรายละเอียดเพิ่มเติม')); ?>
                        </div>
                    </div>

                    <!-- Access Code Card -->
                    <div class="access-code-card" id="access-code-section">
                        <div class="access-code-header">
                            <div class="access-code-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <div>
                                <h3 class="section-title mb-1">รหัสยืนยันการเข้าร่วมกิจกรรม</h3>
                                <p class="text-muted" style="font-size:0.88rem; line-height:1.5;">กรอกรหัส 4 หลักที่ได้รับจากทางสวนเพื่อแสดงความคิดเห็น</p>
                            </div>
                        </div>

                        <div id="accessCodeContainer">
                            <?php if ($hasAccess): ?>
                                <div class="access-success">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                    <div>
                                        <div>ยืนยันการเข้าร่วมกิจกรรมเรียบร้อยแล้ว</div>
                                        <small>คุณสามารถแสดงความคิดเห็นได้ทันที</small>
                                    </div>
                                </div>
                            <?php else: ?>
                                <form class="access-code-form" id="accessCodeForm">
                                    <input type="password"
                                        id="accessCodeInput"
                                        class="access-code-input"
                                        maxlength="4"
                                        placeholder="XXXX"
                                        autocomplete="off"
                                        required>
                                    <button type="submit" class="access-code-btn" id="submitAccessCode">
                                        <i class="fas fa-check"></i>
                                        ยืนยันรหัส
                                    </button>
                                </form>
                                <div id="accessCodeError" class="access-error d-none"></div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div><!-- /sidebar-column -->
            </div><!-- /top-row -->

            <!-- ===== BOTTOM: Full-width Comments Section ===== -->
            <div class="comments-section">

                <div class="comments-section-header">
                    <div class="comments-section-title">
                        <i class="fas fa-comments" style="width:40px;height:40px;background:linear-gradient(135deg,#016A70,#02939c);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:1rem;"></i>
                        ความคิดเห็นและข้อเสนอแนะ
                    </div>
                    <?php
                    // Count comments
                    $cntStmt = $conn->prepare("SELECT COUNT(*) AS total FROM course_comments WHERE courses_id = ?");
                    $cntStmt->bind_param('i', $courseId);
                    $cntStmt->execute();
                    $cntRow = $cntStmt->get_result()->fetch_assoc();
                    $totalComments = (int)$cntRow['total'];
                    $cntStmt->close();
                    ?>
                    <span class="comments-count-badge"><?php echo $totalComments; ?> ความคิดเห็น</span>
                </div>

                <div class="comments-inner">

                    <!-- Left: Comment Form -->
                    <div id="commentFormContainer"
                         class="comment-form-panel <?php echo $hasAccess ? '' : 'form-locked-overlay'; ?>">

                        <?php if (!$hasAccess): ?>
                            <div style="text-align:center; padding: 1rem 0 1.2rem; color: var(--text-gray);">
                                <i class="fas fa-lock fa-2x mb-2" style="color:#ccc;"></i>
                                <p style="font-size:0.9rem;">กรอกรหัสยืนยันด้านบนก่อนเพื่อแสดงความคิดเห็น</p>
                            </div>
                        <?php endif; ?>

                        <div class="comment-form-panel-title">
                            <i class="fas fa-pen-to-square" style="color:var(--primary-color);"></i>
                            เขียนความคิดเห็น
                        </div>

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
                                    rows="5"
                                    placeholder="กรุณากรอกความคิดเห็นของคุณเกี่ยวกับกิจกรรมนี้"
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

                            <button type="submit" class="submit-btn">
                                <i class="fas fa-paper-plane"></i>
                                ส่งความคิดเห็น
                            </button>
                        </form>

                        <div id="commentFeedback" class="comment-feedback comment-success d-none">
                            <i class="fas fa-check-circle"></i> ขอบคุณสำหรับความคิดเห็นของคุณ
                        </div>
                        <div id="commentError" class="comment-feedback comment-error d-none"></div>
                    </div>

                    <!-- Right: Comments List -->
                    <div class="comments-list-panel">
                        <div id="commentsList" class="comments-list">
                            <?php
                            $commentsQuery = "SELECT
                                                c.*,
                                                (
                                                    SELECT cr.rating
                                                    FROM course_rating cr
                                                    WHERE cr.courses_id = c.courses_id
                                                      AND (
                                                        (c.member_id IS NOT NULL AND cr.member_id = c.member_id)
                                                        OR
                                                        (c.member_id IS NULL AND c.guest_identifier <> '' AND cr.guest_identifier = c.guest_identifier)
                                                      )
                                                    ORDER BY cr.created_at DESC, cr.rating_id DESC
                                                    LIMIT 1
                                                ) AS rating
                                              FROM course_comments c
                                              WHERE c.courses_id = ?
                                              ORDER BY c.created_at DESC
                                              LIMIT 10";
                            $commentsStmt = $conn->prepare($commentsQuery);
                            if ($commentsStmt) {
                                $commentsStmt->bind_param('i', $courseId);
                                $commentsStmt->execute();
                                $commentsResult = $commentsStmt->get_result();

                                if ($commentsResult->num_rows === 0): ?>
                                    <div class="no-comments">
                                        <i class="far fa-comment-dots fa-3x mb-3"></i>
                                        <h4>ยังไม่มีความคิดเห็น</h4>
                                        <p>เป็นคนแรกที่แสดงความคิดเห็นเกี่ยวกับกิจกรรมนี้</p>
                                    </div>
                                    <?php else:
                                    while ($comment = $commentsResult->fetch_assoc()): ?>
                                        <div class="comment-item">
                                            <div class="comment-header">
                                                <div class="comment-author">
                                                    คุณ<?php echo htmlspecialchars($comment['name'] ?? 'ผู้เข้าร่วมกิจกรรม'); ?>
                                                </div>
                                                <div class="comment-date">
                                                    <?php echo date('j M Y H:i', strtotime($comment['created_at'])); ?>
                                                </div>
                                            </div>

                                            <?php if (!empty($comment['rating'])): ?>
                                                <div class="comment-rating">
                                                    <div class="comment-stars">
                                                        <?php
                                                        $rating = (int)$comment['rating'];
                                                        for ($i = 1; $i <= 5; $i++):
                                                            $starClass = $i <= $rating ? 'fas fa-star' : 'far fa-star';
                                                        ?>
                                                            <i class="<?php echo $starClass; ?> comment-star-rating"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <small class="text-muted">(<?php echo $rating; ?>/5)</small>
                                                </div>
                                            <?php endif; ?>

                                            <div class="comment-text-wrapper">
                                                <div class="comment-text">
                                                    <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
                                                </div>
                                                <span class="read-more">อ่านเพิ่มเติม</span>
                                            </div>
                                        </div>
                                    <?php endwhile;
                                    endif;
                                    $commentsStmt->close();
                                }
                            ?>
                        </div>
                    </div>

                </div><!-- /comments-inner -->
            </div><!-- /comments-section -->

        </div>
    </div>

    <!-- Lightbox Modal -->
    <div class="lightbox" id="lightbox">
        <button class="lightbox-close" id="lightboxClose">
            <i class="fas fa-times"></i>
        </button>
        <div class="lightbox-nav">
            <button class="lightbox-btn" id="lightboxPrev">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="lightbox-btn" id="lightboxNext">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <div class="lightbox-content">
            <img id="lightboxImage" src="" alt="" class="lightbox-img">
        </div>
        <div class="lightbox-counter" id="lightboxCounter">1 / <?php echo count($images); ?></div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const images = <?php echo json_encode($images); ?>;
            const placeholderSrc = '<?php echo $placeholderSrc; ?>';

            const mainImage = document.getElementById('mainImage');
            const thumbnails = document.querySelectorAll('.thumbnail-item');
            const lightbox = document.getElementById('lightbox');
            const lightboxImage = document.getElementById('lightboxImage');
            const lightboxClose = document.getElementById('lightboxClose');
            const lightboxPrev = document.getElementById('lightboxPrev');
            const lightboxNext = document.getElementById('lightboxNext');
            const lightboxCounter = document.getElementById('lightboxCounter');
            const viewAllBtn = document.getElementById('viewAllBtn');

            let currentImageIndex = 0;

            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index);
                    updateMainImage(index);
                    updateActiveThumbnail(index);
                    currentImageIndex = index;
                });
            });

            function updateMainImage(index) {
                if (images[index]) {
                    mainImage.src = '../uploads/' + images[index];
                    mainImage.onerror = function() { this.src = placeholderSrc; };
                }
            }

            function updateActiveThumbnail(index) {
                thumbnails.forEach(thumb => {
                    thumb.classList.remove('active');
                    if (parseInt(thumb.dataset.index) === index) thumb.classList.add('active');
                });
            }

            function openLightbox(index = 0) {
                if (images.length === 0) return;
                currentImageIndex = index;
                updateLightboxImage();
                lightbox.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeLightbox() {
                lightbox.classList.remove('show');
                document.body.style.overflow = '';
            }

            function updateLightboxImage() {
                if (images[currentImageIndex]) {
                    lightboxImage.src = '../uploads/' + images[currentImageIndex];
                    lightboxCounter.textContent = (currentImageIndex + 1) + ' / ' + images.length;
                }
            }

            function nextImage() {
                currentImageIndex = (currentImageIndex + 1) % images.length;
                updateLightboxImage();
            }

            function prevImage() {
                currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
                updateLightboxImage();
            }

            if (viewAllBtn) viewAllBtn.addEventListener('click', () => openLightbox(currentImageIndex));
            mainImage.addEventListener('click', () => openLightbox(currentImageIndex));
            lightboxClose.addEventListener('click', closeLightbox);
            lightboxPrev.addEventListener('click', prevImage);
            lightboxNext.addEventListener('click', nextImage);

            document.addEventListener('keydown', (e) => {
                if (!lightbox.classList.contains('show')) return;
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowLeft') prevImage();
                if (e.key === 'ArrowRight') nextImage();
            });

            lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });

            // Character counter
            const commentText = document.getElementById('commentText');
            const charCount = document.getElementById('charCount');
            if (commentText && charCount) {
                commentText.addEventListener('input', function() { charCount.textContent = this.value.length; });
            }

            // Star rating for comments
            const commentStars = document.querySelectorAll('#commentStars .comment-star');
            const commentRatingInput = document.getElementById('commentRating');
            const commentRatingText = document.getElementById('commentRatingText');

            if (commentStars.length > 0) {
                commentStars.forEach(star => {
                    star.addEventListener('click', function() {
                        const value = parseInt(this.dataset.value);
                        commentRatingInput.value = value;
                        updateCommentStars(value);
                        commentRatingText.textContent = value + ' / 5';
                    });
                    star.addEventListener('mouseenter', function() { highlightStars(parseInt(this.dataset.value)); });
                    star.addEventListener('mouseleave', function() { updateCommentStars(parseInt(commentRatingInput.value) || 0); });
                });
            }

            function updateCommentStars(rating) {
                commentStars.forEach(star => {
                    const value = parseInt(star.dataset.value);
                    if (value <= rating) { star.classList.add('fas', 'active'); star.classList.remove('far'); }
                    else { star.classList.add('far'); star.classList.remove('fas', 'active'); }
                });
            }

            function highlightStars(rating) {
                commentStars.forEach(star => {
                    const value = parseInt(star.dataset.value);
                    if (value <= rating) { star.classList.add('fas'); star.classList.remove('far'); }
                });
            }

            // Access Code
            const accessCodeForm = document.getElementById('accessCodeForm');
            const accessCodeInput = document.getElementById('accessCodeInput');
            const accessCodeError = document.getElementById('accessCodeError');

            if (accessCodeForm) {
                accessCodeForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const code = accessCodeInput.value.trim();
                    if (!code || code.length !== 4) { showAccessCodeError('กรุณากรอกรหัส 4 หลักให้ถูกต้อง'); return; }

                    const submitBtn = document.getElementById('submitAccessCode');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังตรวจสอบ...';
                    submitBtn.disabled = true;

                    try {
                        const response = await fetch('verify_access_code.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ code: code })
                        });
                        const result = await response.json();
                        if (result.success) { location.reload(); }
                        else {
                            showAccessCodeError(result.error || 'รหัสไม่ถูกต้อง');
                            accessCodeInput.value = '';
                            accessCodeInput.focus();
                        }
                    } catch (error) {
                        showAccessCodeError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                    } finally {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                });

                accessCodeInput.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '').slice(0, 4);
                    hideAccessCodeError();
                });
            }

            function showAccessCodeError(msg) { accessCodeError.textContent = msg; accessCodeError.classList.remove('d-none'); }
            function hideAccessCodeError() { accessCodeError.classList.add('d-none'); }

            function updateOverallRatingStars(avgRating) {
                const overallStars = document.querySelectorAll('.rating-container .rating-stars .rating-star');
                const roundedRating = Math.round(avgRating);
                overallStars.forEach((star, index) => {
                    if ((index + 1) <= roundedRating) { star.classList.remove('far'); star.classList.add('fas'); }
                    else { star.classList.remove('fas'); star.classList.add('far'); }
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
                    const commentTextVal = document.getElementById('commentText').value.trim();
                    const rating = parseInt(document.getElementById('commentRating').value || 0, 10);

                    if (!userName || !commentTextVal) { showCommentError('กรุณากรอกข้อมูลให้ครบถ้วน'); return; }

                    const submitBtn = this.querySelector('.submit-btn');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังส่ง...';
                    submitBtn.disabled = true;

                    hideCommentMessages();

                    try {
                        let guestIdentifier = null;
                        let ratingMode = null;

                        if (rating > 0) {
                            const ratingResponse = await fetch('rate_course.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ courses_id: parseInt(courseId, 10), rating: rating })
                            });
                            const ratingResult = await ratingResponse.json();
                            if (ratingResult && ratingResult.success) {
                                guestIdentifier = ratingResult.guest_identifier;
                                ratingMode = ratingResult.mode || 'created';
                                if (typeof ratingResult.avg !== 'undefined') {
                                    const avgValue = parseFloat(ratingResult.avg);
                                    document.querySelector('.rating-value').textContent = avgValue.toFixed(1);
                                    updateOverallRatingStars(avgValue);
                                }
                                if (typeof ratingResult.count !== 'undefined') {
                                    document.querySelector('.rating-count').textContent = '(' + ratingResult.count + ' คะแนน)';
                                }
                            } else {
                                showCommentError(ratingResult.error || 'เกิดข้อผิดพลาดในการให้คะแนน');
                                return;
                            }
                        }

                        const commentData = {
                            courses_id: parseInt(courseId, 10),
                            user_name: userName,
                            comment_text: commentTextVal
                        };
                        if (guestIdentifier) commentData.guest_identifier = guestIdentifier;

                        const commentResponse = await fetch('save_comment.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(commentData)
                        });
                        const commentResult = await commentResponse.json();

                        if (commentResult && commentResult.success) {
                            const commentMode = commentResult.mode || 'created';
                            if (ratingMode === 'updated' || commentMode === 'updated') {
                                showCommentSuccess();
                                setTimeout(() => location.reload(), 800);
                                return;
                            }

                            addNewCommentToUI(userName, commentTextVal, rating);

                            commentForm.reset();
                            document.getElementById('charCount').textContent = '0';
                            document.getElementById('commentRating').value = '0';
                            commentRatingText.textContent = 'ยังไม่ได้ให้คะแนน';
                            updateCommentStars(0);

                            if ('<?php echo $loggedInUserName; ?>') {
                                document.getElementById('userName').value = '<?php echo $loggedInUserName; ?>';
                            }

                            showCommentSuccess();
                        } else {
                            showCommentError(commentResult.error || 'เกิดข้อผิดพลาดในการส่งความคิดเห็น');
                        }
                    } catch (error) {
                        console.error('Submit error:', error);
                        showCommentError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                    } finally {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
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
                setTimeout(() => commentFeedback.classList.add('d-none'), 3000);
            }

            function hideCommentMessages() {
                commentFeedback.classList.add('d-none');
                commentError.classList.add('d-none');
            }

            function addNewCommentToUI(userName, commentText, rating) {
                const noComments = document.querySelector('.no-comments');
                if (noComments) noComments.remove();

                const commentsList = document.getElementById('commentsList');
                const now = new Date();
                const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                const dateStr = now.getDate() + ' ' + monthNames[now.getMonth()] + ' ' + now.getFullYear() + ' ' +
                    String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');

                const newComment = document.createElement('div');
                newComment.className = 'comment-item';

                let ratingHTML = '';
                if (rating > 0) {
                    ratingHTML = `<div class="comment-rating">
                        <div class="comment-stars">
                            ${'<i class="fas fa-star comment-star-rating"></i>'.repeat(rating)}
                            ${'<i class="far fa-star comment-star-rating"></i>'.repeat(5 - rating)}
                        </div>
                        <small class="text-muted">(${rating}/5)</small>
                    </div>`;
                }

                newComment.innerHTML = `
                    <div class="comment-header">
                        <div class="comment-author">คุณ${userName}</div>
                        <div class="comment-date">${dateStr}</div>
                    </div>
                    ${ratingHTML}
                    <div class="comment-text">${commentText.replace(/\n/g, '<br>')}</div>
                `;

                commentsList.insertBefore(newComment, commentsList.firstChild);
            }
        });

        // Read more toggle
        document.querySelectorAll('.read-more').forEach(btn => {
            btn.addEventListener('click', function() {
                const text = this.previousElementSibling;
                text.classList.toggle('expanded');
                this.textContent = text.classList.contains('expanded') ? 'ย่อข้อความ' : 'อ่านเพิ่มเติม';
            });
        });
    </script>
</body>
</html>