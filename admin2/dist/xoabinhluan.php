<?php
require_once('ketnoi.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $check = mysqli_query($ketnoi, "SELECT * FROM comments WHERE comment_id = $id");

    if (mysqli_num_rows($check) > 0) {
        $sql = "DELETE FROM comments WHERE comment_id = $id";
        if (mysqli_query($ketnoi, $sql)) {
            echo "<script>alert('✅ Đã xóa bình luận thành công!');
            window.location.href='index.php?page_layout=danhsachbinhluan';</script>";
            exit;
        } else {
            echo "<script>alert('❌ Lỗi khi xóa!');</script>";
        }
    } else {
        echo "<script>alert('Không tìm thấy bình luận!');
        window.location.href='index.php?page_layout=danhsachbinhluan';</script>";
        exit;
    }
} else {
    echo "<script>alert('Thiếu ID!');
    window.location.href='index.php?page_layout=danhsachbinhluan';</script>";
    exit;
}
?>
