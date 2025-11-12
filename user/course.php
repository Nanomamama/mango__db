<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../admin/db.php'; 
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
  <style>
    :root {
      --green-color: #016A70;
      --white-color: #fff;
      --danger: #e74a3b;
      --light: #f8f9fc;
      --primary: #4361ee;
    }

    * {
      font-family: 'Kanit', sans-serif;
    }

    body {
      background-color: var(--light);
    }

    .hero-section {
      background: linear-gradient(135deg, var(--danger) 0%, var(--green-color) 100%);
      color: var(--white-color);
      padding: 3rem 0;
      margin-bottom: 3rem;
    }

    .hero-section h2 {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      padding-top: 5rem;
    }

    .hero-section p {
      font-size: 1.1rem;
      opacity: 0.95;
    }

    .course-card {
      border: none;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      height: 100%;
      display: flex;
      flex-direction: column;
      background: var(--white-color);
    }

    .course-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .carousel-inner {
      border-radius: 15px 15px 0 0;
      background-color: #e9ecef;
      min-height: 250px;
    }

    .carousel-inner img {
      height: 250px;
      object-fit: cover;
      object-position: center;
    }

    .carousel-control-prev,
    .carousel-control-next {
      background: rgba(0, 0, 0, 0.3);
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      top: 50%;
      transform: translateY(-50%);
    }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
      background: rgba(0, 0, 0, 0.5);
    }

    .card-body {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      flex-grow: 1;
      padding: 1.5rem;
    }

    .card-title {
      color: var(--danger);
      font-weight: 700;
      font-size: 1.2rem;
      margin-bottom: 1rem;
      min-height: 60px;
      display: -webkit-box;
      /* -webkit-line-clamp: 2; */
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .card-description {
      color: #666;
      font-size: 0.95rem;
      margin-bottom: 1.5rem;
      line-height: 1.5;
      display: -webkit-box;
      /* -webkit-line-clamp: 3; */
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .btn-learn-more {
      padding: 0.6rem 1.5rem;
      border-radius: 20px;
      font-weight: 600;
      color: var(--danger);
      border: 2px solid var(--danger);
      background-color: transparent;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
      text-align: center;
      align-self: flex-start;
    }

    .btn-learn-more:hover {
      background-color: var(--danger);
      color: var(--white-color);
      text-decoration: none;
    }

    .placeholder-image {
      width: 100%;
      height: 250px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
      color: #999;
      font-size: 3rem;
    }

    .no-courses {
      text-align: center;
      padding: 3rem 1rem;
      background: var(--white-color);
      border-radius: 15px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .no-courses i {
      font-size: 3rem;
      color: #ddd;
      margin-bottom: 1rem;
    }

    .pagination-container {
      display: flex;
      justify-content: center;
      margin-top: 2rem;
      margin-bottom: 2rem;
    }

    .pagination .page-link {
      color: var(--primary);
      border-color: var(--primary);
    }

    .pagination .page-link:hover {
      background-color: var(--primary);
      color: var(--white-color);
      border-color: var(--primary);
    }

    .pagination .page-item.active .page-link {
      background-color: var(--primary);
      border-color: var(--primary);
    }

    /* Rating styles for cards */
    .course-rating {
      display: flex;
      align-items: center;
      gap: .5rem;
      margin-top: .5rem;
    }
    .stars-display { display:flex; gap:4px; }
    .star-icon { font-size:1.05rem; line-height:1; color:#ddd; }
    .star-filled { color:#ffc107; }
    .rating-text { color:#666; font-size:0.85rem; margin-left:.35rem; }

    @media (max-width: 768px) {
      .hero-section h2 {
        font-size: 2rem;
      }

      .card-title {
        font-size: 1rem;
      }

      .carousel-inner img {
        height: 200px;
      }

      .placeholder-image {
        height: 200px;
      }
    }
  </style>
</head>

<body>

  <?php include 'navbar.php'; ?>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-8">
          <h2>กิจกรรมการเรียนรู้</h2>
          <p>รหัสและหลักสูตรการอบรมที่ออกแบบมาเพื่อพัฒนาทักษะของคุณ</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Course Section -->
  <section class="py-5">
    <div class="container">
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
      $limit = 12;
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

      // new: ตั้งค่าพาธ uploads และ placeholder (ใช้ไฟล์จริงถ้ามี ถ้าไม่มีก็ใช้ inline SVG)
      $uploadsDir = __DIR__ . '/../uploads/';
      $placeholderFilePath = $uploadsDir . 'placeholder.jpg';
      if (is_file($placeholderFilePath)) {
          // URL สำหรับ <img> (course.php อยู่ใน /user/ ดังนั้นไปยัง uploads ต้อง ../uploads/...)
          $placeholderSrc = '../uploads/placeholder.jpg';
      } else {
          $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 450"><rect width="100%" height="100%" fill="#e9ecef"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#999" font-family="Kanit, Arial, sans-serif" font-size="28">No image</text></svg>';
          $placeholderSrc = 'data:image/svg+xml;base64,' . base64_encode($svg);
      }
      ?>

      <?php if ($result->num_rows > 0): ?>
        <div class="row g-4">
          <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
              <div class="course-card">
                <!-- Carousel -->
                <div id="carouselCourse<?php echo htmlspecialchars($row['courses_id']); ?>" class="carousel slide" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <?php
                    // รวมรูปภาพที่มีและกรองค่า empty
                    $images = array_filter([
                      $row['image1'] ?? '',
                      $row['image2'] ?? '',
                      $row['image3'] ?? ''
                    ]);

                    if (empty($images)) {
                      // ถ้าไม่มีรูป ให้แสดง placeholder
                      echo '<div class="carousel-item active">';
                      echo '<div class="placeholder-image"><i class="fas fa-image"></i></div>';
                      echo '</div>';
                    } else {
                      $first = true;
                      foreach ($images as $imgName) {
                        $safeName = htmlspecialchars($imgName, ENT_QUOTES, 'UTF-8');
                        // ตรวจสอบในโฟลเดอร์ ../uploads/
                        $uploadsPath = $uploadsDir . $imgName;
                        if (is_file($uploadsPath)) {
                            $src = '../uploads/' . $safeName;
                        } else {
                            $src = $placeholderSrc;
                        }
                        
                        $active = $first ? ' active' : '';
                        $alt = htmlspecialchars($row['course_name'] ?? 'ภาพหลักสูตร', ENT_QUOTES, 'UTF-8');
                        
                        echo "<div class=\"carousel-item{$active}\">";
                        echo "<img src=\"{$src}\" class=\"d-block w-100\" loading=\"lazy\" alt=\"{$alt}\" onerror=\"this.src='{$placeholderSrc}'\">";
                        echo "</div>";

                        $first = false;
                      }
                    }
                    ?>
                  </div>
                  <button class="carousel-control-prev" type="button" data-bs-target="#carouselCourse<?php echo htmlspecialchars($row['id']); ?>" data-bs-slide="prev" aria-label="ภาพก่อนหน้า">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#carouselCourse<?php echo htmlspecialchars($row['id']); ?>" data-bs-slide="next" aria-label="ภาพถัดไป">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  </button>
                </div>

                <!-- Card Body -->
                <div class="card-body">
                  <div>
                    <h5 class="card-title"><?php echo htmlspecialchars($row['course_name'] ?? 'ชื่อหลักสูตร'); ?></h5>
                    <p class="card-description"><?php echo htmlspecialchars($row['course_description'] ?? 'ไม่มีรายละเอียด'); ?></p>

                    <?php
                    // ดึงคะแนนเฉลี่ยและจำนวนโหวตสำหรับแต่ละหลักสูตร
                    $avg_rating = 0;
                    $rating_count = 0;
                    $cid = (int)$row['courses_id'];
                    $stmtRating = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM course_ratings WHERE course_id = ?");
                    if ($stmtRating) {
                        $stmtRating->bind_param('i', $cid);
                        $stmtRating->execute();
                        $resR = $stmtRating->get_result()->fetch_assoc();
                        if ($resR) {
                            $avg_rating = $resR['avg_rating'] !== null ? (float)$resR['avg_rating'] : 0;
                            $rating_count = (int)$resR['cnt'];
                        }
                        $stmtRating->close();
                    }

                    // แสดงดาว (rounded)
                    $filled = (int) round($avg_rating);
                    echo '<div class="course-rating" aria-hidden="true">';
                    echo '<div class="stars-display">';
                    for ($s = 1; $s <= 5; $s++) {
                        $cls = $s <= $filled ? 'star-icon star-filled' : 'star-icon';
                        echo '<span class="'. $cls .'">&#9733;</span>';
                    }
                    echo '</div>';
                    echo '<div class="rating-text">'. number_format($avg_rating,1) .' / 5 (' . $rating_count . ')</div>';
                    echo '</div>';
                    ?>
                  </div>
                  <a href="course_detail.php?id=<?php echo htmlspecialchars($row['courses_id']); ?>" class="btn-learn-more">
                    ดูรายละเอียด <i class="fas fa-arrow-right ms-2"></i>
                  </a>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <div class="pagination-container">
            <nav aria-label="Page navigation">
              <ul class="pagination">
                <?php if ($page > 1): ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=1">หน้าแรก</a>
                  </li>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">ก่อนหน้า</a>
                  </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                  <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                  </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">ถัดไป</a>
                  </li>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $totalPages; ?>">หน้าสุดท้าย</a>
                  </li>
                <?php endif; ?>
              </ul>
            </nav>
          </div>
        <?php endif; ?>

      <?php else: ?>
        <div class="no-courses">
          <i class="fas fa-inbox"></i>
          <h4>ไม่มีหลักสูตรในขณะนี้</h4>
          <p class="text-muted">กรุณากลับมาตรวจสอบภายหลัง</p>
        </div>
      <?php endif; ?>

      <?php $stmt->close(); ?>
    </div>
  </section>

  <?php include 'footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>