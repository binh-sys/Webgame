<?php
// suathegame.php - Sửa thẻ game
require_once('ketnoi.php');

if (!isset($_GET['id'])) {
    echo '<script>alert("Thiếu ID!"); window.location.href="index.php?page_layout=danhsachthegame";</script>';
    exit();
}

$id = intval($_GET['id']);
$result = mysqli_query($ketnoi, "SELECT * FROM tags WHERE tag_id = $id");

if (!$result || mysqli_num_rows($result) == 0) {
    echo '<script>alert("Không tìm thấy thẻ game!"); window.location.href="index.php?page_layout=danhsachthegame";</script>';
    exit();
}

$tag = mysqli_fetch_assoc($result);
$errors = [];

if (isset($_POST['update_tag'])) {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');

    if ($name === '') $errors[] = 'Tên thẻ không được để trống.';
    if ($slug === '') $errors[] = 'Slug không được để trống.';

    if (empty($errors)) {
        $check = mysqli_prepare($ketnoi, "SELECT tag_id FROM tags WHERE slug = ? AND tag_id != ?");
        mysqli_stmt_bind_param($check, 'si', $slug, $id);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            $errors[] = 'Slug đã tồn tại.';
        }
        mysqli_stmt_close($check);
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($ketnoi, "UPDATE tags SET name=?, slug=? WHERE tag_id=?");
        mysqli_stmt_bind_param($stmt, 'ssi', $name, $slug, $id);
        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("✅ Cập nhật thành công!"); window.location.href="index.php?page_layout=danhsachthegame";</script>';
            exit;
        } else {
            $errors[] = 'Lỗi khi cập nhật.';
        }
        mysqli_stmt_close($stmt);
    }
    
    $tag['name'] = $name;
    $tag['slug'] = $slug;
}
?>

<link rel="stylesheet" href="assets/css/admin-forms.css">

<div class="admin-form-container">
    <div class="admin-form-card">
        <div class="admin-form-header">
            <div>
                <h2><i class='bx bx-edit'></i> Chỉnh sửa thẻ game</h2>
                <div class="header-breadcrumb">
                    <a href="index.php">Trang chủ</a> / <a href="?page_layout=danhsachthegame">Thẻ game</a> / Chỉnh sửa
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
                <input type="hidden" name="update_tag" value="1">
                
                <div class="form-section" style="max-width:600px;">
                    <div class="form-section-title">
                        <i class='bx bx-hash'></i> Thông tin thẻ game
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Tên thẻ</label>
                        <input type="text" name="name" class="form-input" 
                               value="<?= htmlspecialchars($tag['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Slug (URL)</label>
                        <input type="text" name="slug" class="form-input" 
                               value="<?= htmlspecialchars($tag['slug']) ?>" required>
                    </div>

                    <div class="form-divider"></div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-save'></i> Lưu thay đổi
                        </button>
                        <a href="?page_layout=xoathegame&id=<?= $id ?>" class="btn btn-danger" 
                           onclick="return confirm('Xóa thẻ game này?');">
                            <i class='bx bx-trash'></i> Xóa
                        </a>
                        <a href="?page_layout=danhsachthegame" class="btn btn-ghost">
                            <i class='bx bx-x'></i> Hủy
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
