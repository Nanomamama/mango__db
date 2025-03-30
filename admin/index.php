<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: "Kanit", sans-serif;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="bg-dark text-white p-3" style="width: 250px; height: 150vh;">
            <h3 class="text-center"> Admin Panel</h3>
            <ul class="nav flex-column">

                <li class="nav-item"><a href="./index.php" class="nav-link text-white">
                    <i class='bx bx-home-alt'></i>
                        Dashboard</a></li>
                <li class="nav-item"><a href="./edit_home.php" class="nav-link text-white">
                    <i class='bx bx-notepad'></i>
                        จัดการหน้าแรก</a></li>
                <li class="nav-item"><a href="./edit_owner.php" class="nav-link text-white">
                    <i class='bx bx-user'></i>
                        ข้อมูลเจ้าของ</a></li>
                <li class="nav-item"><a href="./edit_courses.php" class="nav-link text-white">
                    <i class='bx bx-book'></i>
                        หลักสูตร</a></li>
                <li class="nav-item"><a href="./manage_mango.php" class="nav-link text-white">
                    <i class='bx bx-lemon'></i>
                        จัดการสายพันธุ์มะม่วง</a></li>
                <li class="nav-item"><a href="./manage_mango.php" class="nav-link text-white">
                    <i class='bx bx-lemon'></i>
                        จัดการสินค้าผลิตภัณฑ์</a></li>

                <li class="nav-item">
                    <a href="./booking_list.php" class="nav-link text-white">
                        <i class='bx bxs-calendar'></i> ตารางรายการจอง
                    </a>
                </li>

            </ul>
        </div>

        <!-- Main Content -->
        <div class="p-4" style="flex: 1;">
            <h2><i class='bx bxs-dashboard'></i> Admin Dashboard</h2>
            <div class="row mt-4">
                <!-- Card Sections -->
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">🌍 เนื้อหาในหน้าแรก</h5>
                            <p class="card-text">แก้ไขรายละเอียดในหน้าแรกของเว็บไซต์</p>
                            <a href="edit_home.php" class="btn btn-light">แก้ไข</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">👤 ข้อมูลเจ้าของ</h5>
                            <p class="card-text">จัดการข้อมูลเจ้าของแหล่งเรียนรู้</p>
                            <a href="edit_owner.php" class="btn btn-light">แก้ไข</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title">📚 หลักสูตรการเรียนรู้</h5>
                            <p class="card-text">จัดการหลักสูตรที่มีอยู่ในเว็บไซต์</p>
                            <a href="edit_courses.php" class="btn btn-light">แก้ไข</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">🥭 จัดการสายพันธุ์มะม่วง</h5>
                            <p class="card-text">จัดการสายพันธุ์มะม่วงในเว็บไซต์</p>
                            <a href="manage_mango.php" class="btn btn-light">แก้ไข</a>
                        </div>
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title"> สินค้าผลิตภัณฑ์แปรรูป</h5>
                            <p class="card-text">จัดการสายพันธุ์มะม่วงในเว็บไซต์</p>
                            <a href="manage_product.php" class="btn btn-light">แก้ไข</a>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</body>

</html>