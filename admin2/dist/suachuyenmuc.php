<?php
// suachuyenmuc.php - Sửa chuyên mục
require_once('ketnoi.php');

if (!isset($_GET['id'])) {
    echo '<script>alert("Thiếu ID chuyên mục!"); window.location.href="index.php?page_layout=danhsachchuyenmuc";</script>';
    exit();
}

$id = intval($_GET['id']);
$result = mysqli_query($ketnoi, "SELECT * FROM categories WHERE category_id = $id");

if (!$result || mysqli_num_rows($result) == 0) {
    echo '<script>alert("Không tìm thấy chuyên mục!"); window.location.href="index.php?page_layout=danhsachchuyenmuc";</script>';
    exit();
}

$category = mysqli_fetch_assoc($result);
$errors = [];

if (isset($_POST['update_category'])) {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') $errors[] = 'Tên chuyên mục không được để trống.';
    if ($slug === '') $errors[] = 'Slug không được để trống.';

    // Kiểm tra trùng slug (trừ chính nó)
    if (empty($errors)) {
        $check = mysqli_prepare($ketnoi, "SELECT category_id FROM categories WHERE slug = ? AND category_id != ?");
        mysqli_stmt_bind_param($check, 'si', $slug, $id);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            $errors[] = 'Slug đã tồn tại, vui lòng chọn slug khác.';
        }
        mysqli_stmt_close($check);
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($ketnoi, "UPDATE categories SET name=?, slug=?, description=? WHERE category_id=?");
        mysqli_stmt_bind_param($stmt, 'sssi', $name, $slug, $description, $id);
        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("✅ Cập nhật chuyên mục thành công!"); window.location.href="index.php?page_layout=danhsachchuyenmuc";</script>';
            exit;
        } else {
            $errors[] = 'Lỗi khi cập nhật.';
        }
        mysqli_stmt_close($stmt);
    }
    
    $category['name'] = $name;
    $category['slug'] = $slug;
    $category['description'] = $description;
}
?>

<link rel="stylesheet" href="assets/css/admin-forms.css">

<div class="admin-form-container">
    <div class="admin-form-card">
        <div class="admin-form-header">
            <div>
                <h2><i class='bx bx-edit'></i> Chỉnh sửa chuyên mục</h2>
                <div class="header-breadcrumb">
                    <a href="index.php">Trang chủ</a> / <a href="?page_layout=danhsachchuyenmuc">Chuyên mục</a> / Chỉnh sửa
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
                <input type="hidden" name="update_category" value="1">
                
                <div class="form-section" style="max-width:700px;">
                    <div class="form-section-title">
                        <i class='bx bx-category'></i> Thông tin chuyên mục
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Tên chuyên mục</label>
                        <input type="text" name="name" id="name" class="form-input" 
                               value="<?= htmlspecialchars($category['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Slug (URL)</label>
                        <input type="text" name="slug" id="slug" class="form-input" 
                               value="<?= htmlspecialchars($category['slug']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-textarea" rows="4"><?= htmlspecialchars($category['description']) ?></textarea>
                    </div>

                    <div class="form-divider"></div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-save'></i> Lưu thay đổi
                        </button>
                        <a href="?page_layout=xoachuyenmuc&id=<?= $id ?>" class="btn btn-danger" 
                           onclick="return confirm('Bạn có chắc chắn muốn xóa chuyên mục này?');">
                            <i class='bx bx-trash'></i> Xóa
                        </a>
                        <a href="?page_layout=danhsachchuyenmuc" class="btn btn-ghost">
                            <i class='bx bx-x'></i> Hủy
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
