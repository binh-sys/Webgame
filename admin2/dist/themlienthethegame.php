<?php
require_once('ketnoi.php');

// Lấy danh sách bài viết và thẻ game
$articles = mysqli_query($ketnoi, "SELECT article_id, title FROM articles ORDER BY title ASC");
$tags = mysqli_query($ketnoi, "SELECT tag_id, name FROM tags ORDER BY name ASC");

// Xử lý thêm liên kết
if (isset($_POST['add_link'])) {
  $article_id = $_POST['article_id'];
  $tag_id = $_POST['tag_id'];

  if (!empty($article_id) && !empty($tag_id)) {
    $sql = "INSERT INTO article_tags (article_id, tag_id) VALUES ('$article_id', '$tag_id')";
    if (mysqli_query($ketnoi, $sql)) {
      $_SESSION['message'] = "✅ Thêm liên kết thành công!";
    } else {
      $_SESSION['error'] = "❌ Lỗi khi thêm liên kết: " . mysqli_error($ketnoi);
    }
  } else {
    $_SESSION['error'] = "⚠️ Vui lòng chọn đầy đủ thông tin!";
  }

  header("Location: index.php?page_layout=danhsachlienthethegame");
  exit();
}
?>

<div class="content-wrapper d-flex align-items-center justify-content-center" style="min-height: 100vh;">
  <div class="form-container">

    <!-- Tiêu đề -->
    <div class="form-header text-center mb-4">
      <h3><i class="bx bx-link-alt me-2"></i> Thêm Liên Kết Bài Viết & Thẻ Game</h3>
      <p>Kết nối bài viết với thẻ game để tối ưu hiển thị và quản lý nội dung.</p>
    </div>

    <!-- Form -->
    <form method="POST" class="neon-form">

      <div class="form-group mb-4">
        <label for="article_id"><i class="bx bx-news me-2"></i>Chọn bài viết</label>
        <select name="article_id" id="article_id" class="form-control" required>
          <option value="">-- Chọn bài viết --</option>
          <?php while ($a = mysqli_fetch_assoc($articles)) { ?>
            <option value="<?= $a['article_id'] ?>"><?= htmlspecialchars($a['title']) ?></option>
          <?php } ?>
        </select>
      </div>

      <div class="form-group mb-4">
        <label for="tag_id"><i class="bx bx-purchase-tag me-2"></i>Chọn thẻ game</label>
        <select name="tag_id" id="tag_id" class="form-control" required>
          <option value="">-- Chọn thẻ game --</option>
          <?php while ($t = mysqli_fetch_assoc($tags)) { ?>
            <option value="<?= $t['tag_id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
          <?php } ?>
        </select>
      </div>

      <div class="text-center">
        <button type="submit" name="add_link" class="btn-submit">
          <i class="bx bx-plus-circle me-2"></i> Thêm liên kết
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
    margin: 0;
    padding: 0;
  }

  .form-container {
    background: linear-gradient(145deg, rgba(20, 30, 48, 0.9), rgba(36, 59, 85, 0.95));
    border-radius: 20px;
    padding: 45px 50px;
    width: 600px;
    box-shadow: 0 0 25px rgba(0, 255, 255, 0.15), inset 0 0 10px rgba(0, 255, 255, 0.05);
    backdrop-filter: blur(8px);
    transition: all 0.3s ease;
    text-align: left;
  }

  .form-container:hover {
    box-shadow: 0 0 40px rgba(0, 255, 255, 0.25), inset 0 0 15px rgba(0, 255, 255, 0.1);
    transform: translateY(-4px);
  }

  .form-header h3 {
    font-size: 1.6rem;
    color: #00eaff;
    text-shadow: 0 0 12px #00eaff;
    font-weight: 600;
  }

  .form-header p {
    color: #9bbfd1;
    font-size: 0.95rem;
  }

  .form-group label {
    font-weight: 500;
    color: #cfe9ff;
    margin-bottom: 8px;
    display: block;
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

  .btn-submit {
    background: linear-gradient(90deg, #00bcd4, #1de9b6);
    border: none;
    border-radius: 30px;
    padding: 12px 40px;
    color: #fff;
    font-size: 1.05rem;
    font-weight: 600;
    box-shadow: 0 0 20px rgba(0, 255, 255, 0.4);
    transition: all 0.3s;
  }

  .btn-submit:hover {
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

  select option {
    background-color: #0d1b2a;
    color: #e6f1ff;
  }
</style>
