<?php

require_once 'auth.php';
require_once '../admin/db.php';

// ดึงชื่อ admin จาก session
$admin_name = $_SESSION['admin_name'] ?? '';
$admin_email = $_SESSION['admin_email'] ?? '';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT *, mango_id AS id FROM mango_varieties WHERE mango_name LIKE ?";
$stmt = $conn->prepare($sql);
$like = "%$search%";
$stmt->bind_param("s", $like);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสายพันธุ์มะม่วง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet">
    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #4CAF50;
            --primary-dark: #388E3C;
            --primary-light: #C8E6C9;
            --accent-color: #FF9800;
            --text-dark: #333;
            --text-light: #666;
            --light-bg: #f8f9fa;
            --white: #ffffff;
            --red: #f44336;
            --blue: #4361EE;
            --border-radius: 12px;
            --shadow-sm: 0 4px 12px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        /* เพิ่ม keyframe สำหรับอนิเมชัน */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* เพิ่มอนิเมชันให้การ์ด */
        .mango-card {
            animation: fadeIn 0.5s ease-out;
            animation-fill-mode: both;
        }

        /* เพิ่มอนิเมชันเมื่อไม่มีผลลัพธ์ */
        .no-results {
            animation: fadeIn 1s ease;
        }

        /* เพิ่มเอฟเฟกต์ hover รูปภาพ */
        .mango-img {
            transition: all 0.3s ease;
        }

        .mango-img:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        /* สไตล์ปุ่มเลื่อนขึ้น */
        .scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 99;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #43cea2;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transform: translateY(100px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
        }

        .scroll-top.show {
            opacity: 1;
            transform: translateY(0);
        }

        .scroll-top:hover {
            background: #185a9d;
            transform: translateY(-3px);
        }

        /* เพิ่มอนิเมชันการโหลด */
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }

            100% {
                background-position: 1000px 0;
            }
        }

        .card-placeholder {
            height: 300px;
            background: linear-gradient(to right, #f6f7f8 0%, #e9ebee 20%, #f6f7f8 40%, #f6f7f8 100%);
            background-size: 1000px 100%;
            animation: shimmer 1.5s infinite linear;
            border-radius: 18px;
        }

        /* สไตล์เดิมทั้งหมด */
        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
        }

        .mango-card {
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(67, 97, 238, 0.10);
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
            background: #fff;
        }

        .mango-card:hover {
            transform: translateY(-6px) scale(1.03);
            box-shadow: 0 8px 32px rgba(67, 97, 238, 0.18);
        }

        .mango-img {
            width: 100%;
            height: 220px;
            object-fit: contain;
            background: #f8f9fa;
            display: block;
            margin: 0 auto;
            padding: 8px 0;
        }

        .mango-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #185a9d;
        }

        .mango-actions .btn {
            margin-right: 0.5rem;
        }

        .action-btn {
            border-radius: 50px;
            padding: 0.5rem 1.2rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn i {
            margin-right: 6px;
        }

        .btn-view {
            background: var(--primary-color);
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }

        .btn-view:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-delete {
            background: var(--red);
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }

        .btn-delete:hover {
            background: var(--red);
            transform: translateY(-2px);
        }

        .mango-actions .btn:last-child {
            margin-right: 0;
        }

        .add-btn {
            border-radius: 20px;
            font-weight: bold;
            padding: 0.5rem 1.5rem;
        }

        .mango-header {
            margin-bottom: 2rem;
        }

        .header-icon {
            font-size: 3rem;
            color: #43cea2;
            margin-bottom: 0.5rem;
        }

        .lead {
            font-size: 1.125rem;
            font-weight: 300;
            color: #6c757d;
        }

        .dashboard-header {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: white;
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            position: relative;
            overflow: hidden;
            z-index: 10;
            border-radius: 50px;
        }

        .dashboard-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        .dashboard-title::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50%;
            height: 3px;
            background: white;
            border-radius: 3px;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .admin-profile:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .admin-profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .admin-profile span {
            font-weight: 500;
            color: white;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="p-4" style="margin-left: 250px; flex: 1;">

        <!-- Header -->
        <header class="dashboard-header">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h2 class="dashboard-title mb-0">จัดการสายพันธุ์มะม่วง</h2>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-2 mt-md-0">
                        <div class="admin-profile">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=random&color=fff" alt="Admin">
                            <span><?= htmlspecialchars($admin_name) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <br>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ลบข้อมูลเรียบร้อยแล้ว
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <!-- รายการมะม่วง -->
        <div id="mangoList" class="row g-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($mango = $result->fetch_assoc()): ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="mango-card card h-100">
                            <img src="<?= !empty($mango['fruit_image']) ? $mango['fruit_image'] : 'https://via.placeholder.com/300x180?text=No+Image'; ?>"
                                class="mango-img card-img-top" alt="<?= htmlspecialchars($mango['mango_name']); ?>">
                            <div class="card-body d-flex flex-column">
                                <div class="mango-title"><?= htmlspecialchars($mango['mango_name']); ?></div>
                                <div class="mt-auto mango-actions">

                                    <a href="view_mango.php?id=<?= $mango['id']; ?>" class="btn btn-view">
                                        <i class="fas fa-eye me-2"></i>ดูรายละเอียด
                                    </a>
                                    <button type="button" class="btn btn-delete"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        data-id="<?= $mango['id']; ?>"
                                        data-name="<?= htmlspecialchars($mango['mango_name']); ?>">
                                        <i class='bx bx-trash'></i> ลบ
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 no-results">
                    <i class="bx bx-search" style="font-size:5rem; color:#e9ecef;"></i>
                    <h3 class="mt-3 text-muted">ไม่พบสายพันธุ์มะม่วงที่ค้นหา</h3>
                    <p class="text-muted">ลองค้นหาด้วยคำสำคัญอื่นหรือเพิ่มสายพันธุ์ใหม่</p>
                </div>
            <?php endif; ?>
        </div>
        <br>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="add_mango.php" class="btn btn-primary add-btn"><i class='bx bx-plus'></i> เพิ่มสายพันธุ์</a>
        </div>
    </div>


    <!-- Modal ยืนยันการลบ -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="delete_mango.php">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteModalLabel">ยืนยันการลบ</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        คุณแน่ใจหรือไม่ว่าต้องการลบสายพันธุ์ <strong id="deleteMangoName"></strong>?
                        <input type="hidden" name="delete_id" id="deleteMangoId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ลบ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ปุ่มเลื่อนขึ้นด้านบน -->
    <div class="scroll-top">
        <i class="bx bx-chevron-up" style="font-size: 28px;"></i>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // ฟังก์ชันค้นหามะม่วงแบบเรียลไทม์
        $(document).ready(function() {
            $('.search-input').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                const mangoCards = $('#mangoList .col');

                mangoCards.each(function() {
                    const cardText = $(this).text().toLowerCase();
                    if (cardText.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });

                // แสดงข้อความหากไม่พบผลลัพธ์
                const visibleCards = mangoCards.filter(':visible').length;
                if (visibleCards === 0 && searchTerm !== '') {
                    $('#mangoList').html(`
                        <div class="no-results text-center py-5">
                            <div class="no-results-icon mb-3">
                                <i class="fas fa-search" style="font-size:3rem;color:#e9ecef;"></i>
                            </div>
                            <h4>ไม่พบข้อมูลมะม่วง</h4>
                            <p>ไม่พบมะม่วงที่ตรงกับคำค้นหา "${searchTerm}"</p>
                            <button class="btn btn-primary mt-3" onclick="location.reload()">
                                <i class="fas fa-sync-alt me-2"></i>แสดงทั้งหมด
                            </button>
                        </div>
                    `);
                }
            });

            // ปุ่มค้นหา
            $('.search-btn').on('click', function() {
                $('.search-input').trigger('keyup');
            });
        });

        // JavaScript สำหรับปุ่มเลื่อนขึ้นด้านบน
        document.addEventListener('DOMContentLoaded', function() {
            const scrollBtn = document.querySelector('.scroll-top');

            // ตรวจจับการเลื่อนหน้าเว็บ
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    scrollBtn.classList.add('show');
                } else {
                    scrollBtn.classList.remove('show');
                }
            });

            // การคลิกปุ่มเลื่อนขึ้น
            scrollBtn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // เพิ่มเสียงเมื่อค้นหา
            const searchBtn = document.querySelector('button[type="submit"]');
            const clickSound = new Audio('https://assets.mixkit.co/sfx/preview/mixkit-select-click-1109.mp3');

            if (searchBtn) {
                searchBtn.addEventListener('click', () => {
                    clickSound.play();
                });
            }

            // เพิ่มอนิเมชันการโหลดให้กับการ์ด
            const cards = document.querySelectorAll('.mango-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });

        // JavaScript สำหรับ Modal ยืนยันการลบ
        document.addEventListener('DOMContentLoaded', function() {
            const deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                document.getElementById('deleteMangoId').value = id;
                document.getElementById('deleteMangoName').textContent = name;
            });
        });
    </script>
</body>

</html>