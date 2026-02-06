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
<title>สั่งซื้อสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.product-img {width:60px;height:60px;object-fit:cover;}
</style>
</head>
<body>

<div class="container mt-5">
<h2 class="text-center mb-4">ตะกร้าสินค้า & สั่งซื้อ</h2>

<form action="save_order.php" method="post" onsubmit="return submitOrder()">

<div class="row">

<!-- ซ้าย: ตะกร้า -->
<div class="col-md-7">
<h4>รายการสินค้า</h4>
<table class="table table-bordered">
<thead>
<tr>
<th>สินค้า</th>
<th>ราคา</th>
<th>จำนวน</th>
<th>รวม</th>
<th></th>
</tr>
</thead>
<tbody id="cartBody"></tbody>
</table>
<h5 class="text-end">รวมทั้งสิ้น: <span id="totalPrice">0</span> บาท</h5>
</div>

<!-- ขวา: ฟอร์ม -->
<div class="col-md-5">
<h4>ข้อมูลผู้สั่งซื้อ</h4>

<input type="hidden" name="member_id" value="<?= $member_id ?>">

<div class="mb-2">
<label>ชื่อ</label>
<input type="text" name="customer_name" class="form-control"
value="<?= $member_name ?>" required>
</div>

<div class="mb-2">
<label>เบอร์โทร</label>
<input type="text" name="customer_phone" class="form-control"
value="<?= $member_phone ?>" required>
</div>

<div class="mb-2">
<label>วิธีรับสินค้า</label>
<select name="receive_type" id="receive_type" class="form-control">
<option value="pickup">รับที่สวน</option>
<option value="delivery">จัดส่ง</option>
</select>
</div>

<div class="mb-2">
<label id="address_label">รายละเอียดที่อยู่จัดส่ง</label>
<textarea name="customer_address" id="customer_address"
class="form-control" required></textarea>
</div>



<div class="mb-2">
<label>วันเวลาที่ต้องการรับ</label>
<input type="datetime-local" name="receive_datetime" class="form-control" required>
</div>

<input type="hidden" name="cart_data" id="cartData">

<button class="btn btn-success w-100 mt-3">
ยืนยันการสั่งซื้อ
</button>
</div>

</div>
</form>
</div>

<script>
let cart = JSON.parse(localStorage.getItem("cart")) || [];
let body = document.getElementById("cartBody");
let total = 0;

function renderCart(){
body.innerHTML = "";
total = 0;

cart.forEach((p,i)=>{
let sum = p.price * p.quantity;
total += sum;

body.innerHTML += `
<tr>
<td>${p.name}</td>
<td>${p.price}</td>
<td>
<input type="number" value="${p.quantity}" min="1"
onchange="updateQty(${i},this.value)">
</td>
<td>${sum}</td>
<td>
<button type="button" class="btn btn-danger btn-sm"
onclick="removeItem(${i})">ลบ</button>
</td>
</tr>`;
});

document.getElementById("totalPrice").innerText = total;
}
renderCart();

function updateQty(i,val){
cart[i].quantity = parseInt(val);
localStorage.setItem("cart",JSON.stringify(cart));
renderCart();
}

function removeItem(i){
cart.splice(i,1);
localStorage.setItem("cart",JSON.stringify(cart));
renderCart();
}

function submitOrder(){
if(cart.length==0){
Swal.fire("ไม่มีสินค้าในตะกร้า");
return false;
}
document.getElementById("cartData").value = JSON.stringify(cart);
localStorage.removeItem("cart");
return true;
}


const receiveType = document.getElementById('receive_type');
const addressLabel = document.getElementById('address_label');
const addressInput = document.getElementById('customer_address');

function toggleAddressField() {
    if (receiveType.value === 'pickup') {
        addressLabel.innerText = 'หมายเหตุถึงแอดมิน (ถ้ามี)';
        addressInput.placeholder = 'เช่น จะไปรับช่วงบ่าย 3 โมง';
        addressInput.removeAttribute('required');
    } else {
        addressLabel.innerText = 'รายละเอียดที่อยู่จัดส่ง';
        addressInput.placeholder = 'กรอกที่อยู่สำหรับจัดส่ง';
        addressInput.setAttribute('required', 'required');
    }
}

receiveType.addEventListener('change', toggleAddressField);
toggleAddressField(); // เรียกครั้งแรกตอนโหลดหน้า


</script>

</body>
</html>
