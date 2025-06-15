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
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>  <!-- สำหรับ ocr อ่านสลิป -->
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
                    <select class="form-control" id="province" name="province_id" required>
                        <option value="">-- เลือกจังหวัด --</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="district" class="form-label">อำเภอ</label>
                    <select class="form-control" id="district" name="district_id" required>
                        <option value="">-- เลือกอำเภอ --</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="subdistrict" class="form-label">ตำบล</label>
                    <select class="form-control" id="subdistrict" name="subdistrict_id" required>
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
                <input type="hidden" name="cart" id="cart-data">
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
        fetch('../data/api_province.json').then(res => res.json()),
        fetch('../data/thai_amphures.json').then(res => res.json()),
        fetch('../data/thai_tambons.json').then(res => res.json())
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
        // ถ้าเลือกวิธีโอนเงิน/พร้อมเพย์ ต้องตรวจสอบ slipVerified
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
        if ((paymentMethod === 'bank' || paymentMethod === 'promptpay') && !slipVerified) {
            e.preventDefault();
            Swal.fire("กรุณาแนบสลิปที่ถูกต้อง", "ยอดเงินหรือเลขพร้อมเพย์ไม่ถูกต้อง", "error");
            return;
        }

        e.preventDefault();

        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        let formData = new FormData(this);
        formData.set("cart", JSON.stringify(cart)); // ใช้ set เพื่อแทนที่ค่าเดิม

        fetch("process_checkout.php", {
            method: "POST",
            body: formData
        })
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (err) {
                // แสดง error ที่อ่านง่าย
                Swal.fire("เกิดข้อผิดพลาด", "Response ไม่ใช่ JSON:<br><pre style='text-align:left'>" + text + "</pre>", "error");
                throw new Error("Invalid JSON: " + text);
            }
        })
        .then(data => {
            if (data && data.success) {
                Swal.fire("สำเร็จ!", "การสั่งซื้อของคุณถูกบันทึกเรียบร้อยแล้ว", "success");
                localStorage.removeItem("cart");
                setTimeout(() => window.location.href = `order_summary.php?order_id=${data.order_id}`, 2000);
            } else if (data) {
                Swal.fire("ผิดพลาด!", data.message, "error");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            // ไม่ต้องแสดง Swal ซ้ำ เพราะแสดงไปแล้วใน try-catch ข้างบน
        });
    });

    let slipVerified = false; // สถานะการตรวจสอบสลิป

    document.getElementById('payment-slip').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        slipVerified = false; // reset

        // กำหนดค่าที่ต้องตรวจสอบ
        const expectedAmount = parseFloat(
            (JSON.parse(localStorage.getItem("cart")) || [])
            .reduce((sum, item) => sum + item.price * item.quantity, 0)
            .toFixed(2)
        );
        const expectedAccount = "1429500011543"; // เลขพร้อมเพย์หรือเลขบัญชีที่ต้องการ
        // ตรวจสอบชื่อบัญชี (ไม่สนช่องว่างและตัวพิมพ์เล็ก/ใหญ่)
        let expectedName = "สุกานดา สมเสียง";
        let foundName = text.replace(/\s/g, '').includes(expectedName.replace(/\s/g, ''));

        Swal.fire({title: 'กำลังอ่านข้อมูลจากสลิป...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});

        const reader = new FileReader();
        reader.onload = function(evt) {
            Tesseract.recognize(
                evt.target.result,
                'tha+eng',
                { logger: m => console.log(m) }
            ).then(({ data: { text } }) => {
                Swal.close();
                // แสดงผลลัพธ์ OCR
                Swal.fire("ผลลัพธ์ OCR", `<pre style="text-align:left">${text}</pre>`, "info");

                // ตรวจสอบยอดเงิน
                let amountMatch = text.replace(/,/g, '').match(/ยอด(?:เงิน)?\s*([\d\.]+)/i);
                let foundAmount = amountMatch ? parseFloat(amountMatch[1]) : null;

                // ตรวจสอบเลขพร้อมเพย์/บัญชี (หาเลข 10-13 หลัก)
                let accountMatch = text.match(/\d{10,13}/g);
                let foundAccount = accountMatch ? accountMatch.find(acc => acc === expectedAccount) : null;

                // เงื่อนไขผ่าน
                if (
                    foundAccount &&
                    foundAmount !== null &&
                    Math.abs(foundAmount - expectedAmount) < 0.01 && // ยอมให้คลาดเคลื่อนทศนิยมเล็กน้อย
                    foundName
                ) {
                    slipVerified = true;
                    Swal.fire("✅ สำเร็จ", "ยอดเงิน เลขพร้อมเพย์ และชื่อบัญชีถูกต้อง สามารถดำเนินการสั่งซื้อได้", "success");
                    document.querySelector('button[type="submit"]').disabled = false;
                } else {
                    slipVerified = false;
                    let msg = "";
                    if (foundAmount === null || Math.abs(foundAmount - expectedAmount) >= 0.01)
                        msg += `❌ ยอดเงินไม่ตรง (พบ ${foundAmount ?? "ไม่พบ"} ต้องเป็น ${expectedAmount})<br>`;
                    if (!foundAccount)
                        msg += `❌ ไม่พบเลขพร้อมเพย์/บัญชีที่ถูกต้อง (${expectedAccount})<br>`;
                    if (!foundName)
                        msg += `❌ ไม่พบชื่อบัญชี "${expectedName}"<br>`;
                    Swal.fire("ผิดพลาด", msg, "error");
                    document.querySelector('button[type="submit"]').disabled = true;
                }
            }).catch(err => {
                Swal.close();
                Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถอ่านข้อมูลจากสลิปได้", "error");
                document.querySelector('button[type="submit"]').disabled = true;
            });
        };
        reader.readAsDataURL(file);
    });
</script>

</body>
</html>



