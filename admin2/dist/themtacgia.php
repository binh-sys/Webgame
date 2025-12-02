<?php
require_once('ketnoi.php');

if (isset($_POST['add_author'])) {
    $name = mysqli_real_escape_string($ketnoi, $_POST['name']);
    $email = mysqli_real_escape_string($ketnoi, $_POST['email']);
    $bio = mysqli_real_escape_string($ketnoi, $_POST['bio']);
    $created_at = date('Y-m-d H:i:s');
    $avatar = '';

    // Upload ảnh đại diện
    if (!empty($_FILES['avatar']['name'])) {
        $target_dir = "../uploads/authors/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = time() . "_" . basename($_FILES["avatar"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            $avatar = $file_name;
        }
    }

    $sql = "INSERT INTO authors (name, email, avatar, bio, created_at)
            VALUES ('$name', '$email', '$avatar', '$bio', '$created_at')";
    
    if (mysqli_query($ketnoi, $sql)) {
        echo '<script>alert("✅ Thêm tác giả thành công!");
              window.location.href="index.php?page_layout=danhsachtacgia";</script>';
        exit();
    } else {
        echo '<script>alert("❌ Lỗi khi thêm tác giả!");</script>';
    }
}
?>

<!-- GIAO DIỆN FORM ĐẸP -->
<div class="container py-4">
  <div class="form-card mx-auto">
    <div class="form-header">
      <i class='bx bx-user-plus icon'></i>
      <h4>Thêm tác giả mới</h4>
      <p>Điền thông tin tác giả vào các trường bên dưới</p>
    </div>

    <form method="POST" enctype="multipart/form-data" class="form-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Tên tác giả</label>
          <div class="input-group-custom">
            <i class='bx bx-user'></i>
            <input type="text" name="name" required placeholder="Nhập tên tác giả">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Email</label>
          <div class="input-group-custom">
            <i class='bx bx-envelope'></i>
            <input type="email" name="email" required placeholder="example@email.com">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Ảnh đại diện</label>
          <div class="input-group-custom file">
            <i class='bx bx-image'></i>
            <input type="file" name="avatar" accept="image/*">
          </div>
        </div>

        <div class="col-md-12">
          <label class="form-label">Giới thiệu / Mô tả</label>
          <div class="input-group-custom textarea">
            <i class='bx bx-detail'></i>
            <textarea name="bio" rows="4" placeholder="Giới thiệu ngắn gọn về tác giả..."></textarea>
          </div>
        </div>
      </div>

      <div class="form-footer">
        <a href="index.php?page_layout=danhsachtacgia" class="btn-cancel">
          <i class='bx bx-arrow-back'></i> Quay lại
        </a>
        <button type="submit" name="add_author" class="btn-save">
          <i class='bx bx-plus-circle'></i> Thêm mới
        </button>
      </div>
    </form>
  </div>
</div>

<!-- CSS LÀM ĐẸP -->
<style>
.form-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.08);
  max-width: 900px;
  padding: 30px 40px;
}
.form-header {
  text-align: center;
  margin-bottom: 30px;
}
.form-header .icon {
  font-size: 60px;
  color: #28a745;
}
.form-header h4 {
  font-weight: 700;
  margin-top: 10px;
}
.form-header p {
  color: #6c757d;
  margin-top: 5px;
}

.input-group-custom {
  position: relative;
}
.input-group-custom i {
  position: absolute;
  top: 50%;
  left: 15px;
  transform: translateY(-50%);
  color: #28a745;
  font-size: 20px;
}
.input-group-custom input,
.input-group-custom textarea {
  width: 100%;
  border: 2px solid #e3e6ea;
  border-radius: 12px;
  padding: 10px 14px 10px 45px;
  transition: 0.3s;
  font-size: 1rem;
  resize: none;
}
.input-group-custom.file input {
  padding-left: 45px;
}
.input-group-custom.textarea i {
  top: 20px;
}
.input-group-custom input:focus,
.input-group-custom textarea:focus {
  border-color: #28a745;
  box-shadow: 0 0 10px rgba(40,167,69,0.15);
  outline: none;
}
.form-footer {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  margin-top: 30px;
}
.btn-save {
  background: linear-gradient(90deg, #00b09b, #96c93d);
  border: none;
  color: #fff;
  font-weight: 600;
  padding: 10px 25px;
  border-radius: 10px;
  box-shadow: 0 0 10px #00b09b60;
  transition: 0.3s;
}
.btn-save:hover {
  box-shadow: 0 0 20px #00b09b;
  transform: translateY(-2px);
}
.btn-cancel {
  background: #adb5bd;
  color: #fff;
  padding: 10px 25px;
  border-radius: 10px;
  font-weight: 600;
  text-decoration: none;
  transition: 0.3s;
}
.btn-cancel:hover {
  background: #868e96;
  transform: translateY(-2px);
}
</style>
