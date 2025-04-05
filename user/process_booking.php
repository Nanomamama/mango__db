<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = $_POST['group_name'];
    $booking_date = $_POST['booking_date'];
    $visit_time = $_POST['visit_time'];
    $number_of_people = $_POST['number_of_people'];
    $phone_number = $_POST['phone_number'];

    if (!isset($_SESSION['bookings'])) {
        $_SESSION['bookings'] = [];
    }

    $_SESSION['bookings'][] = [
        "group_name" => $group_name,
        "booking_date" => $booking_date,
        "visit_time" => $visit_time,
        "number_of_people" => $number_of_people,
        "phone_number" => $phone_number,
        "status" => "รอชำระ",
        "slip_path" => null 
    ];

    header("Location: upload_slip.php?date=" . $booking_date);
    exit();
}
?>
