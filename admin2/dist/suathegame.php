<?php
require_once('ketnoi.php');

// Lấy dữ liệu theo ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = mysqli_query($ketnoi, "SELECT * FROM tags WHERE tag_id = $id");
    $tag = mysqli_fetch_assoc($result);
    if (!$tag) {
        echo "<script>alert('Không tìm thấy thẻ game!');
        window.location.href='index.php?page_layout=danhsachthegame';</script>";
        exit;
    }
} else {
    echo "<script>alert('Thiếu ID!');
    window.location.href='index.php?page_layout=danhsachthegame';</script>";
    exit;
}

// Cập nhật dữ liệu
if (isset($_POST['update_tag'])) {
    $name = mysqli_real_escape_string($ketnoi, $_POST['name']);
    $slug = mysqli_real_escape_string($ketnoi, $_POST['slug']);
    $description = mysqli_real_escape_string($ketnoi, $_POST['description']);

    // Kiểm tra trùng slug (ngoại trừ chính nó)
    $check = mysqli_query($ketnoi, "SELECT * FROM tags WHERE slug='$slug' AND tag_id!=$id");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('❌ Slug đã tồn tại!');</script>";
    } else {
        $sql = "UPDATE tags SET name='$name', slug='$slug', description='$description' WHERE tag_id=$id";
        if (mysqli_query($ketnoi, $sql)) {
            echo "<script>alert('✅ Cập nhật thành công!');
            window.location.href='index.php?page_layout=danhsachthegame';</script>";
            exit;
        } else {
            echo "<script>alert('❌ Lỗi khi cập nhật!');</script>";
        }
    }
}
?>

<!-- Giao diện form chỉnh sửa -->
<div class="content-wrapper d-flex align-items-center justify-content-center" style="min-height:100vh;">
  <div class="edit-tag-card">
    <div class="edit-header text-center mb-4">
      <h3><i class="bx bx-edit-alt me-2"></i> Chỉnh sửa Thẻ Game</h3>
      <p>Cập nhật thông tin thẻ để đồng bộ dữ liệu bài viết & tag game.</p>
    </div>

    <form method="POST" class="neon-form">
      <div class="form-group mb-4">
        <label for="name"><i class="bx bx-purchase-tag me-2"></i>Tên thẻ</label>
        <input type="text" name="name" id="name" class="form-control" 
          value="<?php echo htmlspecialchars($tag['name']); ?>" required>
      </div>

      <div class="form-group mb-4">
        <label for="slug"><i class="bx bx-link me-2"></i>Slug</label>
        <input type="text" name="slug" id="slug" class="form-control" 
          value="<?php echo htmlspecialchars($tag['slug']); ?>" required>
      </div>

      <div class="form-group mb-4">
        <label for="description"><i class="bx bx-text me-2"></i>Mô tả</label>
        <textarea name="description" id="description" class="form-control" rows="3"><?php echo htmlspecialchars($tag['description']); ?></textarea>
      </div>

      <div class="text-center mt-4">
        <button type="submit" name="update_tag" class="btn-update">
          <i class="bx bx-save me-2"></i> Cập nhật
        </button>
        <a href="index.php?page_layout=danhsachthegame" class="btn-cancel">
          <i class="bx bx-arrow-back me-1"></i> Quay lại
        </a>
      </div>
    </form>
  </div>
</div>

<style>
  body {
    background: radial-gradient(circle at top left, #0d1b2a, #000814);
    color: #e6f1ff;
    font-family: 'Poppins', sans-serif;
  }

  .edit-tag-card {
    width: 600px;
    background: linear-gradient(145deg, rgba(20, 30, 48, 0.9), rgba(36, 59, 85, 0.95));
    border-radius: 20px;
    padding: 45px 50px;
    box-shadow: 0 0 25px rgba(0, 255, 255, 0.15), inset 0 0 10px rgba(0, 255, 255, 0.05);
    backdrop-filter: blur(8px);
    transition: all 0.3s ease;
  }

  .edit-tag-card:hover {
    box-shadow: 0 0 40px rgba(0, 255, 255, 0.25), inset 0 0 15px rgba(0, 255, 255, 0.1);
    transform: translateY(-3px);
  }

  .edit-header h3 {
    color: #00eaff;
    text-shadow: 0 0 15px #00eaff;
    font-weight: 600;
  }

  .edit-header p {
    color: #9bbfd1;
    font-size: 0.95rem;
  }

  label {
    font-weight: 500;
    color: #cfe9ff;
    display: block;
    margin-bottom: 6px;
  }

  .form-control {
    width: 100%;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(0, 234, 255, 0.3);
    border-radius: 12px;
    color: #e6f1ff;
    padding: 12px 14px;
    font-size: 1rem;
    transition: all 0.3s ease;
  }

  .form-control:focus {
    outline: none;
    border-color: #00eaff;
    box-shadow: 0 0 15px rgba(0, 234, 255, 0.5);
  }

  textarea.form-control {
    resize: none;
  }

  .btn-update {
    background: linear-gradient(90deg, #00bcd4, #1de9b6);
    border: none;
    border-radius: 30px;
    padding: 12px 40px;
    color: #fff;
    font-size: 1.05rem;
    font-weight: 600;
    box-shadow: 0 0 20px rgba(0, 255, 255, 0.4);
    transition: all 0.3s ease;
  }

  .btn-update:hover {
    box-shadow: 0 0 30px rgba(0, 255, 255, 0.8);
    transform: scale(1.05);
  }

  .btn-cancel {
    display: inline-block;
    margin-left: 15px;
    color: #a5b9c9;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 25px;
    padding: 10px 25px;
    text-decoration: none;
    transition: 0.3s;
  }

  .btn-cancel:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
  }
</style>
