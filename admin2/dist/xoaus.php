<?php
require_once('ketnoi.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM users WHERE user_id = $id";
    $query = mysqli_query($ketnoi, $sql);

    if ($query) {
        // Chuyển hướng an toàn về danh sách người dùng
        if (!headers_sent()) {
            header("Location: index.php?page_layout=danhsachnguoidung");
            exit();
        } else {
            echo '<script type="text/javascript">
                    window.location.href = "index.php?page_layout=danhsachnguoidung";
                  </script>';
        }
    } else {
        echo '<script>
                alert("❌ Xóa thất bại! Vui lòng thử lại.");
                window.location.href = "index.php?page_layout=danhsachnguoidung";
              </script>';
    }
} else {
    echo '<script>
            alert("Không tìm thấy ID người dùng!");
            window.location.href = "index.php?page_layout=danhsachnguoidung";
          </script>';
}
?>
