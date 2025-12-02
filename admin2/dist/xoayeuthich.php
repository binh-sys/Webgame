<?php
require_once('ketnoi.php');
$id = $_GET['id'];

$sql = "DELETE FROM favorites WHERE favorite_id = '$id'";
if (mysqli_query($ketnoi, $sql)) {
  header("Location: index.php?page_layout=danhsachyeuthich");
  exit;
} else {
  echo "<div class='alert alert-danger m-4'>❌ Lỗi khi xóa yêu thích!</div>";
}
?>
