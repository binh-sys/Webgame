<?php
session_start();
require_once 'ketnoi.php';

// Kiểm tra đăng nhập và quyền
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['editor', 'admin'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);
$user_role = $_SESSION['role'];

// Kiểm tra có ID bài viết không
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: author-history.php?error=invalid");
    exit;
}

$article_id = intval($_GET['id']);

// Lấy thông tin bài viết
$sql = "SELECT article_id, author_id, title, featured_image FROM articles WHERE article_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $article_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: author-history.php?error=notfound");
    exit;
}

$article = $result->fetch_assoc();

// Kiểm tra quyền: chỉ tác giả hoặc admin mới được xóa
if ($user_role !== 'admin' && $article['author_id'] !== $user_id) {
    header("Location: author-history.php?error=permission");
    exit;
}

// Xóa ảnh đại diện nếu có
if (!empty($article['featured_image'])) {
    $image_path = __DIR__ . '/../' . $article['featured_image'];
    if (file_exists($image_path)) {
        unlink($image_path);
    }
}

// Xóa bài viết
$delete_sql = "DELETE FROM articles WHERE article_id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param('i', $article_id);

if ($delete_stmt->execute()) {
    header("Location: author-history.php?success=deleted");
} else {
    header("Location: author-history.php?error=deletefailed");
}
exit;
