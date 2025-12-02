<?php
require_once('ketnoi.php');
$id = $_GET['id'] ?? 0;

// Lấy dữ liệu hiện tại
$sql = "SELECT * FROM article_tags WHERE id = '$id'";
$result = mysqli_query($ketnoi, $sql);
$link = mysqli_fetch_assoc($result);

if (!$link) {
    echo "<div class='alert alert-danger text-center mt-4'>❌ Không tìm thấy liên kết!</div>";
    exit;
}

// Lấy danh sách bài viết và thẻ game
$articles = mysqli_query($ketnoi, "SELECT article_id, title FROM articles ORDER BY title ASC");
$tags = mysqli_query($ketnoi, "SELECT tag_id, name FROM tags ORDER BY name ASC");

// Cập nhật dữ liệu
if (isset($_POST['update_link'])) {
    $article_id = $_POST['article_id'];
    $tag_id = $_POST['tag_id'];

    $update = "UPDATE article_tags SET article_id='$article_id', tag_id='$tag_id' WHERE id='$id'";
    if (mysqli_query($ketnoi, $update)) {
        echo "<script>
            alert('✅ Cập nhật liên kết thành công!');
            window.location.href='?page_layout=danhsachlienthethegame';
        </script>";
        exit;
    } else {
        echo "<div class='alert alert-danger text-center mt-3'>❌ Lỗi khi cập nhật!</div>";
    }
}
?>

<div class="content-wrapper d-flex align-items-center justify-content-center" style="min-height:100vh;">
  <div class="edit-link-card">
    <div class="edit-header text-center mb-4">
      <h3><i class="bx bx-link-alt me-2"></i> Chỉnh sửa Liên kết Bài viết & Thẻ Game</h3>
      <p>Điều chỉnh mối quan hệ giữa bài viết và thẻ game trong hệ thống.</p>
    </div>

    <form method="POST" class="neon-form">
      <div class="form-group mb-4">
        <label><i class="bx bx-news me-2"></i>Chọn bài viết</label>
        <select name="article_id" class="form-select" required>
          <?php while ($a = mysqli_fetch_assoc($articles)) { ?>
            <option value="<?= $a['article_id'] ?>" <?= ($a['article_id'] == $link['article_id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($a['title']) ?>
            </option>
          <?php } ?>
        </select>
      </div>

      <div class="form-group mb-4">
        <label><i class="bx bx-purchase-tag-alt me-2"></i>Chọn thẻ game</label>
        <select name="tag_id" class="form-select" required>
          <?php while ($t = mysqli_fetch_assoc($tags)) { ?>
            <option value="<?= $t['tag_id'] ?>" <?= ($t['tag_id'] == $link['tag_id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($t['name']) ?>
            </option>
          <?php } ?>
        </select>
      </div>

      <div class="text-center mt-4">
        <button type="submit" name="update_link" class="btn-update">
          <i class="bx bx-save me-2"></i> Cập nhật
        </button>
        <a href="?page_layout=danhsachlienthethegame" class="btn-cancel">
          <i class="bx bx-arrow-back me-1"></i> Quay lại
        </a>
      </div>
    </form>
  </div>
</div>

<style>
  body {
    background: radial-gradient(circle at top left, #0d1b2a, #000814);
    font-family: 'Poppins', sans-serif;
    color: #e6f1ff;
  }

  .edit-link-card {
    width: 600px;
    background: linear-gradient(145deg, rgba(20, 30, 48, 0.9), rgba(36, 59, 85, 0.95));
    border-radius: 20px;
    padding: 45px 50px;
    box-shadow: 0 0 25px rgba(0, 255, 255, 0.15), inset 0 0 10px rgba(0, 255, 255, 0.05);
    backdrop-filter: blur(8px);
    transition: all 0.3s ease;
  }

  .edit-link-card:hover {
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

  .form-select {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(0, 234, 255, 0.3);
    color: #e6f1ff;
    border-radius: 12px;
    padding: 12px 14px;
    font-size: 1rem;
    width: 100%;
    transition: all 0.3s ease;
  }

  .form-select:focus {
    outline: none;
    border-color: #00eaff;
    box-shadow: 0 0 15px rgba(0, 234, 255, 0.5);
  }

  option {
    background: #0d1b2a;
    color: #fff;
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
