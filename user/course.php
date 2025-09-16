<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>หลักสูตรการอบรมแบบมีฐานการเรียนรู้</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
  <style>
    :root {
      --green-color: #016A70;
      --white-color: #fff;
      --Danger: #e74a3b;
      --Light: #f8f9fc;
    }

    .hero h2,
    .hero p {
      text-align: center;
      font-weight: 600;
      color: var(--Danger);
    }

    .card-body h5 {
      color: var(--Danger);
      font-weight: 600;
    }

    .card-body a {
      padding: 0.5rem 1.5rem;
      border-radius: 20px;
      font-weight: bold;
      color: var(--Danger);
      border: 1px solid var(--Danger);
      background-color: transparent;
      transition: background-color 0.5s ease, color 0.5s ease;
    }

    .card-body a:hover {
      background-color: var(--Danger);
      color: var(--Light);
      transition: 0.5s;
    }

    .carousel-inner img {
      height: 250px;
      object-fit: cover;
    }
  </style>
</head>

<body>
  <?php include 'navbar.php'; ?>

  <?php
  require_once '../admin/db.php'; // เชื่อมต่อฐานข้อมูล

  // ดึงข้อมูลหลักสูตรจากฐานข้อมูล
  $query = "SELECT * FROM courses";
  $result = $conn->query($query);

  if (!$result) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลหลักสูตร: " . $conn->error);
  }
  ?>

  <!-- Course Section -->
  <section class="bg-light py-5">
    <div class="container">
      <br>
      <h2 class="text-center mb-4 mt-5">กิจกรรมการอบรม</h2>
      <br>
      <div class="row justify-content-center">
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
              <div class="card">
                <div id="carouselCourse<?php echo $row['id']; ?>" class="carousel slide" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <div class="carousel-item active">
                      <img src="../uploads/<?php echo $row['image1']; ?>" class="d-block w-100" alt="รูปที่ 1">
                    </div>
                    <?php if (!empty($row['image2'])): ?>
                      <div class="carousel-item">
                        <img src="../uploads/<?php echo $row['image2']; ?>" class="d-block w-100" alt="รูปที่ 2">
                      </div>
                    <?php endif; ?>
                    <?php if (!empty($row['image3'])): ?>
                      <div class="carousel-item">
                        <img src="../uploads/<?php echo $row['image3']; ?>" class="d-block w-100" alt="รูปที่ 3">
                      </div>
                    <?php endif; ?>
                  </div>
                  <button class="carousel-control-prev" type="button" data-bs-target="#carouselCourse<?php echo $row['id']; ?>" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#carouselCourse<?php echo $row['id']; ?>" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                  </button>
                </div>
                <div class="card-body text-center">
                  <h5 class="card-title"><?php echo htmlspecialchars($row['course_name']); ?></h5>
                  <a role="button" class="btn btn-outline-danger toggle-details" data-bs-toggle="collapse" data-bs-target="#details-<?php echo $row['id']; ?>">ดูรายละเอียด</a>
                  <div id="details-<?php echo $row['id']; ?>" class="collapse">
                    <p><?php echo htmlspecialchars($row['course_description']); ?></p>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="text-center">ไม่มีข้อมูลหลักสูตร</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php include 'footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  
</body>

</html>

<?php
$conn->close();
?>
