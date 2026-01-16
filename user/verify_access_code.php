<?php
session_start();
$data = json_decode(file_get_contents("php://input"), true);

define('COURSE_ACCESS_CODE', '1234');

if (!isset($_SESSION['course_access'])) {
    $_SESSION['course_access'] = [];
}

if ($data['code'] === COURSE_ACCESS_CODE) {
    $_SESSION['course_access'][] = (int)$data['courses_id'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
