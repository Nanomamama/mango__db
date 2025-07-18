<?php
require_once '../admin/db.php';
$data = json_decode(file_get_contents("php://input"), true);
$course_id = intval($data['course_id']);
$rating = intval($data['rating']);
if ($course_id && $rating >= 1 && $rating <= 5) {
    $stmt = $conn->prepare("INSERT INTO course_ratings (course_id, rating) VALUES (?, ?)");
    $stmt->bind_param("ii", $course_id, $rating);
    $success = $stmt->execute();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>