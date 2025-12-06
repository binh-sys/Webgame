<?php
session_start();
require_once 'ketnoi.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Cấu hình upload
$db_display_path = 'img/';
$server_upload_dir = __DIR__ . '/img/';

if (!is_dir($server_upload_dir)) {
    mkdir($server_upload_dir, 0777, true);
}

// Xử lý cập nhật hồ sơ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_display_name = trim($_POST['display_name']);
    $new_description = trim($_POST['description'] ?? '');
    $new_avatar_file = null;

    // Upload avatar
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['avatar']['tmp_name'];
        $file_name = $_FILES['avatar']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_ext, $allowed_ext)) {
            $unique_filename = md5(time() . $file_name) . '.' . $file_ext;
            $server_target_path = $server_upload_dir . $unique_filename;

            if (move_uploaded_file($file_tmp_name, $server_target_path)) {
                $new_avatar_file = $unique_filename;
                
                // Xóa avatar cũ
                $old_avatar = $_SESSION['avatar'] ?? '';
                if (!empty($old_avatar) && $old_avatar !== 'default-avatar.png') {
                    $old_path = $server_upload_dir . $old_avatar;
                    if (file_exists($old_path)) @unlink($old_path);
                }
            } else {
                $message = 'Lỗi khi tải lên ảnh!';
                $message_type = 'error';
            }
        } else {
            $message = 'Chỉ cho phép JPG, PNG, GIF, WEBP!';
            $message_type = 'error';
        }
    }

    // Cập nhật database
    if (empty($message)) {
        if ($new_avatar_file) {
            $stmt = $conn->prepare("UPDATE users SET display_name = ?, description = ?, avatar = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $new_display_name, $new_description, $new_avatar_file, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET display_name = ?, description = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $new_display_name, $new_description, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['display_name'] = $new_display_name;
            if ($new_avatar_file) $_SESSION['avatar'] = $new_avatar_file;
            $message = 'Cập nhật hồ sơ thành công!';
            $message_type = 'success';
        } else {
            $message = 'Lỗi khi cập nhật!';
            $message_type = 'error';
        }
        $stmt->close();
    }
}

// Lấy thông tin user
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$avatar = !empty($user['avatar']) ? $db_display_path . $user['avatar'] : 'img/default-avatar.png';

// Thống kê user
$stats = [
    'comments' => 0,
    'likes_received' => 0,
    'articles' => 0
];

$stats['comments'] = $conn->query("SELECT COUNT(*) as c FROM comments WHERE user_id = $user_id")->fetch_assoc()['c'] ?? 0;

if ($user['role'] === 'editor' || $user['role'] === 'admin') {
    $stats['articles'] = $conn->query("SELECT COUNT(*) as c FROM articles WHERE author_id = $user_id")->fetch_assoc()['c'] ?? 0;
}

// Hoạt động gần đây
$recent_comments = $conn->query("SELECT c.*, a.title AS article_title, a.slug 
    FROM comments c 
    LEFT JOIN articles a ON c.article_id = a.article_id 
    WHERE c.user_id = $user_id 
    ORDER BY c.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<style>
    /* ===== PROFILE PAGE STYLES ===== */
    .profile-page {
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
        min-height: 100vh;
        padding: 40px 0 80px;
        position: relative;
    }

    .profile-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background:
            radial-gradient(circle at 20% 30%, rgba(59, 130, 246, 0.08) 0%, transparent 40%),
            radial-gradient(circle at 80% 70%, rgba(37, 99, 235, 0.06) 0%, transparent 40%);
        pointer-events: none;
    }

    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        position: relative;
        z-index: 10;
    }

    /* Profile Layout */
    .profile-layout {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 40px;
    }

    @media (max-width: 1000px) {
        .profile-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Profile Card */
    .profile-card {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
        text-align: center;
    }

    .profile-cover {
        height: 120px;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        position: relative;
    }

    .profile-avatar-wrapper {
        position: relative;
        margin-top: -60px;
        margin-bottom: 20px;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid rgba(20, 20, 35, 0.98);
        background: #333;
    }

    .avatar-edit-btn {
        position: absolute;
        bottom: 5px;
        right: calc(50% - 60px);
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        border: 3px solid rgba(20, 20, 35, 0.98);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .avatar-edit-btn:hover {
        transform: scale(1.1);
    }

    .profile-info {
        padding: 0 30px 30px;
    }

    .profile-name {
        color: #fff;
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .profile-role {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 15px;
    }

    .profile-role.admin { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
    .profile-role.editor { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
    .profile-role.user { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }

    .profile-email {
        color: #888;
        font-size: 14px;
        margin-bottom: 20px;
    }

    .profile-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        padding: 20px 0;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        margin-bottom: 20px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        color: #3b82f6;
        font-size: 24px;
        font-weight: 700;
        display: block;
    }

    .stat-label {
        color: #888;
        font-size: 12px;
        text-transform: uppercase;
    }

    .profile-joined {
        color: #666;
        font-size: 13px;
    }

    .profile-joined i {
        color: #3b82f6;
        margin-right: 5px;
    }

    /* Main Content */
    .profile-main {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .profile-section {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
    }

    .section-header {
        padding: 20px 25px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-header i {
        color: #3b82f6;
        font-size: 18px;
    }

    .section-header h3 {
        color: #fff;
        font-size: 18px;
        font-weight: 600;
        margin: 0;
    }

    .section-body {
        padding: 25px;
    }

    /* Edit Form */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        color: #aaa;
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .form-input {
        width: 100%;
        padding: 15px 20px;
        background: rgba(0, 0, 0, 0.3);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        color: #fff;
        font-size: 15px;
        outline: none;
        transition: all 0.3s;
    }

    .form-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.15);
    }

    .form-input:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .form-input::placeholder {
        color: #555;
    }

    .form-textarea {
        min-height: 120px;
        resize: vertical;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 600px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .form-submit {
        padding: 16px 30px;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        border: none;
        border-radius: 12px;
        color: #fff;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
    }

    /* Avatar Upload */
    .avatar-upload-area {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px;
        background: rgba(59, 130, 246, 0.1);
        border: 2px dashed rgba(59, 130, 246, 0.3);
        border-radius: 12px;
        margin-bottom: 25px;
    }

    .avatar-preview-small {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #3b82f6;
    }

    .avatar-upload-info {
        flex: 1;
    }

    .avatar-upload-info h4 {
        color: #fff;
        font-size: 16px;
        margin-bottom: 5px;
    }

    .avatar-upload-info p {
        color: #888;
        font-size: 13px;
        margin-bottom: 10px;
    }

    .avatar-upload-btn {
        padding: 10px 20px;
        background: rgba(59, 130, 246, 0.2);
        border: 1px solid rgba(59, 130, 246, 0.3);
        border-radius: 8px;
        color: #3b82f6;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }

    .avatar-upload-btn:hover {
        background: rgba(59, 130, 246, 0.3);
    }

    .avatar-input {
        display: none;
    }

    /* Recent Activity */
    .activity-item {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        background: rgba(59, 130, 246, 0.15);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #3b82f6;
        font-size: 16px;
        flex-shrink: 0;
    }

    .activity-content {
        flex: 1;
    }

    .activity-text {
        color: #ccc;
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 5px;
    }

    .activity-text a {
        color: #3b82f6;
        text-decoration: none;
    }

    .activity-text a:hover {
        text-decoration: underline;
    }

    .activity-time {
        color: #666;
        font-size: 12px;
    }

    /* Alert */
    .alert {
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.15);
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: #34d399;
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.15);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #f87171;
    }

    .alert i {
        font-size: 20px;
    }

    /* Quick Links */
    .quick-links {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .quick-link {
        padding: 12px 20px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        color: #888;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .quick-link:hover {
        background: rgba(59, 130, 246, 0.1);
        border-color: rgba(59, 130, 246, 0.3);
        color: #3b82f6;
    }

    .quick-link i {
        font-size: 16px;
    }

    /* Empty Activity */
    .empty-activity {
        text-align: center;
        padding: 40px 20px;
        color: #666;
    }

    .empty-activity i {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .empty-activity p {
        font-size: 15px;
    }

    /* Password Change Section */
    .password-section {
        margin-top: 30px;
        padding-top: 30px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    .password-section h4 {
        color: #fff;
        font-size: 16px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .password-section h4 i {
        color: #f59e0b;
    }

    /* Danger Zone */
    .danger-zone {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: 12px;
        padding: 20px;
        margin-top: 30px;
    }

    .danger-zone h4 {
        color: #ef4444;
        font-size: 16px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .danger-zone p {
        color: #888;
        font-size: 14px;
        margin-bottom: 15px;
    }

    .btn-danger {
        padding: 12px 20px;
        background: transparent;
        border: 1px solid rgba(239, 68, 68, 0.5);
        border-radius: 8px;
        color: #ef4444;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-danger:hover {
        background: rgba(239, 68, 68, 0.2);
    }

    /* Badges Section */
    .badges-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 15px;
    }

    .badge-item {
        text-align: center;
        padding: 15px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 12px;
        transition: all 0.3s;
    }

    .badge-item:hover {
        background: rgba(59, 130, 246, 0.1);
        transform: translateY(-3px);
    }

    .badge-icon {
        width: 50px;
        height: 50px;
        margin: 0 auto 10px;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #fff;
    }

    .badge-icon.gold { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .badge-icon.silver { background: linear-gradient(135deg, #9ca3af, #6b7280); }
    .badge-icon.bronze { background: linear-gradient(135deg, #b45309, #92400e); }

    .badge-name {
        color: #ccc;
        font-size: 12px;
        font-weight: 500;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .profile-stats {
            grid-template-columns: repeat(3, 1fr);
        }
        
        .profile-page {
            padding: 20px 0 60px;
        }
        
        .profile-layout {
            gap: 25px;
        }
        
        .avatar-upload-area {
            flex-direction: column;
            text-align: center;
        }
        
        .section-body {
            padding: 20px;
        }
    }

    @media (max-width: 480px) {
        .profile-name {
            font-size: 20px;
        }
        
        .stat-number {
            font-size: 20px;
        }
        
        .quick-links {
            flex-direction: column;
        }
        
        .quick-link {
            justify-content: center;
        }
    }
</style>

<div class="profile-page">
    <div class="profile-container">
        <div class="profile-layout">
            <!-- Sidebar - Profile Card -->
            <div class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-cover"></div>
                    <div class="profile-avatar-wrapper">
                        <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="profile-avatar" id="avatarDisplay">
                        <label for="avatarInput" class="avatar-edit-btn">
                            <i class="fas fa-camera"></i>
                        </label>
                    </div>
                    <div class="profile-info">
                        <h2 class="profile-name"><?= htmlspecialchars($user['display_name']) ?></h2>
                        <span class="profile-role <?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
                        <p class="profile-email"><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                        
                        <div class="profile-stats">
                            <div class="stat-item">
                                <span class="stat-number"><?= $stats['comments'] ?></span>
                                <span class="stat-label">Bình luận</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?= $stats['articles'] ?></span>
                                <span class="stat-label">Bài viết</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?= $stats['likes_received'] ?></span>
                                <span class="stat-label">Lượt thích</span>
                            </div>
                        </div>
                        
                        <p class="profile-joined">
                            <i class="fas fa-calendar-alt"></i>
                            Tham gia: <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                        </p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="profile-section" style="margin-top: 20px;">
                    <div class="section-header">
                        <i class="fas fa-link"></i>
                        <h3>Liên kết nhanh</h3>
                    </div>
                    <div class="section-body">
                        <div class="quick-links">
                            <?php if ($user['role'] === 'editor' || $user['role'] === 'admin'): ?>
                            <a href="new-article.php" class="quick-link">
                                <i class="fas fa-pen"></i> Viết bài mới
                            </a>
                            <a href="author-history.php" class="quick-link">
                                <i class="fas fa-history"></i> Bài đã đăng
                            </a>
                            <?php endif; ?>
                            <a href="index.php" class="quick-link">
                                <i class="fas fa-home"></i> Trang chủ
                            </a>
                            <a href="community.php" class="quick-link">
                                <i class="fas fa-users"></i> Cộng đồng
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="profile-main">
                <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>">
                    <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= $message ?>
                </div>
                <?php endif; ?>

                <!-- Edit Profile Form -->
                <div class="profile-section">
                    <div class="section-header">
                        <i class="fas fa-user-edit"></i>
                        <h3>Chỉnh sửa hồ sơ</h3>
                    </div>
                    <div class="section-body">
                        <form method="POST" enctype="multipart/form-data" id="profileForm">
                            <!-- Avatar Upload -->
                            <div class="avatar-upload-area">
                                <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="avatar-preview-small" id="avatarPreview">
                                <div class="avatar-upload-info">
                                    <h4>Ảnh đại diện</h4>
                                    <p>JPG, PNG, GIF hoặc WEBP. Tối đa 2MB.</p>
                                    <label for="avatarInput" class="avatar-upload-btn">
                                        <i class="fas fa-upload"></i> Chọn ảnh
                                    </label>
                                    <input type="file" name="avatar" id="avatarInput" class="avatar-input" accept="image/*">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Tên hiển thị</label>
                                    <input type="text" name="display_name" class="form-input" 
                                           value="<?= htmlspecialchars($user['display_name']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-input" 
                                           value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Tên đăng nhập</label>
                                    <input type="text" class="form-input" 
                                           value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label>Vai trò</label>
                                    <input type="text" class="form-input" 
                                           value="<?= ucfirst($user['role']) ?>" disabled>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Giới thiệu bản thân</label>
                                <textarea name="description" class="form-input form-textarea" 
                                          placeholder="Viết vài dòng về bản thân..."><?= htmlspecialchars($user['description'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" class="form-submit">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="profile-section">
                    <div class="section-header">
                        <i class="fas fa-clock"></i>
                        <h3>Hoạt động gần đây</h3>
                    </div>
                    <div class="section-body">
                        <?php if (empty($recent_comments)): ?>
                        <div class="empty-activity">
                            <i class="fas fa-comment-slash"></i>
                            <p>Chưa có hoạt động nào</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($recent_comments as $comment): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-comment"></i>
                            </div>
                            <div class="activity-content">
                                <p class="activity-text">
                                    Bạn đã bình luận trong bài viết 
                                    <a href="article.php?slug=<?= htmlspecialchars($comment['slug'] ?? '') ?>">
                                        <?= htmlspecialchars($comment['article_title'] ?? 'Bài viết') ?>
                                    </a>
                                </p>
                                <span class="activity-time">
                                    <i class="far fa-clock"></i> 
                                    <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview avatar before upload
document.getElementById('avatarInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ảnh không được vượt quá 2MB!');
            this.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
            document.getElementById('avatarDisplay').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Form validation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const displayName = this.querySelector('input[name="display_name"]').value.trim();
    
    if (displayName.length < 2) {
        e.preventDefault();
        alert('Tên hiển thị phải có ít nhất 2 ký tự!');
        return;
    }
    
    if (displayName.length > 50) {
        e.preventDefault();
        alert('Tên hiển thị không được vượt quá 50 ký tự!');
        return;
    }
});
</script>

<?php include 'footer.php'; ?>
