<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@100;400;700&family=Prompt:wght@100;400;700&family=Roboto:wght@100;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    
    <style>
        :root {
            --green-color: #016A70;
            --white-color: #fff;
            --Primary: #4e73df;
            --Success:#277859;
            --Info: #36b9cc;
            --Warning: #f6c23e;
            --Danger: #e74a3b;
            --Light: #f8f9fc;
            --Dark: #5a5c69;
            --Darks: #000;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Prompt", sans-serif;
        }

        .navbar {
            position: fixed;
            width: 100%;
            height: 5rem;
            /* background-color: var(--Success); */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: background-color 0.3s ease;
        }

        .navbar.scrolled {
            background-color: var(--white-color);
        }

        .navbar-logo {
            color: var(--Danger);
            font-size: 1.5rem;
            font-weight: 800;
            text-decoration: none;
        }

        .navbar-toggler {
            border: none;
            outline: none;
        }

        .navbar-toggler-icon {
            background-image: url('https://cdn-icons-png.flaticon.com/512/1828/1828859.png');
            width: 30px;
            height: 30px;
        }

        .navbar-nav .nav-link {
            color: var(--Success);
            margin-right: 1rem;
            font-size: 18px;
            font-weight: 500;
            position: relative;
            text-decoration: none;
            padding-bottom: 4px;
        }

        .navbar-nav .nav-link::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 0%;
            height: 4px;
            background-color: var(--Success);
            transition: width 0.3s ease-in-out;
        }

        .navbar-nav .nav-link:hover::after {
            width: 100%;
        }

        .navbar-nav .nav-link:hover {
            color: var(--Success);
        }

        .cta-button {
            border-radius: 20px;
            padding: 0.5rem 1.5rem;
            font-weight: bold;
            color: var(--white-color);
            background-color: var(--Danger);
            border: 1px solid var(--Danger);
            transition: background-color 0.5s ease, color 0.5s ease;
        }

        .cta-button:hover {
            background-color: var(--white-color);
            color: var(--Danger);
        }

        .navbar-collapse {
            background-color: var(--Light);
            padding:1rem;
            border-radius: 10px;
        }
        .navbar-toggler {
            font-style: 24px;
            color:var(--Danger);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-white ">
        <div class="container">
            <a class="navbar-brand" href="../user/index.php">
                <img src="../user/image/สวนลุงเผือก4-Photoroom.png" alt="สวนลุงเผือก" style="max-height:80px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="">เมนู</span> 
            </button>
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <img src="../user/image/สวนลุงเผือก4-Photoroom.png" alt="สวนลุงเผือก" style="max-height:100px;">
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link" href="../user/index.php">หน้าแรก</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../user/mango_varieties.php">สายพันธุ์ทั้งหมด</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../user/products.php">สินค้าผลิตภัณฑ์</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../user/course.php">หลักสูตรการเรียนรู้</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../user/activities.php">จองวันเข้าดูงาน</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('scroll', function () {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
            const navbarToggler = document.querySelector(".navbar-toggler");
            const navbarNav = document.getElementById("navbarNav");
            const bsCollapse = new bootstrap.Collapse(navbarNav, { toggle: false });
            
            navbarToggler.addEventListener("click", function () {
                const isExpanded = navbarToggler.getAttribute("aria-expanded") === "true";
                if (!isExpanded) {
                    bsCollapse.show();
                } else {
                    bsCollapse.hide();
                }
            });

            document.addEventListener("click", function (event) {
                if (!navbarNav.contains(event.target) && !navbarToggler.contains(event.target)) {
                    bsCollapse.hide();
                }
            });
        });
    </script>
</body>

</html>
