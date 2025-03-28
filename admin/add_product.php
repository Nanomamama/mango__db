<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สินค้าผลิตภัณฑ์แปรรูป</title>
    <!-- Latest compiled and minified CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Latest compiled JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/product.css">


</head>

<body>
<div class="container mt-4">
    <h2>➕ สินค้าผลิตภัณฑ์แปรรูป</h2>
    
    <form action="save_mango.php" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">ชื่อสินค้าผลิตภัณฑ์</label>
                    <input type="text" class="form-control" name="product_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">รายละเอียดสินค้า</label>
                    <input type="text" class="form-control" name="product_description" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">หมวดหมู่สินค้า</label>
                    <input type="text" class="form-control" name="product_category" required>
                </div>
               
                <div class="mb-3">
                    <label class="form-label">รูปสินค้า</label>
                    <input type="file" class="form-control" name="product_image" accept="image/*" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">ราคา</label>
                    <input type="text" class="form-control" name="product_price" required>
                </div>    
             </div>
              
        </div>
</div>
        <button type="submit" class="btn btn-primary">💾 บันทึก</button>
        <a href="index.php" class="btn btn-secondary">🔙 กลับ</a>

    </form>

</body>

</html>