<?php
session_start();
require_once '../admin/db.php';

// ถ้าเป็นสมาชิก
$member_id = $_SESSION['member_id'] ?? null;
$member_name = '';
$member_phone = '';

if ($member_id) {
    $m = $conn->prepare("SELECT fullname, phone FROM members WHERE member_id=?");
    $m->bind_param("i", $member_id);
    $m->execute();
    $mem = $m->get_result()->fetch_assoc();
    $member_name = $mem['fullname'];
    $member_phone = $mem['phone'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ตะกร้าสินค้า - สวนลุงเผือก</title>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
* {
    font-family: 'Prompt', sans-serif;
}

body {
    background: #f8f9fa;
}

/* Header */
.page-header {
    background: white;
    padding: 30px 0;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin: 0;
}

.breadcrumb {
    background: none;
    margin: 0;
    padding: 0;
}

.breadcrumb-item a {
    color: #666;
    text-decoration: none;
}

.breadcrumb-item.active {
    color: #333;
}

/* Cart Section */
.cart-section {
    background: white;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    margin-bottom: 25px;
}

.section-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

/* Cart Table */
.cart-table {
    width: 100%;
}

.cart-item {
    border-bottom: 1px solid #f0f0f0;
    padding: 20px 0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.cart-item:last-child {
    border-bottom: none;
}

.product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e5e5e5;
    flex-shrink: 0;
}

.product-info {
    flex: 1;
    min-width: 0;
}

.product-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
    font-size: 1rem;
}

.product-price {
    color: #dc3545;
    font-weight: 600;
    font-size: 1.1rem;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

.qty-btn {
    width: 32px;
    height: 32px;
    border: 1px solid #ddd;
    background: white;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}

.qty-btn:hover {
    background: #f8f9fa;
    border-color: #999;
}

.qty-input {
    width: 60px;
    height: 32px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-weight: 600;
}

.item-total {
    font-weight: 700;
    color: #333;
    font-size: 1.1rem;
    min-width: 100px;
    text-align: right;
}

.btn-remove {
    background: white;
    border: 1px solid #dc3545;
    color: #dc3545;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.btn-remove:hover {
    background: #dc3545;
    color: white;
}

/* Cart Summary */
.cart-summary {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #e0e0e0;
}

.summary-row:last-child {
    border-bottom: none;
    padding-top: 15px;
    margin-top: 10px;
    border-top: 2px solid #333;
}

.summary-label {
    color: #666;
    font-weight: 500;
}

.summary-value {
    font-weight: 600;
    color: #333;
}

.summary-total {
    font-size: 1.5rem;
    color: #dc3545;
}

/* Form Section */
.form-section {
    background: white;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    margin-bottom: 25px;
}

.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.form-control,
.form-select {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 10px 15px;
    transition: all 0.3s;
}

.form-control:focus,
.form-select:focus {
    border-color: #333;
    box-shadow: 0 0 0 0.2rem rgba(51, 51, 51, 0.1);
}

.receive-type-card {
    border: 2px solid #e5e5e5;
    border-radius: 6px;
    padding: 12px 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 12px;
}

.receive-type-card:hover {
    border-color: #999;
}

.receive-type-card.active {
    border-color: #333;
    background: #f8f9fa;
}

.receive-type-card input[type="radio"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.receive-icon {
    font-size: 1.5rem;
    color: #666;
    min-width: 35px;
    text-align: center;
}

.receive-type-card.active .receive-icon {
    color: #333;
}

.receive-content {
    flex: 1;
}

.receive-title {
    font-weight: 600;
    color: #333;
    margin: 0;
    font-size: 1rem;
}

.receive-desc {
    color: #666;
    margin: 0;
    font-size: 0.85rem;
}

/* Buttons */
.btn-submit {
    background: #333;
    border: none;
    color: white;
    padding: 14px 30px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s;
    width: 100%;
}

.btn-submit:hover {
    background: #000;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.btn-continue {
    background: white;
    border: 1px solid #333;
    color: #333;
    padding: 12px 25px;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.btn-continue:hover {
    background: #f8f9fa;
    color: #333;
}

/* Empty Cart */
.empty-cart {
    text-align: center;
    padding: 60px 20px;
}

.empty-cart i {
    font-size: 80px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-cart h4 {
    color: #666;
    margin-bottom: 15px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .page-title {
        font-size: 1.5rem;
    }
    
    .cart-item {
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .product-image {
        width: 60px;
        height: 60px;
    }
    
    .product-info {
        flex: 1 1 100%;
        order: 2;
    }
    
    .quantity-control {
        order: 3;
    }
    
    .item-total {
        order: 4;
        min-width: auto;
    }
    
    .btn-remove {
        order: 5;
        width: 100%;
        margin-top: 10px;
    }
    
    .summary-total {
        font-size: 1.3rem;
    }
}

/* Alert Info */
.info-alert {
    background: #e7f3ff;
    border-left: 4px solid #2196f3;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.info-alert i {
    color: #2196f3;
    margin-right: 10px;
}

/* Loading */
.loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.95);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #333;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Date Time Picker Simplified */
.datetime-picker {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border: 2px solid #e5e5e5;
}

.date-select-group,
.time-select-group {
    margin-bottom: 15px;
}

.date-select-group label,
.time-select-group label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    font-size: 1rem;
}

.date-time-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.date-time-select {
    padding: 12px;
    border: 2px solid #ddd;
    border-radius: 6px;
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    background: white;
    cursor: pointer;
}

.date-time-select:focus {
    border-color: #333;
    outline: none;
}

.time-select-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

@media (max-width: 576px) {
    .date-time-row {
        grid-template-columns: 1fr;
    }
    
    .time-select-row {
        grid-template-columns: 1fr;
    }
}

</style>
</head>

<body>
<?php include __DIR__ . '/navbar.php'; ?>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="products.php"><i class="fas fa-home"></i> หน้าแรก</a></li>
                <li class="breadcrumb-item active">ตะกร้าสินค้า</li>
            </ol>
        </nav>
        <h1 class="page-title"><i class="fas fa-shopping-cart"></i> ตะกร้าสินค้า</h1>
    </div>
</div>

<div class="container mb-5">
    <form action="save_order.php" method="post" onsubmit="return submitOrder()">
        <div class="row">
            <!-- Cart Items Section -->
            <div class="col-lg-7 mb-4">
                <div class="cart-section">
                    <h3 class="section-title">
                        <i class="fas fa-shopping-basket"></i> รายการสินค้า
                        <span class="badge bg-dark ms-2" id="itemCount">0</span>
                    </h3>

                    <div id="cartItems">
                        <!-- Cart items will be rendered here -->
                    </div>

                    <div id="emptyCart" class="empty-cart" style="display: none;">
                        <i class="fas fa-shopping-cart"></i>
                        <h4>ตะกร้าสินค้าว่างเปล่า</h4>
                        <p class="text-muted">ยังไม่มีสินค้าในตะกร้า กรุณาเลือกสินค้าที่ต้องการ</p>
                        <a href="products.php" class="btn-continue mt-3">
                            <i class="fas fa-arrow-left"></i> เลือกซื้อสินค้า
                        </a>
                    </div>

                    <!-- Cart Summary -->
                    <div class="cart-summary" id="cartSummary" style="display: none;">
                        <div class="summary-row">
                            <span class="summary-label">ยอดรวมสินค้า</span>
                            <span class="summary-value" id="subtotal">฿0.00</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">ค่าจัดส่ง</span>
                            <span class="summary-value text-success">ฟรี</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label"><strong>รวมทั้งสิ้น</strong></span>
                            <span class="summary-total" id="totalPrice">฿0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Continue Shopping Button (Mobile) -->
                <div class="d-lg-none mb-3">
                    <a href="products.php" class="btn-continue w-100 text-center">
                        <i class="fas fa-arrow-left"></i> เลือกซื้อสินค้าเพิ่ม
                    </a>
                </div>
            </div>

            <!-- Order Form Section -->
            <div class="col-lg-5">
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user-circle"></i> ข้อมูลผู้สั่งซื้อ
                    </h3>

                    <input type="hidden" name="member_id" value="<?= $member_id ?>">

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-user"></i> ชื่อ-นามสกุล</label>
                        <input type="text" name="customer_name" class="form-control"
                               value="<?= htmlspecialchars($member_name) ?>" 
                               placeholder="กรอกชื่อ-นามสกุล" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-phone"></i> เบอร์โทรศัพท์</label>
                        <input type="tel" name="customer_phone" class="form-control"
                               value="<?= htmlspecialchars($member_phone) ?>" 
                               placeholder="กรอกเบอร์โทรศัพท์" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-truck"></i> วิธีรับสินค้า</label>
                        
                        <div class="receive-type-card active" onclick="selectReceiveType('pickup')">
                            <input type="radio" name="receive_type" value="pickup" id="pickup" checked>
                            <div class="receive-icon"><i class="fas fa-store"></i></div>
                            <div class="receive-content">
                                <div class="receive-title">รับที่สวน</div>
                                <div class="receive-desc">รับสินค้าด้วยตัวเองที่สวนลุงเผือก</div>
                            </div>
                        </div>

                        <div class="receive-type-card" onclick="selectReceiveType('delivery')">
                            <input type="radio" name="receive_type" value="delivery" id="delivery">
                            <div class="receive-icon"><i class="fas fa-shipping-fast"></i></div>
                            <div class="receive-content">
                                <div class="receive-title">จัดส่งถึงบ้าน</div>
                                <div class="receive-desc">ส่งสินค้าถึงบ้าน (เฉพาะบ้านบุฮม)</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" id="address_label">
                            <i class="fas fa-sticky-note"></i> หมายเหตุถึงแอดมิน (ถ้ามี)
                        </label>
                        <textarea name="customer_address" id="customer_address"
                                  class="form-control" rows="3"
                                  placeholder="เช่น จะไปรับช่วงบ่าย 3 โมง"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-calendar-alt"></i> วันเวลาที่ต้องการรับ</label>
                        <div class="datetime-picker">
                            <div class="date-select-group">
                                <label>เลือกวันที่</label>
                                <div class="date-time-row">
                                    <select class="date-time-select" id="day" required>
                                        <option value="">วัน</option>
                                    </select>
                                    <select class="date-time-select" id="month" required>
                                        <option value="">เดือน</option>
                                    </select>
                                    <select class="date-time-select" id="year" required>
                                        <option value="">ปี</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="time-select-group">
                                <label>เลือกเวลา</label>
                                <div class="time-select-row">
                                    <select class="date-time-select" id="hour" required>
                                        <option value="">ชั่วโมง</option>
                                    </select>
                                    <select class="date-time-select" id="minute" required>
                                        <option value="">นาที</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="receive_datetime" id="receive_datetime" required>
                    </div>

                    <div class="info-alert">
                        <i class="fas fa-info-circle"></i>
                        <small>ทางสวนจะติดต่อกลับเพื่อยืนยันคำสั่งซื้ออีกครั้ง</small>
                    </div>

                    <input type="hidden" name="cart_data" id="cartData">

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-check-circle"></i> ยืนยันการสั่งซื้อ
                    </button>
                </div>

                <!-- Continue Shopping Button (Desktop) -->
                <div class="d-none d-lg-block mt-3">
                    <a href="products.php" class="btn-continue w-100 text-center">
                        <i class="fas fa-arrow-left"></i> เลือกซื้อสินค้าเพิ่ม
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
let cart = JSON.parse(localStorage.getItem("cart")) || [];

// Render cart items
function renderCart() {
    const cartItems = document.getElementById("cartItems");
    const emptyCart = document.getElementById("emptyCart");
    const cartSummary = document.getElementById("cartSummary");
    const itemCount = document.getElementById("itemCount");
    const submitBtn = document.getElementById("submitBtn");
    
    if (cart.length === 0) {
        cartItems.innerHTML = "";
        emptyCart.style.display = "block";
        cartSummary.style.display = "none";
        submitBtn.disabled = true;
        itemCount.textContent = "0";
        return;
    }

    emptyCart.style.display = "none";
    cartSummary.style.display = "block";
    submitBtn.disabled = false;
    itemCount.textContent = cart.length;

    let total = 0;
    let html = "";

    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;

        html += `
        <div class="cart-item">
            <img src="${item.image}" alt="${item.name}" class="product-image">
            
            <div class="product-info">
                <div class="product-name">${item.name}</div>
                <div class="product-price">฿${parseFloat(item.price).toFixed(2)}</div>
            </div>
            
            <div class="quantity-control">
                <button type="button" class="qty-btn" onclick="decreaseQty(${index})">
                    <i class="fas fa-minus"></i>
                </button>
                <input type="number" class="qty-input" value="${item.quantity}" 
                       min="1" onchange="updateQty(${index}, this.value)">
                <button type="button" class="qty-btn" onclick="increaseQty(${index})">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            
            <div class="item-total">฿${itemTotal.toFixed(2)}</div>
            
            <button type="button" class="btn-remove" onclick="removeItem(${index})">
                <i class="fas fa-trash"></i> ลบ
            </button>
        </div>
        `;
    });

    cartItems.innerHTML = html;
    document.getElementById("subtotal").textContent = `฿${total.toFixed(2)}`;
    document.getElementById("totalPrice").textContent = `฿${total.toFixed(2)}`;
}

// Quantity controls
function increaseQty(index) {
    cart[index].quantity++;
    saveCart();
    renderCart();
}

function decreaseQty(index) {
    if (cart[index].quantity > 1) {
        cart[index].quantity--;
        saveCart();
        renderCart();
    }
}

function updateQty(index, value) {
    const qty = parseInt(value);
    if (qty > 0) {
        cart[index].quantity = qty;
        saveCart();
        renderCart();
    }
}

function removeItem(index) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: 'คุณต้องการลบสินค้านี้ออกจากตะกร้า?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            cart.splice(index, 1);
            saveCart();
            renderCart();
            
            Swal.fire({
                icon: 'success',
                title: 'ลบสินค้าแล้ว',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}

function saveCart() {
    localStorage.setItem("cart", JSON.stringify(cart));
}

// Receive type selection
function selectReceiveType(type) {
    const cards = document.querySelectorAll('.receive-type-card');
    cards.forEach(card => card.classList.remove('active'));
    
    const selectedCard = document.querySelector(`#${type}`).closest('.receive-type-card');
    selectedCard.classList.add('active');
    
    document.querySelector(`#${type}`).checked = true;
    
    toggleAddressField(type);
}

function toggleAddressField(type) {
    const addressLabel = document.getElementById('address_label');
    const addressInput = document.getElementById('customer_address');
    
    if (type === 'pickup') {
        addressLabel.innerHTML = '<i class="fas fa-sticky-note"></i> หมายเหตุถึงแอดมิน (ถ้ามี)';
        addressInput.placeholder = 'เช่น จะไปรับช่วงบ่าย 3 โมง';
        addressInput.removeAttribute('required');
    } else {
        addressLabel.innerHTML = '<i class="fas fa-map-marker-alt"></i> รายละเอียดที่อยู่จัดส่ง';
        addressInput.placeholder = 'กรอกที่อยู่สำหรับจัดส่ง';
        addressInput.setAttribute('required', 'required');
    }
}

// Submit order
function submitOrder() {
    if (cart.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'ไม่มีสินค้าในตะกร้า',
            text: 'กรุณาเลือกสินค้าก่อนทำการสั่งซื้อ'
        });
        return false;
    }

    document.getElementById("cartData").value = JSON.stringify(cart);
    
    // Show loading
    document.getElementById("loadingOverlay").style.display = "flex";
    
    // Clear cart after successful submission
    setTimeout(() => {
        localStorage.removeItem("cart");
    }, 500);
    
    return true;
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    renderCart();
    
    // Populate date/time dropdowns
    populateDateTimeSelects();
    
    // Initialize receive type
    toggleAddressField('pickup');
});

// Populate Date/Time Dropdowns
function populateDateTimeSelects() {
    const now = new Date();
    const currentYear = now.getFullYear();
    const thaiYear = currentYear + 543; // แปลงเป็น พ.ศ.
    
    // Populate Days (1-31)
    const daySelect = document.getElementById('day');
    for (let i = 1; i <= 31; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = i;
        daySelect.appendChild(option);
    }
    
    // Populate Months (Thai names)
    const monthSelect = document.getElementById('month');
    const thaiMonths = [
        'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];
    thaiMonths.forEach((month, index) => {
        const option = document.createElement('option');
        option.value = index + 1;
        option.textContent = month;
        monthSelect.appendChild(option);
    });
    
    // Populate Years (current + 1 year)
    const yearSelect = document.getElementById('year');
    for (let i = 0; i <= 1; i++) {
        const option = document.createElement('option');
        option.value = currentYear + i;
        option.textContent = (thaiYear + i).toString();
        yearSelect.appendChild(option);
    }
    
    // Populate Hours (0-23)
    const hourSelect = document.getElementById('hour');
    for (let i = 0; i < 24; i++) {
        const option = document.createElement('option');
        const hourValue = i.toString().padStart(2, '0');
        option.value = hourValue;
        option.textContent = `${hourValue} น.`;
        hourSelect.appendChild(option);
    }
    
    // Populate Minutes (00, 15, 30, 45)
    const minuteSelect = document.getElementById('minute');
    [0, 15, 30, 45].forEach(min => {
        const option = document.createElement('option');
        const minValue = min.toString().padStart(2, '0');
        option.value = minValue;
        option.textContent = `${minValue} นาที`;
        minuteSelect.appendChild(option);
    });
    
    // Set default to today
    daySelect.value = now.getDate();
    monthSelect.value = now.getMonth() + 1;
    yearSelect.value = currentYear;
    hourSelect.value = now.getHours().toString().padStart(2, '0');
    minuteSelect.value = '00';
    
    // Update hidden field on change
    const selects = [daySelect, monthSelect, yearSelect, hourSelect, minuteSelect];
    selects.forEach(select => {
        select.addEventListener('change', updateDateTimeField);
    });
    
    // Initialize hidden field
    updateDateTimeField();
}

function updateDateTimeField() {
    const day = document.getElementById('day').value.padStart(2, '0');
    const month = document.getElementById('month').value.padStart(2, '0');
    const year = document.getElementById('year').value;
    const hour = document.getElementById('hour').value;
    const minute = document.getElementById('minute').value;
    
    if (day && month && year && hour && minute) {
        // Format: YYYY-MM-DDTHH:MM
        const datetime = `${year}-${month}-${day}T${hour}:${minute}`;
        document.getElementById('receive_datetime').value = datetime;
    }
}

// Listen to receive type changes
document.querySelectorAll('input[name="receive_type"]').forEach(radio => {
    radio.addEventListener('change', (e) => {
        toggleAddressField(e.target.value);
    });
});
</script>

</body>
</html>