<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ปุ่มแชท</title>
</head>
<a href="https://m.me/thrng.phl.phat.hn.chiy" target="_blank" class="fb-icon" id="messenger-icon">
  <img src="https://upload.wikimedia.org/wikipedia/commons/b/be/Facebook_Messenger_logo_2020.svg" alt="Messenger">
</a>

<style>
.fb-icon {
    position: fixed;
    bottom: 20px;
    right: 30px;
    width: 65px;
    height: 65px;
    background: #a2c9fc;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 9999;
    /* ปิดการสั่นเริ่มต้น */
    animation: none;
}

.fb-icon img {
    width: 32px;
}

/* คีย์เฟรมสำหรับเอฟเฟ็กต์สั่น */
@keyframes shake {
    0% { transform: translateX(0); }
    25% { transform: translateX(-3px); }
    50% { transform: translateX(3px); }
    75% { transform: translateX(-3px); }
    100% { transform: translateX(0); }
}
</style>

<script>
const icon = document.getElementById('messenger-icon');

function triggerShake() {
    // เริ่มการสั่น
    icon.style.animation = 'shake 0.5s ease-in-out';
    
    // เมื่อการสั่นจบ ให้รีเซ็ต animation เพื่อสั่นรอบใหม่ได้
    setTimeout(() => {
        icon.style.animation = 'none';
    }, 500);
}

// เริ่มสั่นทุกๆ 3 วินาที
setInterval(triggerShake, 3000);

// สั่นครั้งแรกเมื่อโหลดหน้า
window.onload = triggerShake;
</script>


<body>
    
</body>
</html>