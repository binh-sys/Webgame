<?php
// content.php - Dashboard trang tổng quan chuyên nghiệp
require_once('ketnoi.php');

// Thống kê tổng quan
$total_articles = (int)($ketnoi->query("SELECT COUNT(*) AS total FROM articles")->fetch_assoc()['total'] ?? 0);
$total_users = (int)($ketnoi->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0);
$total_comments = (int)($ketnoi->query("SELECT COUNT(*) AS total FROM comments")->fetch_assoc()['total'] ?? 0);
$total_favorites = (int)($ketnoi->query("SELECT COUNT(*) AS total FROM favorites")->fetch_assoc()['total'] ?? 0);
$total_categories = (int)($ketnoi->query("SELECT COUNT(*) AS total FROM categories")->fetch_assoc()['total'] ?? 0);
$total_tags = (int)($ketnoi->query("SELECT COUNT(*) AS total FROM tags")->fetch_assoc()['total'] ?? 0);

// Bài viết đã xuất bản vs nháp
$published = (int)($ketnoi->query("SELECT COUNT(*) AS c FROM articles WHERE status='published'")->fetch_assoc()['c'] ?? 0);
$draft = (int)($ketnoi->query("SELECT COUNT(*) AS c FROM articles WHERE status='draft'")->fetch_assoc()['c'] ?? 0);

// Bình luận chờ duyệt
$pending_comments = (int)($ketnoi->query("SELECT COUNT(*) AS c FROM comments WHERE status='pending'")->fetch_assoc()['c'] ?? 0);

// Tổng lượt xem
$total_views = (int)($ketnoi->query("SELECT SUM(views) AS total FROM articles")->fetch_assoc()['total'] ?? 0);

// Dữ liệu biểu đồ - Bài viết theo danh mục
$categories_data = [];
$q = $ketnoi->query("SELECT c.name, COUNT(a.article_id) AS cnt FROM categories c LEFT JOIN articles a ON c.category_id=a.category_id GROUP BY c.category_id ORDER BY cnt DESC LIMIT 6");
while ($r = $q->fetch_assoc()) {
    $categories_data[] = $r;
}

// Dữ liệu biểu đồ - Vai trò người dùng
$roles_data = [];
$qr = $ketnoi->query("SELECT role, COUNT(user_id) AS total FROM users GROUP BY role");
while ($r = $qr->fetch_assoc()) {
    $roles_data[] = $r;
}

// Bài viết gần đây
$recent_articles = $ketnoi->query("SELECT a.*, c.name as category_name, u.display_name as author_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.category_id 
    LEFT JOIN users u ON a.author_id = u.user_id 
    ORDER BY a.created_at DESC LIMIT 5");

// Bình luận gần đây
$recent_comments = $ketnoi->query("SELECT c.*, u.display_name, u.username, a.title as article_title 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.user_id 
    LEFT JOIN articles a ON c.article_id = a.article_id 
    ORDER BY c.created_at DESC LIMIT 5");

// Top bài viết xem nhiều
$top_articles = $ketnoi->query("SELECT title, views, created_at FROM articles ORDER BY views DESC LIMIT 5");
?>

<link rel="stylesheet" href="assets/css/admin-forms.css">

<style>
/* Dashboard Styles */
.dashboard-container {
    padding: 0;
}

/* Welcome Banner */
.welcome-banner {
    background: linear-gradient(135deg, rgba(0, 212, 255, 0.15), rgba(168, 85, 247, 0.1));
    border: 1px solid rgba(0, 212, 255, 0.2);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}

.welcome-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(0, 212, 255, 0.1) 0%, transparent 70%);
    pointer-events: none;
}

.welcome-banner h1 {
    margin: 0 0 8px;
    font-size: 26px;
    font-weight: 700;
    color: #fff;
}

.welcome-banner p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 15px;
}

.welcome-banner .date-time {
    position: absolute;
    right: 32px;
    top: 50%;
    transform: translateY(-50%);
    text-align: right;
    color: var(--text-muted);
    font-size: 14px;
}

.welcome-banner .date-time .time {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary);
    font-family: 'Orbitron', monospace;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 28px;
}

@media (max-width: 1200px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 600px) {
    .stats-grid { grid-template-columns: 1fr; }
}

.stat-card {
    background: linear-gradient(145deg, var(--bg-card), #080c12);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 24px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    border-color: var(--border-hover);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
}

.stat-card .stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    margin-bottom: 16px;
}

.stat-card .stat-icon.cyan { background: rgba(0, 212, 255, 0.15); color: var(--primary); }
.stat-card .stat-icon.purple { background: rgba(168, 85, 247, 0.15); color: #a855f7; }
.stat-card .stat-icon.green { background: rgba(0, 255, 136, 0.15); color: #00ff88; }
.stat-card .stat-icon.pink { background: rgba(255, 71, 107, 0.15); color: #ff476b; }
.stat-card .stat-icon.orange { background: rgba(255, 149, 0, 0.15); color: #ff9500; }
.stat-card .stat-icon.blue { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }

.stat-card h3 {
    font-size: 32px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
    font-family: 'Orbitron', sans-serif;
}

.stat-card p {
    margin: 0;
    color: var(--text-muted);
    font-size: 14px;
}

.stat-card .stat-trend {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 13px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
}

.stat-card .stat-trend.up {
    background: rgba(0, 255, 136, 0.15);
    color: #00ff88;
}

.stat-card .stat-trend.down {
    background: rgba(255, 71, 87, 0.15);
    color: #ff4757;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}

@media (max-width: 900px) {
    .quick-actions { grid-template-columns: repeat(2, 1fr); }
}

.quick-action-btn {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 20px;
    background: linear-gradient(145deg, var(--bg-card), #080c12);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    text-decoration: none;
    color: var(--text-primary);
    transition: all 0.25s ease;
}

.quick-action-btn:hover {
    border-color: var(--primary);
    background: rgba(0, 212, 255, 0.05);
    transform: translateX(4px);
}

.quick-action-btn i {
    font-size: 24px;
    color: var(--primary);
}

.quick-action-btn span {
    font-weight: 600;
    font-size: 14px;
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
    margin-bottom: 28px;
}

@media (max-width: 1100px) {
    .dashboard-grid { grid-template-columns: 1fr; }
}

/* Dashboard Card */
.dashboard-card {
    background: linear-gradient(145deg, var(--bg-card), #080c12);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    overflow: hidden;
}

.dashboard-card-header {
    padding: 18px 22px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dashboard-card-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.dashboard-card-header h3 i {
    font-size: 20px;
}

.dashboard-card-header .view-all {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 13px;
    transition: color 0.2s;
}

.dashboard-card-header .view-all:hover {
    color: var(--primary);
}

.dashboard-card-body {
    padding: 20px 22px;
}

/* Recent Articles List */
.article-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

.article-item:last-child {
    border-bottom: none;
}

.article-thumb {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    background: var(--bg-input);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 24px;
    flex-shrink: 0;
    overflow: hidden;
}

.article-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.article-info {
    flex: 1;
    min-width: 0;
}

.article-info h4 {
    margin: 0 0 6px;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.article-info .meta {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: var(--text-muted);
}

.article-info .meta span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.article-status {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.article-status.published {
    background: rgba(0, 255, 136, 0.15);
    color: #00ff88;
}

.article-status.draft {
    background: rgba(255, 149, 0, 0.15);
    color: #ff9500;
}

/* Comment Item */
.comment-item {
    padding: 14px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

.comment-item:last-child {
    border-bottom: none;
}

.comment-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.comment-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), #a855f7);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 14px;
}

.comment-user {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 14px;
}

.comment-time {
    color: var(--text-muted);
    font-size: 12px;
    margin-left: auto;
}

.comment-content {
    color: var(--text-secondary);
    font-size: 13px;
    line-height: 1.5;
    margin-bottom: 6px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.comment-article {
    font-size: 12px;
    color: var(--primary);
}

/* Top Articles */
.top-article-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

.top-article-item:last-child {
    border-bottom: none;
}

.top-rank {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
}

.top-rank.gold { background: linear-gradient(135deg, #ffd700, #ffaa00); color: #000; }
.top-rank.silver { background: linear-gradient(135deg, #c0c0c0, #a0a0a0); color: #000; }
.top-rank.bronze { background: linear-gradient(135deg, #cd7f32, #b87333); color: #fff; }
.top-rank.normal { background: var(--bg-input); color: var(--text-muted); }

.top-article-info {
    flex: 1;
    min-width: 0;
}

.top-article-info h4 {
    margin: 0;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.top-article-views {
    font-size: 13px;
    color: var(--primary);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Chart Container */
.chart-container {
    height: 280px;
    position: relative;
}

/* Mini Stats */
.mini-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.mini-stat {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 16px;
    text-align: center;
}

.mini-stat h4 {
    margin: 0 0 4px;
    font-size: 22px;
    font-weight: 700;
    color: var(--primary);
}

.mini-stat p {
    margin: 0;
    font-size: 12px;
    color: var(--text-muted);
}
</style>

<div class="dashboard-container">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <h1>👋 Chào mừng trở lại, Admin!</h1>
        <p>Đây là tổng quan hoạt động của hệ thống GameNova Pro</p>
        <div class="date-time">
            <div class="time" id="currentTime">--:--</div>
            <div id="currentDate"><?= date('l, d/m/Y') ?></div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon cyan"><i class='bx bx-news'></i></div>
            <h3><?= number_format($total_articles) ?></h3>
            <p>Tổng bài viết</p>
            <span class="stat-trend up">+<?= $published ?> xuất bản</span>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class='bx bx-group'></i></div>
            <h3><?= number_format($total_users) ?></h3>
            <p>Người dùng</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class='bx bx-message-dots'></i></div>
            <h3><?= number_format($total_comments) ?></h3>
            <p>Bình luận</p>
            <?php if ($pending_comments > 0): ?>
            <span class="stat-trend down"><?= $pending_comments ?> chờ duyệt</span>
            <?php endif; ?>
        </div>
        <div class="stat-card">
            <div class="stat-icon pink"><i class='bx bx-heart'></i></div>
            <h3><?= number_format($total_favorites) ?></h3>
            <p>Lượt yêu thích</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="?page_layout=them_baiviet" class="quick-action-btn">
            <i class='bx bx-plus-circle'></i>
            <span>Thêm bài viết</span>
        </a>
        <a href="?page_layout=themchuyenmuc" class="quick-action-btn">
            <i class='bx bx-folder-plus'></i>
            <span>Thêm chuyên mục</span>
        </a>
        <a href="?page_layout=themus" class="quick-action-btn">
            <i class='bx bx-user-plus'></i>
            <span>Thêm người dùng</span>
        </a>
        <a href="?page_layout=danhsachbinhluan&status=pending" class="quick-action-btn">
            <i class='bx bx-check-circle'></i>
            <span>Duyệt bình luận (<?= $pending_comments ?>)</span>
        </a>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="dashboard-grid">
        <!-- Recent Articles -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3><i class='bx bx-news'></i> Bài viết gần đây</h3>
                <a href="?page_layout=danhsachbaiviet" class="view-all">Xem tất cả →</a>
            </div>
            <div class="dashboard-card-body">
                <?php while ($article = $recent_articles->fetch_assoc()): ?>
                <div class="article-item">
                    <div class="article-thumb">
                        <?php if (!empty($article['featured_image'])): ?>
                            <img src="../../game2/uploads/<?= htmlspecialchars($article['featured_image']) ?>" alt="">
                        <?php else: ?>
                            <i class='bx bx-image'></i>
                        <?php endif; ?>
                    </div>
                    <div class="article-info">
                        <h4><?= htmlspecialchars($article['title']) ?></h4>
                        <div class="meta">
                            <span><i class='bx bx-folder'></i> <?= htmlspecialchars($article['category_name'] ?? 'Chưa phân loại') ?></span>
                            <span><i class='bx bx-user'></i> <?= htmlspecialchars($article['author_name'] ?? 'Ẩn danh') ?></span>
                            <span><i class='bx bx-show'></i> <?= number_format($article['views']) ?></span>
                        </div>
                    </div>
                    <span class="article-status <?= $article['status'] ?>">
                        <?= $article['status'] == 'published' ? 'Xuất bản' : 'Nháp' ?>
                    </span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Sidebar Stats -->
        <div>
            <!-- Mini Stats -->
            <div class="dashboard-card" style="margin-bottom:20px;">
                <div class="dashboard-card-header">
                    <h3><i class='bx bx-bar-chart-alt-2'></i> Thống kê nhanh</h3>
                </div>
                <div class="dashboard-card-body">
                    <div class="mini-stats">
                        <div class="mini-stat">
                            <h4><?= number_format($total_views) ?></h4>
                            <p>Tổng lượt xem</p>
                        </div>
                        <div class="mini-stat">
                            <h4><?= $total_categories ?></h4>
                            <p>Chuyên mục</p>
                        </div>
                        <div class="mini-stat">
                            <h4><?= $total_tags ?></h4>
                            <p>Thẻ game</p>
                        </div>
                        <div class="mini-stat">
                            <h4><?= $draft ?></h4>
                            <p>Bài nháp</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Articles -->
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3><i class='bx bx-trophy'></i> Top bài viết</h3>
                </div>
                <div class="dashboard-card-body">
                    <?php 
                    $rank = 1;
                    while ($top = $top_articles->fetch_assoc()): 
                        $rankClass = $rank == 1 ? 'gold' : ($rank == 2 ? 'silver' : ($rank == 3 ? 'bronze' : 'normal'));
                    ?>
                    <div class="top-article-item">
                        <div class="top-rank <?= $rankClass ?>"><?= $rank ?></div>
                        <div class="top-article-info">
                            <h4><?= htmlspecialchars($top['title']) ?></h4>
                        </div>
                        <div class="top-article-views">
                            <i class='bx bx-show'></i> <?= number_format($top['views']) ?>
                        </div>
                    </div>
                    <?php $rank++; endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row -->
    <div class="dashboard-grid">
        <!-- Recent Comments -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3><i class='bx bx-message-dots'></i> Bình luận gần đây</h3>
                <a href="?page_layout=danhsachbinhluan" class="view-all">Xem tất cả →</a>
            </div>
            <div class="dashboard-card-body">
                <?php while ($comment = $recent_comments->fetch_assoc()): ?>
                <div class="comment-item">
                    <div class="comment-header">
                        <div class="comment-avatar"><?= strtoupper(substr($comment['username'] ?? 'U', 0, 1)) ?></div>
                        <span class="comment-user"><?= htmlspecialchars($comment['display_name'] ?? $comment['username'] ?? 'Ẩn danh') ?></span>
                        <span class="comment-time"><?= date('d/m H:i', strtotime($comment['created_at'])) ?></span>
                    </div>
                    <div class="comment-content"><?= htmlspecialchars($comment['content']) ?></div>
                    <div class="comment-article">📰 <?= htmlspecialchars($comment['article_title'] ?? 'Bài viết') ?></div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Charts -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3><i class='bx bx-pie-chart-alt-2'></i> Bài viết theo danh mục</h3>
            </div>
            <div class="dashboard-card-body">
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Update time
function updateTime() {
    const now = new Date();
    document.getElementById('currentTime').textContent = 
        now.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
}
updateTime();
setInterval(updateTime, 1000);

// Category Chart
const categoryData = <?= json_encode(array_column($categories_data, 'name')) ?>;
const categoryCounts = <?= json_encode(array_map('intval', array_column($categories_data, 'cnt'))) ?>;

if (document.getElementById('categoryChart')) {
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: categoryData,
            datasets: [{
                data: categoryCounts,
                backgroundColor: [
                    'rgba(0, 212, 255, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                    'rgba(0, 255, 136, 0.8)',
                    'rgba(255, 71, 107, 0.8)',
                    'rgba(255, 149, 0, 0.8)',
                    'rgba(59, 130, 246, 0.8)'
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#8b949e',
                        padding: 15,
                        font: { size: 12 }
                    }
                }
            },
            cutout: '65%'
        }
    });
}
</script>
