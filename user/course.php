<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// ตรวจสอบว่าไฟล์ db.php มีอยู่จริงและเรียกใช้งานได้
$db_path = '../admin/db.php';
if (!file_exists($db_path)) {
  // สามารถใส่การจัดการ error หรือหยุดการทำงานได้
  die("Error: Database connection file not found at " . htmlspecialchars($db_path));
}
require_once $db_path;
?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>หลักสูตรการอบรมแบบมีฐานการเรียนรู้</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root {
      --primary-color: #4361ee;
      --primary-light: #eef2ff;
      --secondary-color: #3a0ca3;
      --primary-gradient: linear-gradient(135deg, #016A70 0%, #018992 100%);
      --primary-light: #018992;
      --accent-color: #4895ef;
      --success-color: #4cc9f0;
      --danger-color: #e63946;
      --warning-color: #f8961e;
      --light-color: #f8f9fa;
      --dark-color: #212529;
      --text-color: #333;
      --text-light: #6c757d;
      --border-radius: 12px;
      --border-radius-sm: 8px;
      --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      --box-shadow-hover: 0 20px 50px rgba(0, 0, 0, 0.15);
      --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    * {
      font-family: 'Kanit', sans-serif;
    }

    .page-header {
      background: var(--primary-gradient);
      color: white;
      padding: 2rem 0;
      margin-bottom: 2rem;
      border-radius: 0 0 20px 20px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .page-header h2 {
      font-weight: 700;
      margin: 0;
      position: relative;
      padding-bottom: 15px;
    }

    .page-header h2:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 70px;
      height: 4px;
      background: rgba(255, 255, 255, 0.7);
      border-radius: 10px;
    }

    body {
      background-color: #f5f7fb;
      color: var(--text-color);
      line-height: 1.6;
    }

    .section-title {
      font-size: 2.25rem;
      font-weight: 700;
      margin-bottom: 2.5rem;
      position: relative;
      padding-bottom: 1rem;
      color: var(--dark-color);
    }

    .section-title::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 80px;
      height: 5px;
      background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
      border-radius: 3px;
    }

    /* Modern List Layout */
    .courses-container {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    .course-item {
      background: white;
      border-radius: var(--border-radius);
      padding: 2rem;
      box-shadow: var(--box-shadow);
      transition: var(--transition);
      display: flex;
      gap: 2rem;
      position: relative;
      overflow: hidden;
    }

    .course-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 5px;
      height: 100%;
      background: linear-gradient(to bottom, var(--primary-color), var(--accent-color));
      transition: var(--transition);
    }

    .course-item:hover {
      transform: translateY(-5px);
      box-shadow: var(--box-shadow-hover);
    }

    .course-item:hover::before {
      width: 8px;
    }

    .course-image {
      width: 280px;
      height: 180px;
      border-radius: var(--border-radius-sm);
      object-fit: cover;
      flex-shrink: 0;
    }

    .course-content {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .course-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
    }

    .course-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 0.5rem;
      line-height: 1.3;
    }

    .course-badge {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 0.4rem 1rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
      white-space: nowrap;
    }

    .course-description {
      color: var(--text-light);
      margin-bottom: 1.5rem;
      line-height: 1.6;
    }

    .course-meta {
      display: flex;
      gap: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .meta-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--text-light);
      font-size: 0.9rem;
    }

    .meta-item i {
      color: var(--primary-color);
    }

    .rating-section {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .stars {
      display: flex;
      gap: 2px;
    }

    .star {
      color: #ddd;
      font-size: 1.1rem;
    }

    .star.filled {
      color: #ffc107;
    }

    .rating-value {
      font-weight: 600;
      color: var(--dark-color);
      font-size: 1.1rem;
    }

    .rating-count {
      color: var(--text-light);
      font-size: 0.85rem;
    }

    .course-actions {
      display: flex;
      gap: 1rem;
      margin-top: auto;
    }

    .btn-detail {
      background: var(--primary-color);
      color: white;
      border: none;
      border-radius: var(--border-radius-sm);
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-detail:hover {
      background: var(--secondary-color);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }

    .btn-outline {
      background: transparent;
      color: var(--primary-color);
      border: 2px solid var(--primary-color);
      border-radius: var(--border-radius-sm);
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-outline:hover {
      background: var(--primary-light);
      transform: translateY(-2px);
    }

    /* Grid Layout Alternative */
    .courses-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
      gap: 2rem;
    }

    .grid-item {
      background: white;
      border-radius: var(--border-radius);
      padding: 2rem;
      box-shadow: var(--box-shadow);
      transition: var(--transition);
      position: relative;
      overflow: hidden;
    }

    .grid-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    }

    .grid-item:hover {
      transform: translateY(-8px);
      box-shadow: var(--box-shadow-hover);
    }

    .grid-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
    }

    .grid-title {
      font-size: 1.3rem;
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 0.5rem;
      line-height: 1.3;
    }

    .grid-description {
      color: var(--text-light);
      margin-bottom: 1.5rem;
      line-height: 1.6;
    }

    /* Minimal Layout */
    .minimal-item {
      background: white;
      border-radius: var(--border-radius);
      padding: 2rem;
      box-shadow: var(--box-shadow);
      transition: var(--transition);
      border-left: 4px solid var(--primary-color);
    }

    .minimal-item:hover {
      transform: translateX(10px);
      box-shadow: var(--box-shadow-hover);
    }

    .minimal-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
    }

    .minimal-title {
      font-size: 1.4rem;
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 0.5rem;
    }

    .minimal-meta {
      display: flex;
      gap: 1.5rem;
      margin-bottom: 1rem;
    }

    /* Tab Navigation */
    .layout-tabs {
      display: flex;
      gap: 1rem;
      margin-bottom: 2rem;
      border-bottom: 1px solid #dee2e6;
      padding-bottom: 1rem;
    }

    .layout-tab {
      background: none;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: var(--border-radius-sm);
      font-weight: 500;
      transition: var(--transition);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .layout-tab.active {
      background: var(--primary-color);
      color: white;
    }

    .layout-tab:not(.active):hover {
      background: var(--primary-light);
      color: var(--primary-color);
    }

    /* Layout Containers */
    .layout-container {
      display: none;
    }

    .layout-container.active {
      display: block;
    }

    /* No Courses State */
    .no-courses {
      text-align: center;
      padding: 4rem 2rem;
      background: white;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
    }

    .no-courses i {
      font-size: 4rem;
      color: #dee2e6;
      margin-bottom: 1.5rem;
    }

    .no-courses h4 {
      color: var(--dark-color);
      margin-bottom: 1rem;
    }

    /* Pagination */
    .pagination-container {
      display: flex;
      justify-content: center;
      margin-top: 3rem;
    }

    .page-link {
      color: var(--primary-color);
      border: 1px solid #dee2e6;
      padding: 0.6rem 1.2rem;
      border-radius: var(--border-radius-sm);
      margin: 0 0.25rem;
      transition: var(--transition);
    }

    .page-link:hover {
      background-color: var(--primary-color);
      color: white;
      border-color: var(--primary-color);
      transform: translateY(-2px);
    }

    .page-item.active .page-link {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .course-item {
        flex-direction: column;
        gap: 1rem;
      }

      .course-image {
        width: 100%;
        height: 200px;
      }

      .courses-grid {
        grid-template-columns: 1fr;
      }

      .course-header {
        flex-direction: column;
        gap: 1rem;
      }

      .course-actions {
        flex-direction: column;
      }

      .section-title {
        font-size: 1.75rem;
      }
    }

    /* Animation */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .course-item,
    .grid-item,
    .minimal-item {
      animation: fadeInUp 0.5s ease forwards;
    }

   .course-actions {
  display: flex;
  gap: 12px;
  margin-top: 10px;
}

/* ปุ่มดูรายละเอียด */
.btn-detail {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 18px;
  background: linear-gradient(135deg, #0d6efd, #3b82f6);
  color: #fff;
  border-radius: 30px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-detail:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(13,110,253,0.4);
  background: linear-gradient(135deg, #3b82f6, #0d6efd);
}

/* ปุ่มคอมเมนต์ */
.comment-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border-radius: 30px;
  border: 2px solid #0d6efd;
  background: transparent;
  color: #0d6efd;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.comment-btn:hover {
  background: #0d6efd;
  color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(13,110,253,0.4);
}

.comment-btn:active {
  transform: scale(0.95);
}

  </style>
</head>

<body>

  <?php include 'navbar.php'; ?>

  <div class="page-header mt-5">
    <div class="container">
      <div class="row align-items-center mt-5">
        <div class="col-md-8">
          <h2>กิจกรรมอบรมทั้งหมด</h2>
          <p class="mb-0 mt-2">การเรียนรู้เศรษฐกิจพอเพียง</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Course Section -->
  <section class="py-5">
    <div class="container">
      <!-- Layout Tabs -->
      <div class="layout-tabs">
        <button class="layout-tab active" data-layout="list">
          <i class="fas fa-list"></i>รายการ
        </button>
      </div>

      <?php
      // ตรวจสอบการเชื่อมต่อฐานข้อมูล
      if (!$conn) {
        echo '<div class="alert alert-danger" role="alert">
                <strong>เกิดข้อผิดพลาด:</strong> ไม่สามารถเชื่อมต่อฐานข้อมูลได้
              </div>';
        include 'footer.php';
        exit;
      }

      // ดึงข้อมูลหลักสูตรจากฐานข้อมูล
      $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
      $limit = 9;
      $offset = ($page - 1) * $limit;

      // Validate page number
      if ($page < 1) {
        $page = 1;
        $offset = 0;
      }

      $query = "SELECT * FROM courses ORDER BY courses_id DESC LIMIT ? OFFSET ?";
      $stmt = $conn->prepare($query);

      if (!$stmt) {
        echo '<div class="alert alert-danger">เกิดข้อผิดพลาด: ' . htmlspecialchars($conn->error) . '</div>';
        include 'footer.php';
        exit;
      }

      $stmt->bind_param("ii", $limit, $offset);
      $stmt->execute();
      $result = $stmt->get_result();

      // นับจำนวนทั้งหมดสำหรับ pagination
      $countResult = $conn->query("SELECT COUNT(*) as total FROM courses");
      if (!$countResult) {
        echo '<div class="alert alert-danger">เกิดข้อผิดพลาด: ' . htmlspecialchars($conn->error) . '</div>';
        include 'footer.php';
        exit;
      }
      $countRow = $countResult->fetch_assoc();
      $totalPages = ceil($countRow['total'] / $limit);

      // ตั้งค่าพาธ uploads และ placeholder
      $uploadsDir = __DIR__ . '/../uploads/';
      $placeholderFilePath = $uploadsDir . 'placeholder.jpg';
      if (is_file($placeholderFilePath)) {
        $placeholderSrc = '../uploads/placeholder.jpg';
      } else {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 450"><rect width="100%" height="100%" fill="#e9ecef"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#999" font-family="Kanit, Arial, sans-serif" font-size="28">No image</text></svg>';
        $placeholderSrc = 'data:image/svg+xml;base64,' . base64_encode($svg);
      }
      ?>

      <?php if ($result->num_rows > 0): ?>

        <!-- List Layout -->
        <div class="layout-container active" id="list-layout">
          <div class="courses-container">
            <?php while ($row = $result->fetch_assoc()): ?>
              <?php
              $courseId = (int)($row['courses_id'] ?? 0);
              $courseName = htmlspecialchars($row['course_name'] ?? 'ชื่อหลักสูตร', ENT_QUOTES, 'UTF-8');
              $courseDesc = htmlspecialchars($row['course_description'] ?? 'ไม่มีรายละเอียด', ENT_QUOTES, 'UTF-8');

              // ดึงรูปภาพแรกสำหรับแสดงใน card
              $images = array_filter([
                $row['image1'] ?? '',
                $row['image2'] ?? '',
                $row['image3'] ?? ''
              ]);

              if (!empty($images)) {
                $firstImage = reset($images);
                $uploadsPath = $uploadsDir . $firstImage;
                if (is_file($uploadsPath)) {
                  $imageSrc = '../uploads/' . htmlspecialchars($firstImage, ENT_QUOTES, 'UTF-8');
                } else {
                  $imageSrc = $placeholderSrc;
                }
              } else {
                $imageSrc = $placeholderSrc;
              }

              // ดึงคะแนนเฉลี่ย
              $ratingStmt = $conn->prepare("
                SELECT 
                  ROUND(AVG(rating),1) AS avg_rating,
                  COUNT(*) AS cnt
                FROM course_rating
                WHERE courses_id = ?
              ");

              if ($ratingStmt) {
                $ratingStmt->bind_param('i', $courseId);
                $ratingStmt->execute();
                $ratingRes = $ratingStmt->get_result()->fetch_assoc();

                $avg_rating   = $ratingRes['avg_rating'] ?? 0;
                $rating_count = $ratingRes['cnt'] ?? 0;

                $ratingStmt->close();
              } else {
                $avg_rating = 0;
                $rating_count = 0;
              }

              ?>

              <div class="course-item">
                <img src="<?php echo $imageSrc; ?>" alt="<?php echo $courseName; ?>" class="course-image">
                <div class="course-content">
                  <div class="course-header">
                    <div>
                      <h3 class="course-title"><?php echo $courseName; ?></h3>
                      <p class="course-description"><?php echo $courseDesc; ?></p>
                    </div>
                  </div>

                  <div class="course-meta">

                    <div class="meta-item">
                      <i class="fas fa-users"></i>
                      <span> <?php echo $rating_count; ?> ผู้เรียน</span>
                    </div>
                  </div>

                  <div class="rating-section">
                    <div class="stars">
                      <?php
                      $rounded = (int) round($avg_rating);
                      for ($i = 1; $i <= 5; $i++) {
                        $class = $i <= $rounded ? 'star filled' : 'star';
                        echo '<span class="' . $class . '">★</span>';
                      }
                      ?>
                    </div>
                    <span class="rating-value"><?php echo number_format($avg_rating, 1, '.', ''); ?></span>
                    <span class="rating-count"> คะแนน</span>
                  </div>

                  <div class="course-actions">
                    <a href="course_detail.php?id=<?php echo $courseId; ?>" class="btn-detail">
                      ดูรายละเอียด <i class="fas fa-arrow-right ms-1"></i>
                    </a>

                    <button class="comment-btn"
                      onclick="window.location.href='course_detail.php?id=<?= $courseId ?>#comments'">
                      <i class="fas fa-comment-dots"></i>
                      แสดงความคิดเห็น
                    </button>
                  </div>


                </div>
              </div>
            <?php endwhile; ?>
          </div>
        </div>

        <!-- Grid Layout -->
        <div class="layout-container" id="grid-layout">
          <div class="courses-grid">
            <?php
            // Reset result pointer
            $result->data_seek(0);
            while ($row = $result->fetch_assoc()):
            ?>
              <?php
              $courseId = (int)($row['courses_id'] ?? 0);
              $courseName = htmlspecialchars($row['course_name'] ?? 'ชื่อหลักสูตร', ENT_QUOTES, 'UTF-8');
              $courseDesc = htmlspecialchars($row['course_description'] ?? 'ไม่มีรายละเอียด', ENT_QUOTES, 'UTF-8');

              // ดึงคะแนนเฉลี่ย
              $ratingStmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM course_ratings WHERE courses_id = ?");
              if ($ratingStmt) {
                $ratingStmt->bind_param('i', $courseId);
                $ratingStmt->execute();
                $ratingRes = $ratingStmt->get_result()->fetch_assoc();
                $avg_rating = $ratingRes['avg_rating'] ? round((float)$ratingRes['avg_rating'], 1) : 0;
                $rating_count = (int)$ratingRes['cnt'];
                $ratingStmt->close();
              } else {
                $avg_rating = 0;
                $rating_count = 0;
              }
              ?>

              <div class="grid-item">
                <div class="grid-header">
                  <h3 class="grid-title"><?php echo $courseName; ?></h3>
                  <span class="course-badge">หลักสูตรใหม่</span>
                </div>

                <p class="grid-description"><?php echo $courseDesc; ?></p>

                <div class="course-meta">
                  <div class="meta-item">
                    <i class="far fa-clock"></i>
                    <span>12 ชั่วโมง</span>
                  </div>
                  <div class="meta-item">
                    <i class="fas fa-signal"></i>
                    <span>ระดับกลาง</span>
                  </div>
                </div>

                <div class="rating-section">
                  <div class="stars">
                    <?php
                    $rounded = (int) round($avg_rating);
                    for ($i = 1; $i <= 5; $i++) {
                      $class = $i <= $rounded ? 'star filled' : 'star';
                      echo '<span class="' . $class . '">★</span>';
                    }
                    ?>
                  </div>
                  <span class="rating-value"><?php echo number_format($avg_rating, 1, '.', ''); ?></span>
                  <span class="rating-count">(<?php echo $rating_count; ?> คะแนน)</span>
                </div>

                <div class="course-actions">
                  <a href="course_detail.php?id=<?php echo $courseId; ?>" class="btn-detail">
                    ดูรายละเอียด
                  </a>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        </div>

        <!-- Minimal Layout -->
        <div class="layout-container" id="minimal-layout">
          <div class="courses-container">
            <?php
            // Reset result pointer
            $result->data_seek(0);
            while ($row = $result->fetch_assoc()):
            ?>
              <?php
              $courseId = (int)($row['courses_id'] ?? 0);
              $courseName = htmlspecialchars($row['course_name'] ?? 'ชื่อหลักสูตร', ENT_QUOTES, 'UTF-8');
              $courseDesc = htmlspecialchars($row['course_description'] ?? 'ไม่มีรายละเอียด', ENT_QUOTES, 'UTF-8');

              // ดึงคะแนนเฉลี่ย
              $ratingStmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM course_ratings WHERE course_id = ?");
              if ($ratingStmt) {
                $ratingStmt->bind_param('i', $courseId);
                $ratingStmt->execute();
                $ratingRes = $ratingStmt->get_result()->fetch_assoc();
                $avg_rating = $ratingRes['avg_rating'] ? round((float)$ratingRes['avg_rating'], 1) : 0;
                $rating_count = (int)$ratingRes['cnt'];
                $ratingStmt->close();
              } else {
                $avg_rating = 0;
                $rating_count = 0;
              }
              ?>

              <div class="minimal-item">
                <div class="minimal-header">
                  <h3 class="minimal-title"><?php echo $courseName; ?></h3>
                  <span class="course-badge">หลักสูตรใหม่</span>
                </div>

                <p class="course-description"><?php echo $courseDesc; ?></p>

                <div class="minimal-meta">
                  <div class="meta-item">
                    <i class="far fa-clock"></i>
                    <span>12 ชั่วโมง</span>
                  </div>
                  <div class="meta-item">
                    <i class="fas fa-signal"></i>
                    <span>ระดับกลาง</span>
                  </div>
                  <div class="rating-section">
                    <div class="stars">
                      <?php
                      $rounded = (int) round($avg_rating);
                      for ($i = 1; $i <= 5; $i++) {
                        $class = $i <= $rounded ? 'star filled' : 'star';
                        echo '<span class="' . $class . '">★</span>';
                      }
                      ?>
                    </div>
                    <span class="rating-value"><?php echo number_format($avg_rating, 1, '.', ''); ?></span>
                  </div>
                </div>

                <div class="course-actions">
                  <a href="course_detail.php?id=<?php echo $courseId; ?>" class="btn-detail">
                    ดูรายละเอียด
                  </a>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <div class="pagination-container">
            <nav aria-label="Page navigation">
              <ul class="pagination">
                <?php if ($page > 1): ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=1">
                      <i class="fas fa-angle-double-left"></i>
                    </a>
                  </li>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                      <i class="fas fa-angle-left"></i>
                    </a>
                  </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                  <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                  </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                      <i class="fas fa-angle-right"></i>
                    </a>
                  </li>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $totalPages; ?>">
                      <i class="fas fa-angle-double-right"></i>
                    </a>
                  </li>
                <?php endif; ?>
              </ul>
            </nav>
          </div>
        <?php endif; ?>

      <?php else: ?>
        <div class="no-courses">
          <i class="fas fa-book-open"></i>
          <h4>ไม่มีหลักสูตรในขณะนี้</h4>
          <p class="text-muted">กรุณากลับมาตรวจสอบภายหลัง</p>
        </div>
      <?php endif; ?>

      <?php $stmt->close(); ?>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Layout Tabs Functionality
    document.querySelectorAll('.layout-tab').forEach(tab => {
      tab.addEventListener('click', function() {
        // Remove active class from all tabs
        document.querySelectorAll('.layout-tab').forEach(t => {
          t.classList.remove('active');
        });

        // Add active class to clicked tab
        this.classList.add('active');

        // Hide all layout containers
        document.querySelectorAll('.layout-container').forEach(container => {
          container.classList.remove('active');
        });

        // Show selected layout
        const layout = this.getAttribute('data-layout');
        document.getElementById(`${layout}-layout`).classList.add('active');
      });
    });

    // Animation on scroll
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, observerOptions);

    // Observe all course items for animation
    document.querySelectorAll('.course-item, .grid-item, .minimal-item').forEach(item => {
      item.style.opacity = '0';
      item.style.transform = 'translateY(20px)';
      item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      observer.observe(item);
    });
  </script>

</body>

</html>