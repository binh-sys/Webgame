<?php
require_once('ketnoi.php');

// Lấy danh sách bài viết và người dùng
$articles = mysqli_query($ketnoi, "SELECT article_id, title FROM articles ORDER BY created_at DESC");
$users = mysqli_query($ketnoi, "SELECT user_id, display_name FROM users ORDER BY display_name ASC");

if (isset($_POST['add'])) {
  $article_id = $_POST['article_id'];
  $user_id = $_POST['user_id'];

  $sql = "
    INSERT INTO favorites (user_id, article_id, created_at)
    VALUES ('$user_id', '$article_id', NOW())
  ";

  if (mysqli_query($ketnoi, $sql)) {
    header("Location: index.php?page_layout=danhsachyeuthich");
    exit;
  } else {
    echo "<div class='alert alert-danger m-4'>❌ Lỗi khi thêm yêu thích!</div>";
  }
}
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">➕ Thêm yêu thích</h4>

    <div class="card">
      <div class="card-body">
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Người dùng</label>
            <select name="user_id" class="form-select" required>
              <option value="">-- Chọn người dùng --</option>
              <?php while ($u = mysqli_fetch_assoc($users)) { ?>
                <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['display_name']); ?></option>
              <?php } ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Bài viết</label>
            <select name="article_id" class="form-select" required>
              <option value="">-- Chọn bài viết --</option>
              <?php while ($a = mysqli_fetch_assoc($articles)) { ?>
                <option value="<?php echo $a['article_id']; ?>"><?php echo htmlspecialchars($a['title']); ?></option>
              <?php } ?>
            </select>
          </div>

          <button type="submit" name="add" class="btn btn-primary">Thêm yêu thích</button>
          <a href="index.php?page_layout=danhsachyeuthich" class="btn btn-secondary">Hủy</a>
        </form>
      </div>
    </div>
  </div>
</div>
