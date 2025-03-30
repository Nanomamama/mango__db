<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลเจ้าของ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: "Kanit", sans-serif;
        }
    </style>
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