<?php
require_once('ketnoi.php');

// Lấy danh sách bài viết và người dùng để chọn
$articles = mysqli_query($ketnoi, "SELECT article_id, title FROM articles");
$users = mysqli_query($ketnoi, "SELECT user_id, display_name FROM users");

if (isset($_POST['add_comment'])) {
    $article_id = intval($_POST['article_id']);
    $user_id = intval($_POST['user_id']);
    $content = mysqli_real_escape_string($ketnoi, $_POST['content']);

    if (empty($content)) {
        echo "<script>alert('❌ Nội dung không được để trống!');</script>";
    } else {
        $sql = "INSERT INTO comments (article_id, user_id, content, created_at)
                VALUES ('$article_id', '$user_id', '$content', NOW())";
        if (mysqli_query($ketnoi, $sql)) {
            echo "<script>alert('✅ Thêm bình luận thành công!');
            window.location.href='index.php?page_layout=danhsachbinhluan';</script>";
            exit;
        } else {
            echo "<script>alert('❌ Lỗi khi thêm bình luận!');</script>";
        }
    }
}
?>

<div class="container mt-4">
  <div class="card shadow-sm">
    <div class="card-header bg-success text-white">
      <h4 class="mb-0">➕ Thêm bình luận</h4>
    </div>
    <div class="card-body">
      <form method="POST">
        <div class="mb-3">
          <label class="form-label fw-bold">Bài viết</label>
          <select name="article_id" class="form-select" required>
            <option value="">-- Chọn bài viết --</option>
            <?php while ($a = mysqli_fetch_assoc($articles)) { ?>
              <option value="<?php echo $a['article_id']; ?>"><?php echo htmlspecialchars($a['title']); ?></option>
            <?php } ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Người bình luận</label>
          <select name="user_id" class="form-select" required>
            <option value="">-- Chọn người dùng --</option>
            <?php while ($u = mysqli_fetch_assoc($users)) { ?>
              <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['display_name']); ?></option>
            <?php } ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Nội dung bình luận</label>
          <textarea name="content" class="form-control" rows="4" required></textarea>
        </div>

        <button type="submit" name="add_comment" class="btn btn-success px-4">Lưu</button>
        <a href="index.php?page_layout=danhsachbinhluan" class="btn btn-secondary px-4">Quay lại</a>
      </form>
    </div>
  </div>
</div>
