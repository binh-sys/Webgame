<?php
require_once 'ketnoi.php';

// Lấy bài viết nổi bật (nhiều view nhất)
$featured_sql = "SELECT a.*, c.name AS category_name, u.display_name AS author_name
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.category_id
    LEFT JOIN users u ON a.author_id = u.user_id
    WHERE a.status = 'published'
    ORDER BY a.views DESC LIMIT 5";
$featured_articles = $conn->query($featured_sql)->fetch_all(MYSQLI_ASSOC);

// Lấy bài viết mới nhất
$latest_sql = "SELECT a.*, c.name AS category_name, u.display_name AS author_name,
    (SELECT COUNT(*) FROM comments cm WHERE cm.article_id = a.article_id) AS comment_count
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.category_id
    LEFT JOIN users u ON a.author_id = u.user_id
    WHERE a.status = 'published'
    ORDER BY a.created_at DESC LIMIT 6";
$latest_articles = $conn->query($latest_sql)->fetch_all(MYSQLI_ASSOC);

// Lấy bài viết phổ biến (7 ngày gần đây)
$trending_sql = "SELECT a.*, c.name AS category_name
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.category_id
    WHERE a.status = 'published' AND a.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY a.views DESC LIMIT 4";
$trending_articles = $conn->query($trending_sql)->fetch_all(MYSQLI_ASSOC);

// Lấy danh mục
$categories_sql = "SELECT c.*, COUNT(a.article_id) as article_count 
    FROM categories c 
    LEFT JOIN articles a ON c.category_id = a.category_id AND a.status = 'published'
    GROUP BY c.category_id ORDER BY article_count DESC LIMIT 6";
$categories = $conn->query($categories_sql)->fetch_all(MYSQLI_ASSOC);

// Lấy bài đánh giá
$review_cat_sql = "SELECT category_id FROM categories WHERE slug='review' OR name LIKE '%review%' OR name LIKE '%đánh giá%' LIMIT 1";
$review_cat = $conn->query($review_cat_sql)->fetch_assoc();
$review_cat_id = $review_cat['category_id'] ?? 0;

$reviews_sql = "SELECT a.*, c.name AS category_name FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.category_id
    WHERE a.status = 'published'" . ($review_cat_id > 0 ? " AND a.category_id = $review_cat_id" : "") . "
    ORDER BY a.created_at DESC LIMIT 4";
$reviews = $conn->query($reviews_sql)->fetch_all(MYSQLI_ASSOC);

// Thống kê
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM articles WHERE status='published') as total_articles,
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT SUM(views) FROM articles) as total_views,
    (SELECT COUNT(*) FROM comments) as total_comments";
$stats = $conn->query($stats_sql)->fetch_assoc();

include 'header.php';
?>

<style>
    /* ===== HOMEPAGE STYLES ===== */
    .homepage {
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
        min-height: 100vh;
    }

    /* Hero Section */
    .hero-section {
        position: relative;
        height: 600px;
        overflow: hidden;
    }

    .hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 50%, rgba(0, 0, 0, 0.6) 100%);
    }

    .hero-content {
        position: relative;
        z-index: 10;
        height: 100%;
        display: flex;
        align-items: center;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .hero-text {
        max-width: 650px;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 20px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        border-radius: 30px;
        margin-bottom: 25px;
    }

    .hero-title {
        color: #fff;
        font-size: 52px;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 20px;
    }

    .hero-title span {
        color: #ff6b35;
    }

    .hero-desc {
        color: #bbb;
        font-size: 18px;
        line-height: 1.7;
        margin-bottom: 30px;
    }

    .hero-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 35px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 30px;
        text-decoration: none;
        transition: all 0.3s;
        box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
    }

    .hero-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(255, 107, 53, 0.4);
        color: #fff;
        position: relative;
    }

    .pagination-dot.active::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 8px;
        height: 8px;
        background: #ff6b35;
        border-radius: 50%;
    }

    /* Progress Bar */
    .slider-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: rgba(255, 255, 255, 0.1);
        z-index: 20;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #ff6b35, #f7931e);
        width: 0%;
        transition: width 0.1s linear;
    }

    /* Slide Counter */
    .slide-counter {
        position: absolute;
        bottom: 40px;
        right: 50px;
        color: rgba(255, 255, 255, 0.5);
        font-size: 14px;
        font-weight: 600;
        z-index: 20;
    }

    .slide-counter .current {
        color: #ff6b35;
        font-size: 28px;
        font-weight: 800;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .slide-title { font-size: 46px; }
        .slide-content { padding: 0 30px; }
    }

    @media (max-width: 768px) {
        .hero-section { height: 550px; }
        .slide-title { font-size: 34px; }
        .slide-desc { font-size: 16px; }
        .slide-btn { padding: 15px 30px; font-size: 14px; }
        .slider-nav { display: none; }
        .slide-counter { display: none; }
    }

    /* Old Progress Bar - keep for compatibility */
    .slider-progress-old {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: rgba(255, 255, 255, 0.1);
        z-index: 20;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #ff6b35, #f7931e);
        width: 0%;
        transition: width 0.1s linear;
    }

    .hero-content {
        position: relative;
        z-index: 10;
        height: 100%;
        display: flex;
        align-items: center;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .hero-text {
        max-width: 650px;
    }

    .hero-badge {
        display: inline-block;
        padding: 8px 20px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        border-radius: 30px;
        margin-bottom: 25px;
    }

    .hero-title {
        color: #fff;
        font-size: 52px;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 20px;
    }

    .hero-title span {
        color: #ff6b35;
    }

    .hero-desc {
        color: #bbb;
        font-size: 18px;
        line-height: 1.7;
        margin-bottom: 30px;
    }

    .hero-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 35px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 30px;
        text-decoration: none;
        transition: all 0.3s;
        box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
    }

    .hero-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(255, 107, 53, 0.4);
        color: #fff;
    }

    /* News Ticker */
    .news-ticker-section {
        background: linear-gradient(90deg, #ff6b35, #f7931e);
        padding: 0;
        position: relative;
        z-index: 100;
    }

    .ticker-wrapper {
        display: flex;
        align-items: center;
        max-width: 1400px;
        margin: 0 auto;
    }

    .ticker-label {
        background: rgba(0, 0, 0, 0.2);
        padding: 15px 25px;
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ticker-content {
        flex: 1;
        overflow: hidden;
        padding: 15px 20px;
    }

    .ticker-track {
        display: flex;
        animation: ticker 30s linear infinite;
        white-space: nowrap;
    }

    .ticker-track:hover {
        animation-play-state: paused;
    }

    @keyframes ticker {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }

    .ticker-item {
        display: inline-flex;
        align-items: center;
        margin-right: 50px;
        color: #fff;
        font-size: 14px;
    }

    .ticker-item a {
        color: #fff;
        text-decoration: none;
        font-weight: 500;
    }

    .ticker-item a:hover {
        text-decoration: underline;
    }

    .ticker-dot {
        width: 6px;
        height: 6px;
        background: rgba(255, 255, 255, 0.5);
        border-radius: 50%;
        margin-right: 12px;
    }

    /* Section Common */
    .section-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 35px;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .section-title h2 {
        color: #fff;
        font-size: 28px;
        font-weight: 700;
        margin: 0;
    }

    .section-title-icon {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 18px;
    }

    .section-link {
        color: #ff6b35;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .section-link:hover {
        color: #f7931e;
        gap: 12px;
    }

    /* Featured Section */
    .featured-section {
        padding: 60px 0;
    }

    .featured-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 25px;
    }

    @media (max-width: 1000px) {
        .featured-grid {
            grid-template-columns: 1fr;
        }
    }

    .featured-main {
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        height: 450px;
    }

    .featured-main .card-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: transform 0.5s;
    }

    .featured-main:hover .card-bg {
        transform: scale(1.05);
    }

    .featured-main .card-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0.2) 60%);
    }

    .featured-main .card-content {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 40px;
        z-index: 10;
    }

    .card-category {
        display: inline-block;
        padding: 6px 14px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        border-radius: 20px;
        margin-bottom: 15px;
    }

    .featured-main .card-title {
        color: #fff;
        font-size: 28px;
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 15px;
    }

    .featured-main .card-title a {
        color: inherit;
        text-decoration: none;
    }

    .featured-main .card-title a:hover {
        color: #ff6b35;
    }

    .card-meta {
        display: flex;
        align-items: center;
        gap: 20px;
        color: #aaa;
        font-size: 14px;
    }

    .card-meta span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .card-meta i {
        color: #ff6b35;
    }

    .featured-sidebar {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .featured-small {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        height: calc(50% - 10px);
        min-height: 210px;
    }

    .featured-small .card-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: transform 0.5s;
    }

    .featured-small:hover .card-bg {
        transform: scale(1.1);
    }

    .featured-small .card-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.1) 100%);
    }

    .featured-small .card-content {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 25px;
        z-index: 10;
    }

    .featured-small .card-title {
        color: #fff;
        font-size: 18px;
        font-weight: 600;
        line-height: 1.4;
        margin-bottom: 10px;
    }

    .featured-small .card-title a {
        color: inherit;
        text-decoration: none;
    }

    .featured-small .card-title a:hover {
        color: #ff6b35;
    }

    /* Latest Articles */
    .latest-section {
        padding: 60px 0;
        background: rgba(0, 0, 0, 0.3);
    }

    .articles-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }

    @media (max-width: 1000px) {
        .articles-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 600px) {
        .articles-grid {
            grid-template-columns: 1fr;
        }
    }

    .article-card {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
        transition: all 0.3s;
    }

    .article-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        border-color: rgba(255, 107, 53, 0.3);
    }

    .article-card-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .article-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }

    .article-card:hover .article-card-image img {
        transform: scale(1.1);
    }

    .article-card-content {
        padding: 25px;
    }

    .article-card-title {
        color: #fff;
        font-size: 18px;
        font-weight: 600;
        line-height: 1.4;
        margin-bottom: 15px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .article-card-title a {
        color: inherit;
        text-decoration: none;
    }

    .article-card-title a:hover {
        color: #ff6b35;
    }

    .article-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 15px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        color: #666;
        font-size: 13px;
    }

    .article-card-footer span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .article-card-footer i {
        color: #ff6b35;
    }

    /* Trending Section */
    .trending-section {
        padding: 60px 0;
    }

    .trending-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
    }

    @media (max-width: 1100px) {
        .trending-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 600px) {
        .trending-grid {
            grid-template-columns: 1fr;
        }
    }

    .trending-card {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        height: 280px;
    }

    .trending-card .card-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: transform 0.5s;
    }

    .trending-card:hover .card-bg {
        transform: scale(1.1);
    }

    .trending-card .card-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0.1) 100%);
    }

    .trending-card .card-content {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 25px;
        z-index: 10;
    }

    .trending-number {
        position: absolute;
        top: 15px;
        left: 15px;
        width: 35px;
        height: 35px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 16px;
        font-weight: 800;
        z-index: 10;
    }

    .trending-card .card-title {
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .trending-card .card-title a {
        color: inherit;
        text-decoration: none;
    }

    .trending-card .card-title a:hover {
        color: #ff6b35;
    }

    /* Stats Section */
    .stats-section {
        padding: 60px 0;
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.1), rgba(247, 147, 30, 0.05));
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
    }

    @media (max-width: 800px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .stat-card {
        text-align: center;
        padding: 30px;
        background: rgba(20, 20, 35, 0.8);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 24px;
        color: #fff;
    }

    .stat-number {
        color: #fff;
        font-size: 36px;
        font-weight: 800;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #888;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Categories Section */
    .categories-section {
        padding: 60px 0;
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 20px;
    }

    @media (max-width: 1000px) {
        .categories-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 600px) {
        .categories-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .category-card {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        padding: 30px 20px;
        text-align: center;
        transition: all 0.3s;
        text-decoration: none;
    }

    .category-card:hover {
        transform: translateY(-5px);
        border-color: rgba(255, 107, 53, 0.3);
        background: rgba(255, 107, 53, 0.1);
    }

    .category-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 22px;
        color: #fff;
    }

    .category-name {
        color: #fff;
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .category-count {
        color: #666;
        font-size: 13px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero-section {
            height: 500px;
        }

        .hero-slide {
            height: 500px;
        }

        .hero-title {
            font-size: 32px;
        }

        .hero-desc {
            font-size: 16px;
        }

        .section-title h2 {
            font-size: 22px;
        }

        .featured-main {
            height: 350px;
        }

        .featured-main .card-title {
            font-size: 22px;
        }
    }
</style>

<div class="homepage">
    <!-- Hero Section -->
    <section class="hero-section">
        <?php if (!empty($featured_articles[0])): 
            $hero = $featured_articles[0]; 
            $hero_img = !empty($hero['featured_image']) ? 'uploads/' . $hero['featured_image'] : 'img/banner1.jpg'; 
        ?>
        <div class="hero-bg" style="background-image: url('<?= htmlspecialchars($hero_img) ?>');"></div>
        <?php else: ?>
        <div class="hero-bg" style="background-image: url('img/banner1.jpg');"></div>
        <?php endif; ?>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-text">
                <span class="hero-badge"><i class="fas fa-fire"></i> Hot News</span>
                <h1 class="hero-title">Cập nhật <span>Tin Tức Game</span> Mới Nhất</h1>
                <p class="hero-desc">Khám phá thế giới game đầy hấp dẫn với những bài viết chất lượng, đánh giá chuyên sâu và tin tức nóng hổi từ cộng đồng game thủ.</p>
                <a href="categories.php" class="hero-btn">
                    <i class="fas fa-gamepad"></i> Khám phá ngay
                </a>
            </div>
        </div>
    </section>

    <!-- News Ticker -->
    <div class="news-ticker-section">
        <div class="ticker-wrapper">
            <div class="ticker-label">
                <i class="fas fa-bolt"></i> Tin mới
            </div>
            <div class="ticker-content">
                <div class="ticker-track">
                    <?php
                    $ticker_sql = "SELECT title, slug FROM articles WHERE status='published' ORDER BY created_at DESC LIMIT 10";
                    $ticker_news = $conn->query($ticker_sql)->fetch_all(MYSQLI_ASSOC);
                    foreach ($ticker_news as $news):
                    ?>
                        <div class="ticker-item">
                            <span class="ticker-dot"></span>
                            <a href="article.php?slug=<?= urlencode($news['slug']) ?>"><?= htmlspecialchars($news['title']) ?></a>
                        </div>
                    <?php endforeach; ?>
                    <?php foreach ($ticker_news as $news): ?>
                        <div class="ticker-item">
                            <span class="ticker-dot"></span>
                            <a href="article.php?slug=<?= urlencode($news['slug']) ?>"><?= htmlspecialchars($news['title']) ?></a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Section -->
    <section class="featured-section">
        <div class="section-container">
            <div class="section-header">
                <div class="section-title">
                    <div class="section-title-icon"><i class="fas fa-fire"></i></div>
                    <h2>Bài viết nổi bật</h2>
                </div>
                <a href="categories.php" class="section-link">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>

            <?php if (!empty($featured_articles)): ?>
                <div class="featured-grid">
                    <!-- Main Featured -->
                    <?php $main = $featured_articles[0]; $main_img = !empty($main['featured_image']) ? '../uploads/' . basename($main['featured_image']) : 'img/default.jpg'; ?>
                    <div class="featured-main">
                        <div class="card-bg" style="background-image: url('<?= htmlspecialchars($main_img) ?>');"></div>
                        <div class="card-overlay"></div>
                        <div class="card-content">
                            <span class="card-category"><?= htmlspecialchars($main['category_name'] ?? 'Tin tức') ?></span>
                            <h3 class="card-title">
                                <a href="article.php?id=<?= $main['article_id'] ?>"><?= htmlspecialchars($main['title']) ?></a>
                            </h3>
                            <div class="card-meta">
                                <span><i class="fas fa-user"></i> <?= htmlspecialchars($main['author_name'] ?? 'Admin') ?></span>
                                <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($main['created_at'])) ?></span>
                                <span><i class="fas fa-eye"></i> <?= number_format($main['views']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Featured -->
                    <div class="featured-sidebar">
                        <?php for ($i = 1; $i <= 2; $i++): ?>
                            <?php if (isset($featured_articles[$i])): ?>
                                <?php $item = $featured_articles[$i]; $item_img = !empty($item['featured_image']) ? '../uploads/' . basename($item['featured_image']) : 'img/default.jpg'; ?>
                                <div class="featured-small">
                                    <div class="card-bg" style="background-image: url('<?= htmlspecialchars($item_img) ?>');"></div>
                                    <div class="card-overlay"></div>
                                    <div class="card-content">
                                        <span class="card-category"><?= htmlspecialchars($item['category_name'] ?? 'Tin tức') ?></span>
                                        <h4 class="card-title">
                                            <a href="article.php?id=<?= $item['article_id'] ?>"><?= htmlspecialchars($item['title']) ?></a>
                                        </h4>
                                        <div class="card-meta">
                                            <span><i class="fas fa-eye"></i> <?= number_format($item['views']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Latest Articles -->
    <section class="latest-section">
        <div class="section-container">
            <div class="section-header">
                <div class="section-title">
                    <div class="section-title-icon"><i class="fas fa-newspaper"></i></div>
                    <h2>Bài viết mới nhất</h2>
                </div>
                <a href="categories.php" class="section-link">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="articles-grid">
                <?php foreach ($latest_articles as $article): ?>
                    <?php $img = !empty($article['featured_image']) ? '../uploads/' . basename($article['featured_image']) : 'img/default.jpg'; ?>
                    <div class="article-card">
                        <div class="article-card-image">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                            <span class="card-category" style="position: absolute; top: 15px; left: 15px;"><?= htmlspecialchars($article['category_name'] ?? 'Tin tức') ?></span>
                        </div>
                        <div class="article-card-content">
                            <h3 class="article-card-title">
                                <a href="article.php?id=<?= $article['article_id'] ?>"><?= htmlspecialchars($article['title']) ?></a>
                            </h3>
                            <div class="article-card-footer">
                                <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($article['created_at'])) ?></span>
                                <span><i class="fas fa-eye"></i> <?= number_format($article['views']) ?></span>
                                <span><i class="fas fa-comments"></i> <?= $article['comment_count'] ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Trending Section -->
    <?php if (!empty($trending_articles)): ?>
        <section class="trending-section">
            <div class="section-container">
                <div class="section-header">
                    <div class="section-title">
                        <div class="section-title-icon"><i class="fas fa-chart-line"></i></div>
                        <h2>Đang thịnh hành</h2>
                    </div>
                </div>

                <div class="trending-grid">
                    <?php $num = 1; foreach ($trending_articles as $trend): ?>
                        <?php $img = !empty($trend['featured_image']) ? '../uploads/' . basename($trend['featured_image']) : 'img/default.jpg'; ?>
                        <div class="trending-card">
                            <div class="card-bg" style="background-image: url('<?= htmlspecialchars($img) ?>');"></div>
                            <div class="card-overlay"></div>
                            <span class="trending-number"><?= $num++ ?></span>
                            <div class="card-content">
                                <span class="card-category"><?= htmlspecialchars($trend['category_name'] ?? 'Tin tức') ?></span>
                                <h4 class="card-title">
                                    <a href="article.php?id=<?= $trend['article_id'] ?>"><?= htmlspecialchars($trend['title']) ?></a>
                                </h4>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="section-container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-newspaper"></i></div>
                    <div class="stat-number"><?= number_format($stats['total_articles'] ?? 0) ?></div>
                    <div class="stat-label">Bài viết</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-number"><?= number_format($stats['total_users'] ?? 0) ?></div>
                    <div class="stat-label">Thành viên</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-eye"></i></div>
                    <div class="stat-number"><?= number_format($stats['total_views'] ?? 0) ?></div>
                    <div class="stat-label">Lượt xem</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-comments"></i></div>
                    <div class="stat-number"><?= number_format($stats['total_comments'] ?? 0) ?></div>
                    <div class="stat-label">Bình luận</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="section-container">
            <div class="section-header">
                <div class="section-title">
                    <div class="section-title-icon"><i class="fas fa-folder"></i></div>
                    <h2>Danh mục</h2>
                </div>
                <a href="categories.php" class="section-link">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="categories-grid">
                <?php 
                $cat_icons = ['fas fa-gamepad', 'fas fa-trophy', 'fas fa-star', 'fas fa-bolt', 'fas fa-fire', 'fas fa-gem'];
                $i = 0;
                foreach ($categories as $cat): 
                ?>
                    <a href="categories.php?cat=<?= $cat['category_id'] ?>" class="category-card">
                        <div class="category-icon"><i class="<?= $cat_icons[$i % count($cat_icons)] ?>"></i></div>
                        <div class="category-name"><?= htmlspecialchars($cat['name']) ?></div>
                        <div class="category-count"><?= $cat['article_count'] ?> bài viết</div>
                    </a>
                <?php $i++; endforeach; ?>
            </div>
        </div>
    </section>
</div>



<?php include 'footer.php'; ?>
