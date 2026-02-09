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
$hasAccess = false;

if (
    isset($_SESSION['temp_access_token']) &&
    isset($_SESSION['temp_access_time']) &&
    isset($_SESSION['temp_booking_id'])
) {
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
            --primary: #016A70;
            --primary-light: #E3F2FD;
            --secondary: #FF6B6B;
            --accent: #4ECDC4;
            --dark: #1A2A3A;
            --light: #F8FAFC;
            --gray: #94A3B8;
            --gradient-primary: linear-gradient(135deg, #016A70 0%, #018992 100%);
            --gradient-secondary: linear-gradient(135deg, #FF6B6B 0%, #FF8E8E 100%);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 8px 30px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 50px rgba(0, 0, 0, 0.12);
            --radius-lg: 20px;
            --radius-md: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);
            color: var(--dark);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Header */
        .page-header {
            background: var(--gradient-primary);
            padding: 2rem 0;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 30% 70%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 70% 30%, rgba(255,255,255,0.05) 0%, transparent 50%);
        }

        .back-link {
            color: white;
            text-decoration: none;
            font-weight: 400;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            background: rgba(255,255,255,0.1);
            border-radius: 50px;
            transition: var(--transition);
            backdrop-filter: blur(5px);
        }

        .back-link:hover {
            background: rgba(255,255,255,0.2);
            transform: translateX(-5px);
            color: white;
        }

        .course-title {
            color: white;
            font-weight: 800;
            margin-top: 1rem;
            font-size: 2.5rem;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* Main Content - 2 Column Layout */
        .main-content {
            padding: 3rem 0;
        }

        /* Left Column - Gallery */
        .gallery-column {
            position: sticky;
            top: 2rem;
        }

        /* Modern Gallery */
        .modern-gallery {
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            background: white;
            transition: var(--transition);
        }

        .modern-gallery:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-5px);
        }

        .main-image-container {
            aspect-ratio: 16/10;
            overflow: hidden;
            position: relative;
        }

        .main-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .modern-gallery:hover .main-image {
            transform: scale(1.02);
        }

        .thumbnail-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            background: var(--light);
        }

        .thumbnail-item {
            aspect-ratio: 4/3;
            overflow: hidden;
            cursor: pointer;
            position: relative;
            transition: var(--transition);
            background: white;
        }

        .thumbnail-item:hover {
            transform: translateY(-3px);
            z-index: 2;
        }

        .thumbnail-item.active {
            position: relative;
        }

        .thumbnail-item.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 3px solid var(--primary);
            z-index: 1;
        }

        .thumbnail-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .thumbnail-item:hover img {
            transform: scale(1.1);
        }

        .view-all-btn {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(5px);
            transition: var(--transition);
            z-index: 3;
        }

        .view-all-btn:hover {
            background: rgba(0,0,0,0.9);
            transform: translateY(-2px);
        }

        /* Right Column - Content */
        .content-column {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* Course Info Card */
        .course-info-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .course-info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient-primary);
        }

        .rating-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .rating-value {
            font-size: 2rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .rating-stars {
            display: flex;
            gap: 4px;
        }

        .rating-star {
            font-size: 1.3rem;
            color: #FFD700;
        }

        .rating-count {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .section-title {
            color: var(--dark);
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 1rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--gradient-primary);
            border-radius: 3px;
        }

        .course-description {
            color: var(--dark);
            line-height: 1.7;
            font-size: 1rem;
        }

        /* Access Code Section */
        .access-code-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 2px solid var(--light);
            transition: var(--transition);
        }

        .access-code-card:hover {
            border-color: var(--primary);
        }

        .access-code-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .access-code-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .access-code-form {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .access-code-input {
            flex: 1;
            min-width: 200px;
            padding: 1rem 1.5rem;
            border: 2px solid var(--light);
            border-radius: var(--radius-md);
            font-size: 1.2rem;
            text-align: center;
            letter-spacing: 0.5rem;
            transition: var(--transition);
            font-family: 'Kanit', sans-serif;
        }

        .access-code-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(1, 106, 112, 0.1);
        }

        .access-code-btn {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }

        .access-code-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(1, 106, 112, 0.2);
        }

        .access-code-btn:disabled {
            background: var(--gray);
            cursor: not-allowed;
        }

        .access-success {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #10B981;
            font-weight: 600;
            padding: 1rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: var(--radius-md);
            border: 1px solid #10B981;
        }

        .access-error {
            color: #EF4444;
            margin-top: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(239, 68, 68, 0.1);
            border-radius: var(--radius-md);
            display: none;
        }

        /* Comments Section */
        .comments-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
        }

        .comment-form-container {
            background: var(--light);
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--light);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--light);
            border-radius: var(--radius-md);
            font-family: 'Kanit', sans-serif;
            transition: var(--transition);
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(1, 106, 112, 0.1);
        }

        .char-count {
            text-align: right;
            color: var(--gray);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .comment-rating-stars {
            display: flex;
            gap: 5px;
            margin-top: 0.5rem;
        }

        .comment-star {
            font-size: 2rem;
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
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
        }

        .submit-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(1, 106, 112, 0.2);
        }

        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .comment-feedback {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: var(--radius-md);
            display: none;
        }

        .comment-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10B981;
            border: 1px solid #10B981;
        }

        .comment-error {
            background: rgba(239, 68, 68, 0.1);
            color: #EF4444;
            border: 1px solid #EF4444;
        }

        /* Comments List */
        .comments-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .comment-item {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            border: 1px solid var(--light);
            transition: var(--transition);
        }

        .comment-item:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .comment-author {
            font-weight: 700;
            color: var(--dark);
            font-size: 1.1rem;
        }

        .comment-date {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .comment-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .comment-stars {
            display: flex;
            gap: 2px;
        }

        .comment-star-rating {
            font-size: 1rem;
            color: #FFD700;
        }

        .comment-text {
            color: var(--dark);
            line-height: 1.7;
        }

        .no-comments {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }

        /* Lightbox */
        .lightbox {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .lightbox.show {
            display: flex;
            opacity: 1;
        }

        .lightbox-content {
            max-width: 90vw;
            max-height: 90vh;
            position: relative;
            background: transparent;
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
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            backdrop-filter: blur(5px);
        }

        .lightbox-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .lightbox-close {
            position: absolute;
            top: 2rem;
            right: 2rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            backdrop-filter: blur(5px);
        }

        .lightbox-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .lightbox-counter {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            background: rgba(0, 0, 0, 0.5);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            backdrop-filter: blur(5px);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .main-content {
                padding: 2rem 0;
            }
            
            .gallery-column {
                position: static;
                margin-bottom: 2rem;
            }
            
            .course-title {
                font-size: 2rem;
            }
            
            .access-code-form {
                flex-direction: column;
            }
            
            .access-code-input {
                min-width: 100%;
            }
            
            .access-code-btn {
                width: 100%;
                justify-content: center;
            }
            
            .lightbox-btn {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem 0;
            }
            
            .course-title {
                font-size: 1.8rem;
            }
            
            .thumbnail-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .rating-value {
                font-size: 1.5rem;
            }
            
            .lightbox-nav {
                padding: 0 1rem;
            }
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <a href="course.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                กลับไปหน้าหลักสูตร
            </a>
            <h1 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h1>
        </div>
    </div>

    <!-- Main Content - 2 Columns -->
    <div class="main-content">
        <div class="container">
            <div class="row g-4">
                <!-- Left Column: Gallery -->
                <div class="col-lg-6">
                    <div class="gallery-column">
                        <div class="modern-gallery">
                            <!-- Main Image -->
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

                            <!-- Thumbnails -->
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
                </div>

                <!-- Right Column: Content, Access, Comments -->
                <div class="col-lg-6">
                    <div class="content-column">
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

                            <h3 class="section-title">รายละเอียดหลักสูตร</h3>
                            <div class="course-description">
                                <?php echo nl2br(htmlspecialchars($course['course_description'] ?? 'ไม่มีรายละเอียดเพิ่มเติม')); ?>
                            </div>
                        </div>

                        <!-- Access Code Section -->
                        <div class="access-code-card" id="access-code-section">
                            <div class="access-code-header">
                                <div class="access-code-icon">
                                    <i class="fas fa-key"></i>
                                </div>
                                <div>
                                    <h3 class="section-title">รหัสยืนยันการเข้าร่วมกิจกรรม</h3>
                                    <p class="text-muted">กรอกรหัส 4 หลักที่ได้รับจากทางสวนเพื่อแสดงความคิดเห็น</p>
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

                        <!-- Comments Section -->
                        <div class="comments-card">
                            <h3 class="section-title mb-4">ความคิดเห็นและข้อเสนอแนะ</h3>

                            <!-- Comment Form -->
                            <div id="commentFormContainer" 
                                 class="comment-form-container" 
                                 style="<?= $hasAccess ? '' : 'opacity: 0.6; pointer-events: none;' ?>">
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
                                </form>
                                
                                <!-- Feedback Messages -->
                                <div id="commentFeedback" class="comment-feedback comment-success d-none">
                                    <i class="fas fa-check-circle"></i> ขอบคุณสำหรับความคิดเห็นของคุณ
                                </div>
                                <div id="commentError" class="comment-feedback comment-error d-none"></div>
                            </div>

                            <!-- Comments List -->
                            <div id="commentsList">
                                <?php
                                // Query comments
                                $commentsQuery = "SELECT * FROM course_comments WHERE courses_id = ? ORDER BY created_at DESC LIMIT 10";
                                $commentsStmt = $conn->prepare($commentsQuery);
                                if ($commentsStmt) {
                                    $commentsStmt->bind_param('i', $courseId);
                                    $commentsStmt->execute();
                                    $commentsResult = $commentsStmt->get_result();
                                    
                                    if ($commentsResult->num_rows === 0): ?>
                                        <div class="no-comments">
                                            <i class="far fa-comment-dots fa-3x mb-3"></i>
                                            <h4>ยังไม่มีความคิดเห็น</h4>
                                            <p>เป็นคนแรกที่แสดงความคิดเห็นเกี่ยวกับหลักสูตรนี้</p>
                                        </div>
                                    <?php else: 
                                        while ($comment = $commentsResult->fetch_assoc()): ?>
                                            <div class="comment-item">
                                                <div class="comment-header">
                                                    <div class="comment-author">
                                                        คุณ<?php echo htmlspecialchars($comment['name'] ?? 'ผู้เข้าร่วมกิจกรรมอบรม'); ?>
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

                                                <div class="comment-text">
                                                    <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
                                                </div>
                                            </div>
                                        <?php endwhile;
                                    endif;
                                    $commentsStmt->close();
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
            
            // Gallery Navigation
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

            // Update main image when thumbnail is clicked
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
                    mainImage.onerror = function() {
                        this.src = placeholderSrc;
                    };
                }
            }

            function updateActiveThumbnail(index) {
                thumbnails.forEach(thumb => {
                    thumb.classList.remove('active');
                    if (parseInt(thumb.dataset.index) === index) {
                        thumb.classList.add('active');
                    }
                });
            }

            // Lightbox functionality
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

            // Event listeners for lightbox
            if (viewAllBtn) {
                viewAllBtn.addEventListener('click', () => openLightbox(currentImageIndex));
            }

            mainImage.addEventListener('click', () => openLightbox(currentImageIndex));

            lightboxClose.addEventListener('click', closeLightbox);
            lightboxPrev.addEventListener('click', prevImage);
            lightboxNext.addEventListener('click', nextImage);

            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (!lightbox.classList.contains('show')) return;
                
                switch(e.key) {
                    case 'Escape':
                        closeLightbox();
                        break;
                    case 'ArrowLeft':
                        prevImage();
                        break;
                    case 'ArrowRight':
                        nextImage();
                        break;
                }
            });

            // Close lightbox when clicking outside image
            lightbox.addEventListener('click', (e) => {
                if (e.target === lightbox) {
                    closeLightbox();
                }
            });

            // Comment functionality
            const commentText = document.getElementById('commentText');
            const charCount = document.getElementById('charCount');
            const commentStars = document.querySelectorAll('#commentStars .comment-star');
            const commentRatingInput = document.getElementById('commentRating');
            const commentRatingText = document.getElementById('commentRatingText');

            // Character counter
            if (commentText && charCount) {
                commentText.addEventListener('input', function() {
                    charCount.textContent = this.value.length;
                });
            }

            // Star rating for comments
            if (commentStars.length > 0) {
                commentStars.forEach(star => {
                    star.addEventListener('click', function() {
                        const value = parseInt(this.dataset.value);
                        commentRatingInput.value = value;
                        updateCommentStars(value);
                        commentRatingText.textContent = value + ' / 5';
                    });

                    star.addEventListener('mouseenter', function() {
                        const value = parseInt(this.dataset.value);
                        highlightStars(value);
                    });

                    star.addEventListener('mouseleave', function() {
                        const currentValue = parseInt(commentRatingInput.value) || 0;
                        updateCommentStars(currentValue);
                    });
                });
            }

            function updateCommentStars(rating) {
                commentStars.forEach(star => {
                    const value = parseInt(star.dataset.value);
                    if (value <= rating) {
                        star.classList.add('fas', 'active');
                        star.classList.remove('far');
                    } else {
                        star.classList.add('far');
                        star.classList.remove('fas', 'active');
                    }
                });
            }

            function highlightStars(rating) {
                commentStars.forEach(star => {
                    const value = parseInt(star.dataset.value);
                    if (value <= rating) {
                        star.classList.add('fas');
                        star.classList.remove('far');
                    }
                });
            }

            // Access Code functionality
            const accessCodeForm = document.getElementById('accessCodeForm');
            const accessCodeInput = document.getElementById('accessCodeInput');
            const accessCodeError = document.getElementById('accessCodeError');

            if (accessCodeForm) {
                accessCodeForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const code = accessCodeInput.value.trim();
                    
                    if (!code || code.length !== 4) {
                        showAccessCodeError('กรุณากรอกรหัส 4 หลักให้ถูกต้อง');
                        return;
                    }

                    // Show loading state
                    const submitBtn = document.getElementById('submitAccessCode');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังตรวจสอบ...';
                    submitBtn.disabled = true;

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
                            // Reload page to update UI
                            location.reload();
                        } else {
                            showAccessCodeError(result.error || 'รหัสไม่ถูกต้อง');
                            accessCodeInput.value = '';
                            accessCodeInput.focus();
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showAccessCodeError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                    } finally {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                });

                // Format input
                accessCodeInput.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '').slice(0, 4);
                    hideAccessCodeError();
                });
            }

            function showAccessCodeError(message) {
                accessCodeError.textContent = message;
                accessCodeError.classList.remove('d-none');
            }

            function hideAccessCodeError() {
                accessCodeError.classList.add('d-none');
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

                    // Show loading state
                    const submitBtn = this.querySelector('.submit-btn');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังส่ง...';
                    submitBtn.disabled = true;

                    hideCommentMessages();

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
                                    document.querySelector('.rating-value').textContent = parseFloat(ratingResult.avg).toFixed(1);
                                }
                                if (typeof ratingResult.count !== 'undefined') {
                                    document.querySelector('.rating-count').textContent = '(' + ratingResult.count + ' คะแนน)';
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
                            if (!'<?php echo $loggedInUserName; ?>') {
                                localStorage.setItem('courseCommentUserName', userName);
                            }

                            // Add new comment to the list
                            addNewCommentToUI(userName, commentText, rating);

                            // Reset form
                            commentForm.reset();
                            document.getElementById('charCount').textContent = '0';
                            document.getElementById('commentRating').value = '0';
                            commentRatingText.textContent = 'ยังไม่ได้ให้คะแนน';
                            updateCommentStars(0);

                            // Keep user name if logged in
                            if ('<?php echo $loggedInUserName; ?>') {
                                document.getElementById('userName').value = '<?php echo $loggedInUserName; ?>';
                            }

                            // Show success message
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

                // Hide success message after 3 seconds
                setTimeout(() => {
                    commentFeedback.classList.add('d-none');
                }, 3000);
            }

            function hideCommentMessages() {
                commentFeedback.classList.add('d-none');
                commentError.classList.add('d-none');
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
                    ratingHTML = `
                        <div class="comment-rating">
                            <div class="comment-stars">
                                ${'<i class="fas fa-star comment-star-rating"></i>'.repeat(rating)}
                                ${'<i class="far fa-star comment-star-rating"></i>'.repeat(5 - rating)}
                            </div>
                            <small class="text-muted">(${rating}/5)</small>
                        </div>
                    `;
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