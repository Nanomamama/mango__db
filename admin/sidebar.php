<link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
<style>
    * {
        font-family: "Kanit", sans-serif;
    }
    :root {
    --main-color: #5342ed;
    --light-color: #f7f7f7;
    --grey-color: #9f9f9f;
    --dark-color: #000000;
    --primary-color: #4B0082;
    --secondary-color: #D63384;
    --darkprimary-color: #9b42f5;
    --asas: #212529;
}

    .nav-link {
        transition: background-color 0.3s, color 0.3s;
    }

    .nav-link:hover {
        background-color: var(--light-color);
        color: black !important;
    }
</style>
<div class="bg-dark text-white p-3" style="width: 250px; height: 100vh; position: fixed;">
    <h3 class="text-center">Admin Panel</h3>
    <ul class="nav flex-column">
        <a href="./index.php" id="dashboard" class="nav-link text-white">
            <i class='bx bxs-dashboard'></i>
            Dashboard
        </a>

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
        <li class="nav-item"><a href="./manage_product.php" class="nav-link text-white">
                <i class='bx bx-lemon'></i>
                จัดการสินค้าผลิตภัณฑ์</a></li>

        <li class="nav-item"><a href="./booking_list.php" class="nav-link text-white">
                <i class='bx bxs-calendar'></i>
                ตารางรายการจอง</a></li>
    </ul>

</div>