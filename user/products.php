<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สินค้าผลิตภัณฑ์</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .cart-button {
            position: fixed;
            bottom: 2px;/* ระยะจากขอบล่าง */
            left: 50%; /* วางตรงกลางของหน้าจอ */
            transform: translateX(-50%);/* ปรับปุ่มให้อยู่ตรงกลาง */
            z-index: 1050; /* ให้อยู่หน้าสุด */
            border-radius: 50px;/* ทำให้ดูโค้งๆ */
            padding: 12px 20px;
            font-size: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .product-image {
            width: 300px;
            height: 250px;
            object-fit: cover; /* ครอบรูปภาพให้พอดีกับพื้นที่ */
            border-radius: 5px; /* เพิ่มมุมโค้งมน */
            display: block; /* ทำให้ภาพเป็นบล็อก */
            margin: auto; /* จัดให้อยู่ตรงกลาง */
        }

       
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <?php
    require_once '../admin/db.php'; // เชื่อมต่อฐานข้อมูล

    $query = "SELECT * FROM products"; // ดึงข้อมูลสินค้าทั้งหมด
    $result = $conn->query($query);

    if (!$result) {
        die("เกิดข้อผิดพลาดในการดึงข้อมูลสินค้า: " . $conn->error);
    }

    $products = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row; // เก็บข้อมูลสินค้าใน Array
        }
    }
    ?>

    <br>
    <br>
    <div class="container text-cente">
        <div class="row">

            <!-- Content -->
            <div class="col ">
                <div class="container mt-4 text-center">

                    <br>
                    <h1>สินค้าผลิตภัณฑ์</h1>
                    <a href="order_status.php" class="btn btn-info mb-3">ติดตามสินค้า</a>
                    <a href="cart.php" class="btn btn-primary mb-3"> ไปที่ตะกร้าสินค้า</a>
                    <input type="text" id="searchInput" class="form-control mb-3" placeholder=" ค้นหาสินค้า...">

                    <div class="row" id="product-list">
                        <?php
                        if (!empty($products)) {
                            foreach ($products as $product) {
                                $images = json_decode($product['images'], true); // แปลง JSON ของรูปภาพเป็น Array
                                $image = !empty($images) ? $images[0] : 'default-image.jpg'; // ใช้รูปแรก หรือรูปภาพเริ่มต้นหากไม่มีรูป

                                // การ์ดสินค้า
                                echo '<div class="col-lg-3 col-md-4 col-sm-6 mb-4">';
                                echo '    <div class="card h-100 shadow-sm">';
                                echo '        <img src="../admin/productsimage/' . htmlspecialchars($image) . '" class="product-image" alt="' . htmlspecialchars($product["name"]) . '">';
                                echo '        <div class="card-body text-center">';
                                echo '            <h5 class="card-title">' . htmlspecialchars($product["name"]) . '</h5>';
                                echo '            <p class="card-text text-danger fw-bold">฿' . number_format($product["price"], 2) . '</p>';
                                echo'             <p>สินค้าคงเหลือ:' . htmlspecialchars($product["stock"]) . '</p>';
                                echo '            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#productModal' . $product["id"] . '">ดูรายละเอียด</button>';
                                echo '            <button class="btn btn-success add-to-cart" data-id="' . $product["id"] . '" data-name="' . htmlspecialchars($product["name"]) . '" data-price="' . $product["price"] . '" data-stock="' . $product["stock"] . '" data-image="../admin/productsimage/' . htmlspecialchars($image) . '" ' . ($product["stock"] == 0 ? 'disabled' : '') . '>🛒 เพิ่มลงตะกร้า</button>';
                                echo '        </div>';
                                echo '    </div>';
                                echo '</div>';

                                // Modal สำหรับแสดงรายละเอียดสินค้า
                                echo '<div class="modal fade" id="productModal' . $product["id"] . '" tabindex="-1" aria-labelledby="productModalLabel' . $product["id"] . '" aria-hidden="true">';
                                echo '    <div class="modal-dialog">';
                                echo '        <div class="modal-content">';
                                echo '            <div class="modal-header">';
                                echo '                <h5 class="modal-title" id="productModalLabel' . $product["id"] . '">' . htmlspecialchars($product["name"]) . '</h5>';
                                echo '                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                                echo '            </div>';
                                echo '            <div class="modal-body">';
                                echo '                <img src="../admin/productsimage/' . htmlspecialchars($image) . '" class="img-fluid mb-3" alt="' . htmlspecialchars($product["name"]) . '">';
                                echo '                <h4>' . htmlspecialchars($product["name"]) . '</h4>';
                                echo '                <p>' . htmlspecialchars($product["description"]) . '</p>';
                                echo '                <p><strong>ราคา:</strong> ฿' . number_format($product["price"], 2) . '</p>';
                                
                                echo '            </div>';
                                echo '            <div class="modal-footer">';
                                echo '                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>';
                                echo '            </div>';
                                echo '        </div>';
                                echo '    </div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="text-center">ไม่มีสินค้าในขณะนี้</p>';
                        }
                        ?>
                    </div>

                    <a href="cart.php" class="btn btn-warning cart-button">🛒 ไปที่ตะกร้าสินค้า</a>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ค้นหาสินค้า
        $("#searchInput").on("keyup", function() {
            let filter = $(this).val().toLowerCase();
            $("#product-list .col-lg-3").each(function() {
                $(this).toggle($(this).find(".card-title").text().toLowerCase().indexOf(filter) > -1);
            });
        });

        // เพิ่มสินค้าลงตะกร้า
        $(".add-to-cart").click(function () {
            let product = {
                id: $(this).data("id"),
                name: $(this).data("name"),
                price: parseFloat($(this).data("price")),
                image: $(this).data("image"),
                quantity: 1,
                stock: parseInt($(this).data("stock")) // ดึงจำนวนสต็อกจาก data-stock
            };

            let cart = JSON.parse(localStorage.getItem("cart")) || [];
            let found = cart.find(item => item.id === product.id);

            if (found) {
                found.quantity++;
            } else {
                cart.push(product);
            }

            localStorage.setItem("cart", JSON.stringify(cart));

            Swal.fire({
                icon: "success",
                title: "เพิ่มสินค้าเรียบร้อย!",
                text: product.name + " ถูกเพิ่มลงตะกร้าแล้ว!",
                showConfirmButton: false,
                timer: 1500
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>