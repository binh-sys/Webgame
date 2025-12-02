<?php
require_once('ketnoi.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Kiểm tra tồn tại
    $check = mysqli_query($ketnoi, "SELECT * FROM tags WHERE tag_id = $id");
    if (mysqli_num_rows($check) > 0) {
        $sql = "DELETE FROM tags WHERE tag_id = $id";
        if (mysqli_query($ketnoi, $sql)) {
            echo "<script>alert('✅ Đã xóa thẻ game thành công!');
            window.location.href='index.php?page_layout=danhsachthegame';</script>";
            exit;
        } else {
            echo "<script>alert('❌ Lỗi khi xóa!');</script>";
        }
    } else {
        echo "<script>alert('Không tìm thấy thẻ game!');
        window.location.href='index.php?page_layout=danhsachthegame';</script>";
        exit;
    }
} else {
    echo "<script>alert('Thiếu ID!');
    window.location.href='index.php?page_layout=danhsachthegame';</script>";
    exit;
}
?>
