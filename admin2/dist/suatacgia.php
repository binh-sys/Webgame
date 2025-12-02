<?php
require_once('ketnoi.php');

// Lấy thông tin tác giả cần sửa
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM authors WHERE author_id = $id";
    $result = mysqli_query($ketnoi, $sql);

    if (mysqli_num_rows($result) == 0) {
        echo '<script>alert("Không tìm thấy tác giả!"); window.location.href="index.php?page_layout=danhsachtacgia";</script>';
        exit();
    }
    $author = mysqli_fetch_assoc($result);
} else {
    echo '<script>alert("Thiếu ID tác giả!"); window.location.href="index.php?page_layout=danhsachtacgia";</script>';
    exit();
}

// Cập nhật dữ liệu
if (isset($_POST['update_author'])) {
    $name = mysqli_real_escape_string($ketnoi, $_POST['name']);
    $email = mysqli_real_escape_string($ketnoi, $_POST['email']);
    $bio = mysqli_real_escape_string($ketnoi, $_POST['bio']);
    $avatar = $author['avatar'];

    // Nếu có upload ảnh mới
    if (!empty($_FILES['avatar']['name'])) {
        $target_dir = "../uploads/authors/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = time() . "_" . basename($_FILES["avatar"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            if (!empty($avatar) && file_exists($target_dir . $avatar)) {
                unlink($target_dir . $avatar);
            }
            $avatar = $file_name;
        }
    }

    $sql_update = "UPDATE authors SET name='$name', email='$email', avatar='$avatar', bio='$bio' WHERE author_id=$id";
    if (mysqli_query($ketnoi, $sql_update)) {
        echo '<script>alert("✅ Cập nhật thành công!");
              window.location.href="index.php?page_layout=danhsachtacgia";</script>';
        exit();
    } else {
        echo '<script>alert("❌ Cập nhật thất bại!");</script>';
    }
}
?>

<!-- GIAO DIỆN FORM -->
<div class="container py-4">
  <div class="form-card mx-auto">
    <div class="form-header bg-warning-subtle">
      <i class='bx bx-edit icon text-warning'></i>
      <h4 class="text-dark fw-bold">Chỉnh sửa thông tin tác giả</h4>
      <p>Cập nhật thông tin chi tiết của tác giả bên dưới</p>
    </div>

    <form method="POST" enctype="multipart/form-data" class="form-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Tên tác giả</label>
          <div class="input-group-custom">
            <i class='bx bx-user'></i>
            <input type="text" name="name" required 
              value="<?php echo htmlspecialchars($author['name']); ?>" placeholder="Nhập tên tác giả">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Email</label>
          <div class="input-group-custom">
            <i class='bx bx-envelope'></i>
            <input type="email" name="email" required 
              value="<?php echo htmlspecialchars($author['email']); ?>" placeholder="example@email.com">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Ảnh đại diện hiện tại</label><br>
          <div class="d-flex align-items-center gap-3 mb-2">
            <img id="avatarPreview" 
                 src="../uploads/authors/<?php echo $author['avatar'] ?: '../assets/img/avatars/default.png'; ?>" 
                 width="90" class="rounded border shadow-sm">
          </div>
          <div class="input-group-custom file">
            <i class='bx bx-image'></i>
            <input type="file" name="avatar" accept="image/*" onchange="previewImage(event)">
          </div>
        </div>

        <div class="col-md-12">
          <label class="form-label">Mô tả / Giới thiệu</label>
          <div class="input-group-custom textarea">
            <i class='bx bx-detail'></i>
            <textarea name="bio" rows="4" placeholder="Giới thiệu ngắn gọn về tác giả..."><?php echo htmlspecialchars($author['bio']); ?></textarea>
          </div>
        </div>
      </div>

      <div class="form-footer">
        <a href="index.php?page_layout=danhsachtacgia" class="btn-cancel">
          <i class='bx bx-arrow-back'></i> Quay lại
        </a>
        <button type="submit" name="update_author" class="btn-save">
          <i class='bx bx-save'></i> Cập nhật
        </button>
      </div>
    </form>
  </div>
</div>

<!-- CSS ĐẸP -->
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
  margin-bottom: 25px;
}
.form-header .icon {
  font-size: 60px;
}
.form-header h4 {
  margin-top: 10px;
}
.form-header p {
  color: #6c757d;
  margin-bottom: 0;
}

.input-group-custom {
  position: relative;
}
.input-group-custom i {
  position: absolute;
  top: 50%;
  left: 15px;
  transform: translateY(-50%);
  color: #f0ad4e;
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
  border-color: #f0ad4e;
  box-shadow: 0 0 10px rgba(240,173,78,0.3);
  outline: none;
}

.form-footer {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  margin-top: 30px;
}
.btn-save {
  background: linear-gradient(90deg, #f6d365, #fda085);
  border: none;
  color: #fff;
  font-weight: 600;
  padding: 10px 25px;
  border-radius: 10px;
  box-shadow: 0 0 10px #f0ad4e60;
  transition: 0.3s;
}
.btn-save:hover {
  box-shadow: 0 0 20px #f0ad4e;
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

<!-- XEM TRƯỚC ẢNH -->
<script>
function previewImage(event) {
  const reader = new FileReader();
  reader.onload = function(){
    const output = document.getElementById('avatarPreview');
    output.src = reader.result;
  };
  reader.readAsDataURL(event.target.files[0]);
}
</script>
