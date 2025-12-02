<?php
require_once('ketnoi.php');

if (!isset($_GET['id'])) {
    echo '<script>alert("Thiếu ID bài viết!"); window.location.href="index.php?page_layout=danhsachbaiviet";</script>';
    exit();
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM articles WHERE article_id = $id";
$result = mysqli_query($ketnoi, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo '<script>alert("Không tìm thấy bài viết!"); window.location.href="index.php?page_layout=danhsachbaiviet";</script>';
    exit();
}

$article = mysqli_fetch_assoc($result);
$categories = mysqli_query($ketnoi, "SELECT * FROM categories ORDER BY name ASC");
$authors = mysqli_query($ketnoi, "SELECT * FROM authors ORDER BY name ASC");

// Khi nhấn nút cập nhật
if (isset($_POST['update_article'])) {
    $title = mysqli_real_escape_string($ketnoi, $_POST['title']);
    $slug = mysqli_real_escape_string($ketnoi, $_POST['slug']);
    $excerpt = mysqli_real_escape_string($ketnoi, $_POST['excerpt']);
    $content = mysqli_real_escape_string($ketnoi, $_POST['content']);
    $category_id = intval($_POST['category_id']);
    $author_id = intval($_POST['author_id']);
    $status = mysqli_real_escape_string($ketnoi, $_POST['status']);
    $featured_image = $article['featured_image'];

    // Nếu có ảnh mới thì thay ảnh cũ
    if (!empty($_FILES['featured_image']['name'])) {
        $upload_dir = '../uploads/articles/';
        $original_name = basename($_FILES['featured_image']['name']);
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $filename_only = pathinfo($original_name, PATHINFO_FILENAME);

        // Loại bỏ ký tự lạ để an toàn
        $safe_name = preg_replace("/[^a-zA-Z0-9_-]/", "_", $filename_only);
        $target_file = $upload_dir . $safe_name . '.' . $extension;

        // Nếu file trùng tên, tự thêm -copy1, -copy2, ...
        $counter = 1;
        while (file_exists($target_file)) {
            $target_file = $upload_dir . $safe_name . '-copy' . $counter . '.' . $extension;
            $counter++;
        }

        // Kiểm tra loại file (an toàn)
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array(strtolower($extension), $allowed_types)) {
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
                // Xóa ảnh cũ nếu có
                if (!empty($article['featured_image']) && file_exists($upload_dir . $article['featured_image'])) {
                    unlink($upload_dir . $article['featured_image']);
                }
                $featured_image = basename($target_file); // lưu tên file vào CSDL
            } else {
                echo '<script>alert("❌ Lỗi khi upload ảnh!");</script>';
            }
        } else {
            echo '<script>alert("❌ Loại file không hợp lệ! Chỉ nhận jpg, png, gif, webp.");</script>';
        }
    }

    $sql_update = "UPDATE articles 
                   SET title='$title', slug='$slug', excerpt='$excerpt', content='$content',
                       category_id=$category_id, author_id=$author_id, status='$status',
                       featured_image='$featured_image'
                   WHERE article_id = $id";

    if (mysqli_query($ketnoi, $sql_update)) {
        echo '<script>alert("✅ Cập nhật bài viết thành công!"); window.location.href="index.php?page_layout=danhsachbaiviet";</script>';
        exit();
    } else {
        echo '<script>alert("❌ Lỗi khi cập nhật bài viết!");</script>';
    }
}
?>

<style>
/* Khung tổng */
.edit-container {
    padding: 20px;
}

/* Card */
.edit-card {
    background: #0d0f1a;
    border-radius: 14px;
    border: 1px solid #142437;
    box-shadow: 0 0 25px rgba(0, 255, 255, 0.08);
}

/* Header */
.edit-header {
    background: linear-gradient(to right, #041726, #052b40);
    padding: 18px;
    border-radius: 14px 14px 0 0;
    color: #33e7ff;
    text-shadow: 0 0 8px #00eaff;
}

.edit-header h3 {
    margin: 0;
    font-weight: 600;
}

/* Body */
.edit-body {
    padding: 25px;
    color: #cdeaff;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #00eaff;
    text-shadow: 0 0 8px #00eaff;
    margin-bottom: 15px;
    margin-top: 15px;
}

/* Input neon */
.neon-input, .neon-textarea {
    width: 100%;
    background: #080c14;
    border: 1px solid #1c3046;
    color: #d5edff;
    padding: 12px 14px;
    border-radius: 10px;
    font-size: 16px;
    transition: .25s;
}

.neon-input:focus,
.neon-textarea:focus {
    border-color: #00eaff;
    box-shadow: 0 0 9px #00eaff;
}

/* Textarea */
.neon-textarea {
    min-height: 140px;
}

.content-area {
    height: 260px;
}

/* Ảnh */
.preview-img {
    width: 220px;
    border-radius: 10px;
    border: 1px solid #00eaff;
    box-shadow: 0 0 10px #00eaff;
}

/* Divider */
.divider {
    margin: 30px 0;
    border-color: rgba(0, 255, 255, 0.1);
}

/* Buttons */
.action-btns {
    display: flex;
    justify-content: space-between;
}

.btn-save {
    background: #00ffa6;
    border: none;
    color: #002d1f;
    font-size: 18px;
    font-weight: 600;
    padding: 14px 30px;
    border-radius: 12px;
    transition: .25s;
}

.btn-save:hover {
    background: #00eaff;
    box-shadow: 0 0 12px #00eaff;
}

.btn-back {
    background: #1a2636;
    color: #c5dfff;
    padding: 14px 30px;
    border-radius: 12px;
    font-size: 18px;
    transition: .25s;
}

.btn-back:hover {
    background: #0f1823;
    box-shadow: 0 0 10px #375d7a;
}

</style>

<div class="edit-container">

  <div class="edit-card">
    <div class="edit-header">
      <h3><i class="bx bx-edit-alt"></i> CHỈNH SỬA BÀI VIẾT</h3>
    </div>

    <div class="edit-body">

      <form method="POST" enctype="multipart/form-data">

        <div class="section-title">📝 Thông tin bài viết</div>

        <!-- Tiêu đề -->
        <div class="mb-4">
          <label class="form-label">Tiêu đề</label>
          <input type="text" name="title" class="neon-input"
                 value="<?= htmlspecialchars($article['title']) ?>" required>
        </div>

        <!-- Slug -->
        <div class="mb-4">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" class="neon-input"
                 value="<?= htmlspecialchars($article['slug']) ?>" required>
        </div>

        <!-- Tóm tắt -->
        <div class="mb-4">
          <label class="form-label">Tóm tắt</label>
          <textarea name="excerpt" class="neon-textarea"><?= htmlspecialchars($article['excerpt']) ?></textarea>
        </div>

        <!-- Nội dung -->
        <div class="mb-4">
          <label class="form-label">Nội dung</label>
          <textarea name="content" class="neon-textarea content-area"><?= htmlspecialchars($article['content']) ?></textarea>
        </div>

        <div class="section-title">📌 Phân loại & Tác giả</div>

        <div class="row">
          <!-- Danh mục -->
          <div class="col-md-6 mb-4">
            <label class="form-label">Danh mục</label>
            <select name="category_id" class="neon-input" required>
              <?php while ($cat = mysqli_fetch_assoc($categories)) { ?>
                <option value="<?= $cat['category_id'] ?>" <?= ($cat['category_id'] == $article['category_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['name']) ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <!-- Tác giả -->
          <div class="col-md-6 mb-4">
            <label class="form-label">Tác giả</label>
            <select name="author_id" class="neon-input" required>
              <?php while ($au = mysqli_fetch_assoc($authors)) { ?>
                <option value="<?= $au['author_id'] ?>" <?= ($au['author_id'] == $article['author_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($au['name']) ?>
                </option>
              <?php } ?>
            </select>
          </div>
        </div>

        <!-- Trạng thái -->
        <div class="mb-4">
          <label class="form-label">Trạng thái</label>
          <select name="status" class="neon-input">
            <option value="draft" <?= ($article['status'] == 'draft') ? 'selected' : '' ?>>Nháp</option>
            <option value="published" <?= ($article['status'] == 'published') ? 'selected' : '' ?>>Xuất bản</option>
          </select>
        </div>

        <div class="section-title">🖼 Ảnh nổi bật</div>

        <div class="mb-4">
          <label class="form-label">Ảnh hiện tại</label><br>

          <?php if (!empty($article['featured_image'])) { ?>
            <img src="../uploads/articles/<?= $article['featured_image'] ?>" class="preview-img mb-3">
          <?php } else { ?>
            <p><em>Chưa có ảnh</em></p>
          <?php } ?>

          <input type="file" name="featured_image" class="neon-input">
        </div>

        <hr class="divider">

        <div class="action-btns">
          <button type="submit" name="update_article" class="btn-save">
            <i class="bx bx-save"></i> Lưu thay đổi
          </button>

          <a href="index.php?page_layout=danhsachbaiviet" class="btn-back">
            <i class="bx bx-arrow-back"></i> Quay lại
          </a>
        </div>

      </form>

    </div>
  </div>
</div>
