<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    http_response_code(404);
    echo "หลักสูตรไม่พบ";
    exit;
}

// ดึงข้อมูลคะแนนเฉลี่ยและจำนวนโหวต (ตาราง course_ratings ควรมี: id, course_id, user_id (nullable), rating INT, created_at)
$avgRating = 0;
$ratingCount = 0;
$stmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM course_ratings WHERE course_id = ?");
if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
        $avgRating = round((float)$res['avg_rating'], 2);
        $ratingCount = (int)$res['cnt'];
    }
    $stmt->close();
}

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
        body { font-family: 'Kanit', sans-serif; background:#f7f8fb; }
        .course-hero { padding:2.5rem 0; }
        .course-card { border-radius:12px; overflow:hidden; box-shadow:0 6px 18px rgba(0,0,0,0.06); background:#fff; }
        .course-title { color:#1f7a6b; font-weight:700; }
        .btn-enroll { border-radius: 999px; padding:0.6rem 1.2rem; }
        .meta { color:#666; font-size:0.95rem; }

        /* Rating styles */
        .rating { display:flex; align-items:center; gap:0.5rem; user-select:none; }
        .stars { display:flex; gap:6px; }
        .star { width:28px; height:28px; display:inline-block; cursor:pointer; color:#ddd; transition: color .15s ease; font-size:28px; line-height:28px; }
        .star.filled { color:#ffc107; } /* gold */
        .rating-info { color:#666; font-size:0.95rem; }

        .rate-feedback { margin-top:0.5rem; color:green; font-size:0.95rem; display:none; }
        .rate-error { margin-top:0.5rem; color:#c0392b; font-size:0.95rem; display:none; }
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
                            echo '<div class="carousel-item active"><img src="' . $placeholderSrc . '" class="d-block w-100" alt="placeholder" loading="lazy"></div>';
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
                                echo '<img src="' . $src . '" class="d-block w-100" alt="' . htmlspecialchars($course['course_name']) . '" loading="lazy" onerror="this.src=\'' . $placeholderSrc . '\'">';
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
                    <p class="meta mb-2">รหัสหลักสูตร: <strong><?php echo (int)$course['courses_id']; ?></strong></p>
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
                                $rounded = (int) round($avgRating);
                                for ($i = 1; $i <= 5; $i++) {
                                    $class = $i <= $rounded ? 'star filled' : 'star';
                                    echo '<span class="' . $class . '" data-value="' . $i . '" role="button" aria-label="' . $i . ' ดาว">&#9733;</span>';
                                }
                                ?>
                            </div>
                            <div class="rating-info ms-2">
                                <strong id="avgRating"><?php echo $avgRating > 0 ? $avgRating : '0.00'; ?></strong>
                                <small>/5</small>
                                <div><small id="ratingCount"><?php echo $ratingCount; ?></small> คะแนน</div>
                            </div>
                        </div>

                        <div>
                            <a href="booking.php?course_id=<?php echo (int)$course['courses_id']; ?>" class="btn btn-success btn-enroll">ลงทะเบียนทันที</a>
                        </div>
                    </div>

                    <div class="rate-feedback" id="rateFeedback">ขอบคุณสำหรับการให้คะแนน</div>
                    <div class="rate-error" id="rateError"></div>
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
          <h4 class="mb-4">ความคิดเห็นและข้อเสนอแนะ</h4>

          <!-- Comment Form -->
          <div class="card p-4 mb-4">
            <h6 class="mb-3">เพิ่มความคิดเห็นของคุณ</h6>
            <form id="commentForm">
              <input type="hidden" name="course_id" value="<?php echo (int)$course['courses_id']; ?>">
              
              <div class="mb-3">
                <label for="userName" class="form-label">ชื่อของคุณ</label>
                <input type="text" class="form-control" id="userName" placeholder="กรุณากรอกชื่อของคุณ" maxlength="100" required>
              </div>

              <div class="mb-3">
                <label for="commentText" class="form-label">ความคิดเห็น</label>
                <textarea class="form-control" id="commentText" rows="4" placeholder="กรุณากรอกความคิดเห็น" maxlength="1000" required></textarea>
                <small class="text-muted">(<span id="charCount">0</span>/1000)</small>
              </div>

              <!-- Rating input for this comment (guest allowed) -->
              <div class="mb-3">
                <label class="form-label">ให้คะแนนความพึงพอใจ (ถ้ามี)</label>
                <div class="d-flex align-items-center">
                  <div class="stars" id="commentStars" data-course-id="<?php echo (int)$course['courses_id']; ?>" aria-hidden="false" role="radiogroup">
                    <?php for ($i=1;$i<=5;$i++): ?>
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

          <!-- Comments List -->
          <div id="commentsList">
            <div id="noComments" class="alert alert-info">ยังไม่มีความคิดเห็น</div>
            <?php
            // ดึงความคิดเห็นจากฐานข้อมูล
            $commentsStmt = $conn->prepare("SELECT user_name, comment_text, created_at FROM course_comments WHERE course_id = ? ORDER BY created_at DESC LIMIT 50");
            if ($commentsStmt) {
                $commentsStmt->bind_param('i', $id);
                $commentsStmt->execute();
                $commentsResult = $commentsStmt->get_result();
                
                if ($commentsResult->num_rows > 0) {
                    echo '<script>document.getElementById("noComments").style.display = "none";</script>';
                    
                    while ($comment = $commentsResult->fetch_assoc()) {
                        $date = date('j M Y H:i', strtotime($comment['created_at']));
                        echo '<div class="card mb-3 p-3">';
                        echo '<div class="d-flex justify-content-between align-items-center">';
                        echo '<h6 class="mb-0 text-primary">' . htmlspecialchars($comment['user_name']) . '</h6>';
                        echo '<small class="text-muted">' . $date . '</small>';
                        echo '</div>';
                        echo '<p class="card-text mt-2">' . nl2br(htmlspecialchars($comment['comment_text'])) . '</p>';
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
(function(){
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
        s.addEventListener('mouseenter', () => setVisual(parseInt(s.dataset.value,10)));
        s.addEventListener('mouseleave', () => {
            // restore to current average rounded
            const current = Math.round(parseFloat(avgEl.textContent) || 0);
            setVisual(current);
        });
        s.addEventListener('click', () => {
            const value = parseInt(s.dataset.value, 10);
            submitRating(value);
        });
    });

    function submitRating(value) {
        feedback.style.display = 'none';
        errorEl.style.display = 'none';

        fetch('rate_course.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ courses_id: parseInt(courseId,10), rating: value })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // update UI: average and count
                if (typeof data.avg !== 'undefined') {
                    avgEl.textContent = parseFloat(data.avg).toFixed(2);
                }
                if (typeof data.count !== 'undefined') {
                    countEl.textContent = data.count;
                }
                setVisual(Math.round(parseFloat(avgEl.textContent)));
                feedback.style.display = 'block';
            } else {
                errorEl.textContent = data.error || 'เกิดข้อผิดพลาด';
                errorEl.style.display = 'block';
            }
        })
        .catch(err => {
            errorEl.textContent = err.message || 'ไม่สามารถบันทึกคะแนนได้';
            errorEl.style.display = 'block';
            console.error(err);
        });
    }
})();

  // commentStars behavior
  (function(){
      const commentStars = document.getElementById('commentStars');
      const commentRatingInput = document.getElementById('commentRating');
      const commentRatingText = document.getElementById('commentRatingText');
      if (commentStars) {
          const cs = Array.from(commentStars.querySelectorAll('.star'));
          function setCommentVisual(r) {
              cs.forEach(s => {
                  const v = parseInt(s.dataset.value,10);
                  if (v <= r) s.classList.add('filled');
                  else s.classList.remove('filled');
                  s.setAttribute('aria-checked', v === r ? 'true' : 'false');
              });
              commentRatingText.textContent = r > 0 ? `${r} / 5` : 'ยังไม่ได้ให้คะแนน';
          }
          cs.forEach(s => {
              s.addEventListener('mouseenter', ()=> setCommentVisual(parseInt(s.dataset.value,10)));
              s.addEventListener('mouseleave', ()=> setCommentVisual(parseInt(commentRatingInput.value || 0,10)));
              s.addEventListener('click', ()=> {
                  const v = parseInt(s.dataset.value,10);
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

  // Submit comment (async: send rating first if provided, then save comment)
  document.getElementById('commentForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const courseId = this.querySelector('input[name="course_id"]').value;
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

      try {
          // If user provided a rating with the comment, submit it first
          if (rating > 0) {
              const r = await fetch('rate_course.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ course_id: parseInt(courseId,10), rating: rating })
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
                      console.error('Invalid JSON:', text);
                      throw new Error('Invalid JSON from server');
                  }
              });

              if (r && r.success) {
                  // update top aggregate UI
                  const avgEl = document.getElementById('avgRating');
                  const countEl = document.getElementById('ratingCount');
                  if (typeof r.avg !== 'undefined') avgEl.textContent = parseFloat(r.avg).toFixed(2);
                  if (typeof r.count !== 'undefined') countEl.textContent = r.count;
                  // update top stars visual
                  const topStars = document.querySelectorAll('#stars .star');
                  const rounded = Math.round(parseFloat(document.getElementById('avgRating').textContent) || 0);
                  topStars.forEach(s => {
                      const v = parseInt(s.dataset.value,10);
                      if (v <= rounded) s.classList.add('filled'); else s.classList.remove('filled');
                  });
              }
          }

          // then save comment
          const res = await fetch('save_comment.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                  course_id: parseInt(courseId),
                  user_name: userName,
                  comment_text: commentText
              })
          }).then(r => r.json());

          if (res.success) {
              const noComments = document.getElementById('noComments');
              if (noComments) noComments.remove();

              const now = new Date();
              const dateStr = now.getDate() + ' ' + 
                  ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][now.getMonth()] + ' ' + 
                  now.getFullYear() + ' ' + 
                  String(now.getHours()).padStart(2, '0') + ':' + 
                  String(now.getMinutes()).padStart(2, '0');

              const commentsList = document.getElementById('commentsList');
              const newComment = document.createElement('div');
              newComment.className = 'card mb-3 p-3';
              newComment.innerHTML = `
                  <div class="d-flex justify-content-between align-items-center">
                      <h6 class="mb-0 text-primary">${userName}</h6>
                      <small class="text-muted">${dateStr}</small>
                  </div>
                  <p class="card-text mt-2">${commentText}</p>
              `;
              commentsList.insertBefore(newComment, commentsList.firstChild);

              // reset form and comment stars
              document.getElementById('commentForm').reset();
              document.getElementById('charCount').textContent = '0';
              document.getElementById('commentRating').value = '0';
              // clear comment star visuals
              const cs = document.querySelectorAll('#commentStars .star');
              cs.forEach(s => s.classList.remove('filled'));
              document.getElementById('commentRatingText').textContent = 'ยังไม่ได้ให้คะแนน';

              feedback.style.display = 'block';
              setTimeout(() => { feedback.style.display = 'none'; }, 3000);
          } else {
              errorEl.textContent = res.error || 'เกิดข้อผิดพลาด';
              errorEl.style.display = 'block';
          }
      } catch (err) {
          console.error('Submit error:', err);
          errorEl.textContent = 'เกิดข้อผิดพลาดในการส่งข้อมูล';
          errorEl.style.display = 'block';
      }
  });
  </script>
</body>
</html>