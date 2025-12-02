<?php
require_once('ketnoi.php');
$id = $_GET['id'] ?? 0;

if ($id) {
    $sql = "DELETE FROM article_tags WHERE id = '$id'";
    if (mysqli_query($ketnoi, $sql)) {
        header("Location: ?page_layout=danhsachlienthethegame");
        exit();
    } else {
        echo "<div class='alert alert-danger text-center mt-3'>❌ Không thể xóa liên kết!</div>";
    }
} else {
    echo "<div class='alert alert-warning text-center mt-3'>⚠️ Thiếu ID cần xóa!</div>";
}
?>
