<?php session_start(); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อัปโหลดสลิปค่ามัดจำ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<?php include 'navbar.php'; ?>
ิ<br>
ิ<br>
<div class="container py-5">
    <h2 class="mb-4">อัปโหลดสลิปค่ามัดจำ</h2>
    
    <?php
    // ตรวจสอบสถานะการอนุมัติจากแอดมิน
    if (isset($_SESSION['approval_status']) && $_SESSION['approval_status'] === 'approved') {
        // ถ้าได้รับการอนุมัติแล้ว แสดงคิวอาร์โค้ด
        echo '<h3 class="mb-4">คุณได้รับการอนุมัติแล้ว</h3>';
        echo '<img src="generate_qr_code.php?amount=500" alt="QR Code for Payment" class="img-fluid">';
    } else {
        // ถ้ายังไม่ได้รับการอนุมัติ
        echo '<p>กรุณารอการอนุมัติจากแอดมินก่อนที่จะเห็นคิวอาร์โค้ด</p>';
    }
    ?>

    <!-- ฟอร์มสำหรับอัปโหลดสลิปการโอนเงิน -->
    <form action="process_slip.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="booking_date" value="<?php echo $_GET['date']; ?>">
        <div class="mb-3">
            <label for="slip" class="form-label">แนบสลิปการโอนเงิน</label>
            <input type="file" class="form-control" name="slip" required>
        </div>
        <button type="submit" class="btn btn-primary">ส่งสลิป</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
