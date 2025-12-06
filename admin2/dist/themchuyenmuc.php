<?php
// themchuyenmuc.php - Thêm chuyên mục mới
require_once('ketnoi.php');

$errors = [];

if (isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') $errors[] = 'Tên chuyên mục không được để trống.';
    if ($slug === '') $errors[] = 'Slug không được để trống.';

    // Kiểm tra trùng slug
    if (empty($errors)) {
        $check = mysqli_prepare($ketnoi, "SELECT category_id FROM categories WHERE slug = ?");
        mysqli_stmt_bind_param($check, 's', $slug);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            $errors[] = 'Slug đã tồn tại, vui lòng chọn slug khác.';
        }
        mysqli_stmt_close($check);
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($ketnoi, "INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $name, $slug, $description);
        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("✅ Thêm chuyên mục thành công!"); window.location.href="index.php?page_layout=danhsachchuyenmuc";</script>';
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
                <h2><i class='bx bx-folder-plus'></i> Thêm chuyên mục mới</h2>
                <div class="header-breadcrumb">
                    <a href="index.php">Trang chủ</a> / <a href="?page_layout=danhsachchuyenmuc">Chuyên mục</a> / Thêm mới
                </div>
            </div>
            <div class="header-actions">
                <a href="?page_layout=danhsachchuyenmuc" class="btn btn-ghost">
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

            <form method="POST" id="categoryForm">
                <input type="hidden" name="add_category" value="1">
                
                <div class="form-section" style="max-width:700px;">
                    <div class="form-section-title">
                        <i class='bx bx-category'></i> Thông tin chuyên mục
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Tên chuyên mục</label>
                        <input type="text" name="name" id="name" class="form-input" 
                               placeholder="VD: Tin tức Game" 
                               value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Slug (URL)</label>
                        <input type="text" name="slug" id="slug" class="form-input" 
                               placeholder="VD: tin-tuc-game" 
                               value="<?= isset($slug) ? htmlspecialchars($slug) : '' ?>" required>
                        <div class="form-helper">
                            <i class='bx bx-info-circle'></i> Slug sẽ được tự động tạo từ tên chuyên mục
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-textarea" rows="4" 
                                  placeholder="Mô tả ngắn về chuyên mục..."><?= isset($description) ? htmlspecialchars($description) : '' ?></textarea>
                    </div>

                    <div class="form-divider"></div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-success">
                            <i class='bx bx-check'></i> Thêm chuyên mục
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class='bx bx-reset'></i> Đặt lại
                        </button>
                        <a href="?page_layout=danhsachchuyenmuc" class="btn btn-ghost">
                            <i class='bx bx-x'></i> Hủy
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto generate slug
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
