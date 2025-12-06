<?php
require_once 'ketnoi.php';

// Đảm bảo kết nối DB
if (!isset($conn) || $conn === null) {
    $conn = new mysqli("localhost", "root", "", "webtintuc");
    if ($conn->connect_error) die("DB ERROR: " . $conn->connect_error);
}

// Lấy category review
$category_id = 0;
$q = "SELECT category_id FROM categories WHERE slug='review' OR name LIKE '%review%' OR name LIKE '%đánh giá%' LIMIT 1";
$r = $conn->query($q);
if ($r && $r->num_rows > 0) {
    $category_id = intval($r->fetch_assoc()['category_id']);
}

// Phân trang
$limit = 8;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Đếm tổng bài review
$count_sql = "SELECT COUNT(*) as total FROM articles WHERE status='published'";
if ($category_id > 0) {
    $count_sql .= " AND category_id = $category_id";
}
$total_reviews = $conn->query($count_sql)->fetch_assoc()['total'] ?? 0;
$total_pages = max(1, ceil($total_reviews / $limit));

// Lấy danh sách review
$sql = "SELECT a.*, c.name AS category_name, u.display_name AS author_name
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.category_id
    LEFT JOIN users u ON a.author_id = u.user_id
    WHERE a.status='published'";
if ($category_id > 0) {
    $sql .= " AND a.category_id = $category_id";
}
$sql .= " ORDER BY a.created_at DESC LIMIT $limit OFFSET $offset";
$review_list = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Lấy bài review nổi bật (nhiều view nhất)
$featured_sql = "SELECT a.*, c.name AS category_name, u.display_name AS author_name
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.category_id
    LEFT JOIN users u ON a.author_id = u.user_id
    WHERE a.status='published'";
if ($category_id > 0) {
    $featured_sql .= " AND a.category_id = $category_id";
}
$featured_sql .= " ORDER BY a.views DESC LIMIT 3";
$featured_reviews = $conn->query($featured_sql)->fetch_all(MYSQLI_ASSOC);

// Lấy thống kê
$stats = [
    'total' => $total_reviews,
    'this_month' => 0,
    'total_views' => 0
];
$stats_sql = "SELECT COUNT(*) as this_month, SUM(views) as total_views FROM articles WHERE status='published' AND MONTH(created_at) = MONTH(NOW())";
if ($category_id > 0) {
    $stats_sql .= " AND category_id = $category_id";
}
$stats_result = $conn->query($stats_sql)->fetch_assoc();
$stats['this_month'] = $stats_result['this_month'] ?? 0;
$stats['total_views'] = $stats_result['total_views'] ?? 0;

include 'header.php';
?>

<style>
    /* ===== REVIEW PAGE STYLES ===== */
    .review-page {
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
        min-height: 100vh;
        padding: 40px 0 80px;
        position: relative;
    }

    .review-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background:
            radial-gradient(circle at 20% 30%, rgba(255, 193, 7, 0.08) 0%, transparent 40%),
            radial-gradient(circle at 80% 70%, rgba(255, 107, 53, 0.06) 0%, transparent 40%);
        pointer-events: none;
    }

    .review-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
        position: relative;
        z-index: 10;
    }

    /* Page Header */
    .review-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .review-header-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #ffc107, #ff9800);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        box-shadow: 0 15px 40px rgba(255, 193, 7, 0.3);
    }

    .review-header-icon i {
        font-size: 36px;
        color: #111;
    }

    .review-header h1 {
        color: #fff;
        font-size: 42px;
        font-weight: 800;
        margin-bottom: 15px;
    }

    .review-header p {
        color: #888;
        font-size: 18px;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Stats Bar */
    .stats-bar {
        display: flex;
        justify-content: center;
        gap: 50px;
        margin-bottom: 50px;
        flex-wrap: wrap;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        color: #ffc107;
        font-size: 36px;
        font-weight: 800;
        display: block;
    }

    .stat-label {
        color: #888;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Featured Reviews */
    .featured-section {
        margin-bottom: 60px;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
    }

    .section-title h2 {
        color: #fff;
        font-size: 24px;
        font-weight: 700;
        margin: 0;
    }

    .section-title-line {
        flex: 1;
        height: 2px;
        background: linear-gradient(to right, rgba(255, 193, 7, 0.5), transparent);
    }

    .featured-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr;
        gap: 25px;
    }

    @media (max-width: 1100px) {
        .featured-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .featured-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Featured Card Large */
    .featured-card-large {
        grid-row: span 2;
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        height: 100%;
        min-height: 500px;
    }

    .featured-card-large .card-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: transform 0.5s;
    }

    .featured-card-large:hover .card-bg {
        transform: scale(1.05);
    }

    .featured-card-large .card-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0.3) 50%, rgba(0, 0, 0, 0.1) 100%);
    }

    .featured-card-large .card-content {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 40px;
        z-index: 10;
    }

    .featured-card-large .score-badge {
        position: absolute;
        top: 25px;
        right: 25px;
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #ffc107, #ff9800);
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 30px rgba(255, 193, 7, 0.4);
        z-index: 10;
    }

    .score-badge .score {
        color: #111;
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
    }

    .score-badge .score-label {
        color: rgba(0, 0, 0, 0.6);
        font-size: 10px;
        text-transform: uppercase;
    }

    .featured-card-large .card-title {
        color: #fff;
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 15px;
        line-height: 1.3;
    }

    .featured-card-large .card-title a {
        color: inherit;
        text-decoration: none;
    }

    .featured-card-large .card-title a:hover {
        color: #ffc107;
    }

    .card-rating {
        display: flex;
        gap: 5px;
        margin-bottom: 15px;
    }

    .card-rating i {
        color: #ffc107;
        font-size: 16px;
    }

    .card-rating i.empty {
        color: #444;
    }

    .card-excerpt {
        color: #aaa;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .card-meta {
        display: flex;
        align-items: center;
        gap: 20px;
        color: #888;
        font-size: 14px;
    }

    .card-meta span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .card-meta i {
        color: #ffc107;
    }

    /* Featured Card Small */
    .featured-card-small {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        height: 240px;
    }

    .featured-card-small .card-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: transform 0.5s;
    }

    .featured-card-small:hover .card-bg {
        transform: scale(1.1);
    }

    .featured-card-small .card-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.2) 100%);
    }

    .featured-card-small .card-content {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 25px;
        z-index: 10;
    }

    .featured-card-small .score-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #ffc107, #ff9800);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    .featured-card-small .score-badge .score {
        color: #111;
        font-size: 20px;
        font-weight: 800;
    }

    .featured-card-small .card-title {
        color: #fff;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .featured-card-small .card-title a {
        color: inherit;
        text-decoration: none;
    }

    .featured-card-small .card-title a:hover {
        color: #ffc107;
    }

    .featured-card-small .card-rating {
        margin-bottom: 0;
    }

    .featured-card-small .card-rating i {
        font-size: 14px;
    }

    /* Reviews Grid */
    .reviews-section {
        margin-bottom: 50px;
    }

    .reviews-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
    }

    @media (max-width: 1200px) {
        .reviews-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 900px) {
        .reviews-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .reviews-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Review Card */
    .review-card {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
        transition: all 0.3s;
    }

    .review-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        border-color: rgba(255, 193, 7, 0.3);
    }

    .review-card-image {
        position: relative;
        height: 180px;
        overflow: hidden;
    }

    .review-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }

    .review-card:hover .review-card-image img {
        transform: scale(1.1);
    }

    .review-card-score {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #ffc107, #ff9800);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #111;
        font-size: 18px;
        font-weight: 800;
    }

    .review-card-content {
        padding: 20px;
    }

    .review-card-title {
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
    }

    .review-card-title a {
        color: inherit;
        text-decoration: none;
    }

    .review-card-title a:hover {
        color: #ffc107;
    }

    .review-card-rating {
        display: flex;
        gap: 4px;
        margin-bottom: 12px;
    }

    .review-card-rating i {
        color: #ffc107;
        font-size: 13px;
    }

    .review-card-rating i.empty {
        color: #333;
    }

    .review-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 15px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        color: #666;
        font-size: 12px;
    }

    .review-card-footer span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .review-card-footer i {
        color: #ffc107;
    }

    /* Pagination */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 50px;
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
        background: rgba(255, 193, 7, 0.1);
        border-color: rgba(255, 193, 7, 0.3);
        color: #ffc107;
    }

    .pagination-btn.active {
        background: linear-gradient(135deg, #ffc107, #ff9800);
        border-color: transparent;
        color: #111;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: rgba(20, 20, 35, 0.98);
        border-radius: 24px;
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
    }

    /* Responsive */
    @media (max-width: 768px) {
        .review-header h1 {
            font-size: 32px;
        }

        .stats-bar {
            gap: 30px;
        }

        .stat-number {
            font-size: 28px;
        }

        .featured-card-large {
            min-height: 400px;
        }

        .featured-card-large .card-title {
            font-size: 22px;
        }

        .featured-card-large .card-content {
            padding: 25px;
        }
    }
</style>

<?php
// Hàm tạo điểm ngẫu nhiên cho demo (thực tế nên lưu trong DB)
function getScore($article_id) {
    srand($article_id);
    return number_format(rand(70, 98) / 10, 1);
}

// Hàm tạo rating stars
function getRatingStars($score) {
    $stars = round($score / 2);
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<i class="fas fa-star ' . ($i <= $stars ? '' : 'empty') . '"></i>';
    }
    return $html;
}
?>

<div class="review-page">
    <div class="review-container">
        <!-- Header -->
        <div class="review-header">
            <div class="review-header-icon">
                <i class="fas fa-star"></i>
            </div>
            <h1>Đánh Giá Game</h1>
            <p>Những bài đánh giá chi tiết và chuyên sâu về các tựa game mới nhất</p>
        </div>

        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-number"><?= number_format($stats['total']) ?></span>
                <span class="stat-label">Bài đánh giá</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= number_format($stats['this_month']) ?></span>
                <span class="stat-label">Tháng này</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= number_format($stats['total_views']) ?></span>
                <span class="stat-label">Lượt xem</span>
            </div>
        </div>

        <!-- Featured Reviews -->
        <?php if (!empty($featured_reviews) && $page == 1): ?>
            <div class="featured-section">
                <div class="section-title">
                    <h2><i class="fas fa-fire" style="color: #ffc107; margin-right: 10px;"></i>Đánh giá nổi bật</h2>
                    <div class="section-title-line"></div>
                </div>

                <div class="featured-grid">
                    <?php foreach ($featured_reviews as $index => $review): ?>
                        <?php 
                        $img = !empty($review['featured_image']) ? '../uploads/' . basename($review['featured_image']) : 'img/default.jpg';
                        $score = getScore($review['article_id']);
                        $excerpt = mb_substr(strip_tags($review['excerpt'] ?? $review['content']), 0, 150) . '...';
                        ?>
                        
                        <?php if ($index == 0): ?>
                            <!-- Large Featured Card -->
                            <div class="featured-card-large">
                                <div class="card-bg" style="background-image: url('<?= htmlspecialchars($img) ?>');"></div>
                                <div class="card-overlay"></div>
                                <div class="score-badge">
                                    <span class="score"><?= $score ?></span>
                                    <span class="score-label">Score</span>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title">
                                        <a href="article.php?id=<?= $review['article_id'] ?>"><?= htmlspecialchars($review['title']) ?></a>
                                    </h3>
                                    <div class="card-rating">
                                        <?= getRatingStars($score) ?>
                                    </div>
                                    <p class="card-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                                    <div class="card-meta">
                                        <span><i class="fas fa-user"></i> <?= htmlspecialchars($review['author_name'] ?? 'Admin') ?></span>
                                        <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($review['created_at'])) ?></span>
                                        <span><i class="fas fa-eye"></i> <?= number_format($review['views']) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Small Featured Card -->
                            <div class="featured-card-small">
                                <div class="card-bg" style="background-image: url('<?= htmlspecialchars($img) ?>');"></div>
                                <div class="card-overlay"></div>
                                <div class="score-badge">
                                    <span class="score"><?= $score ?></span>
                                </div>
                                <div class="card-content">
                                    <h4 class="card-title">
                                        <a href="article.php?id=<?= $review['article_id'] ?>"><?= htmlspecialchars($review['title']) ?></a>
                                    </h4>
                                    <div class="card-rating">
                                        <?= getRatingStars($score) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- All Reviews -->
        <div class="reviews-section">
            <div class="section-title">
                <h2><i class="fas fa-gamepad" style="color: #ffc107; margin-right: 10px;"></i>Tất cả đánh giá</h2>
                <div class="section-title-line"></div>
            </div>

            <?php if (!empty($review_list)): ?>
                <div class="reviews-grid">
                    <?php foreach ($review_list as $review): ?>
                        <?php 
                        $img = !empty($review['featured_image']) ? '../uploads/' . basename($review['featured_image']) : 'img/default.jpg';
                        $score = getScore($review['article_id']);
                        ?>
                        <div class="review-card">
                            <div class="review-card-image">
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($review['title']) ?>">
                                <span class="review-card-score"><?= $score ?></span>
                            </div>
                            <div class="review-card-content">
                                <h3 class="review-card-title">
                                    <a href="article.php?id=<?= $review['article_id'] ?>"><?= htmlspecialchars($review['title']) ?></a>
                                </h3>
                                <div class="review-card-rating">
                                    <?= getRatingStars($score) ?>
                                </div>
                                <div class="review-card-footer">
                                    <span><i class="fas fa-user"></i> <?= htmlspecialchars($review['author_name'] ?? 'Admin') ?></span>
                                    <span><i class="fas fa-eye"></i> <?= number_format($review['views']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-wrapper">
                        <a href="?page=<?= max(1, $page - 1) ?>" class="pagination-btn <?= $page <= 1 ? 'disabled' : '' ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
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
                    <i class="fas fa-star"></i>
                    <h3>Chưa có bài đánh giá</h3>
                    <p>Hiện chưa có bài đánh giá nào. Hãy quay lại sau!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
