<?php
require_once('ketnoi.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // --- Kiểm tra chuyên mục có tồn tại không ---
    $check = mysqli_query($ketnoi, "SELECT * FROM categories WHERE category_id = $id");
    if (mysqli_num_rows($check) == 0) {
        echo "<script>alert('❌ Không tìm thấy chuyên mục cần xóa!'); window.location='index.php?page_layout=danhsachchuyenmuc';</script>";
        exit();
    }

    // --- Xóa chuyên mục ---
    $sql = "DELETE FROM categories WHERE category_id = $id";
    if (mysqli_query($ketnoi, $sql)) {
        echo "<script>
            alert('✅ Xóa chuyên mục thành công!');
            window.location = 'index.php?page_layout=danhsachchuyenmuc';
        </script>";
        exit();
    } else {
        echo "<script>alert('❌ Lỗi khi xóa chuyên mục!');</script>";
    }
} else {
    echo "<script>window.location='index.php?page_layout=danhsachchuyenmuc';</script>";
}
?>
