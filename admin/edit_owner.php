<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลเจ้าของ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>


        <div class="container mt-4" style="margin-left: 270px; max-width: 800px;">
            <h2>👤 แก้ไขข้อมูลเจ้าของ</h2>
            <form action="save_owner.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">ชื่อเจ้าของ</label>
                    <input type="text" class="form-control" name="owner_name" value="ชื่อเจ้าของเดิม">
                </div>
                <div class="mb-3">
                    <label class="form-label">รายละเอียดเกี่ยวกับเจ้าของ</label>
                    <textarea class="form-control" name="owner_details" rows="4">ข้อมูลเกี่ยวกับเจ้าของ...</textarea>
                </div>
                <button type="submit" class="btn btn-success">💾 บันทึก</button>
            </form>
        </div>

    </div>



</body>

</html>