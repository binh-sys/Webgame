<?php
require_once('ketnoi.php');

if (isset($_GET['id'])) {
    $article_id = intval($_GET['id']);

    // Cập nhật trạng thái bài viết
    $sql = "UPDATE articles SET status='published' WHERE article_id=$article_id";
    if (mysqli_query($ketnoi, $sql)) {
        // Thông báo thành công và quay về trang quản lý
        header("Location: ?page_layout=danhsachbaiviet");
        exit();
    } else {
        echo "Lỗi duyệt bài viết: " . mysqli_error($ketnoi);
    }
} else {
    echo "ID bài viết không hợp lệ.";
}
