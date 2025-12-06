<?php
require_once 'ketnoi.php';

// Chỉ cho tác giả/editor xem
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'editor') {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Lấy filter từ URL
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$where = "WHERE a.author_id = $user_id";
if ($filter_status !== 'all') {
    $filter_status_safe = mysqli_real_escape_string($conn, $filter_status);
    $where .= " AND a.status = '$filter_status_safe'";
}
if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $where .= " AND a.title LIKE '%$search_safe%'";
}

// Lấy thống kê
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    SUM(views) as total_views
    FROM articles WHERE author_id = $user_id";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_sql));

// Lấy danh sách bài viết
$sql = "SELECT a.article_id, a.title, a.slug, a.status, a.created_at, a.views, a.featured_image,
        c.name AS category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.category_id
        $where
        ORDER BY a.created_at DESC";
$result = mysqli_query($conn, $sql);

// Xử lý thông báo
$message = '';
$message_type = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'deleted':
            $message = 'Đã xóa bài viết thành công!';
            $message_type = 'success';
            break;
        case 'updated':
            $message = 'Đã cập nhật bài viết thành công!';
            $message_type = 'success';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid':
            $message = 'ID bài viết không hợp lệ!';
            $message_type = 'error';
            break;
        case 'notfound':
            $message = 'Không tìm thấy bài viết!';
            $message_type = 'error';
            break;
        case 'permission':
            $message = 'Bạn không có quyền thực hiện hành động này!';
            $message_type = 'error';
            break;
        case 'deletefailed':
            $message = 'Lỗi khi xóa bài viết!';
            $message_type = 'error';
            break;
    }
}
?>
<?php include 'header.php'; ?>

<style>
    /* ===== HISTORY PAGE STYLES ===== */
    .history-page {
        min-height: calc(100vh - 200px);
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
        padding: 40px 0;
        position: relative;
    }

    .history-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background:
            radial-gradient(circle at 10% 20%, rgba(138, 43, 226, 0.08) 0%, transparent 40%),
            radial-gradient(circle at 90% 80%, rgba(255, 179, 0, 0.06) 0%, transparent 40%);
        pointer-events: none;
    }

    .history-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
        position: relative;
        z-index: 10;
    }

    /* Header */
    .history-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .history-title {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .history-title-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #8a2be2, #9b59b6);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 30px rgba(138, 43, 226, 0.3);
    }

    .history-title-icon i {
        font-size: 28px;
        color: #fff;
    }

    .history-title h1 {
        color: #fff;
        font-size: 28px;
        font-weight: 700;
        margin: 0;
    }

    .history-title p {
        color: #888;
        font-size: 14px;
        margin: 5px 0 0;
    }

    .btn-new-article {
        padding: 14px 28px;
        background: linear-gradient(135deg, #8a2be2, #9b59b6);
        border: none;
        border-radius: 12px;
        color: #fff;
        font-weight: 600;
        font-size: 15px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;
        box-shadow: 0 5px 20px rgba(138, 43, 226, 0.4);
    }

    .btn-new-article:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(138, 43, 226, 0.5);
        color: #fff;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: rgba(20, 20, 35, 0.95);
        border-radius: 16px;
        padding: 25px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        gap: 20px;
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .stat-icon.total {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
    }

    .stat-icon.published {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: #fff;
    }

    .stat-icon.pending {
        background: linear-gradient(135deg, #ffc107, #ff9800);
        color: #111;
    }

    .stat-icon.rejected {
        background: linear-gradient(135deg, #dc3545, #e74c3c);
        color: #fff;
    }

    .stat-icon.views {
        background: linear-gradient(135deg, #17a2b8, #00bcd4);
        color: #fff;
    }

    .stat-info h3 {
        color: #fff;
        font-size: 28px;
        font-weight: 700;
        margin: 0;
    }

    .stat-info p {
        color: #888;
        font-size: 14px;
        margin: 5px 0 0;
    }

    /* Filter & Search */
    .filter-bar {
        background: rgba(20, 20, 35, 0.95);
        border-radius: 16px;
        padding: 20px 25px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }

    .filter-tabs {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 10px 20px;
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid transparent;
        border-radius: 10px;
        color: #888;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-tab:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    .filter-tab.active {
        background: rgba(138, 43, 226, 0.2);
        border-color: #8a2be2;
        color: #8a2be2;
    }

    .filter-tab .count {
        background: rgba(255, 255, 255, 0.1);
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 12px;
    }

    .filter-tab.active .count {
        background: rgba(138, 43, 226, 0.3);
    }

    .search-box {
        position: relative;
        width: 300px;
    }

    .search-box input {
        width: 100%;
        padding: 12px 20px 12px 45px;
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        color: #fff;
        font-size: 14px;
        outline: none;
        transition: all 0.3s;
    }

    .search-box input:focus {
        border-color: #8a2be2;
        background: rgba(255, 255, 255, 0.08);
    }

    .search-box input::placeholder {
        color: #666;
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
    }

    /* Articles Grid */
    .articles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 25px;
    }

    .article-card {
        background: rgba(20, 20, 35, 0.95);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
        transition: all 0.3s;
    }

    .article-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        border-color: rgba(138, 43, 226, 0.3);
    }

    .article-thumbnail {
        width: 100%;
        height: 180px;
        background: linear-gradient(135deg, #1a1a2e, #16213e);
        position: relative;
        overflow: hidden;
    }

    .article-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .article-card:hover .article-thumbnail img {
        transform: scale(1.05);
    }

    .article-thumbnail .no-image {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #444;
        font-size: 50px;
    }

    .article-status {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .article-status.published {
        background: rgba(40, 167, 69, 0.9);
        color: #fff;
    }

    .article-status.pending {
        background: rgba(255, 193, 7, 0.9);
        color: #111;
    }

    .article-status.rejected {
        background: rgba(220, 53, 69, 0.9);
        color: #fff;
    }

    .article-content {
        padding: 25px;
    }

    .article-category {
        display: inline-block;
        padding: 4px 12px;
        background: rgba(138, 43, 226, 0.2);
        color: #8a2be2;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        margin-bottom: 12px;
    }

    .article-title {
        color: #fff;
        font-size: 18px;
        font-weight: 600;
        margin: 0 0 15px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .article-title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.3s;
    }

    .article-title a:hover {
        color: #8a2be2;
    }

    .article-meta {
        display: flex;
        align-items: center;
        gap: 20px;
        color: #666;
        font-size: 13px;
        margin-bottom: 20px;
    }

    .article-meta span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .article-meta i {
        color: #8a2be2;
    }

    .article-actions {
        display: flex;
        gap: 10px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    .btn-article {
        flex: 1;
        padding: 12px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        border: none;
    }

    .btn-view {
        background: rgba(138, 43, 226, 0.15);
        color: #8a2be2;
        border: 1px solid rgba(138, 43, 226, 0.3);
    }

    .btn-view:hover {
        background: rgba(138, 43, 226, 0.25);
        color: #a855f7;
    }

    .btn-edit {
        background: rgba(23, 162, 184, 0.15);
        color: #17a2b8;
        border: 1px solid rgba(23, 162, 184, 0.3);
    }

    .btn-edit:hover {
        background: rgba(23, 162, 184, 0.25);
        color: #20c997;
    }

    .btn-delete {
        background: rgba(220, 53, 69, 0.15);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .btn-delete:hover {
        background: rgba(220, 53, 69, 0.25);
        color: #e74c3c;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: rgba(20, 20, 35, 0.95);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .empty-state i {
        font-size: 80px;
        color: #333;
        margin-bottom: 25px;
    }

    .empty-state h3 {
        color: #fff;
        font-size: 24px;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #666;
        font-size: 16px;
        margin-bottom: 25px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .history-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .filter-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            width: 100%;
        }

        .articles-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<div class="history-page">
    <div class="history-container">
        <!-- Header -->
        <div class="history-header">
            <div class="history-title">
                <div class="history-title-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div>
                    <h1>Bài viết của tôi</h1>
                    <p>Quản lý và theo dõi tất cả bài viết đã đăng</p>
                </div>
            </div>
            <a href="new-article.php" class="btn-new-article">
                <i class="fas fa-plus"></i> Viết bài mới
            </a>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?= intval($stats['total']) ?></h3>
                    <p>Tổng bài viết</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon published">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= intval($stats['published']) ?></h3>
                    <p>Đã xuất bản</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?= intval($stats['pending']) ?></h3>
                    <p>Chờ duyệt</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon rejected">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= intval($stats['rejected']) ?></h3>
                    <p>Bị từ chối</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon views">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format(intval($stats['total_views'])) ?></h3>
                    <p>Tổng lượt xem</p>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-tabs">
                <a href="?status=all" class="filter-tab <?= $filter_status === 'all' ? 'active' : '' ?>">
                    <i class="fas fa-list"></i> Tất cả
                    <span class="count"><?= intval($stats['total']) ?></span>
                </a>
                <a href="?status=published" class="filter-tab <?= $filter_status === 'published' ? 'active' : '' ?>">
                    <i class="fas fa-check"></i> Đã duyệt
                    <span class="count"><?= intval($stats['published']) ?></span>
                </a>
                <a href="?status=pending" class="filter-tab <?= $filter_status === 'pending' ? 'active' : '' ?>">
                    <i class="fas fa-hourglass-half"></i> Chờ duyệt
                    <span class="count"><?= intval($stats['pending']) ?></span>
                </a>
                <a href="?status=rejected" class="filter-tab <?= $filter_status === 'rejected' ? 'active' : '' ?>">
                    <i class="fas fa-ban"></i> Từ chối
                    <span class="count"><?= intval($stats['rejected']) ?></span>
                </a>
            </div>
            <form class="search-box" method="GET">
                <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Tìm kiếm bài viết..." value="<?= htmlspecialchars($search) ?>">
            </form>
        </div>

        <!-- Articles Grid -->
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="articles-grid">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="article-card">
                        <div class="article-thumbnail">
                            <?php if (!empty($row['featured_image'])): ?>
                                <img src="../<?= htmlspecialchars($row['featured_image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            <span class="article-status <?= $row['status'] ?>">
                                <?php
                                switch ($row['status']) {
                                    case 'published':
                                        echo 'Đã duyệt';
                                        break;
                                    case 'pending':
                                        echo 'Chờ duyệt';
                                        break;
                                    case 'rejected':
                                        echo 'Từ chối';
                                        break;
                                    default:
                                        echo $row['status'];
                                }
                                ?>
                            </span>
                        </div>
                        <div class="article-content">
                            <?php if ($row['category_name']): ?>
                                <span class="article-category"><?= htmlspecialchars($row['category_name']) ?></span>
                            <?php endif; ?>
                            <h3 class="article-title">
                                <a href="article.php?slug=<?= htmlspecialchars($row['slug']) ?>">
                                    <?= htmlspecialchars($row['title']) ?>
                                </a>
                            </h3>
                            <div class="article-meta">
                                <span><i class="fas fa-calendar"></i> <?= date("d/m/Y", strtotime($row['created_at'])) ?></span>
                                <span><i class="fas fa-eye"></i> <?= number_format($row['views']) ?> lượt xem</span>
                            </div>
                            <div class="article-actions">
                                <a href="article.php?slug=<?= htmlspecialchars($row['slug']) ?>" class="btn-article btn-view">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                                <a href="edit-article.php?id=<?= $row['article_id'] ?>" class="btn-article btn-edit">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <button class="btn-article btn-delete" onclick="confirmDelete(<?= $row['article_id'] ?>)">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>Chưa có bài viết nào</h3>
                <p>Bắt đầu viết bài đầu tiên của bạn ngay!</p>
                <a href="new-article.php" class="btn-new-article">
                    <i class="fas fa-pen"></i> Viết bài ngay
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3>Xác nhận xóa</h3>
        <p>Bạn có chắc chắn muốn xóa bài viết này?<br>Hành động này không thể hoàn tác!</p>
        <div class="modal-actions">
            <button class="btn-modal btn-cancel" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i> Hủy
            </button>
            <a href="#" class="btn-modal btn-confirm" id="confirmDeleteBtn">
                <i class="fas fa-trash"></i> Xóa
            </a>
        </div>
    </div>
</div>

<style>
    /* Toast */
    .toast-container {
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 9999;
    }

    .toast {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 25px;
        border-radius: 14px;
        color: #fff;
        font-size: 15px;
        font-weight: 500;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
        transform: translateX(120%);
        transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        margin-bottom: 10px;
    }

    .toast.show {
        transform: translateX(0);
    }

    .toast.success {
        background: linear-gradient(135deg, #28a745, #20c997);
    }

    .toast.error {
        background: linear-gradient(135deg, #dc3545, #e74c3c);
    }

    .toast i {
        font-size: 22px;
    }

    /* Modal */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        backdrop-filter: blur(5px);
    }

    .modal-overlay.show {
        display: flex;
    }

    .modal-box {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 24px;
        padding: 40px;
        max-width: 420px;
        width: 90%;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
        animation: modalSlide 0.3s ease;
    }

    @keyframes modalSlide {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .modal-icon {
        width: 80px;
        height: 80px;
        background: rgba(220, 53, 69, 0.15);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
    }

    .modal-icon i {
        font-size: 36px;
        color: #dc3545;
    }

    .modal-box h3 {
        color: #fff;
        font-size: 24px;
        margin-bottom: 15px;
    }

    .modal-box p {
        color: #888;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 30px;
    }

    .modal-actions {
        display: flex;
        gap: 15px;
    }

    .btn-modal {
        flex: 1;
        padding: 14px 20px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        border: none;
    }

    .btn-cancel {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .btn-cancel:hover {
        background: rgba(255, 255, 255, 0.15);
    }

    .btn-confirm {
        background: linear-gradient(135deg, #dc3545, #e74c3c);
        color: #fff;
    }

    .btn-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
        color: #fff;
    }
</style>

<script>
    // Toast notification
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        container.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 4000);
    }

    // Delete modal
    function confirmDelete(articleId) {
        const modal = document.getElementById('deleteModal');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        confirmBtn.href = 'delete-article.php?id=' + articleId;
        modal.classList.add('show');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('show');
    }

    // Close modal on overlay click
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });

    // Show toast on page load if there's a message
    <?php if (!empty($message)): ?>
        showToast('<?= addslashes($message) ?>', '<?= $message_type ?>');
    <?php endif; ?>
</script>

<?php include 'footer.php'; ?>
