<?php
// themthegame.php - Thêm thẻ game mới
require_once('ketnoi.php');

$errors = [];

if (isset($_POST['add_tag'])) {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');

    if ($name === '') $errors[] = 'Tên thẻ không được để trống.';
    if ($slug === '') $errors[] = 'Slug không được để trống.';

    if (empty($errors)) {
        $check = mysqli_prepare($ketnoi, "SELECT tag_id FROM tags WHERE slug = ?");
        mysqli_stmt_bind_param($check, 's', $slug);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            $errors[] = 'Slug đã tồn tại.';
        }
        mysqli_stmt_close($check);
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($ketnoi, "INSERT INTO tags (name, slug) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, 'ss', $name, $slug);
        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("✅ Thêm thẻ game thành công!"); window.location.href="index.php?page_layout=danhsachthegame";</script>';
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
                <h2><i class='bx bx-purchase-tag-alt'></i> Thêm thẻ game mới</h2>
                <div class="header-breadcrumb">
                    <a href="index.php">Trang chủ</a> / <a href="?page_layout=danhsachthegame">Thẻ game</a> / Thêm mới
                </div>
            </div>
            <div class="header-actions">
                <a href="?page_layout=danhsachthegame" class="btn btn-ghost">
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
                <input type="hidden" name="add_tag" value="1">
                
                <div class="form-section" style="max-width:600px;">
                    <div class="form-section-title">
                        <i class='bx bx-hash'></i> Thông tin thẻ game
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Tên thẻ</label>
                        <input type="text" name="name" id="name" class="form-input" 
                               placeholder="VD: Action, RPG, FPS..." 
                               value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Slug (URL)</label>
                        <input type="text" name="slug" id="slug" class="form-input" 
                               placeholder="VD: action, rpg, fps..." 
                               value="<?= isset($slug) ? htmlspecialchars($slug) : '' ?>" required>
                    </div>

                    <div class="form-divider"></div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-success">
                            <i class='bx bx-check'></i> Thêm thẻ game
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class='bx bx-reset'></i> Đặt lại
                        </button>
                        <a href="?page_layout=danhsachthegame" class="btn btn-ghost">
                            <i class='bx bx-x'></i> Hủy
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('name').addEventListener('input', function() {
    const slug = this.value
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd').replace(/Đ/g, 'd')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
    document.getElementById('slug').value = slug;
});
</script>
