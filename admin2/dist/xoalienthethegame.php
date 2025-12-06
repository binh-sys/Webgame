<?php
// xoalienthethegame.php - Xóa liên kết bài viết - thẻ game
require_once('ketnoi.php');

if (!isset($_GET['article_id']) || !isset($_GET['tag_id'])) {
    echo '<script>alert("Thiếu thông tin!"); window.location.href="index.php?page_layout=danhsachlienthethegame";</script>';
    exit();
}

$article_id = intval($_GET['article_id']);
$tag_id = intval($_GET['tag_id']);

$stmt = mysqli_prepare($ketnoi, "DELETE FROM article_tags WHERE article_id = ? AND tag_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $article_id, $tag_id);

if (mysqli_stmt_execute($stmt)) {
    echo '<script>alert("✅ Đã xóa liên kết!"); window.location.href="index.php?page_layout=danhsachlienthethegame";</script>';
} else {
    echo '<script>alert("❌ Lỗi khi xóa!"); window.location.href="index.php?page_layout=danhsachlienthethegame";</script>';
}

mysqli_stmt_close($stmt);
