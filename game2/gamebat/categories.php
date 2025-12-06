<?php
include 'ketnoi.php';

// Lấy danh mục hiện tại
$category_id = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
$current_category = null;

if ($category_id > 0) {
    $cat_stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
    $cat_stmt->bind_param("i", $category_id);
    $cat_stmt->execute();
    $current_category = $cat_stmt->get_result()->fetch_assoc();
    $cat_stmt->close();
}

// Lấy tất cả danh mục với số bài viết
$categories_sql = "SELECT c.*, COUNT(a.article_id) as article_count 
    FROM categories c 
    LEFT JOIN articles a ON c.category_id = a.category_id AND a.status = 'published'
    GROUP BY c.category_id 
    ORDER BY c.name ASC";
$all_categories = $conn->query($categories_sql)->fetch_all(MYSQLI_ASSOC);

// Phân trang
$limit = 9;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Đếm tổng bài viết
$count_sql = "SELECT COUNT(*) AS total FROM articles WHERE status='published'";
if ($category_id > 0) {
    $count_sql .= " AND category_id = $category_id";
}
$total_articles = $conn->query($count_sql)->fetch_assoc()['total'] ?? 0;
$total_pages = max(1, ceil($total_articles / $limit));

// Lấy bài viết
$sql = "SELECT a.*, c.name AS category_name, u.display_name AS author_name
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.category_id
    LEFT JOIN users u ON a.author_id = u.user_id
    WHERE a.status='published'";
if ($category_id > 0) {
    $sql .= " AND a.category_id = $category_id";
}
$sql .= " ORDER BY a.created_at DESC LIMIT $limit OFFSET $offset";
$articles = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Bài viết nổi bật (nhiều view nhất)
$featured_sql = "SELECT a.*, c.name AS category_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.category_id
    WHERE a.status='published'";
if ($category_id > 0) {
    $featured_sql .= " AND a.category_id = $category_id";
}
$featured_sql .= " ORDER BY a.views DESC LIMIT 1";
$featured_article = $conn->query($featured_sql)->fetch_assoc();

include 'header.php';
?>

<style>
    /* ===== CATEGORIES PAGE STYLES ===== */
    .categories-page {
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
        min-height: 100vh;
        padding: 40px 0 80px;
    }

    .categories-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Page Header */
    .page-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .page-header h1 {
        color: #fff;
        font-size: 42px;
        font-weight: 800;
        margin-bottom: 15px;
    }

    .page-header p {
        color: #888;
        font-size: 18px;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Category Tabs */
    .category-tabs {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 12px;
        margin-bottom: 50px;
    }

    .category-tab {
        padding: 12px 24px;
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 30px;
        color: #888;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .category-tab:hover {
        background: rgba(255, 107, 53, 0.1);
        border-color: rgba(255, 107, 53, 0.3);
        color: #ff6b35;
    }

    .category-tab.active {
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        border-color: transparent;
        color: #fff;
    }

    .category-tab .count {
        background: rgba(0, 0, 0, 0.2);
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 12px;
    }

    .category-tab.active .count {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Featured Article */
    .featured-article {
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        margin-bottom: 50px;
        height: 450px;
    }

    .featured-article-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: transform 0.5s;
    }

    .featured-article:hover .featured-article-bg {
        transform: scale(1.05);
    }

    .featured-article-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.3) 50%, rgba(0, 0, 0, 0.1) 100%);
    }

    .featured-article-content {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 40px;
        z-index: 10;
    }

    .featured-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        border-radius: 20px;
        margin-bottom: 20px;
    }

    .featured-badge i {
        font-size: 14px;
    }

    .featured-article-title {
        color: #fff;
        font-size: 36px;
        font-weight: 800;
        line-height: 1.3;
        margin-bottom: 15px;
        max-width: 700px;
    }

    .featured-article-title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.3s;
    }

    .featured-article-title a:hover {
        color: #ff6b35;
    }

    .featured-article-meta {
        display: flex;
        align-items: center;
        gap: 25px;
        color: #aaa;
        font-size: 14px;
    }

    .featured-article-meta span {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .featured-article-meta i {
        color: #ff6b35;
    }

    /* Articles Grid */
    .articles-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin-bottom: 50px;
    }

    @media (max-width: 1100px) {
        .articles-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .articles-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Article Card */
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

    .article-card-category {
        position: absolute;
        top: 15px;
        left: 15px;
        padding: 6px 14px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        border-radius: 20px;
    }

    .article-card-content {
        padding: 25px;
    }

    .article-card-title {
        color: #fff;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.4;
        margin-bottom: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .article-card-title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.3s;
    }

    .article-card-title a:hover {
        color: #ff6b35;
    }

    .article-card-excerpt {
        color: #888;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .article-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    .article-card-author {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .article-card-author img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
    }

    .article-card-author span {
        color: #aaa;
        font-size: 13px;
    }

    .article-card-stats {
        display: flex;
        align-items: center;
        gap: 15px;
        color: #666;
        font-size: 13px;
    }

    .article-card-stats span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .article-card-stats i {
        color: #ff6b35;
        font-size: 12px;
    }

    /* Pagination */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        gap: 8px;
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
        background: rgba(255, 107, 53, 0.1);
        border-color: rgba(255, 107, 53, 0.3);
        color: #ff6b35;
    }

    .pagination-btn.active {
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        border-color: transparent;
        color: #fff;
    }

    .pagination-btn.disabled {
        opacity: 0.5;
        pointer-events: none;
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

    /* Sidebar */
    .sidebar-section {
        display: flex;
        flex-direction: column;
        gap: 30px;
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
        color: #ff6b35;
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

    /* Search Box */
    .search-box {
        position: relative;
    }

    .search-box input {
        width: 100%;
        padding: 15px 20px 15px 50px;
        background: rgba(0, 0, 0, 0.3);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        color: #fff;
        font-size: 15px;
        outline: none;
        transition: all 0.3s;
    }

    .search-box input:focus {
        border-color: #ff6b35;
    }

    .search-box input::placeholder {
        color: #555;
    }

    .search-box i {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
    }

    .search-box button {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        background: #ff6b35;
        border: none;
        border-radius: 10px;
        color: #fff;
        cursor: pointer;
        transition: all 0.3s;
    }

    .search-box button:hover {
        background: #e55a2b;
    }

    /* Popular Articles */
    .popular-item {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .popular-item:last-child {
        border-bottom: none;
    }

    .popular-number {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .popular-info {
        flex: 1;
    }

    .popular-title {
        color: #fff;
        font-size: 14px;
        font-weight: 500;
        line-height: 1.4;
        margin-bottom: 5px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .popular-title a {
        color: inherit;
        text-decoration: none;
    }

    .popular-title a:hover {
        color: #ff6b35;
    }

    .popular-meta {
        color: #666;
        font-size: 12px;
    }

    /* Categories List */
    .categories-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .category-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 15px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 10px;
        color: #aaa;
        text-decoration: none;
        transition: all 0.3s;
    }

    .category-list-item:hover {
        background: rgba(255, 107, 53, 0.1);
        color: #ff6b35;
    }

    .category-list-item.active {
        background: rgba(255, 107, 53, 0.15);
        color: #ff6b35;
    }

    .category-list-item span {
        background: rgba(255, 255, 255, 0.1);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
    }

    /* Layout */
    .main-layout {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 40px;
    }

    @media (max-width: 1100px) {
        .main-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 32px;
        }

        .featured-article {
            height: 350px;
        }

        .featured-article-title {
            font-size: 24px;
        }

        .featured-article-content {
            padding: 25px;
        }
    }
</style>

<div class="categories-page">
    <div class="categories-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><?= $current_category ? htmlspecialchars($current_category['name']) : 'Tất cả bài viết' ?></h1>
            <p><?= $current_category ? htmlspecialchars($current_category['description'] ?? 'Khám phá các bài viết trong danh mục này') : 'Khám phá tất cả bài viết mới nhất từ cộng đồng game thủ' ?></p>
        </div>

        <!-- Category Tabs -->
        <div class="category-tabs">
            <a href="categories.php" class="category-tab <?= $category_id == 0 ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i>
                Tất cả
                <span class="count"><?= $total_articles ?></span>
            </a>
            <?php foreach ($all_categories as $cat): ?>
                <a href="categories.php?cat=<?= $cat['category_id'] ?>" class="category-tab <?= $category_id == $cat['category_id'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                    <span class="count"><?= $cat['article_count'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Featured Article -->
        <?php if ($featured_article && $page == 1): ?>
            <?php $featured_img = !empty($featured_article['featured_image']) ? '../uploads/' . basename($featured_article['featured_image']) : 'img/default.jpg'; ?>
            <div class="featured-article">
                <div class="featured-article-bg" style="background-image: url('<?= htmlspecialchars($featured_img) ?>');"></div>
                <div class="featured-article-overlay"></div>
                <div class="featured-article-content">
                    <span class="featured-badge">
                        <i class="fas fa-fire"></i>
                        Nổi bật
                    </span>
                    <h2 class="featured-article-title">
                        <a href="article.php?id=<?= $featured_article['article_id'] ?>"><?= htmlspecialchars($featured_article['title']) ?></a>
                    </h2>
                    <div class="featured-article-meta">
                        <span><i class="fas fa-folder"></i> <?= htmlspecialchars($featured_article['category_name'] ?? 'Chưa phân loại') ?></span>
                        <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($featured_article['created_at'])) ?></span>
                        <span><i class="fas fa-eye"></i> <?= number_format($featured_article['views']) ?> lượt xem</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="main-layout">
            <!-- Articles Grid -->
            <div class="articles-section">
                <?php if (!empty($articles)): ?>
                    <div class="articles-grid">
                        <?php foreach ($articles as $article): ?>
                            <?php 
                            $img = !empty($article['featured_image']) ? '../uploads/' . basename($article['featured_image']) : 'img/default.jpg';
                            $excerpt = mb_substr(strip_tags($article['excerpt'] ?? $article['content']), 0, 120) . '...';
                            ?>
                            <div class="article-card">
                                <div class="article-card-image">
                                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                                    <span class="article-card-category"><?= htmlspecialchars($article['category_name'] ?? 'Khác') ?></span>
                                </div>
                                <div class="article-card-content">
                                    <h3 class="article-card-title">
                                        <a href="article.php?id=<?= $article['article_id'] ?>"><?= htmlspecialchars($article['title']) ?></a>
                                    </h3>
                                    <p class="article-card-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                                    <div class="article-card-footer">
                                        <div class="article-card-author">
                                            <img src="img/default-avatar.png" alt="Author">
                                            <span><?= htmlspecialchars($article['author_name'] ?? 'Admin') ?></span>
                                        </div>
                                        <div class="article-card-stats">
                                            <span><i class="fas fa-eye"></i> <?= number_format($article['views']) ?></span>
                                            <span><i class="fas fa-calendar"></i> <?= date('d/m', strtotime($article['created_at'])) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination-wrapper">
                            <a href="?page=<?= max(1, $page - 1) ?><?= $category_id > 0 ? '&cat=' . $category_id : '' ?>" class="pagination-btn <?= $page <= 1 ? 'disabled' : '' ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?= $i ?><?= $category_id > 0 ? '&cat=' . $category_id : '' ?>" class="pagination-btn <?= $i == $page ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            <a href="?page=<?= min($total_pages, $page + 1) ?><?= $category_id > 0 ? '&cat=' . $category_id : '' ?>" class="pagination-btn <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-newspaper"></i>
                        <h3>Chưa có bài viết</h3>
                        <p>Danh mục này hiện chưa có bài viết nào.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="sidebar-section">
                <!-- Search -->
                <div class="sidebar-card">
                    <div class="sidebar-body">
                        <form action="search.php" method="GET" class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="keyword" placeholder="Tìm kiếm bài viết...">
                            <button type="submit"><i class="fas fa-arrow-right"></i></button>
                        </form>
                    </div>
                </div>

                <!-- Categories -->
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <i class="fas fa-folder"></i>
                        <h3>Danh mục</h3>
                    </div>
                    <div class="sidebar-body">
                        <div class="categories-list">
                            <a href="categories.php" class="category-list-item <?= $category_id == 0 ? 'active' : '' ?>">
                                Tất cả bài viết
                                <span><?= array_sum(array_column($all_categories, 'article_count')) ?></span>
                            </a>
                            <?php foreach ($all_categories as $cat): ?>
                                <a href="categories.php?cat=<?= $cat['category_id'] ?>" class="category-list-item <?= $category_id == $cat['category_id'] ? 'active' : '' ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                    <span><?= $cat['article_count'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Popular Articles -->
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <i class="fas fa-fire"></i>
                        <h3>Bài viết phổ biến</h3>
                    </div>
                    <div class="sidebar-body">
                        <?php
                        $popular = $conn->query("SELECT article_id, title, views, created_at FROM articles WHERE status='published' ORDER BY views DESC LIMIT 5");
                        $popular_articles = $popular ? $popular->fetch_all(MYSQLI_ASSOC) : [];
                        $num = 1;
                        foreach ($popular_articles as $pop):
                        ?>
                            <div class="popular-item">
                                <span class="popular-number"><?= $num++ ?></span>
                                <div class="popular-info">
                                    <h4 class="popular-title">
                                        <a href="article.php?id=<?= $pop['article_id'] ?>"><?= htmlspecialchars($pop['title']) ?></a>
                                    </h4>
                                    <span class="popular-meta">
                                        <i class="fas fa-eye"></i> <?= number_format($pop['views']) ?> lượt xem
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
