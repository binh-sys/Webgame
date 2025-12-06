<?php
require_once 'ketnoi.php';

// Phân trang
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Đếm tổng thành viên
$total_members = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'] ?? 0;

// Lấy thành viên mới nhất
$new_members_sql = "SELECT user_id, display_name, username, avatar, created_at, role FROM users ORDER BY created_at DESC LIMIT 8";
$new_members = $conn->query($new_members_sql)->fetch_all(MYSQLI_ASSOC);

// Lấy thành viên tích cực (nhiều comment nhất)
$active_members_sql = "SELECT u.user_id, u.display_name, u.username, u.avatar, u.role,
    COUNT(c.comment_id) as comment_count
    FROM users u
    LEFT JOIN comments c ON u.user_id = c.user_id
    GROUP BY u.user_id
    ORDER BY comment_count DESC LIMIT 5";
$active_members = $conn->query($active_members_sql)->fetch_all(MYSQLI_ASSOC);

// Đếm tổng bình luận
$total_comments = $conn->query("SELECT COUNT(*) as total FROM comments")->fetch_assoc()['total'] ?? 0;
$total_pages = max(1, ceil($total_comments / $limit));

// Lấy bình luận gần đây
$comments_sql = "SELECT c.comment_id, c.content, c.created_at, c.article_id,
    u.user_id, u.display_name, u.avatar, u.role,
    a.title AS article_title, a.slug AS article_slug
    FROM comments c
    LEFT JOIN users u ON c.user_id = u.user_id
    LEFT JOIN articles a ON c.article_id = a.article_id
    ORDER BY c.created_at DESC
    LIMIT $limit OFFSET $offset";
$comments = $conn->query($comments_sql)->fetch_all(MYSQLI_ASSOC);

// Thống kê
$stats = [
    'members' => $total_members,
    'comments' => $total_comments,
    'articles' => $conn->query("SELECT COUNT(*) as total FROM articles WHERE status='published'")->fetch_assoc()['total'] ?? 0,
    'online' => rand(10, 50) // Demo - thực tế cần tracking session
];

// Bài viết được thảo luận nhiều nhất
$hot_discussions_sql = "SELECT a.article_id, a.title, a.slug, a.featured_image,
    COUNT(c.comment_id) as comment_count
    FROM articles a
    LEFT JOIN comments c ON a.article_id = c.article_id
    WHERE a.status = 'published'
    GROUP BY a.article_id
    ORDER BY comment_count DESC LIMIT 5";
$hot_discussions = $conn->query($hot_discussions_sql)->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<style>
    /* ===== COMMUNITY PAGE STYLES ===== */
    .community-page {
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
        min-height: 100vh;
        padding: 40px 0 80px;
        position: relative;
    }

    .community-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background:
            radial-gradient(circle at 20% 30%, rgba(99, 102, 241, 0.08) 0%, transparent 40%),
            radial-gradient(circle at 80% 70%, rgba(139, 92, 246, 0.06) 0%, transparent 40%);
        pointer-events: none;
    }

    .community-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
        position: relative;
        z-index: 10;
    }

    /* Page Header */
    .community-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .community-header-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        box-shadow: 0 15px 40px rgba(99, 102, 241, 0.3);
    }

    .community-header-icon i {
        font-size: 36px;
        color: #fff;
    }

    .community-header h1 {
        color: #fff;
        font-size: 42px;
        font-weight: 800;
        margin-bottom: 15px;
    }

    .community-header p {
        color: #888;
        font-size: 18px;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Stats Bar */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 50px;
    }

    @media (max-width: 800px) {
        .stats-bar {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .stat-card {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        padding: 25px;
        display: flex;
        align-items: center;
        gap: 20px;
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        border-color: rgba(99, 102, 241, 0.3);
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: #fff;
    }

    .stat-icon.members { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
    .stat-icon.comments { background: linear-gradient(135deg, #10b981, #34d399); }
    .stat-icon.articles { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .stat-icon.online { background: linear-gradient(135deg, #ef4444, #f87171); }

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

    /* Main Layout */
    .community-layout {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 40px;
    }

    @media (max-width: 1100px) {
        .community-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Section Title */
    .section-title {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 25px;
    }

    .section-title h2 {
        color: #fff;
        font-size: 22px;
        font-weight: 700;
        margin: 0;
    }

    .section-title i {
        color: #6366f1;
        font-size: 20px;
    }

    /* Activity Feed */
    .activity-feed {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .activity-card {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        padding: 25px;
        transition: all 0.3s;
    }

    .activity-card:hover {
        border-color: rgba(99, 102, 241, 0.3);
    }

    .activity-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .activity-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #6366f1;
    }

    .activity-user-info {
        flex: 1;
    }

    .activity-username {
        color: #fff;
        font-weight: 600;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .role-badge {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .role-badge.admin { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
    .role-badge.editor { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
    .role-badge.user { background: rgba(99, 102, 241, 0.2); color: #6366f1; }

    .activity-meta {
        color: #666;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 15px;
        margin-top: 3px;
    }

    .activity-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .activity-meta i {
        color: #6366f1;
        font-size: 12px;
    }

    .activity-content {
        color: #ccc;
        font-size: 15px;
        line-height: 1.7;
        margin-bottom: 15px;
        padding: 15px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 12px;
        border-left: 3px solid #6366f1;
    }

    .activity-article {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 15px;
        background: rgba(99, 102, 241, 0.1);
        border-radius: 10px;
        color: #6366f1;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.3s;
    }

    .activity-article:hover {
        background: rgba(99, 102, 241, 0.2);
        color: #818cf8;
    }

    .activity-article i {
        font-size: 16px;
    }

    /* Sidebar */
    .community-sidebar {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .sidebar-card {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
    }

    .sidebar-header {
        padding: 20px 25px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sidebar-header i {
        color: #6366f1;
        font-size: 18px;
    }

    .sidebar-header h3 {
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }

    .sidebar-body {
        padding: 20px;
    }

    /* New Members */
    .members-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
    }

    .member-item {
        text-align: center;
    }

    .member-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #6366f1;
        margin-bottom: 8px;
    }

    .member-name {
        color: #fff;
        font-size: 12px;
        font-weight: 500;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Active Members */
    .active-member-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .active-member-item:last-child {
        border-bottom: none;
    }

    .active-member-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .active-member-info {
        flex: 1;
    }

    .active-member-name {
        color: #fff;
        font-size: 14px;
        font-weight: 500;
    }

    .active-member-stats {
        color: #666;
        font-size: 12px;
    }

    .active-member-rank {
        width: 28px;
        height: 28px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
    }

    /* Hot Discussions */
    .discussion-item {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .discussion-item:last-child {
        border-bottom: none;
    }

    .discussion-thumb {
        width: 60px;
        height: 45px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .discussion-info {
        flex: 1;
    }

    .discussion-title {
        color: #fff;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.4;
        margin-bottom: 5px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .discussion-title a {
        color: inherit;
        text-decoration: none;
    }

    .discussion-title a:hover {
        color: #6366f1;
    }

    .discussion-comments {
        color: #6366f1;
        font-size: 12px;
    }

    /* Pagination */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 40px;
    }

    .pagination-btn {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        color: #888;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
    }

    .pagination-btn:hover {
        background: rgba(99, 102, 241, 0.1);
        border-color: rgba(99, 102, 241, 0.3);
        color: #6366f1;
    }

    .pagination-btn.active {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-color: transparent;
        color: #fff;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .empty-state i {
        font-size: 60px;
        margin-bottom: 20px;
        color: #333;
    }

    .empty-state h3 {
        color: #fff;
        font-size: 20px;
        margin-bottom: 10px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .community-header h1 {
            font-size: 32px;
        }

        .members-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
</style>

<div class="community-page">
    <div class="community-container">
        <!-- Header -->
        <div class="community-header">
            <div class="community-header-icon">
                <i class="fas fa-users"></i>
            </div>
            <h1>Cộng Đồng Game Thủ</h1>
            <p>Kết nối, chia sẻ và thảo luận cùng hàng ngàn game thủ khác</p>
        </div>

        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-icon members"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3><?= number_format($stats['members']) ?></h3>
                    <p>Thành viên</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon comments"><i class="fas fa-comments"></i></div>
                <div class="stat-info">
                    <h3><?= number_format($stats['comments']) ?></h3>
                    <p>Bình luận</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon articles"><i class="fas fa-newspaper"></i></div>
                <div class="stat-info">
                    <h3><?= number_format($stats['articles']) ?></h3>
                    <p>Bài viết</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon online"><i class="fas fa-circle"></i></div>
                <div class="stat-info">
                    <h3><?= $stats['online'] ?></h3>
                    <p>Đang online</p>
                </div>
            </div>
        </div>

        <div class="community-layout">
            <!-- Main Content - Activity Feed -->
            <div class="community-main">
                <div class="section-title">
                    <i class="fas fa-stream"></i>
                    <h2>Hoạt động gần đây</h2>
                </div>

                <?php if (!empty($comments)): ?>
                    <div class="activity-feed">
                        <?php foreach ($comments as $comment): ?>
                            <?php $avatar = !empty($comment['avatar']) ? 'img/' . $comment['avatar'] : 'img/default-avatar.png'; ?>
                            <div class="activity-card">
                                <div class="activity-header">
                                    <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="activity-avatar">
                                    <div class="activity-user-info">
                                        <div class="activity-username">
                                            <?= htmlspecialchars($comment['display_name'] ?? 'Ẩn danh') ?>
                                            <span class="role-badge <?= $comment['role'] ?? 'user' ?>"><?= $comment['role'] ?? 'user' ?></span>
                                        </div>
                                        <div class="activity-meta">
                                            <span><i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></span>
                                            <span><i class="fas fa-comment"></i> Đã bình luận</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="activity-content">
                                    <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                </div>
                                <?php if (!empty($comment['article_title'])): ?>
                                    <a href="article.php?slug=<?= urlencode($comment['article_slug']) ?>" class="activity-article">
                                        <i class="fas fa-newspaper"></i>
                                        <span><?= htmlspecialchars($comment['article_title']) ?></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination-wrapper">
                            <a href="?page=<?= max(1, $page - 1) ?>" class="pagination-btn <?= $page <= 1 ? 'disabled' : '' ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <?php for ($i = 1; $i <= min($total_pages, 5); $i++): ?>
                                <a href="?page=<?= $i ?>" class="pagination-btn <?= $i == $page ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            <a href="?page=<?= min($total_pages, $page + 1) ?>" class="pagination-btn <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>Chưa có hoạt động</h3>
                        <p>Hãy là người đầu tiên bình luận!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="community-sidebar">
                <!-- New Members -->
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <i class="fas fa-user-plus"></i>
                        <h3>Thành viên mới</h3>
                    </div>
                    <div class="sidebar-body">
                        <div class="members-grid">
                            <?php foreach ($new_members as $member): ?>
                                <?php $avatar = !empty($member['avatar']) ? 'img/' . $member['avatar'] : 'img/default-avatar.png'; ?>
                                <div class="member-item">
                                    <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="member-avatar">
                                    <span class="member-name"><?= htmlspecialchars($member['display_name'] ?? $member['username']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Active Members -->
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <i class="fas fa-trophy"></i>
                        <h3>Thành viên tích cực</h3>
                    </div>
                    <div class="sidebar-body">
                        <?php $rank = 1; foreach ($active_members as $member): ?>
                            <?php $avatar = !empty($member['avatar']) ? 'img/' . $member['avatar'] : 'img/default-avatar.png'; ?>
                            <div class="active-member-item">
                                <span class="active-member-rank"><?= $rank++ ?></span>
                                <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="active-member-avatar">
                                <div class="active-member-info">
                                    <div class="active-member-name"><?= htmlspecialchars($member['display_name'] ?? $member['username']) ?></div>
                                    <div class="active-member-stats"><?= $member['comment_count'] ?> bình luận</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Hot Discussions -->
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <i class="fas fa-fire"></i>
                        <h3>Thảo luận sôi nổi</h3>
                    </div>
                    <div class="sidebar-body">
                        <?php foreach ($hot_discussions as $disc): ?>
                            <?php $img = !empty($disc['featured_image']) ? '../uploads/' . basename($disc['featured_image']) : 'img/default.jpg'; ?>
                            <div class="discussion-item">
                                <img src="<?= htmlspecialchars($img) ?>" alt="" class="discussion-thumb">
                                <div class="discussion-info">
                                    <h4 class="discussion-title">
                                        <a href="article.php?slug=<?= urlencode($disc['slug']) ?>"><?= htmlspecialchars($disc['title']) ?></a>
                                    </h4>
                                    <span class="discussion-comments">
                                        <i class="fas fa-comments"></i> <?= $disc['comment_count'] ?> bình luận
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
