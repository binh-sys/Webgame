<?php
// themlienthethegame.php - Thêm liên kết bài viết - thẻ game
require_once('ketnoi.php');

$articles = mysqli_query($ketnoi, "SELECT article_id, title FROM articles ORDER BY created_at DESC");
$tags = mysqli_query($ketnoi, "SELECT tag_id, name FROM tags ORDER BY name ASC");

$errors = [];

if (isset($_POST['add_link'])) {
    $article_id = intval($_POST['article_id'] ?? 0);
    $tag_id = intval($_POST['tag_id'] ?? 0);

    if ($article_id <= 0) $errors[] = 'Vui lòng chọn bài viết.';
    if ($tag_id <= 0) $errors[] = 'Vui lòng chọn thẻ game.';

    // Kiểm tra trùng
    if (empty($errors)) {
        $check = mysqli_prepare($ketnoi, "SELECT article_tag_id FROM article_tags WHERE article_id = ? AND tag_id = ?");
        mysqli_stmt_bind_param($check, 'ii', $article_id, $tag_id);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            $errors[] = 'Liên kết này đã tồn tại.';
        }
        mysqli_stmt_close($check);
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($ketnoi, "INSERT INTO article_tags (article_id, tag_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, 'ii', $article_id, $tag_id);
        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("✅ Thêm liên kết thành công!"); window.location.href="index.php?page_layout=danhsachlienthethegame";</script>';
            exit;
        } else {
            $errors[] = 'Lỗi khi lưu vào cơ sở dữ liệu.';
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<link rel="stylesheet" href="assets/css/admin-forms.css">

<div class="admin-form-container">
    <div class="admin-form-card">
        <div class="admin-form-header">
            <div>
                <h2><i class='bx bx-link-alt'></i> Thêm liên kết thẻ game</h2>
                <div class="header-breadcrumb">
                    <a href="index.php">Trang chủ</a> / <a href="?page_layout=danhsachlienthethegame">Liên kết thẻ</a> / Thêm mới
                </div>
            </div>
            <div class="header-actions">
                <a href="?page_layout=danhsachlienthethegame" class="btn btn-ghost">
                    <i class='bx bx-arrow-back'></i> Quay lại
                </a>
            </div>
        </div>

        <div class="admin-form-body">
            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class='bx bx-error-circle'></i>
                <div class="alert-content">
                    <div class="alert-title">Có lỗi xảy ra!</div>
                    <ul style="margin:0;padding-left:18px;">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="add_link" value="1">
                
                <div class="form-section" style="max-width:600px;">
                    <div class="form-section-title">
                        <i class='bx bx-link'></i> Liên kết bài viết với thẻ game
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Bài viết</label>
                        <select name="article_id" class="form-select" required>
                            <option value="">-- Chọn bài viết --</option>
                            <?php while ($a = mysqli_fetch_assoc($articles)): ?>
                                <option value="<?= $a['article_id'] ?>" <?= (isset($article_id) && $article_id == $a['article_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($a['title']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Thẻ game</label>
                        <select name="tag_id" class="form-select" required>
                            <option value="">-- Chọn thẻ game --</option>
                            <?php while ($t = mysqli_fetch_assoc($tags)): ?>
                                <option value="<?= $t['tag_id'] ?>" <?= (isset($tag_id) && $tag_id == $t['tag_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-divider"></div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-success">
                            <i class='bx bx-check'></i> Thêm liên kết
                        </button>
                        <a href="?page_layout=danhsachlienthethegame" class="btn btn-ghost">
                            <i class='bx bx-x'></i> Hủy
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
