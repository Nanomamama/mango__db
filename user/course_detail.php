<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// กำหนดค่าเริ่มต้นของชื่อผู้ใช้
$loggedInUserName = '';

// ตรวจสอบว่ามีข้อมูลผู้ใช้ใน Session หรือไม่
if (isset($_SESSION['member_id']) && !empty($_SESSION['member_id'])) {
    require_once '../admin/db.php';

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
require_once '../admin/db.php';

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
// 🔍 Debug session (ลบออกหลังแก้เสร็จ)
// echo '<pre style="background:#000;color:#0f0;padding:10px;position:fixed;top:0;right:0;z-index:9999;font-size:12px;">';
// echo "Session Data:\n";
// print_r($_SESSION);
// echo "\n\$hasAccess = " . ($hasAccess ? 'TRUE' : 'FALSE');
// echo '</pre>';

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($course['course_name']); ?> - รายละเอียดหลักสูตร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background: #f7f8fb;
        }

        .course-hero {
            padding: 2.5rem 0;
        }

        .course-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            background: #fff;
        }

        .course-title {
            color: #1f7a6b;
            font-weight: 700;
        }

        .btn-enroll {
            border-radius: 999px;
            padding: 0.6rem 1.2rem;
        }

        .meta {
            color: #666;
            font-size: 0.95rem;
        }

        /* Carousel image sizing - responsive */
        #courseCarousel {
            overflow: hidden;
        }

        #courseCarousel .carousel-inner {
            aspect-ratio: 16 / 9;
            overflow: hidden;
        }

        #courseCarousel .carousel-item {
            height: 100%;
        }

        #courseCarousel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* ภาพไม่บิดเบี้ยว, crop ตามสัดส่วน */
            display: block;
        }

        /* Rating styles */
        .rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            user-select: none;
        }

        .stars {
            display: flex;
            gap: 6px;
        }

        .star {
            width: 28px;
            height: 28px;
            display: inline-block;
            cursor: pointer;
            color: #ddd;
            transition: color .15s ease;
            font-size: 28px;
            line-height: 28px;
        }

        .star.filled {
            color: #ffc107;
        }

        /* gold */
        .rating-info {
            color: #666;
            font-size: 0.95rem;
        }

        .rate-feedback {
            margin-top: 0.5rem;
            color: green;
            font-size: 0.95rem;
            display: none;
        }

        .rate-error {
            margin-top: 0.5rem;
            color: #c0392b;
            font-size: 0.95rem;
            display: none;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="container course-hero">
        <div class="row">
            <div class="col-12 mb-3">
                <a href="course.php" class="btn btn-link">&larr; ย้อนกลับไปหน้าหลักสูตร</a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="course-card">
                    <div id="courseCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php
                            if (empty($images)) {
                                echo '<div class="carousel-item active"><img src="' . $placeholderSrc . '" class="d-block w-100 img-fluid" alt="placeholder" loading="lazy"></div>';
                            } else {
                                $first = true;
                                foreach ($images as $img) {
                                    $safe = htmlspecialchars($img, ENT_QUOTES, 'UTF-8');
                                    $path = $uploadsDir . $img;
                                    if (is_file($path)) {
                                        $src = '../uploads/' . $safe;
                                    } else {
                                        $src = $placeholderSrc;
                                    }
                                    $active = $first ? ' active' : '';
                                    echo '<div class="carousel-item' . $active . '">';
                                    echo '<img src="' . $src . '" class="d-block w-100 img-fluid" alt="' . htmlspecialchars($course['course_name']) . '" loading="lazy" onerror="this.src=\'' . $placeholderSrc . '\'">';
                                    echo '</div>';
                                    $first = false;
                                }
                            }
                            ?>
                        </div>
                        <?php if (count($images) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#courseCarousel" data-bs-slide="prev" aria-label="ก่อนหน้า">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#courseCarousel" data-bs-slide="next" aria-label="ถัดไป">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="course-card p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h1 class="course-title mb-2"><?php echo htmlspecialchars($course['course_name']); ?></h1>

                        <hr>
                        <h5 class="mt-3">รายละเอียด</h5>
                        <p><?php echo nl2br(htmlspecialchars($course['course_description'] ?? 'ไม่มีรายละเอียดเพิ่มเติม')); ?></p>
                    </div>

                    <div class="mt-4">
                        <!-- Rating block -->
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="rating" aria-label="ให้คะแนนหลักสูตร">
                                <div class="stars" id="stars" data-course-id="<?php echo (int)$course['courses_id']; ?>">
                                    <?php
                                    // แสดง 5 ดาว (filled ตามค่าเฉลี่ย)
                                    $rounded = (int) round($avg_rating);
                                    for ($i = 1; $i <= 5; $i++) {
                                        $class = $i <= $rounded ? 'star filled' : 'star';
                                        echo '<span class="' . $class . '" data-value="' . $i . '" role="button" aria-label="' . $i . ' ดาว">&#9733;</span>';
                                    }
                                    ?>
                                </div>
                                <div class="rating-info ms-2">
                                    <strong id="avgRating"><?php echo $avg_rating > 0 ? $avg_rating : '0.00'; ?></strong>
                                    <small>/5</small>
                                    <div><small id="ratingCount"><?php echo $rating_count; ?></small> คะแนน</div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Comment Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h4 class="mb-4" id="comments">ความคิดเห็นและข้อเสนอแนะ</h4>

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">ยืนยันการเข้าร่วมกิจกรรม</h5>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted">
                                กรุณากรอก <strong>เลข 4 ตัว ที่ได้รับจากทางสวน</strong>
                            </p>

                            <input type="password" id="accessCodeInput" class="form-control text-center fs-4" maxlength="6" placeholder="กรอกรหัสเข้าร่วมกิจกรรม" aria-label="กรอกรหัสเข้าร่วมกิจกรรม" <?php if ($hasAccess): echo 'disabled value="****"';
                                                                                                                                                                                                        endif; ?>>
                            <div class="text-danger mt-2 d-none" id="accessCodeError">รหัสไม่ถูกต้อง</div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary w-100" id="submitAccessCode">ยืนยัน</button>
                        </div>
                    </div>
                    <!-- Comment Form -->
                    <div id="lockedSection" style="<?= $hasAccess ? '' : 'pointer-events:none; opacity:.4;' ?>">
                        <div class="card p-4 mb-4">
                            <h6 class="mb-3">เพิ่มความคิดเห็นของคุณ</h6>
                            <form id="commentForm">
                                <input type="hidden" name="courses_id" value="<?php echo (int)$course['courses_id']; ?>">
                                <div class="mb-3">
                                    <label for="userName" class="form-label">ชื่อผู้แสดงความคิดเห็น</label>
                                    <input type="text"
                                        class="form-control"
                                        id="userName"
                                        placeholder="กรุณากรอกชื่อ"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label for="commentText" class="form-label">ความคิดเห็น</label>
                                    <textarea class="form-control" id="commentText" rows="4" placeholder="กรุณากรอกความคิดเห็น" maxlength="1000" required></textarea>
                                    <small class="text-muted">(<span id="charCount">0</span>/1000)</small>
                                </div>

                                <!-- Rating input for this comment (guest allowed) -->
                                <div class="mb-3">
                                    <label class="form-label">ให้คะแนนความพึงพอใจ</label>
                                    <div class="d-flex align-items-center">
                                        <div class="stars" id="commentStars" data-course-id="<?php echo (int)$course['courses_id']; ?>" aria-hidden="false" role="radiogroup">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star" data-value="<?php echo $i; ?>" role="radio" aria-checked="false">&#9733;</span>
                                            <?php endfor; ?>
                                        </div>
                                        <input type="hidden" id="commentRating" name="rating" value="0">
                                        <small class="text-muted ms-2" id="commentRatingText">ยังไม่ได้ให้คะแนน</small>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">ส่งความคิดเห็น</button>
                                <div id="commentFeedback" class="alert alert-success mt-2" style="display:none;">ขอบคุณสำหรับความคิดเห็นของคุณ</div>
                                <div id="commentError" class="alert alert-danger mt-2" style="display:none;"></div>
                            </form>
                        </div>
                    </div>
                    <!-- Comments List -->
                    <div id="commentsList">
                        <div id="noComments" class="alert alert-info">ยังไม่มีความคิดเห็น</div>
                        <?php
                        // JOIN course_comments กับ course_rating เพื่อดึงคะแนนมาด้วย
                        // ใช้ CASE WHEN เพื่อ match ทั้ง member และ guest
                        $commentsStmt = $conn->prepare("
                               SELECT 
                                    cc.name,
                                    cc.comment_text,
                                    cc.created_at,
                                    cr.rating
                                    FROM course_comments cc
                                    LEFT JOIN course_rating cr
                                    ON cc.courses_id = cr.courses_id
                                    AND cc.guest_identifier = cr.guest_identifier
                                    WHERE cc.courses_id = ?
                                    ORDER BY cc.created_at DESC
                                    LIMIT 10
                                ");


                        if ($commentsStmt) {
                            $commentsStmt->bind_param('i', $id);
                            $commentsStmt->execute();
                            $commentsResult = $commentsStmt->get_result();

                            if ($commentsResult->num_rows > 0) {
                                echo '<script>document.getElementById("noComments").style.display = "none";</script>';

                                while ($comment = $commentsResult->fetch_assoc()) {
                                    $date = date('j M Y H:i', strtotime($comment['created_at']));
                                    $userName = htmlspecialchars($comment['name'] ?? 'ผู้เข้าร่วมกิจกรรมอบรม');
                                    $commentText = nl2br(htmlspecialchars($comment['comment_text']));
                                    $rating = (int)($comment['rating'] ?? 0);

                                    echo '<div class="card mb-3 p-3">';
                                    echo '<div class="d-flex justify-content-between align-items-center">';
                                    echo '<h6 class="mb-0 text-primary">คุณ' . $userName . '</h6>';
                                    echo '<small class="text-muted">' . $date . '</small>';
                                    echo '</div>';

                                    // แสดงดาว (ถ้ามีการให้คะแนน)
                                    if ($rating > 0) {
                                        echo '<div class="mt-2">';
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<span style="color: #ffc107; font-size: 18px;">★</span>';
                                            } else {
                                                echo '<span style="color: #ddd; font-size: 18px;">★</span>';
                                            }
                                        }
                                        echo ' <small class="text-muted">(' . $rating . '/5)</small>';
                                        echo '</div>';
                                    }

                                    echo '<p class="card-text mt-2">' . $commentText . '</p>';
                                    echo '</div>';
                                }
                            }
                            $commentsStmt->close();
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            const starsContainer = document.getElementById('stars');
            if (!starsContainer) return;
            const stars = Array.from(starsContainer.querySelectorAll('.star'));
            const courseId = starsContainer.dataset.courseId;
            const feedback = document.getElementById('rateFeedback');
            const errorEl = document.getElementById('rateError');
            const avgEl = document.getElementById('avgRating');
            const countEl = document.getElementById('ratingCount');

            function setVisual(rating) {
                stars.forEach(s => {
                    const val = parseInt(s.dataset.value, 10);
                    if (val <= rating) s.classList.add('filled');
                    else s.classList.remove('filled');
                });
            }

            stars.forEach(s => {

                // restore to current average rounded
                const current = Math.round(parseFloat(avgEl.textContent) || 0);
                setVisual(current);
            });

        });



        // commentStars behavior
        (function() {
            const commentStars = document.getElementById('commentStars');
            const commentRatingInput = document.getElementById('commentRating');
            const commentRatingText = document.getElementById('commentRatingText');
            if (commentStars) {
                const cs = Array.from(commentStars.querySelectorAll('.star'));

                function setCommentVisual(r) {
                    cs.forEach(s => {
                        const v = parseInt(s.dataset.value, 10);
                        if (v <= r) s.classList.add('filled');
                        else s.classList.remove('filled');
                        s.setAttribute('aria-checked', v === r ? 'true' : 'false');
                    });
                    commentRatingText.textContent = r > 0 ? `${r} / 5` : 'ยังไม่ได้ให้คะแนน';
                }
                cs.forEach(s => {
                    s.addEventListener('mouseenter', () => setCommentVisual(parseInt(s.dataset.value, 10)));
                    s.addEventListener('mouseleave', () => setCommentVisual(parseInt(commentRatingInput.value || 0, 10)));
                    s.addEventListener('click', () => {
                        const v = parseInt(s.dataset.value, 10);
                        commentRatingInput.value = v;
                        setCommentVisual(v);
                    });
                });
            }
        })();

        // Character counter
        document.getElementById('commentText').addEventListener('input', function() {
            document.getElementById('charCount').textContent = this.value.length;
        });

        // Load user name from session or localStorage
        (function() {
            const userNameInput = document.getElementById('userName');
            const loggedInName = '<?php echo $loggedInUserName; ?>';

            // ถ้ามีผู้ใช้เข้าสู่ระบบแล้ว ไม่ต้องโหลดจาก localStorage
            if (loggedInName) {
                userNameInput.value = loggedInName;
                userNameInput.readOnly = true; // ห้ามแก้ไขชื่อ
            } else {
                // ถ้าไม่มี ให้โหลดจาก localStorage (guest)
                const savedName = localStorage.getItem('courseCommentUserName');
                if (savedName) {
                    userNameInput.value = savedName;
                }
            }
        })();

        // Submit comment (async: send rating first if provided, then save comment)
        document.getElementById('commentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const courseId = this.querySelector('input[name="courses_id"]').value;
            const userName = document.getElementById('userName').value.trim();
            const commentText = document.getElementById('commentText').value.trim();
            const rating = parseInt(document.getElementById('commentRating').value || 0, 10);
            const feedback = document.getElementById('commentFeedback');
            const errorEl = document.getElementById('commentError');

            if (!userName || !commentText) {
                errorEl.textContent = 'กรุณากรอกข้อมูลให้ครบถ้วน';
                errorEl.style.display = 'block';
                feedback.style.display = 'none';
                return;
            }

            feedback.style.display = 'none';
            errorEl.style.display = 'none';

            let guestIdentifier = null; // เก็บ identifier จากการให้คะแนน

            try {
                // If user provided a rating with the comment, submit it first
                if (rating > 0) {
                    const r = await fetch('rate_course.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            courses_id: parseInt(courseId, 10),
                            rating: rating
                        })
                    }).then(async res => {
                        const ct = res.headers.get('content-type') || '';
                        const text = await res.text();
                        if (ct.indexOf('application/json') === -1) {
                            console.error('Non-JSON response from server:', text);
                            throw new Error('Server returned non-JSON response');
                        }
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.log('RAW SERVER RESPONSE >>>', text);
                            

                            throw new Error('Invalid JSON from server');
                        }
                    });

                    if (r && r.success) {
                        guestIdentifier = r.guest_identifier; // เก็บ identifier

                        // update top aggregate UI
                        const avgEl = document.getElementById('avgRating');
                        const countEl = document.getElementById('ratingCount');
                        if (typeof r.avg !== 'undefined') avgEl.textContent = parseFloat(r.avg).toFixed(2);
                        if (typeof r.count !== 'undefined') countEl.textContent = r.count;

                        // update top stars visual
                        const topStars = document.querySelectorAll('#stars .star');
                        const rounded = Math.round(parseFloat(document.getElementById('avgRating').textContent) || 0);
                        topStars.forEach(s => {
                            const v = parseInt(s.dataset.value, 10);
                            if (v <= rounded) s.classList.add('filled');
                            else s.classList.remove('filled');
                        });
                    }
                }

                // then save comment (ส่ง guest_identifier ไปด้วยถ้ามี)
                const commentData = {
                    courses_id: parseInt(courseId, 10), // ✔ ให้ตรงกับ PHP
                    user_name: userName,
                    comment_text: commentText
                };


                if (guestIdentifier) {
                    commentData.guest_identifier = guestIdentifier;
                }

                const fetchResp = await fetch('save_comment.php', {

                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(commentData)
                });

                const respText = await fetchResp.text();
                let res;
                try {
                    res = respText ? JSON.parse(respText) : null;
                } catch (e) {
                    console.error('Invalid JSON response from save_comment.php:', respText);
                    errorEl.textContent = 'Server error: ' + (respText || 'Empty response');
                    errorEl.style.display = 'block';
                    return;
                }

                if (res && res.success) {
                    // Save user name to localStorage เฉพาะ guest
                    const loggedInName = '<?php echo $loggedInUserName; ?>';
                    if (!loggedInName) {
                        localStorage.setItem('courseCommentUserName', userName);
                    }

                    const noComments = document.getElementById('noComments');
                    if (noComments) noComments.remove();

                    const now = new Date();
                    const dateStr = now.getDate() + ' ' + ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][now.getMonth()] + ' ' +
                        now.getFullYear() + ' ' +
                        String(now.getHours()).padStart(2, '0') + ':' +
                        String(now.getMinutes()).padStart(2, '0');

                    const commentsList = document.getElementById('commentsList');
                    const newComment = document.createElement('div');
                    newComment.className = 'card mb-3 p-3';

                    // แสดงดาวถ้ามีการให้คะแนน
                    let starsHtml = '';
                    if (rating > 0) {
                        starsHtml = '<div class="mt-2">';
                        for (let i = 1; i <= 5; i++) {
                            if (i <= rating) {
                                starsHtml += '<span style="color: #ffc107; font-size: 18px;">★</span>';
                            } else {
                                starsHtml += '<span style="color: #ddd; font-size: 18px;">★</span>';
                            }
                        }
                        starsHtml += ` <small class="text-muted">(${rating}/5)</small></div>`;
                    }

                    newComment.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-primary">คุณ${userName}</h6>
                    <small class="text-muted">${dateStr}</small>
                </div>
                ${starsHtml}
                <p class="card-text mt-2">${commentText.replace(/\n/g, '<br>')}</p>
            `;
                    commentsList.insertBefore(newComment, commentsList.firstChild);

                    // reset form
                    document.getElementById('commentForm').reset();
                    document.getElementById('charCount').textContent = '0';
                    document.getElementById('commentRating').value = '0';
                    const cs = document.querySelectorAll('#commentStars .star');
                    cs.forEach(s => s.classList.remove('filled'));
                    document.getElementById('commentRatingText').textContent = 'ยังไม่ได้ให้คะแนน';
                    document.getElementById('userName').value = userName;

                    feedback.style.display = 'block';
                    setTimeout(() => {
                        feedback.style.display = 'none';
                    }, 3000);
                } else {
                    const msg = (res && res.error) ? res.error : (respText || 'เกิดข้อผิดพลาด');
                    errorEl.textContent = msg;
                    errorEl.style.display = 'block';
                }
            } catch (err) {
                console.error('Submit error:', err);
                errorEl.textContent = 'เกิดข้อผิดพลาดในการส่งข้อมูล';
                errorEl.style.display = 'block';
            }
        });


        function openAccessModal() {
            const modal = new bootstrap.Modal(document.getElementById('accessCodeModal'));
            modal.show();
        }

        // วางโค้ดนี้แทนส่วน submitAccessCode เดิม (ประมาณบรรทัด 475)

document.getElementById('submitAccessCode').addEventListener('click', async () => {
    const codeInput = document.getElementById('accessCodeInput');
    const code = codeInput.value.trim();
    const errorEl = document.getElementById('accessCodeError');
    const submitBtn = document.getElementById('submitAccessCode');

    // ตรวจสอบว่ากรอกรหัสหรือยัง
    if (!code) {
        errorEl.textContent = 'กรุณากรอกรหัส 4 หลัก';
        errorEl.classList.remove('d-none');
        return;
    }

    // ตรวจสอบรูปแบบ (ต้องเป็นตัวเลข 4 หลัก)
    if (!/^\d{4}$/.test(code)) {
        errorEl.textContent = 'กรุณากรอกเลข 4 หลักเท่านั้น';
        errorEl.classList.remove('d-none');
        return;
    }

    // ซ่อน error และ disable ปุ่มชั่วคราว
    errorEl.classList.add('d-none');
    submitBtn.disabled = true;
    submitBtn.textContent = 'กำลังตรวจสอบ...';

    try {
        const res = await fetch('verify_access_code.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                code: code
            })
        }).then(r => r.json());

        if (res.success) {
            // ✅ ตรวจสอบรหัสสำเร็จ
            submitBtn.textContent = '✓ ยืนยันสำเร็จ!';
            submitBtn.classList.replace('btn-primary', 'btn-success');
            
            // แสดงข้อความสั้นๆ แล้ว reload
            setTimeout(() => {
                location.reload();
            }, 800);
        } else {
            // ❌ รหัสไม่ถูกต้อง
            errorEl.textContent = res.error || 'รหัสไม่ถูกต้อง';
            errorEl.classList.remove('d-none');
            submitBtn.disabled = false;
            submitBtn.textContent = 'ยืนยัน';

            // เคลียร์ input และ focus กลับ
            codeInput.value = '';
            codeInput.focus();
        }
    } catch (err) {
        console.error('Access code error:', err);
        errorEl.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
        errorEl.classList.remove('d-none');
        submitBtn.disabled = false;
        submitBtn.textContent = 'ยืนยัน';
    }
});

// เพิ่ม: กด Enter ในช่องรหัสก็ส่งได้เลย
document.getElementById('accessCodeInput').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('submitAccessCode').click();
    }
});
    </script>
</body>

</html>