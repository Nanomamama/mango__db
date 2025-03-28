<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสายพันธุ์มะม่วง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>


<div class="container mt-4">
    <h2>✏️ แก้ไขสายพันธุ์มะม่วง</h2>
    <form action="#" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">ชื่อสายพันธุ์</label>
            <input type="text" class="form-control" name="name" value="น้ำดอกไม้" required>
        </div>
        <div class="mb-3">
            <label class="form-label">รสชาติ</label>
            <input type="text" class="form-control" name="taste" value="หวาน" required>
        </div>
        <div class="mb-3">
            <label class="form-label">รูปภาพปัจจุบัน</label><br>
            <img src="https://media.thairath.co.th/image/fmQpvmjp1V2ZIs1a2hU4OGKwkdosTnm1j4VXg22TebXFCs1a2hPSxQe9vA1.jpg" width="100">
        </div>
        <div class="mb-3">
            <label class="form-label">อัปโหลดรูปใหม่ (ถ้ามี)</label>
            <input type="file" class="form-control" name="image">
        </div>
        <button type="submit" class="btn btn-success">💾 บันทึก</button>
        <a href="manage_mango.php" class="btn btn-secondary">🔙 กลับ</a>
    </form>
</div>

</body>
</html>
