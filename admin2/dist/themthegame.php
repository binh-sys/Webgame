<?php
require_once('ketnoi.php');

if (isset($_POST['add_tag'])) {
    $name = mysqli_real_escape_string($ketnoi, $_POST['name']);
    $slug = mysqli_real_escape_string($ketnoi, $_POST['slug']);
    $description = mysqli_real_escape_string($ketnoi, $_POST['description']);

    // Kiểm tra trùng slug
    $check = mysqli_query($ketnoi, "SELECT * FROM tags WHERE slug = '$slug'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('❌ Slug đã tồn tại!');</script>";
    } else {
        $sql = "INSERT INTO tags (name, slug, description) VALUES ('$name', '$slug', '$description')";
        if (mysqli_query($ketnoi, $sql)) {
            echo "<script>alert('✅ Thêm thẻ game thành công!');
            window.location.href='index.php?page_layout=danhsachthegame';</script>";
            exit;
        } else {
            echo "<script>alert('❌ Lỗi khi thêm dữ liệu!');</script>";
        }
    }
}
?>

<div class='container mt-4'>
  <div class='card shadow-sm'>
    <div class='card-header bg-success text-white'>
      <h4 class='mb-0'>➕ Thêm thẻ game</h4>
    </div>
    <div class='card-body'>
      <form method='POST'>
        <div class='mb-3'>
          <label class='form-label fw-bold'>Tên thẻ</label>
          <input type='text' name='name' class='form-control' required>
        </div>

        <div class='mb-3'>
          <label class='form-label fw-bold'>Slug</label>
          <input type='text' name='slug' class='form-control' required>
        </div>

        <div class='mb-3'>
          <label class='form-label fw-bold'>Mô tả</label>
          <textarea name='description' class='form-control' rows='3'></textarea>
        </div>

        <button type='submit' name='add_tag' class='btn btn-success px-4'>Lưu</button>
        <a href='index.php?page_layout=danhsachthegame' class='btn btn-secondary px-4'>Quay lại</a>
      </form>
    </div>
  </div>
</div>
