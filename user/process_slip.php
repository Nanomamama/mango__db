<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_date = $_POST['booking_date'];

    $target_dir = "Paymentslip-Gardenreservation/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $fileName = date('Ymd_His') . '_' . substr(md5(mt_rand()),0,6) . '_' . basename($_FILES["slip"]["name"]);
    $target_file = rtrim($target_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
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
