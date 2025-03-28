<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขหลักสูตร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>

        <div class="container mt-4" style="margin-left: 250px; flex: 1;">

            <h2>📚 จัดการหลักสูตร</h2>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ชื่อหลักสูตร</th>
                        <th>คำอธิบาย</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>หลักสูตรตัวอย่าง</td>
                        <td>รายละเอียดหลักสูตร</td>
                        <td>
                            <a href="edit_course.php?id=1" class="btn btn-warning btn-sm">✏️ แก้ไข</a>
                            <a href="delete_course.php?id=1" class="btn btn-danger btn-sm">🗑️ ลบ</a>
                        </td>
                    </tr>
                </tbody>
            </table>

            <a href="add_course.php" class="btn btn-primary">➕ เพิ่มหลักสูตร</a>
        </div>
    </div>
</body>

</html>