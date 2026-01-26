<?php
$servername = "119.59.120.143"; 
$username   = "kratipho_db_mango";         
$password   = "kratipho_db_mango";            
$dbname     = "kratipho_db_mango";      

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// กำหนด charset เป็น UTF-8
$conn->set_charset("utf8mb4");

// หรือใช้ utf8 ธรรมดา (ถ้า DB ไม่รองรับ utf8mb4)
// $conn->set_charset("utf8");

// echo "✅ Connected with UTF-8 successfully";
?>
