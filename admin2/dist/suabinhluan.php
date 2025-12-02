<?php
require_once('ketnoi.php');

if (!isset($_GET['id'])) {
    echo "<script>alert('Thiếu ID bình luận!');
    window.location.href='index.php?page_layout=danhsachbinhluan';</script>";
    exit;
}

$id = intval($_GET['id']);
$comment = mysqli_query($ketnoi, "
  SELECT c.*, a.title AS article_title, u.display_name AS user_name
  FROM comments c
  LEFT JOIN articles a ON c.article_id = a.article_id
  LEFT JOIN users u ON c.user_id = u.user_id
  WHERE c.comment_id = $id
");
$c = mysqli_fetch_assoc($comment);

if (!$c) {
    echo "<script>alert('Không tìm thấy bình luận!');
    window.location.href='index.php?page_layout=danhsachbinhluan';</script>";
    exit;
}

// Cập nhật
if (isset($_POST['update_comment'])) {
    $content = mysqli_real_escape_string($ketnoi, $_POST['content']);
    if (empty($content)) {
        echo "<script>alert('❌ Nội dung không được để trống!');</script>";
    } else {
        $sql = "UPDATE comments SET content='$content' WHERE comment_id=$id";
        if (mysqli_query($ketnoi, $sql)) {
            echo "<script>alert('✅ Cập nhật bình luận thành công!');
            window.location.href='index.php?page_layout=danhsachbinhluan';</script>";
            exit;
        } else {
            echo "<script>alert('❌ Lỗi khi cập nhật!');</script>";
        }
    }
}
?>

<div class="container mt-4">
  <div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
      <h4 class="mb-0">✏️ Sửa bình luận</h4>
    </div>
    <div class="card-body">
      <form method="POST">
        <div class="mb-3">
          <label class="form-label fw-bold">Bài viết</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($c['article_title']); ?>" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Người bình luận</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($c['user_name']); ?>" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Nội dung</label>
          <textarea name="content" class="form-control" rows="4" required><?php echo htmlspecialchars($c['content']); ?></textarea>
        </div>

        <button type="submit" name="update_comment" class="btn btn-warning text-dark px-4">Cập nhật</button>
        <a href="index.php?page_layout=danhsachbinhluan" class="btn btn-secondary px-4">Quay lại</a>
      </form>
    </div>
  </div>
</div>
