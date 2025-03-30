<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขหลักสูตร</title>
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