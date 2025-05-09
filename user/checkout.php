<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✅ ดำเนินการสั่งซื้อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
</head>
<body>
<?php include 'navbar.php'; ?>
<br>
<br>
<br>
<div class="container mt-4">
   
    <div class="row justify-content-center">
        <div class="col-md-6">
            <form id="checkout-form" method="POST" action="process_checkout.php" enctype="multipart/form-data">
                <h2 class="text-center">ดำเนินการสั่งซื้อ</h2>
                <h4>ข้อมูลลูกค้า</h4>
                <div class="mb-3">
                    <label for="customer-name" class="form-label">ชื่อ-นามสกุล</label>
                    <input type="text" class="form-control" id="customer-name" name="customer_name" required>
                </div>
                <div class="mb-3">
                    <label for="customer-phone" class="form-label">เบอร์โทรศัพท์</label>
                    <input type="text" class="form-control" id="customer-phone" name="customer_phone" required>
                </div>

                <h4>ที่อยู่จัดส่ง</h4>
                <div class="mb-3">
                    <label for="address-number" class="form-label">เลขที่</label>
                    <input type="text" class="form-control" id="address-number" name="address_number" required>
                </div>
                <div class="mb-3">
                    <label for="province" class="form-label">จังหวัด</label>
                    <select class="form-control" id="province" name="province" required>
                        <option value="">-- เลือกจังหวัด --</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="district" class="form-label">อำเภอ</label>
                    <select class="form-control" id="district" name="district" required>
                        <option value="">-- เลือกอำเภอ --</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="subdistrict" class="form-label">ตำบล</label>
                    <select class="form-control" id="subdistrict" name="sub_district" required>
                        <option value="">-- เลือกตำบล --</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="postal-code" class="form-label">รหัสไปรษณีย์</label>
                    <input type="text" class="form-control" id="postal-code" name="postal_code" required>
                </div>

                <h4>วิธีการชำระเงิน</h4>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="payment-bank" value="bank" required>
                        <label class="form-check-label" for="payment-bank">
                            โอนเงินผ่านธนาคาร
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="payment-cod" value="cod">
                        <label class="form-check-label" for="payment-cod">
                            เก็บเงินปลายทาง
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="payment-promptpay" value="promptpay">
                        <label class="form-check-label" for="payment-promptpay">
                            ชำระเงินผ่าน PromptPay
                        </label>
                    </div>
                </div>

                <div class="mb-3" id="bank-selection" style="display: none;">
                    <label for="bank-name" class="form-label">เลือกธนาคาร</label>
                    <select class="form-control" id="bank-name" name="bank_name">
                        <option value="">-- กรุณาเลือกธนาคาร --</option>
                        <option value="kbank">ธนาคารกสิกรไทย</option>
                        <option value="scb">ธนาคารไทยพาณิชย์</option>
                        <option value="bbl">ธนาคารกรุงเทพ</option>
                        <option value="ktb">ธนาคารกรุงไทย</option>
                    </select>
                </div>
                <!-- เพิ่มโค้ด QR Payment -->
                <div id="qr-payment" class="text-center mt-4" style="display: none;">
                    <h5>📱 สแกนเพื่อชำระเงิน (PromptPay)</h5>
                    <img id="promptpay-qr" alt="PromptPay QR Code" style="width: 250px; height: 250px;">
                    <p class="mt-2" style="color: green;">โปรดชำระเงินให้ตรงยอด และแนบสลิปในขั้นตอนถัดไป</p>
                </div>
                <div class="mb-3" id="slip-upload" style="display: none;">
                    <label for="payment-slip" class="form-label">แนบสลิปโอนเงิน</label>
                    <input type="file" class="form-control" id="payment-slip" name="payment_slip" accept="image/*">
                </div>
                
                <h4>รายการสินค้า</h4>
                <div id="cart-summary"></div>
                <div class="d-flex justify-content-between mt-4">
                    <a href="cart.php" class="btn btn-warning">🔙 กลับ</a>
                    <button type="submit" class="btn btn-primary">ยืนยันการสั่งซื้อ</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
<script>
    const promptpayNumber = "1429500011543"; // หมายเลข PromptPay ของคุณ

    // โหลดข้อมูลจากตะกร้า
    function loadCartSummary() {
        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        let total = 0;

        cart.forEach(item => {
            total += item.price * item.quantity;
        });

        let summaryHtml = cart.map(item => `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center">
                    <img src="${item.image}" alt="${item.name}" style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px; margin-right: 10px;">
                    <span>${item.name} ${item.price}  x ${item.quantity}</span>
                </div>
                <span>฿${(item.price * item.quantity).toFixed(2)}</span>
            </div>
        `).join("");

        summaryHtml += `<div class="text-end  mt-3" style="color:red;"><strong>ยอดรวม: ฿${total.toFixed(2)}</strong></div>`;

        $("#cart-summary").html(summaryHtml);
    }

    let provinces = [];
    let districts = [];
    let subdistricts = [];

    // ดึงข้อมูลจาก API
    Promise.all([
        fetch('../data/api_province.json')
            .then(res => res.text()) // ใช้ .text() เพื่อดูข้อมูลที่ได้รับ
            .then(data => {
                console.log("Response data:", data); // ตรวจสอบข้อมูลใน Console
                return JSON.parse(data); // แปลงเป็น JSON หากข้อมูลถูกต้อง
            })
            .catch(error => console.error("Error parsing JSON:", error)),
        fetch('../data/thai_amphures.json').then(res => {
            if (!res.ok) throw new Error(`Error loading thai_amphures.json: ${res.status}`);
            return res.json();
        }),
        fetch('../data/thai_tambons.json').then(res => {
            if (!res.ok) throw new Error(`Error loading thai_tambons.json: ${res.status}`);
            return res.json();
        })
    ]).then(([provData, distData, subDistData]) => {
        provinces = provData;
        districts = distData;
        subdistricts = subDistData;
        loadProvinces(); // เริ่มต้นด้วยการโหลดจังหวัด
    }).catch(error => {
        console.error("Error loading data:", error);
        Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถโหลดข้อมูลที่อยู่ได้", "error");
    });

    // โหลดข้อมูลจังหวัด
    function loadProvinces() {
        const provSelect = document.getElementById("province");
        provSelect.innerHTML = '<option value="">-- เลือกจังหวัด --</option>';
        provinces.forEach(prov => {
            provSelect.innerHTML += `<option value="${prov.id}">${prov.name_th}</option>`;
        });

        provSelect.onchange = function () {
            const provId = this.value;
            loadDistricts(provId); // โหลดอำเภอเมื่อเลือกจังหวัด
            document.getElementById("subdistrict").innerHTML = '<option value="">-- เลือกตำบล --</option>'; // รีเซ็ตตำบล
        };
    }

    // โหลดข้อมูลอำเภอ
    function loadDistricts(provinceId) {
        const distSelect = document.getElementById("district");
        distSelect.innerHTML = '<option value="">-- เลือกอำเภอ --</option>';
        const filteredDistricts = districts.filter(dist => dist.province_id == provinceId); // กรองอำเภอตาม province_id
        filteredDistricts.forEach(dist => {
            distSelect.innerHTML += `<option value="${dist.id}">${dist.name_th}</option>`;
        });

        distSelect.onchange = function () {
            const distId = this.value;
            loadSubdistricts(distId); // โหลดตำบลเมื่อเลือกอำเภอ
        };
    }

    // โหลดข้อมูลตำบล
    function loadSubdistricts(districtId) {
        const subDistSelect = document.getElementById("subdistrict");
        subDistSelect.innerHTML = '<option value="">-- เลือกตำบล --</option>';
        const filteredSubdistricts = subdistricts.filter(sub => sub.amphure_id == districtId); // กรองตำบลตาม amphure_id
        filteredSubdistricts.forEach(sub => {
            subDistSelect.innerHTML += `<option value="${sub.id}">${sub.name_th}</option>`;
        });
    }

    $(document).ready(function () {
        loadCartSummary();

        document.querySelectorAll('input[name="payment_method"]').forEach(input => {
            input.addEventListener('change', function () {
                const bankSelection = document.getElementById('bank-selection');
                const qrPayment = document.getElementById('qr-payment');
                const promptpayQR = document.getElementById('promptpay-qr');
                const slipUpload = document.getElementById('slip-upload');

                if (this.value === 'bank') {
                    bankSelection.style.display = 'block'; // แสดงฟิลด์เลือกธนาคาร
                    qrPayment.style.display = 'none'; // ซ่อน QR Payment
                    slipUpload.style.display = 'block'; // แสดงฟิลด์แนบสลิป
                } else if (this.value === 'promptpay') {
                    bankSelection.style.display = 'none'; // ซ่อนฟิลด์เลือกธนาคาร
                    qrPayment.style.display = 'block'; // แสดง QR Payment
                    slipUpload.style.display = 'block'; // แสดงฟิลด์แนบสลิป

                    // เพิ่มโค้ดนี้สำหรับสร้าง QR Code
                    if (localStorage.getItem("cart")) {
                        const cart = JSON.parse(localStorage.getItem("cart"));
                        const totalAmount = cart.reduce((sum, item) => sum + item.price * item.quantity, 0).toFixed(2);

                        if (promptpayNumber && totalAmount > 0) {
                            const qrUrl = `https://promptpay.io/${promptpayNumber}/${totalAmount}.png`;
                            $("#promptpay-qr").attr("src", qrUrl);
                        } else {
                            console.error("PromptPay Number หรือยอดเงินไม่ถูกต้อง");
                        }
                    } else {
                        console.error("ไม่มีข้อมูลในตะกร้าสินค้า");
                    }
                } else {
                    bankSelection.style.display = 'none'; // ซ่อนฟิลด์เลือกธนาคาร
                    qrPayment.style.display = 'none'; // ซ่อน QR Payment
                    slipUpload.style.display = 'none'; // ซ่อนฟิลด์แนบสลิป
                }
            });
        });
    });

    document.getElementById("checkout-form").addEventListener("submit", function (e) {
        e.preventDefault(); // ป้องกันการส่งฟอร์มแบบปกติ

        let cart = JSON.parse(localStorage.getItem("cart")) || []; // ดึงข้อมูลจาก localStorage
        let formData = new FormData(this); // สร้าง FormData จากฟอร์ม
        formData.append("cart", JSON.stringify(cart)); // เพิ่มข้อมูล cart ลงใน FormData

        fetch("checkout.php", { // เปลี่ยนเส้นทางเป็น checkout.php
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire("สำเร็จ!", "การสั่งซื้อของคุณถูกบันทึกเรียบร้อยแล้ว", "success");
                localStorage.removeItem("cart"); // ล้างตะกร้าสินค้า
                setTimeout(() => window.location.href = `order_summary.php?order_id=${data.order_id}`, 2000); // เปลี่ยนเส้นทางไปยัง order_summary.php
            } else {
                Swal.fire("ผิดพลาด!", data.message, "error");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถดำเนินการได้", "error");
        });
    });
</script>

</body>
</html>

<?php
require_once '../admin/db.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูลจากฟอร์ม
    $customer_name = $_POST['customer_name'];
    $customer_phone = $_POST['customer_phone'];
    $address_number = $_POST['address_number'];
    $sub_district = $_POST['sub_district'];
    $district = $_POST['district'];
    $province = $_POST['province'];
    $postal_code = $_POST['postal_code'];
    $payment_method = $_POST['payment_method'];
    $cart = json_decode($_POST['cart'], true);

    // ตรวจสอบว่ามีการแนบไฟล์สลิปหรือไม่
    $slip_data = null;
    if ($payment_method === 'bank' || $payment_method === 'promptpay') {
        if (isset($_FILES['payment_slip']) && $_FILES['payment_slip']['error'] === UPLOAD_ERR_OK) {
            $slip_data = base64_encode(file_get_contents($_FILES['payment_slip']['tmp_name']));
        }
    }

    // คำนวณยอดรวม
    $total_price = 0;
    foreach ($cart as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

    // บันทึกคำสั่งซื้อในตาราง `orders`
    $query = "INSERT INTO orders (customer_name, customer_phone, address_number, sub_district, district, province, postal_code, payment_method, total_price, slip_path, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssssdss", $customer_name, $customer_phone, $address_number, $sub_district, $district, $province, $postal_code, $payment_method, $total_price, $slip_data);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถบันทึกคำสั่งซื้อได้']);
        exit;
    }

    // รับ ID ของคำสั่งซื้อที่เพิ่งบันทึก
    $order_id = $stmt->insert_id;

    // บันทึกรายการสินค้าในตาราง `order_items`
    foreach ($cart as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];

        $query = "INSERT INTO order_items (order_id, product_id, quantity, price, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        $stmt->execute();
    }

    echo json_encode(['success' => true, 'message' => 'การสั่งซื้อสำเร็จ', 'order_id' => $order_id]);
    exit;
}
?>

