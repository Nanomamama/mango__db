<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_date = $_POST['booking_date'];

    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($_FILES["slip"]["name"]);
    move_uploaded_file($_FILES["slip"]["tmp_name"], $target_file);

    foreach ($_SESSION['bookings'] as &$booking) {
        if ($booking['booking_date'] === $booking_date) {
            $booking['slip_path'] = $target_file;
            $booking['status'] = "จองแล้ว";
        }
    }

    header("Location: index.php");
    exit();
}
?>
