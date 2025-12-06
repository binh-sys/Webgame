<?php
// suatacgia.php - Sửa thông tin tác giả
require_once('ketnoi.php');

if (!isset($_GET['id'])) {
    echo '<script>alert("Thiếu ID!"); window.location.href="index.php?page_layout=danhsachtacgia";</script>';
    exit();
}

$id = intval($_GET['id']);
$result = mysqli_query($ketnoi, "SELECT * FROM users WHERE user_id = $id AND role = 'editor'");

if (!$result || mysqli_num_rows($result) == 0) {
    echo '<script>alert("Không tìm thấy tác giả!"); window.location.href="index.php?page_layout=danhsachtacgia";</script>';
    exit();
}

$author = mysqli_fetch_assoc($result);
$errors = [];

if (isset($_POST['update_author'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $display_name = trim($_POST['display_name'] ?? '');
    $role = 'editor'; // Tác giả luôn là biên tập viên
    $new_password = $_POST['new_password'] ?? '';

    if ($username === '') $errors[] = 'Tên đăng nhập không được để trống.';
    if ($email === '') $errors[] = 'Email không được để trống.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    if ($display_name === '') $display_name = $username;

    if (empty($errors)) {
        $check = mysqli_prepare($ketnoi, "SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        mysqli_stmt_bind_param($check, 'ssi', $username, $email, $id);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            $errors[] = 'Tên đăng nhập hoặc email đã tồn tại.';
        }
        mysqli_stmt_close($check);
    }

    if (empty($errors)) {
        if (!empty($new_password)) {
            if (strlen($new_password) < 6) {
                $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($ketnoi, "UPDATE users SET username=?, email=?, password=?, display_name=?, role=? WHERE user_id=?");
                mysqli_stmt_bind_param($stmt, 'sssssi', $username, $email, $hashed_password, $display_name, $role, $id);
            }
        } else {
            $stmt = mysqli_prepare($ketnoi, "UPDATE users SET username=?, email=?, display_name=?, role=? WHERE user_id=?");
            mysqli_stmt_bind_param($stmt, 'ssssi', $username, $email, $display_name, $role, $id);
        }
        
        if (empty($errors) && isset($stmt)) {
            if (mysqli_stmt_execute($stmt)) {
                echo '<script>alert("✅ Cập nhật tác giả thành công!"); window.location.href="index.php?page_layout=danhsachtacgia";</script>';
                exit;
            } else {
                $errors[] = 'Lỗi khi cập nhật.';
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    $author['username'] = $username;
    $author['email'] = $email;
    $author['display_name'] = $display_name;
    $author['role'] = $role;
}

// Đếm số bài viết
$article_count = mysqli_fetch_assoc(mysqli_query($ketnoi, "SELECT COUNT(*) as c FROM articles WHERE author_id = $id"))['c'];
?>

<link rel="stylesheet" href="assets/css/admin-forms.css">

<div class="admin-form-container">
    <div class="admin-form-card">
        <div class="admin-form-header">
            <div>
                <h2><i class='bx bx-edit'></i> Chỉnh sửa tác giả</h2>
                <div class="header-breadcrumb">
                    <a href="index.php">Trang chủ</a> / <a href="?page_layout=danhsachtacgia">Tác giả</a> / Chỉnh sửa
                </div>
            </div>
            <div class="header-actions">
                <a href="?page_layout=danhsachtacgia" class="btn btn-ghost">
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

            <div class="alert alert-info" style="margin-bottom:24px;">
                <i class='bx bx-info-circle'></i>
                <div class="alert-content">
                    <strong>ID:</strong> <?= $author['user_id'] ?> | 
                    <strong>Số bài viết:</strong> <?= $article_count ?> | 
                    <strong>Ngày tham gia:</strong> <?= date('d/m/Y', strtotime($author['created_at'])) ?>
                </div>
            </div>

            <form method="POST">
                <input type="hidden" name="update_author" value="1">
                
                <div class="form-grid" style="grid-template-columns: 1fr 1fr; max-width: 900px;">
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class='bx bx-user'></i> Thông tin tài khoản
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Tên đăng nhập</label>
                            <input type="text" name="username" class="form-input" 
                                   value="<?= htmlspecialchars($author['username']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Email</label>
                            <input type="email" name="email" class="form-input" 
                                   value="<?= htmlspecialchars($author['email']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tên hiển thị</label>
                            <input type="text" name="display_name" class="form-input" 
                                   value="<?= htmlspecialchars($author['display_name']) ?>">
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title">
                            <i class='bx bx-lock'></i> Bảo mật & Quyền hạn
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-input" 
                                   placeholder="Để trống nếu không đổi">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Vai trò</label>
                            <div class="status-indicator" style="background:rgba(168,85,247,0.1);border-color:rgba(168,85,247,0.2);color:var(--accent-purple);">
                                <i class='bx bx-edit'></i>
                                <span>Biên tập viên</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-divider"></div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-save'></i> Lưu thay đổi
                    </button>
                    <a href="?page_layout=xoatacgia&id=<?= $id ?>" class="btn btn-danger" 
                       onclick="return confirm('Xóa tác giả này? Các bài viết của tác giả sẽ không bị xóa.');">
                        <i class='bx bx-trash'></i> Xóa
                    </a>
                    <a href="?page_layout=danhsachtacgia" class="btn btn-ghost">
                        <i class='bx bx-x'></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
