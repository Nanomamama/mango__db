<?php
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  require_once __DIR__ . '/../db/db.php';

  // Fetch images for the hero carousel
  $carousel_images = [];
  $carousel_sql = "SELECT course_name, image1 FROM courses WHERE image1 IS NOT NULL AND image1 != '' ORDER BY courses_id DESC LIMIT 7";
  $carousel_result = $conn->query($carousel_sql);
  if ($carousel_result) {
      while ($carousel_row = $carousel_result->fetch_assoc()) {
          $carousel_images[] = $carousel_row;
      }
  }
?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>กิจกรรมอบรม</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
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
      --radius-sm: 8px;
      --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
      overflow-x: hidden;
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
      width: 10px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
      background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
      border-radius: 10px;
    }

    /* Hero Carousel */
    .hero-carousel .carousel-item {
      height: 70vh;
      min-height: 400px;
      background-color: #777;
    }

    .hero-carousel .carousel-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
    }

    .hero-carousel .carousel-caption {
      bottom: 3rem;
      background: rgba(26, 42, 58, 0.6);
      border-radius: var(--radius-md);
      padding: 1.5rem;
      text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    }

    .hero-content {
      position: relative;
      z-index: 2;
      text-align: center;
    }

    .title-gradient {
      background: var(--gradient-primary);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 800;
      margin-bottom: 20px;
    }

    .subtitle {
      font-size: 1.2rem;
      color: var(--gray);
      max-width: 600px;
      margin: 0 auto 40px;
      line-height: 1.6;
    }

    /* Stats Counter */
    .stats-container {
      display: flex;
      justify-content: center;
      gap: 40px;
      margin: 40px 0;
      flex-wrap: wrap;
    }

    .stat-item {
      text-align: center;
      padding: 20px;
      min-width: 150px;
    }

    .stat-number {
      font-size: 2.5rem;
      font-weight: 800;
      background: var(--gradient-primary);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      line-height: 1;
      margin-bottom: 10px;
    }

    .stat-label {
      color: var(--gray);
      font-size: 0.9rem;
      font-weight: 500;
    }

    /* Course Cards - Modern Design */
    .course-card {
      background: white;
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-md);
      transition: var(--transition);
      overflow: hidden;
      height: 100%;
      position: relative;
      border: none;
      
    }

    .course-card:hover {
      transform: translateY(-10px);
      box-shadow: var(--shadow-lg);
    }

    .course-card::before {
      display: none;
    }

    .card-image-container {
      position: relative;
      height: 200px;
      overflow: hidden;
    }

    .course-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .course-card:hover .course-image {
      transform: scale(1.1);
    }

    .image-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(to bottom, transparent 60%, rgba(0, 0, 0, 0.7));
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .course-card:hover .image-overlay {
      opacity: 1;
    }

    .card-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: var(--gradient-secondary);
      color: white;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      z-index: 2;
      box-shadow: var(--shadow-sm);
    }

    .card-content {
      padding: 25px;
      position: relative;
    }

    .course-title {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 12px;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .course-description {
      color: var(--gray);
      font-size: 0.95rem;
      line-height: 1.6;
      margin-bottom: 20px;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .card-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 20px;
      border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    .meta-item {
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--gray);
      font-size: 0.9rem;
    }

    .meta-item i {
      color: var(--primary);
      font-size: 1rem;
    }

    .rating-stars {
      display: flex;
      gap: 2px;
    }

    .rating-stars i {
      color: #FFD700;
      font-size: 0.9rem;
    }

    .card-action {
      padding: 0 25px 25px;
      text-align: center;
    }

    .button-group {
      display: flex;
      gap: 10px;
      justify-content: center;
    }

    .btn-action {
      flex: 1;
      padding: 12px 10px;
      border-radius: var(--radius-sm);
      font-weight: 600;
      transition: var(--transition);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      border: 2px solid transparent;
      text-align: center;
      font-size: 0.9rem;
    }

    .btn-view {
      background: var(--gradient-primary);
      color: white;
      box-shadow: var(--shadow-sm);
    }

    .btn-view:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(1, 106, 112, 0.2);
      color: white;
    }

    .btn-comment {
      background: var(--primary-light);
      color: var(--primary);
    }

    .btn-comment:hover {
      background: var(--primary);
      color: white;
      transform: translateY(-3px);
      box-shadow: var(--shadow-sm);
    }

    .btn-primary-gradient {
      background: var(--gradient-primary);
      border: none;
      color: white;
      padding: 12px 30px;
      border-radius: 10px;
      font-weight: 600;
      transition: var(--transition);
      position: relative;
      overflow: hidden;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      gap: 10px;
    }

    .btn-primary-gradient:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(1, 106, 112, 0.2);
      color: white;
    }

    .btn-primary-gradient::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: 0.5s;
    }

    .btn-primary-gradient:hover::before {
      left: 100%;
    }

    /* Featured Course */
    .featured-course {
      grid-column: 1 / -1;
      background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
      color: white;
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: var(--shadow-lg);
      margin: 40px 0;
    }

    .featured-content {
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .featured-badge {
      background: white;
      color: var(--primary);
      padding: 8px 20px;
      border-radius: 50px;
      font-weight: 700;
      font-size: 0.9rem;
      display: inline-block;
      margin-bottom: 20px;
      width: fit-content;
    }

    .featured-title {
      font-size: 2.2rem;
      font-weight: 800;
      margin-bottom: 20px;
      line-height: 1.2;
    }

    .featured-description {
      font-size: 1.1rem;
      opacity: 0.9;
      margin-bottom: 30px;
      line-height: 1.6;
    }

    .featured-image {
      height: 100%;
      min-height: 300px;
      background-size: cover;
      background-position: center;
      position: relative;
    }

    .featured-image::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(90deg, rgba(0,0,0,0.3) 0%, transparent 100%);
    }

    /* Filter Section */
    .filter-section {
      background: white;
      border-radius: var(--radius-lg);
      padding: 25px;
      box-shadow: var(--shadow-sm);
      margin-bottom: 40px;
    }

    .filter-group {
      display: flex;
      gap: 15px;
      align-items: center;
      flex-wrap: wrap;
    }

    .filter-btn {
      background: var(--light);
      border: 2px solid transparent;
      color: var(--dark);
      padding: 10px 20px;
      border-radius: 50px;
      font-weight: 500;
      transition: var(--transition);
      cursor: pointer;
    }

    .filter-btn:hover,
    .filter-btn.active {
      background: var(--gradient-primary);
      color: white;
      border-color: var(--primary);
    }

    .search-box {
      position: relative;
      flex: 1;
      min-width: 250px;
    }

    .search-box input {
      width: 100%;
      padding: 12px 20px 12px 45px;
      border: 2px solid var(--light);
      border-radius: 50px;
      font-size: 1rem;
      transition: var(--transition);
    }

    .search-box input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(1, 106, 112, 0.1);
    }

    .search-box i {
      position: absolute;
      left: 20px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--gray);
    }

    /* Loading Animation */
    .loading-skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: loading 1.5s infinite;
      border-radius: var(--radius-md);
    }

    @keyframes loading {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    /* No Courses State */
    .no-courses {
      text-align: center;
      padding: 80px 20px;
      background: white;
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-md);
    }

    .no-courses-icon {
      font-size: 5rem;
      background: var(--gradient-primary);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 20px;
      opacity: 0.5;
    }

    .no-courses h3 {
      color: var(--dark);
      margin-bottom: 15px;
      font-weight: 700;
    }

    .no-courses p {
      color: var(--gray);
      font-size: 1.1rem;
      max-width: 500px;
      margin: 0 auto;
    }

    /* Pagination */
    .pagination-custom {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 50px;
    }

    .page-link-custom {
      width: 45px;
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: white;
      border: 2px solid var(--light);
      color: var(--dark);
      border-radius: 12px;
      font-weight: 600;
      transition: var(--transition);
      text-decoration: none;
    }

    .page-link-custom:hover {
      background: var(--gradient-primary);
      color: white;
      border-color: var(--primary);
      transform: translateY(-2px);
    }

    .page-link-custom.active {
      background: var(--gradient-primary);
      color: white;
      border-color: var(--primary);
    }

    /* Animations */
    .fade-in-up {
      animation: fadeInUp 0.6s ease forwards;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .float-animation {
      animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-20px); }
    }

    /* Responsive */
    @media (max-width: 768px) {
      .hero-section {
        padding: 60px 0 40px;
      }
      
      .stats-container {
        gap: 20px;
      }
      
      .stat-item {
        min-width: 120px;
        padding: 15px;
      }
      
      .stat-number {
        font-size: 2rem;
      }
      
      .filter-group {
        flex-direction: column;
        align-items: stretch;
      }
      
      .search-box {
        min-width: 100%;
      }
      
      .featured-title {
        font-size: 1.8rem;
      }
    }
  </style>
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>
<?php include __DIR__ . '/fb_chat_button.php'; ?>

  <!-- Hero Carousel Section -->
  <section class="hero-carousel-section">
      <div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel">
          <div class="carousel-indicators">
              <?php foreach ($carousel_images as $index => $image): ?>
                  <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $index + 1 ?>"></button>
              <?php endforeach; ?>
          </div>
          <div class="carousel-inner">
              <?php if (empty($carousel_images)): ?>
                  <div class="carousel-item active">
                      <img src="<?= $placeholderSrc ?? 'https://via.placeholder.com/1920x1080' ?>" class="d-block w-100" alt="Placeholder Image">
                      <div class="carousel-caption d-none d-md-block">
                  </div>
              <?php else: ?>
                  <?php foreach ($carousel_images as $index => $image): ?>
                      <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>" data-bs-interval="5000">
                          <img src="../uploads/<?= htmlspecialchars($image['image1']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($image['course_name']) ?>">
                          <div class="carousel-caption d-none d-md-block">
                              <h5><?= htmlspecialchars($image['course_name']) ?></h5>
                          </div>
                      </div>
                  <?php endforeach; ?>
              <?php endif; ?>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Next</span>
          </button>
      </div>
  </section>

  <!-- Main Content -->
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
      $limit = 9;
      $offset = ($page - 1) * $limit;

      if ($page < 1) {
        $page = 1;
        $offset = 0;
      }

      // ใช้ LEFT JOIN เพื่อดึง ratings พร้อมกับ courses ในการ query เดียว (แก้ N+1 Problem)
      $query = "SELECT 
                  c.*,
                  ROUND(AVG(cr.rating), 1) AS avg_rating,
                  COUNT(cr.rating) AS rating_count
                FROM courses c
                LEFT JOIN course_rating cr ON c.courses_id = cr.courses_id
                GROUP BY c.courses_id
                ORDER BY c.courses_id DESC 
                LIMIT ? OFFSET ?";
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

      <!-- Courses Grid -->
      <?php if ($result->num_rows > 0): ?>
        <div class="row g-4">
          <?php $animationDelay = 0.2; ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <?php
            $courseId = (int)($row['courses_id'] ?? 0);
            $courseName = htmlspecialchars($row['course_name'] ?? 'ชื่อหลักสูตร', ENT_QUOTES, 'UTF-8');
            $courseDesc = htmlspecialchars($row['course_description'] ?? 'ไม่มีรายละเอียด', ENT_QUOTES, 'UTF-8');

            $images = array_filter([
              $row['image1'] ?? '',
              $row['image2'] ?? '',
              $row['image3'] ?? ''
            ]);

            $firstImage = !empty($images) ? reset($images) : null;
            $imageSrc = $firstImage && is_file($uploadsDir . $firstImage) ? '../uploads/' . htmlspecialchars($firstImage, ENT_QUOTES, 'UTF-8') : $placeholderSrc;

            // ใช้ข้อมูล ratings จาก JOIN query ข้างบนแล้ว (ไม่ต้อง query แยก)
            $avg_rating = (float)($row['avg_rating'] ?? 0);
            $rating_count = (int)($row['rating_count'] ?? 0);

            // เพิ่ม badge สำหรับคอร์สพิเศษ
              // เพิ่ม badge สำหรับคอร์สพิเศษ: แสดง "ยอดนิยม" ถ้ามีผู้เรียน/เรตติ้งมากกว่า 5
              $badge = ($rating_count > 5) ? 'ยอดนิยม' : null;
            ?>

            <div class="col-lg-4 col-md-6">
              <div class="course-card fade-in-up" style="animation-delay: <?php echo $animationDelay; ?>s">
                <?php if ($badge): ?>
                  <div class="card-badge"><?php echo $badge; ?></div>
                <?php endif; ?>
                
                <div class="card-image-container">
                  <img src="<?php echo $imageSrc; ?>" 
                       alt="<?php echo $courseName; ?>" 
                       class="course-image"
                       loading="lazy">
                  <div class="image-overlay"></div>
                </div>
                
                <div class="card-content">
                  <h3 class="course-title"><?php echo $courseName; ?></h3>
                  <p class="course-description"><?php echo $courseDesc; ?></p>
                  
                  <div class="card-meta">
                    <div class="meta-item">
                      <i class="fas fa-users"></i>
                      <span><?php echo $rating_count; ?> ผู้เรียน</span>
                    </div>
                    <div class="meta-item">
                      <div class="rating-stars">
                        <?php
                        $stars = round($avg_rating);
                        for ($i = 1; $i <= 5; $i++) {
                          if ($i <= $stars) {
                            echo '<i class="fas fa-star"></i>';
                          } else {
                            echo '<i class="far fa-star"></i>';
                          }
                        }
                        ?>
                      </div>
                      <span><?php echo number_format($avg_rating, 1, '.', ''); ?></span>
                    </div>
                  </div>
                </div>
                
                <div class="card-action">
                  <div class="button-group">
                    <a href="course_detail.php?id=<?php echo $courseId; ?>" class="btn-action btn-view">
                        <i class="fas fa-eye"></i>
                        <span>ดูรายละเอียด</span>
                    </a>
                    <a href="course_detail.php?id=<?php echo $courseId; ?>#access-code-section" class="btn-action btn-comment">
                        <i class="fas fa-comment-dots"></i>
                        <span>แสดงความเห็น</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
            <?php $animationDelay += 0.1; ?>
          <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <div class="pagination-custom fade-in-up" style="animation-delay: 0.3s">
            <?php if ($page > 1): ?>
              <a href="?page=1" class="page-link-custom">
                <i class="fas fa-angle-double-left"></i>
              </a>
              <a href="?page=<?php echo $page - 1; ?>" class="page-link-custom">
                <i class="fas fa-angle-left"></i>
              </a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
              <a href="?page=<?php echo $i; ?>" 
                 class="page-link-custom <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
              </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
              <a href="?page=<?php echo $page + 1; ?>" class="page-link-custom">
                <i class="fas fa-angle-right"></i>
              </a>
              <a href="?page=<?php echo $totalPages; ?>" class="page-link-custom">
                <i class="fas fa-angle-double-right"></i>
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      <?php else: ?>
        <div class="no-courses fade-in-up">
          <div class="no-courses-icon">
            <i class="fas fa-book-open"></i>
          </div>
          <h3>ยังไม่มีหลักสูตรในขณะนี้</h3>
          <p>เรากำลังเตรียมหลักสูตรคุณภาพสำหรับคุณ โปรดกลับมาตรวจสอบในภายหลัง</p>
          <button class="btn-primary-gradient mt-4" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i>
            รีเฟรชหน้านี้
          </button>
        </div>
      <?php endif; ?>

      <?php $stmt->close(); ?>
    </div>
  </section>

  <?php include __DIR__ . '/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
  <script>
    // Stats Counter Animation
    document.addEventListener('DOMContentLoaded', function() {
      // Add hover effects to cards
      const cards = document.querySelectorAll('.course-card');
      cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
          gsap.to(card, {
            scale: 1.02,
            duration: 0.3,
            ease: "power2.out"
          });
        });
        
        card.addEventListener('mouseleave', () => {
          gsap.to(card, {
            scale: 1,
            duration: 0.3,
            ease: "power2.out"
          });
        });
      });

      // Filter button interaction
      const filterBtns = document.querySelectorAll('.filter-btn');
      filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          filterBtns.forEach(b => b.classList.remove('active'));
          this.classList.add('active');
          
          // Add ripple effect
          const ripple = document.createElement('span');
          const rect = this.getBoundingClientRect();
          const size = Math.max(rect.width, rect.height);
          const x = event.clientX - rect.left - size / 2;
          const y = event.clientY - rect.top - size / 2;
          
          ripple.style.cssText = `
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            transform: scale(0);
            animation: ripple 0.6s linear;
            width: ${size}px;
            height: ${size}px;
            top: ${y}px;
            left: ${x}px;
          `;
          
          this.appendChild(ripple);
          setTimeout(() => ripple.remove(), 600);
        });
      });

      // Search functionality
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
          clearTimeout(searchTimeout);
          searchTimeout = setTimeout(() => {
            const searchTerm = this.value.toLowerCase();
            const cards = document.querySelectorAll('.course-card');
            
            cards.forEach(card => {
              const title = card.querySelector('.course-title').textContent.toLowerCase();
              const description = card.querySelector('.course-description').textContent.toLowerCase();
              
              if (title.includes(searchTerm) || description.includes(searchTerm)) {
                card.style.display = 'block';
                gsap.fromTo(card, 
                  { opacity: 0, y: 20 },
                  { opacity: 1, y: 0, duration: 0.5 }
                );
              } else {
                gsap.to(card, {
                  opacity: 0,
                  y: 20,
                  duration: 0.3,
                  onComplete: () => {
                    card.style.display = 'none';
                  }
                });
              }
            });
          }, 300);
        });
      }

      // Add CSS for ripple effect
      const style = document.createElement('style');
      style.textContent = `
        @keyframes ripple {
          to {
            transform: scale(4);
            opacity: 0;
          }
        }
      `;
      document.head.appendChild(style);

    });
  </script>
</body>
</html>