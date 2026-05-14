<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../db/db.php';

// ตรวจสอบสถานะผู้ใช้ที่เข้าสู่ระบบ
if (isset($_SESSION['member_id'])) {
  $member_id_for_status_check = $_SESSION['member_id'];
  $stmt_status = $conn->prepare("SELECT status FROM members WHERE member_id = ?");
  if ($stmt_status) {
    $stmt_status->bind_param("i", $member_id_for_status_check);
    $stmt_status->execute();
    $result_status = $stmt_status->get_result();
    if ($row_status = $result_status->fetch_assoc()) {
      if ((int)$row_status['status'] === 0) {
        // บัญชีถูกปิดใช้งาน, ทำลาย session และ redirect
        session_unset();
        session_destroy();
        header('Location: index.php?login_error=disabled');
        exit;
      }
    }
    $stmt_status->close();
  }
}

// Fetch images for the hero carousel
$carousel_images = [];
$carousel_sql = "SELECT course_name, image1 FROM courses WHERE image1 IS NOT NULL AND image1 != '' ORDER BY courses_id DESC LIMIT 7";
$carousel_result = $conn->query($carousel_sql);
if ($carousel_result) {
  while ($carousel_row = $carousel_result->fetch_assoc()) {
    $carousel_images[] = $carousel_row;
  }
}

$heroImage = '../uploads/placeholder.jpg';

if (!empty($carousel_images)) {
  $firstHero = $carousel_images[0]['image1'];

  if (is_file(__DIR__ . '/../uploads/' . $firstHero)) {
    $heroImage = '../uploads/' . htmlspecialchars($firstHero, ENT_QUOTES, 'UTF-8');
  }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <link rel="apple-touch-icon" sizes="180x180" href="/mango/logo/logo_01.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/mango/logo/logo_01.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>สวนลุงเผือก</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

  <style>
    :root {

      --sage: #016A70;
      --sage-light: #2ad3bc;

      --bg: #f5f7f8;

      --card: #ffffff;

      --text: #163336;

      --muted: #6b7c7d;

      --border: #dfe9ea;

      --primary: var(--sage);

      --primary-light: #e8f7f5;

      --accent: var(--sage-light);

      --light: #f8fafc;

      --dark: var(--text);

      --gray: #8ca3a5;

      --gradient-primary:
        linear-gradient(135deg,
          var(--sage),
          var(--sage-light));

      --gradient-secondary:
        linear-gradient(135deg,
          #01949a,
          #2ad3bc);

      --shadow-sm:
        0 4px 10px rgba(1, 106, 112, .05);

      --shadow-md:
        0 10px 30px rgba(1, 106, 112, .08);

      --shadow-lg:
        0 20px 50px rgba(1, 106, 112, .12);

      --radius-lg: 24px;

      --radius-md: 18px;

      --radius-sm: 12px;

      --transition: .3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {

      font-family: 'Kanit', sans-serif;

      background: var(--bg);

      color: var(--text);

      min-height: 100vh;

      overflow-x: hidden;

      line-height: 1.6;
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

    /* Hero */

    /* HERO BANNER */

    .hero-banner {

      padding: 35px 0;

      position: relative;

      overflow: hidden;
    }

    .hero-banner::before {

      content: '';

      position: absolute;

      width: 600px;

      height: 600px;

      background: rgba(42, 211, 188, 0.10);

      border-radius: 50%;

      top: -250px;

      right: -180px;

      filter: blur(60px);

      z-index: 0;
    }

   
    /* LEFT CONTENT */

    .hero-content {

      position: relative;

      z-index: 2;
    }

    .hero-chip {

      display: inline-flex;

      align-items: center;

      gap: 10px;

      background: #e8faf7;

      color: var(--sage);

      padding: 10px 18px;

      border-radius: 999px;

      font-weight: 600;

      margin-bottom: 24px;

      font-size: .95rem;

      box-shadow: var(--shadow-sm);
    }


    .hero-title {

      font-size: 3.8rem;

      font-weight: 800;

      line-height: 1.08;

      margin-bottom: 24px;

      color: var(--text);

      letter-spacing: -1px;
    }

    .hero-desc {

      font-size: 1.05rem;

      color: var(--muted);

      line-height: 1.9;

      max-width: 560px;

      margin-bottom: 20px;
    }

    .hero-text {

      font-size: 1.05rem;

      color: var(--muted);

      line-height: 1.9;

      max-width: 560px;

      margin-bottom: 30px;
    }

    .hero-buttons {

      display: flex;

      gap: 15px;

      flex-wrap: wrap;

      margin-bottom: 40px;
    }

    .hero-btn {

      padding: 14px 24px;

      border-radius: 14px;

      text-decoration: none;

      font-weight: 600;

      display: inline-flex;

      align-items: center;

      gap: 10px;

      transition: .3s ease;
    }

    .hero-btn-primary {

      background: var(--gradient-primary);

      color: white;

      box-shadow: var(--shadow-md);
    }

    .hero-btn-primary:hover {

      transform: translateY(-3px);

      color: white;
    }

    .hero-btn-outline {

      border: 1px solid var(--border);

      background: white;

      color: var(--text);
    }

    .hero-btn-outline:hover {

      background: #f7f7f7;

      color: var(--text);
    }

    .hero-stats {

      display: flex;

      gap: 35px;

      flex-wrap: wrap;
    }

    .hero-stat h3 {

      font-size: 2rem;

      margin-bottom: 6px;

      font-weight: 800;

      color: var(--sage);
    }

    .hero-stat span {

      color: var(--muted);
    }

    .hero-image-wrap {

      position: relative;

      z-index: 2;
    }


    .hero-main-image {

      width: 100%;

      height: 460px;

      object-fit: cover;

      border-radius: 34px;

      box-shadow: 0 30px 60px rgba(0, 0, 0, .12);

      position: relative;

      z-index: 2;
    }

    .hero-glow {

      position: absolute;

      width: 100%;

      height: 100%;

      background: linear-gradient(135deg,
          rgba(42, 211, 188, .25),
          rgba(1, 106, 112, .18));
      border-radius: 34px;

      top: 20px;

      left: 20px;

      z-index: 1;

      filter: blur(10px);
    }

    .hero-floating {

      position: absolute;

      background: rgba(255, 255, 255, .75);

      backdrop-filter: blur(16px);

      border: 1px solid rgba(255, 255, 255, .4);

      padding: 14px 18px;

      border-radius: 18px;

      box-shadow: var(--shadow-md);

      display: flex;

      align-items: center;

      gap: 10px;

      font-weight: 600;

      color: var(--text);

      z-index: 3;

      animation: floating 4s ease-in-out infinite;
    }

    .floating-top {

      top: 30px;

      left: -30px;
    }

    .floating-middle {

      top: 50%;

      right: -30px;

      transform: translateY(-50%);
    }

    .floating-bottom {

      bottom: 30px;

      left: 20px;
    }

    .hero-floating i {

      color: var(--sage);

      font-size: 1rem;
    }

    .floating-card {

      position: absolute;

      background: rgba(255, 255, 255, .7);

      backdrop-filter: blur(16px);

      border: 1px solid rgba(255, 255, 255, .4);

      padding: 14px 18px;

      border-radius: 18px;

      box-shadow: var(--shadow-md);

      display: flex;

      align-items: center;

      gap: 10px;

      font-weight: 600;

      animation: float 4s ease-in-out infinite;
    }

    /* FLOAT ANIMATION */

    @keyframes floating {

      0%,
      100% {
        transform: translateY(0);
      }

      50% {
        transform: translateY(-12px);
      }
    }

    .floating-card i {

      color: var(--sage);
    }

    .floating-1 {

      top: 30px;

      left: -30px;
    }

    .floating-2 {

      bottom: 40px;

      right: -20px;
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

      background: var(--card);

      border-radius: 24px;

      overflow: hidden;

      border: 1px solid var(--border);

      box-shadow: var(--shadow-md);

      transition: .3s ease;

      height: 100%;

      position: relative;
    }

    .course-card:hover {

      transform: translateY(-4px);

      box-shadow: var(--shadow-lg);
    }

    .course-card::before {
      display: none;
    }

    .card-image-container {

      position: relative;

      aspect-ratio: 16/10;

      overflow: hidden;
    }

    .course-image {

      width: 100%;

      height: 100%;

      object-fit: cover;

      transition: transform .5s ease;
    }

    .course-card:hover .course-image {
      transform: scale(1.05);
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
      font-size: 1.08rem;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 12px;
      line-height: 1.5;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .course-description {
      color: var(--muted);

      font-size: .93rem;

      line-height: 1.7;
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

      background: var(--sage);

      color: white;

      border: none;
    }

    .btn-view:hover {

      background: #01565a;

      transform: translateY(-2px);

      color: white;
    }

    .btn-comment {

      background: #eef7f6;

      color: var(--sage);

      border: 1px solid #d7ece9;
    }

    .btn-comment:hover {

      background: var(--sage);

      color: white;

      transform: translateY(-2px);
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
      background: linear-gradient(90deg, rgba(0, 0, 0, 0.3) 0%, transparent 100%);
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
      0% {
        background-position: 200% 0;
      }

      100% {
        background-position: -200% 0;
      }
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

      width: 42px;

      height: 42px;

      display: flex;

      align-items: center;

      justify-content: center;

      background: white;

      border: 1px solid var(--border);

      color: var(--text);

      border-radius: 14px;

      font-weight: 600;

      transition: .25s ease;

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

      0%,
      100% {
        transform: translateY(0);
      }

      50% {
        transform: translateY(-20px);
      }
    }

    /* Horizontal Scroll */

    .course-scroll {

      display: flex;

      gap: 18px;

      overflow-x: auto;

      padding-bottom: 10px;

      scroll-snap-type: x mandatory;

      -webkit-overflow-scrolling: touch;
    }

    .course-scroll::-webkit-scrollbar {

      height: 8px;
    }

    .course-scroll::-webkit-scrollbar-thumb {

      background: #cfd8dc;

      border-radius: 20px;
    }

    .course-item {

      flex: 0 0 340px;

      scroll-snap-align: start;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .hero-title {

        font-size: 2.4rem;

        text-align: center;
      }

      .hero-desc {

        text-align: center;
        margin-bottom: 20px;
        margin-inline: auto;
      }

      .hero-chip {

        margin-inline: auto;
      }

      .hero-buttons {

        justify-content: center;
      }

      .hero-stats {

        justify-content: center;

        gap: 24px;
      }

      .hero-main-image {

        height: 340px;

        border-radius: 24px;
      }

      .hero-floating {

        font-size: .82rem;

        padding: 10px 14px;
      }

      .floating-top {

        left: 10px;

        top: 10px;
      }

      .floating-middle {

        right: 10px;
      }

      .floating-bottom {

        bottom: 10px;

        left: 10px;
      }

      .course-item {

        flex: 0 0 85%;
      }

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

      .button-group {

        flex-direction: column;
      }

      .btn-action {

        width: 100%;
      }

      .hero-carousel .carousel-item {

        height: 320px;
    
      }
.carousel-item {
  transition: transform 1s ease-in-out;
}
      .course-card {

        border-radius: 20px;
      }

      .card-content {

        padding: 18px;
      }

      /* HERO MOBILE */

      .hero-banner {

        padding: 40px 0 20px;
      }

      .hero-content {

        text-align: center;
      }

      .hero-title {

        font-size: 2.3rem;

        line-height: 1.15;

        margin-bottom: 18px;
      }

      .hero-text {

        font-size: .96rem;

        margin-inline: auto;

        line-height: 1.8;
      }

      .hero-buttons {
        width: 100%;
        justify-content: center;

        gap: 12px;
      }

      .hero-btn {

        width: 100%;

        justify-content: center;

        padding: 14px;
      }

      .hero-stats {

        justify-content: center;

        gap: 24px;

        margin-top: 10px;
      }

      .hero-stat {

        min-width: 90px;
      }

      .hero-stat h3 {

        font-size: 1.6rem;
      }

      /* IMAGE */



      .hero-main-image {

        height: 320px;

        border-radius: 24px;
      }

      /* FLOATING CARDS */

    }


    /* tablet responsive */
    @media (min-width: 769px) and (max-width: 991px) {

      .hero-banner {

        padding: 50px 0;
      }

      .hero-content {

        text-align: center;
      }



      .hero-buttons {

        justify-content: center;
      }

      .hero-stats {

        justify-content: center;
      }

      .hero-main-image {

        height: 420px;

        margin-top: 30px;
      }

      .floating-1 {

        left: 10px;
      }

      .floating-2 {

        right: 10px;
      }
    }

    .course-card {

      margin-bottom: 4px;
    }

    .card-meta {

      flex-direction: column;

      align-items: flex-start;

      gap: 10px;
    }

    .card-action {

      padding: 0 18px 18px;
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/navbar.php'; ?>
  <?php include __DIR__ . '/fb_chat_button.php'; ?>

  <?php

  // จำนวนกิจกรรมทั้งหมด
  $totalCourses = 0;

  $sqlCourses = "SELECT COUNT(*) AS total FROM courses";
  $resultCourses = $conn->query($sqlCourses);

  if ($resultCourses && $rowCourses = $resultCourses->fetch_assoc()) {
    $totalCourses = (int)$rowCourses['total'];
  }


  // จำนวนผู้เข้าร่วมทั้งหมด
  $totalParticipants = 0;

  // ตัวอย่างนับจากคนให้คะแนน
  $sqlParticipants = "SELECT SUM(visitor_count) AS total FROM bookings";
  $resultParticipants = $conn->query($sqlParticipants);

  if ($resultParticipants && $rowParticipants = $resultParticipants->fetch_assoc()) {
    $totalParticipants = (int)$rowParticipants['total'];
  }
  ?>
  <section class="hero-banner">

    <div class="container">

      <div class="row align-items-center gy-5">

        <!-- LEFT -->
        <div class="col-lg-6">

          <div class="hero-content">

            <div class="hero-chip">
              <i class="fas fa-seedling"></i>
              ระบบประเมินความพึงพอใจกิจกรรม
            </div>

            <h1 class="hero-title">
              ร่วมเรียนรู้<br>
              ผ่านกิจกรรม<br>
              และประสบการณ์จริง<br>
              ภายในสวน
            </h1>

            <p class="hero-desc">

              ระบบประเมินกิจกรรมและอบรมภายในสวน
              เพื่อสะท้อนความคิดเห็น ความประทับใจ
              และช่วยพัฒนากิจกรรมให้ดียิ่งขึ้น

            </p>

            <div class="hero-buttons">

              <a href="#course-section"
                class="hero-btn hero-btn-primary">

                <i class="fas fa-leaf"></i>
                ดูกิจกรรมทั้งหมด

              </a>

              <a href="#"
                class="hero-btn hero-btn-outline">

                <i class="fas fa-star"></i>
                แสดงความคิดเห็น

              </a>

            </div>

            <div class="hero-stats">

              <div class="hero-stat">
                <h3><?= number_format($totalCourses) ?></h3>
                <span>กิจกรรมอบรมทั้งหมด</span>
              </div>

              <div class="hero-stat">
                <h3><?= shortNumber($totalParticipants) ?></h3>
                <span>ผู้เข้าร่วม</span>
              </div>

            </div>

          </div>

        </div>

        <!-- RIGHT -->
        <div class="col-lg-6">

          <div class="hero-image-wrap">

            

            <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">

  <div class="carousel-inner">

    <?php if (!empty($carousel_images)): ?>

      <?php foreach ($carousel_images as $index => $image): ?>

        <?php
        $imgFile = $image['image1'] ?? '';
        $imgPath = '../uploads/' . htmlspecialchars($imgFile, ENT_QUOTES, 'UTF-8');

        $realPath = __DIR__ . '/../uploads/' . $imgFile;

        if (!is_file($realPath)) {
          continue;
        }
        ?>

        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">

          <img src="<?= $imgPath ?>"
            class="hero-main-image d-block w-100"
            alt="<?= htmlspecialchars($image['course_name']) ?>">

        </div>

      <?php endforeach; ?>

    <?php else: ?>

      <div class="carousel-item active">
        <img src="../uploads/placeholder.jpg"
          class="hero-main-image d-block w-100"
          alt="ไม่มีรูปภาพ">
      </div>

    <?php endif; ?>

  </div>

</div>

            <!-- FLOATING CARD -->

            <div class="hero-floating floating-top">
              <i class="fas fa-users"></i>
              เรียนรู้ร่วมกัน
            </div>

            <div class="hero-floating floating-bottom">
              <i class="fas fa-tree"></i>
              กิจกรรมที่น่าสนใจภายในสวน
            </div>

            <div class="hero-floating floating-middle">
              <i class="fas fa-heart"></i>
              ความประทับใจจากผู้เข้าร่วม
            </div>

          </div>

        </div>

      </div>

    </div>

  </section>

  <!-- Main Content -->
  <section class="py-5">
    <div class="container px-3 px-md-4">

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
      <div id="course-section">
        <?php if ($result->num_rows > 0): ?>
          <div class="course-scroll">
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

              <div class="course-item">
                <div class="course-card fade-in-up d-flex flex-column" style="animation-delay: <?php echo $animationDelay; ?>s">
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

                  <div class="card-content flex-grow-1">
                    <h3 class="course-title"><?php echo $courseName; ?></h3>
                   

                    <div class="card-meta">
                      <div class="meta-item">
                        <i class="fas fa-users"></i>
                        <span><?php echo $rating_count; ?> คนให้คะแนน</span>
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
  <script>
    <?php
    function shortNumber($num)
    {
      if ($num >= 1000000) {
        return round($num / 1000000, 1) . 'M+';
      }

      if ($num >= 1000) {
        return round($num / 1000, 1) . 'K+';
      }

      return number_format($num) . '+';
    }
    ?>
    // Stats Counter Animation
    document.addEventListener('DOMContentLoaded', function() {


      // Filter button interaction
      const filterBtns = document.querySelectorAll('.filter-btn');
      filterBtns.forEach(btn => {
        btn.addEventListener('click', function(event) {
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