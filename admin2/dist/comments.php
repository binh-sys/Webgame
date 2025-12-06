<?php
require_once('ketnoi.php');

// Tự động thêm cột status nếu chưa có
$check_column = mysqli_query($ketnoi, "SHOW COLUMNS FROM comments LIKE 'status'");
if (mysqli_num_rows($check_column) == 0) {
    mysqli_query($ketnoi, "ALTER TABLE comments ADD COLUMN status ENUM('pending', 'approved', 'hidden') DEFAULT 'approved' AFTER content");
    mysqli_query($ketnoi, "UPDATE comments SET status = 'approved' WHERE status IS NULL");
}

// Danh sách từ nhạy cảm
$sensitive_words = ['đm', 'vcl', 'vl', 'cc', 'clm', 'đéo', 'địt', 'lồn', 'buồi', 'cặc', 'đĩ', 'cave', 'ngu', 'óc chó', 'thằng chó', 'con chó', 'mẹ mày', 'bố mày', 'fuck', 'shit', 'damn', 'bitch', 'asshole'];

function contains_sensitive_words($content, $words) {
    $content_lower = mb_strtolower($content, 'UTF-8');
    foreach ($words as $word) {
        if (mb_strpos($content_lower, mb_strtolower($word, 'UTF-8')) !== false) {
            return true;
        }
    }
    return false;
}

// Xử lý AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['ajax_action'];
    $comment_id = intval($_POST['comment_id'] ?? 0);
    
    if ($comment_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        exit;
    }
    
    switch ($action) {
        case 'approve':
            $stmt = mysqli_prepare($ketnoi, "UPDATE comments SET status = 'approved' WHERE comment_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $comment_id);
            $result = mysqli_stmt_execute($stmt);
            echo json_encode(['success' => $result, 'message' => $result ? 'Đã phê duyệt bình luận' : 'Lỗi']);
            break;
        case 'hide':
            $stmt = mysqli_prepare($ketnoi, "UPDATE comments SET status = 'hidden' WHERE comment_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $comment_id);
            $result = mysqli_stmt_execute($stmt);
            echo json_encode(['success' => $result, 'message' => $result ? 'Đã ẩn bình luận' : 'Lỗi']);
            break;
        case 'pending':
            $stmt = mysqli_prepare($ketnoi, "UPDATE comments SET status = 'pending' WHERE comment_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $comment_id);
            $result = mysqli_stmt_execute($stmt);
            echo json_encode(['success' => $result, 'message' => $result ? 'Đã chuyển về chờ duyệt' : 'Lỗi']);
            break;
        case 'delete':
            $stmt = mysqli_prepare($ketnoi, "DELETE FROM comments WHERE comment_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $comment_id);
            $result = mysqli_stmt_execute($stmt);
            echo json_encode(['success' => $result, 'message' => $result ? 'Đã xóa bình luận' : 'Lỗi']);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
    }
    exit;
}

// Lọc theo trạng thái
$status_filter = $_GET['status'] ?? 'all';
$where_clause = "";
if ($status_filter !== 'all') {
    $status_filter = mysqli_real_escape_string($ketnoi, $status_filter);
    $where_clause = "WHERE c.status = '$status_filter'";
}

// Lấy bình luận
$sql = "SELECT c.comment_id, c.content, c.created_at, c.status,
        a.title AS article_title, a.article_id,
        u.display_name AS user_name, u.user_id
        FROM comments c
        LEFT JOIN articles a ON c.article_id = a.article_id
        LEFT JOIN users u ON c.user_id = u.user_id
        $where_clause
        ORDER BY c.created_at DESC";
$query = mysqli_query($ketnoi, $sql);

// Đếm số lượng
$count_all = mysqli_fetch_assoc(mysqli_query($ketnoi, "SELECT COUNT(*) as c FROM comments"))['c'];
$count_pending = mysqli_fetch_assoc(mysqli_query($ketnoi, "SELECT COUNT(*) as c FROM comments WHERE status = 'pending'"))['c'];
$count_approved = mysqli_fetch_assoc(mysqli_query($ketnoi, "SELECT COUNT(*) as c FROM comments WHERE status = 'approved'"))['c'];
$count_hidden = mysqli_fetch_assoc(mysqli_query($ketnoi, "SELECT COUNT(*) as c FROM comments WHERE status = 'hidden'"))['c'];
?>

<link rel="stylesheet" href="assets/css/admin-forms.css">

<style>
/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: linear-gradient(145deg, var(--bg-card), #080c12);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: var(--transition-normal);
    text-decoration: none;
    cursor: pointer;
}

.stat-card:hover, .stat-card.active {
    border-color: var(--border-hover);
    transform: translateY(-2px);
}

.stat-card.active {
    box-shadow: 0 0 20px var(--primary-glow);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stat-icon.blue { background: rgba(0, 212, 255, 0.15); color: var(--primary); }
.stat-icon.yellow { background: rgba(255, 213, 0, 0.15); color: var(--accent-yellow); }
.stat-icon.green { background: rgba(0, 255, 136, 0.15); color: var(--accent-green); }
.stat-icon.red { background: rgba(255, 71, 87, 0.15); color: var(--accent-red); }

.stat-info h3 {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}

.stat-info p {
    font-size: 13px;
    color: var(--text-muted);
    margin: 4px 0 0;
}

/* Table Card */
.table-card {
    background: linear-gradient(145deg, var(--bg-card), #080c12);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.table-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-title {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--primary);
    font-size: 16px;
    font-weight: 600;
}

/* Data Table */
.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: rgba(0, 212, 255, 0.05);
    color: var(--primary);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 16px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.data-table td {
    padding: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    color: var(--text-primary);
    font-size: 14px;
    vertical-align: middle;
}

.data-table tbody tr {
    transition: var(--transition-fast);
}

.data-table tbody tr:hover {
    background: rgba(0, 212, 255, 0.03);
}

.data-table tbody tr.sensitive {
    background: rgba(255, 71, 87, 0.05);
    border-left: 3px solid var(--accent-red);
}

/* Comment Content */
.comment-content {
    max-width: 300px;
    max-height: 60px;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.5;
}

.sensitive-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 8px;
    padding: 4px 8px;
    background: rgba(255, 71, 87, 0.15);
    border: 1px solid rgba(255, 71, 87, 0.3);
    border-radius: 12px;
    color: var(--accent-red);
    font-size: 11px;
    font-weight: 600;
}

/* Article Link */
.article-link {
    color: var(--primary);
    text-decoration: none;
    max-width: 200px;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.article-link:hover {
    text-decoration: underline;
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.pending {
    background: rgba(255, 213, 0, 0.15);
    color: var(--accent-yellow);
    border: 1px solid rgba(255, 213, 0, 0.3);
}

.status-badge.approved {
    background: rgba(0, 255, 136, 0.15);
    color: var(--accent-green);
    border: 1px solid rgba(0, 255, 136, 0.3);
}

.status-badge.hidden {
    background: rgba(255, 71, 87, 0.15);
    color: var(--accent-red);
    border: 1px solid rgba(255, 71, 87, 0.3);
}

/* Action Buttons */
.action-btns {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border-color);
    background: transparent;
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition-fast);
}

.action-btn:hover {
    transform: scale(1.1);
}

.action-btn.approve:hover {
    border-color: var(--accent-green);
    color: var(--accent-green);
    background: rgba(0, 255, 136, 0.1);
}

.action-btn.hide:hover {
    border-color: var(--accent-orange);
    color: var(--accent-orange);
    background: rgba(255, 149, 0, 0.1);
}

.action-btn.pending:hover {
    border-color: var(--accent-yellow);
    color: var(--accent-yellow);
    background: rgba(255, 213, 0, 0.1);
}

.action-btn.delete:hover {
    border-color: var(--accent-red);
    color: var(--accent-red);
    background: rgba(255, 71, 87, 0.1);
}

/* Toast */
.toast {
    position: fixed;
    bottom: 30px;
    right: 30px;
    padding: 16px 24px;
    border-radius: var(--radius-md);
    color: #fff;
    font-weight: 600;
    z-index: 9999;
    animation: slideIn 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

.toast.success {
    background: linear-gradient(135deg, var(--accent-green), #00cc6a);
    color: #000;
}

.toast.error {
    background: linear-gradient(135deg, var(--accent-red), #cc3344);
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}
</style>

<div class="admin-form-container">
    <!-- Stats -->
    <div class="stats-grid">
        <a href="?page_layout=danhsachbinhluan&status=all" class="stat-card <?= $status_filter === 'all' ? 'active' : '' ?>">
            <div class="stat-icon blue"><i class='bx bx-message-square-dots'></i></div>
            <div class="stat-info">
                <h3><?= $count_all ?></h3>
                <p>Tất cả</p>
            </div>
        </a>
        <a href="?page_layout=danhsachbinhluan&status=pending" class="stat-card <?= $status_filter === 'pending' ? 'active' : '' ?>">
            <div class="stat-icon yellow"><i class='bx bx-time-five'></i></div>
            <div class="stat-info">
                <h3><?= $count_pending ?></h3>
                <p>Chờ duyệt</p>
            </div>
        </a>
        <a href="?page_layout=danhsachbinhluan&status=approved" class="stat-card <?= $status_filter === 'approved' ? 'active' : '' ?>">
            <div class="stat-icon green"><i class='bx bx-check-circle'></i></div>
            <div class="stat-info">
                <h3><?= $count_approved ?></h3>
                <p>Đã duyệt</p>
            </div>
        </a>
        <a href="?page_layout=danhsachbinhluan&status=hidden" class="stat-card <?= $status_filter === 'hidden' ? 'active' : '' ?>">
            <div class="stat-icon red"><i class='bx bx-hide'></i></div>
            <div class="stat-info">
                <h3><?= $count_hidden ?></h3>
                <p>Đã ẩn</p>
            </div>
        </a>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <i class='bx bx-message-dots'></i>
                <span>Quản lý bình luận</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Bài viết</th>
                        <th>Người bình luận</th>
                        <th>Nội dung</th>
                        <th>Trạng thái</th>
                        <th>Ngày</th>
                        <th style="width:150px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($query)): 
                            $has_sensitive = contains_sensitive_words($row['content'], $sensitive_words);
                            $status = $row['status'] ?? 'approved';
                        ?>
                        <tr data-id="<?= $row['comment_id'] ?>" class="<?= $has_sensitive ? 'sensitive' : '' ?>">
                            <td><strong><?= $i++ ?></strong></td>
                            <td>
                                <a href="../../game2/gamebat/article.php?id=<?= $row['article_id'] ?>" target="_blank" class="article-link">
                                    <?= htmlspecialchars($row['article_title'] ?? 'N/A') ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($row['user_name'] ?? 'Ẩn danh') ?></td>
                            <td>
                                <div class="comment-content"><?= nl2br(htmlspecialchars($row['content'])) ?></div>
                                <?php if ($has_sensitive): ?>
                                    <span class="sensitive-badge"><i class='bx bx-error'></i> Ngôn từ nhạy cảm</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge <?= $status ?>">
                                    <?php 
                                    switch($status) {
                                        case 'pending': echo '<i class="bx bx-time"></i> Chờ duyệt'; break;
                                        case 'approved': echo '<i class="bx bx-check"></i> Đã duyệt'; break;
                                        case 'hidden': echo '<i class="bx bx-hide"></i> Đã ẩn'; break;
                                    }
                                    ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                            <td>
                                <div class="action-btns">
                                    <?php if ($status !== 'approved'): ?>
                                    <button class="action-btn approve" onclick="commentAction(<?= $row['comment_id'] ?>, 'approve')" title="Phê duyệt">
                                        <i class='bx bx-check'></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($status !== 'hidden'): ?>
                                    <button class="action-btn hide" onclick="commentAction(<?= $row['comment_id'] ?>, 'hide')" title="Ẩn">
                                        <i class='bx bx-hide'></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($status === 'hidden'): ?>
                                    <button class="action-btn pending" onclick="commentAction(<?= $row['comment_id'] ?>, 'pending')" title="Chờ duyệt lại">
                                        <i class='bx bx-revision'></i>
                                    </button>
                                    <?php endif; ?>
                                    <button class="action-btn delete" onclick="commentAction(<?= $row['comment_id'] ?>, 'delete')" title="Xóa">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class='bx bx-message-dots'></i>
                                    <p>Không có bình luận nào</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function commentAction(commentId, action) {
    const messages = {
        'approve': 'Phê duyệt bình luận này?',
        'hide': 'Ẩn bình luận này?',
        'pending': 'Chuyển về chờ duyệt?',
        'delete': 'Xóa vĩnh viễn bình luận này?'
    };
    
    if (!confirm(messages[action])) return;
    
    fetch('comments.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `ajax_action=${action}&comment_id=${commentId}`
    })
    .then(res => res.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            if (action === 'delete') {
                document.querySelector(`tr[data-id="${commentId}"]`).remove();
            } else {
                setTimeout(() => location.reload(), 500);
            }
        }
    })
    .catch(() => showToast('Có lỗi xảy ra!', 'error'));
}

function showToast(msg, type) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class='bx ${type === 'success' ? 'bx-check-circle' : 'bx-error-circle'}'></i> ${msg}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>
