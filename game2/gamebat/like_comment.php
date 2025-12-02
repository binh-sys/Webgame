<?php
session_start();
include 'ketnoi.php';

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "not_logged_in"]);
    exit;
}

if (!isset($_POST['comment_id'])) {
    echo json_encode(["status" => "error"]);
    exit;
}

$comment_id = intval($_POST['comment_id']);
$user_id = $_SESSION['user_id'];

// Kiểm tra xem đã like chưa
$check = $conn->prepare("SELECT * FROM comment_likes WHERE user_id=? AND comment_id=?");
$check->bind_param("ii", $user_id, $comment_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    // Chưa like → thêm mới
    $stmt = $conn->prepare("INSERT INTO comment_likes (comment_id, user_id, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $comment_id, $user_id);
    $stmt->execute();
    $liked = true;
} else {
    // Đã like → bỏ like
    $stmt = $conn->prepare("DELETE FROM comment_likes WHERE user_id=? AND comment_id=?");
    $stmt->bind_param("ii", $user_id, $comment_id);
    $stmt->execute();
    $liked = false;
}

// Đếm lại số like
$count = $conn->prepare("SELECT COUNT(*) AS total FROM comment_likes WHERE comment_id=?");
$count->bind_param("i", $comment_id);
$count->execute();
$total = $count->get_result()->fetch_assoc()['total'];

echo json_encode([
    "status" => "success",
    "liked"  => $liked,
    "likes"  => $total
]);
exit;
