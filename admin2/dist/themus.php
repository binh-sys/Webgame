<?php
// themus.php - Thêm người dùng mới
require_once('ketnoi.php');

$errors = [];

if (isset($_POST['add_user'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $display_name = trim($_POST['display_name'] ?? '');
    $role = in_array($_POST['role'] ?? '', ['user', 'editor', 'admin']) ? $_POST['role'] : 'user';

    if ($username === '') $errors[] = 'Tên đăng nhập không được để trống.';
    if ($email === '') $errors[] = 'Email không được để trống.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    if (strlen($password) < 6) $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
    if ($password !== $confirm_password) $errors[] = 'Mật khẩu xác nhận không khớp.';
    if ($display_name === '') $display_name = $username;

    // Kiểm tra trùng username/email
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
            echo '<script>alert("✅ Thêm người dùng thành công!"); window.location.href="index.php?page_layout=danhsachnguoidung";</script>';
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
                <h2><i class='bx bx-user-plus'></i> Thêm người dùng mới</h2>
                <div class="header-breadcrumb">
                    <a href="index.php">Trang chủ</a> / <a href="?page_layout=danhsachnguoidung">Người dùng</a> / Thêm mới
                </div>
            </div>
            <div class="header-actions">
                <a href="?page_layout=danhsachnguoidung" class="btn btn-ghost">
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

            <form method="POST" id="userForm">
                <input type="hidden" name="add_user" value="1">
                
                <div class="form-grid" style="grid-template-columns: 1fr 1fr; max-width: 900px;">
                    <!-- Thông tin tài khoản -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class='bx bx-user'></i> Thông tin tài khoản
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Tên đăng nhập</label>
                            <input type="text" name="username" class="form-input" 
                                   placeholder="VD: johndoe" 
                                   value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Email</label>
                            <input type="email" name="email" class="form-input" 
                                   placeholder="VD: john@example.com" 
                                   value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tên hiển thị</label>
                            <input type="text" name="display_name" class="form-input" 
                                   placeholder="VD: John Doe" 
                                   value="<?= isset($display_name) ? htmlspecialchars($display_name) : '' ?>">
                            <div class="form-helper">
                                <i class='bx bx-info-circle'></i> Để trống sẽ dùng tên đăng nhập
                            </div>
                        </div>
                    </div>

                    <!-- Bảo mật & Quyền -->
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
                            <label class="form-label required">Xác nhận mật khẩu</label>
                            <input type="password" name="confirm_password" class="form-input" 
                                   placeholder="Nhập lại mật khẩu" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Vai trò</label>
                            <select name="role" class="form-select">
                                <option value="user" <?= (isset($role) && $role == 'user') ? 'selected' : '' ?>>👤 Người dùng</option>
                                <option value="editor" <?= (isset($role) && $role == 'editor') ? 'selected' : '' ?>>✏️ Biên tập viên</option>
                                <option value="admin" <?= (isset($role) && $role == 'admin') ? 'selected' : '' ?>>🛡️ Quản trị viên</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-divider"></div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-success">
                        <i class='bx bx-check'></i> Thêm người dùng
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class='bx bx-reset'></i> Đặt lại
                    </button>
                    <a href="?page_layout=danhsachnguoidung" class="btn btn-ghost">
                        <i class='bx bx-x'></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
