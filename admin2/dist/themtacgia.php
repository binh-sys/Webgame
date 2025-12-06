<?php
// themtacgia.php - Thêm tác giả mới
require_once('ketnoi.php');

$errors = [];

if (isset($_POST['add_author'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $display_name = trim($_POST['display_name'] ?? '');
    $role = 'editor'; // Tác giả luôn là biên tập viên

    if ($username === '') $errors[] = 'Tên đăng nhập không được để trống.';
    if ($email === '') $errors[] = 'Email không được để trống.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    if (strlen($password) < 6) $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
    if ($display_name === '') $display_name = $username;

    if (empty($errors)) {
        $check = mysqli_prepare($ketnoi, "SELECT user_id FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($check, 'ss', $username, $email);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            $errors[] = 'Tên đăng nhập hoặc email đã tồn tại.';
        }
        mysqli_stmt_close($check);
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $created_at = date('Y-m-d H:i:s');
        
        $stmt = mysqli_prepare($ketnoi, "INSERT INTO users (username, email, password, display_name, role, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssssss', $username, $email, $hashed_password, $display_name, $role, $created_at);
        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("✅ Thêm tác giả thành công!"); window.location.href="index.php?page_layout=danhsachtacgia";</script>';
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
                <h2><i class='bx bx-user-plus'></i> Thêm tác giả mới</h2>
                <div class="header-breadcrumb">
                    <a href="index.php">Trang chủ</a> / <a href="?page_layout=danhsachtacgia">Tác giả</a> / Thêm mới
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

            <form method="POST">
                <input type="hidden" name="add_author" value="1">
                
                <div class="form-grid" style="grid-template-columns: 1fr 1fr; max-width: 900px;">
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class='bx bx-user'></i> Thông tin tài khoản
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Tên đăng nhập</label>
                            <input type="text" name="username" class="form-input" 
                                   placeholder="VD: author_name" 
                                   value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Email</label>
                            <input type="email" name="email" class="form-input" 
                                   placeholder="VD: author@example.com" 
                                   value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tên hiển thị</label>
                            <input type="text" name="display_name" class="form-input" 
                                   placeholder="VD: Nguyễn Văn A" 
                                   value="<?= isset($display_name) ? htmlspecialchars($display_name) : '' ?>">
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title">
                            <i class='bx bx-lock'></i> Bảo mật & Quyền hạn
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Mật khẩu</label>
                            <input type="password" name="password" class="form-input" 
                                   placeholder="Tối thiểu 6 ký tự" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Vai trò</label>
                            <div class="status-indicator" style="background:rgba(168,85,247,0.1);border-color:rgba(168,85,247,0.2);color:var(--accent-purple);">
                                <i class='bx bx-edit'></i>
                                <span>Biên tập viên</span>
                            </div>
                            <div class="form-helper">
                                <i class='bx bx-info-circle'></i> Tác giả sẽ được gán quyền biên tập viên
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-divider"></div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-success">
                        <i class='bx bx-check'></i> Thêm tác giả
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class='bx bx-reset'></i> Đặt lại
                    </button>
                    <a href="?page_layout=danhsachtacgia" class="btn btn-ghost">
                        <i class='bx bx-x'></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
