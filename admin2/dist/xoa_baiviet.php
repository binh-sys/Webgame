<?php
require_once('ketnoi.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Lấy thông tin ảnh đại diện của bài viết
    $sql_img = "SELECT featured_image FROM articles WHERE article_id = $id";
    $result = mysqli_query($ketnoi, $sql_img);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Nếu có ảnh thì xóa file ảnh trong thư mục uploads/articles
        if (!empty($row['featured_image']) && file_exists("../uploads/articles/" . $row['featured_image'])) {
            unlink("../uploads/articles/" . $row['featured_image']);
        }
    }

    // Xóa bài viết
    $sql = "DELETE FROM articles WHERE article_id = $id";
    $query = mysqli_query($ketnoi, $sql);

    if ($query) {
        echo '<script>alert("✅ Xóa bài viết thành công!"); 
              window.location.href="index.php?page_layout=danhsachbaiviet";</script>';
        exit();
    } else {
        echo '<script>alert("❌ Lỗi khi xóa bài viết!"); 
              window.location.href="index.php?page_layout=danhsachbaiviet";</script>';
    }
} else {
    echo '<script>alert("Không tìm thấy ID bài viết!"); 
          window.location.href="index.php?page_layout=danhsachbaiviet";</script>';
}
?>
