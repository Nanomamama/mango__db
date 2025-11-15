<?php
// 1. ตั้งค่า Header เพื่อให้เบราว์เซอร์รู้ว่ากำลังรับข้อมูล JSON
header('Content-Type: application/json');

// 2. ตั้งค่าการเชื่อมต่อฐานข้อมูล (เปลี่ยนค่าเหล่านี้ตามฐานข้อมูลจริงของคุณ)
$servername = "119.59.120.143"; 
$username   = "kratipho_db_mango";         
$password   = "kratipho_db_mango";            
$dbname     = "kratipho_db_mango";      
$tableName = "bookings";
// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    // ส่ง Error กลับไปเป็น JSON หากเชื่อมต่อฐานข้อมูลล้มเหลว
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// 3. เตรียมโครงสร้างข้อมูลสำหรับ FullCalendar
$fullCalendarEvents = [];
$bookingNames = [];
$approvedDates = [];
$allDates = [];

// 4. ดึงข้อมูลการจองและสถานะการอนุมัติ
// ค้นหาวันที่ที่มีการจองและได้รับการอนุมัติ หรือวันที่ที่ถูกตั้งค่าเป็น unavailable
$sql = "SELECT 
            id,
            name,
            date, 
            status 
        FROM {$tableName}
        WHERE status IN ('approved', 'unavailable') 
        ORDER BY date ASC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $date = $row['date'];
        $status = $row['status'];
        $name = $row['name'];

        // เก็บข้อมูลชื่อผู้จองสำหรับวันที่เดียวกัน (กรณีมีหลายการจองในวันเดียวที่ approved)
        if ($status === 'approved') {
            if (!isset($bookingNames[$date])) {
                $bookingNames[$date] = [];
            }
            $bookingNames[$date][] = $name;
            $approvedDates[] = $date;
        }

        // เก็บสถานะของวันที่นั้น ๆ (ให้ 'unavailable' มีผลเหนือกว่า 'approved' ในการวนซ้ำถัดไป)
        // แต่ในโค้ดนี้เราจะให้ 'approved' override 'unavailable' เพื่อแสดงการจอง
        // อย่างไรก็ตามเราเก็บข้อมูลทั้งหมดไว้ก่อน
        if (!isset($allDatesData[$date])) {
             $allDatesData[$date] = $status;
        }
    }
}

// 5. จัดรูปแบบข้อมูลให้เป็น Events Array ของ FullCalendar
// ใช้ array_unique เพื่อให้แน่ใจว่าแต่ละวันที่ถูกประมวลผลเพียงครั้งเดียว
$uniqueDates = array_unique(array_merge(array_keys($bookingNames), array_keys($allDates)));

foreach ($uniqueDates as $date) {
    $event = [
        'start' => $date,
        'allDay' => true,
        'id' => $date 
    ];

    if (in_array($date, $approvedDates)) {
        // กิจกรรมที่ได้รับการอนุมัติ
        $title = !empty($bookingNames[$date]) ? implode(', ', $bookingNames[$date]) : 'จองแล้ว';
        $event['title'] = $title;
        $event['className'] = 'booked';
        
    } elseif (isset($allDatesData[$date]) && $allDatesData[$date] === 'unavailable') {
        // วันที่ไม่ว่าง (ถูกบล็อก)
        $event['title'] = 'ไม่ว่าง';
        $event['className'] = 'unavailable';

    } else {
        // ในทางทฤษฎีไม่น่าจะมาถึงตรงนี้หากฐานข้อมูลมีแค่ approved/unavailable
        // แต่ถ้าฐานข้อมูลมี 'available' ด้วย ต้องรวมมาใน SQL และจัดการที่นี่
        $event['title'] = 'ว่าง';
        $event['className'] = 'available';
    }
    
    $fullCalendarEvents[] = $event;
}

// 6. ส่งผลลัพธ์กลับไปในรูปแบบ JSON
echo json_encode([
    'events' => $fullCalendarEvents
]);

$conn->close();

?>