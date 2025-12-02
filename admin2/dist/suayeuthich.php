<?php
require_once('ketnoi.php');
$id = $_GET['id'];

$result = mysqli_query($ketnoi, "SELECT * FROM favorites WHERE favorite_id = '$id'");
$data = mysqli_fetch_assoc($result);

$articles = mysqli_query($ketnoi, "SELECT article_id, title FROM articles");
$users = mysqli_query($ketnoi, "SELECT user_id, display_name FROM users");

if (isset($_POST['update'])) {
  $article_id = $_POST['article_id'];
  $user_id = $_POST['user_id'];

  $sql = "
    UPDATE favorites 
    SET user_id = '$user_id', article_id = '$article_id' 
    WHERE favorite_id = '$id'
  ";

  if (mysqli_query($ketnoi, $sql)) {
    header("Location: index.php?page_layout=danhsachyeuthich");
    exit;
  } else {
    echo "<div class='alert alert-danger m-4'>❌ Lỗi khi cập nhật yêu thích!</div>";
  }
}
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">✏️ Sửa yêu thích</h4>

    <div class="card">
      <div class="card-body">
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Người dùng</label>
            <select name="user_id" class="form-select" required>
              <?php while ($u = mysqli_fetch_assoc($users)) { ?>
                <option value="<?php echo $u['user_id']; ?>" <?php if ($u['user_id'] == $data['user_id']) echo 'selected'; ?>>
                  <?php echo htmlspecialchars($u['display_name']); ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Bài viết</label>
            <select name="article_id" class="form-select" required>
              <?php while ($a = mysqli_fetch_assoc($articles)) { ?>
                <option value="<?php echo $a['article_id']; ?>" <?php if ($a['article_id'] == $data['article_id']) echo 'selected'; ?>>
                  <?php echo htmlspecialchars($a['title']); ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <button type="submit" name="update" class="btn btn-primary">Cập nhật</button>
          <a href="index.php?page_layout=danhsachyeuthich" class="btn btn-secondary">Hủy</a>
        </form>
      </div>
    </div>
  </div>
</div>
