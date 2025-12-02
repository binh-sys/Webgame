<?php
require_once('ketnoi.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Xóa ảnh nếu có
    $result = mysqli_query($ketnoi, "SELECT avatar FROM authors WHERE author_id = $id");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (!empty($row['avatar']) && file_exists("../uploads/authors/" . $row['avatar'])) {
            unlink("../uploads/authors/" . $row['avatar']);
        }
    }

    $sql = "DELETE FROM authors WHERE author_id = $id";
    if (mysqli_query($ketnoi, $sql)) {
        echo '<script>alert("🗑️ Đã xóa tác giả!"); 
              window.location.href="index.php?page_layout=danhsachtacgia";</script>';
        exit();
    } else {
        echo '<script>alert("❌ Xóa thất bại!");</script>';
    }
} else {
    echo '<script>alert("Thiếu ID tác giả!"); 
          window.location.href="index.php?page_layout=danhsachtacgia";</script>';
}
?>
