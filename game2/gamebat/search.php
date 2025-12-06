<?php
require_once 'ketnoi.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Phân trang
$limit = 9;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$results = [];
$total_results = 0;

if (!empty($keyword)) {
    $search = "%$keyword%";
    
    // Đếm tổng kết quả - tìm theo tiêu đề, nội dung, danh mục, tags
    $count_sql = "SELECT COUNT(DISTINCT a.article_id) as total 
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN article_tags at ON a.article_id = at.article_id
        LEFT JOIN tags t ON at.tag_id = t.tag_id
        WHERE a.status = 'published' 
        AND (a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ? OR c.name LIKE ? OR t.name LIKE ?)";
    
    if ($category_filter > 0) {
        $count_sql .= " AND a.category_id = ?";
    }
    
    $count_stmt = $conn->prepare($count_sql);
    if ($category_filter > 0) {
        $count_stmt->bind_param("sssssi", $search, $search, $search, $search, $search, $category_filter);
    } else {
        $count_stmt->bind_param("sssss", $search, $search, $search, $search, $search);
    }
    $count_stmt->execute();
    $total_results = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();
    
    $total_pages = max(1, ceil($total_results / $limit));
    
    // Lấy kết quả - tìm theo tiêu đề, nội dung, danh mục, tags
    $order = $sort === 'oldest' ? 'ASC' : ($sort === 'views' ? 'DESC' : 'DESC');
    $order_by = $sort === 'views' ? 'a.views' : 'a.created_at';
    
    $sql = "SELECT DISTINCT a.*, c.name AS category_name, u.display_name AS author_name,
            GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') AS tags
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN users u ON a.author_id = u.user_id
        LEFT JOIN article_tags at ON a.article_id = at.article_id
        LEFT JOIN tags t ON at.tag_id = t.tag_id
        WHERE a.status = 'published' 
        AND (a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ? OR c.name LIKE ? OR t.name LIKE ?)";
    
    if ($category_filter > 0) {
        $sql .= " AND a.category_id = ?";
    }
    $sql .= " GROUP BY a.article_id ORDER BY $order_by $order LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    if ($category_filter > 0) {
        $stmt->bind_param("sssssiii", $search, $search, $search, $search, $search, $category_filter, $limit, $offset);
    } else {
        $stmt->bind_param("sssssii", $search, $search, $search, $search, $search, $limit, $offset);
    }
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Lấy danh mục cho filter
$categories = $conn->query("SELECT category_id, name FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Lấy tags cho filter
$tags_list = $conn->query("SELECT tag_id, name FROM tags ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Lấy từ khóa phổ biến từ tags
$popular_tags = $conn->query("SELECT t.name FROM tags t 
    INNER JOIN article_tags at ON t.tag_id = at.tag_id 
    GROUP BY t.tag_id ORDER BY COUNT(at.article_id) DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
$popular_keywords = array_column($popular_tags, 'name');
if (empty($popular_keywords)) {
    $popular_keywords = ['GTA 6', 'Elden Ring', 'Valorant', 'League of Legends', 'PUBG', 'Minecraft', 'FIFA 24', 'Genshin Impact'];
}

include 'header.php';
?>

<style>
    /* ===== SEARCH PAGE STYLES ===== */
    .search-page {
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
        min-height: 100vh;
        padding: 40px 0 80px;
        position: relative;
    }

    .search-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background:
            radial-gradient(circle at 20% 30%, rgba(236, 72, 153, 0.08) 0%, transparent 40%),
            radial-gradient(circle at 80% 70%, rgba(219, 39, 119, 0.06) 0%, transparent 40%);
        pointer-events: none;
    }

    .search-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
        position: relative;
        z-index: 10;
    }

    /* Search Header */
    .search-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .search-header-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #ec4899, #db2777);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        box-shadow: 0 15px 40px rgba(236, 72, 153, 0.3);
    }

    .search-header-icon i {
        font-size: 36px;
        color: #fff;
    }

    .search-header h1 {
        color: #fff;
        font-size: 36px;
        font-weight: 800;
        margin-bottom: 15px;
    }

    /* Search Box */
    .search-box-wrapper {
        max-width: 700px;
        margin: 0 auto 30px;
    }

    .search-box {
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 20px 60px 20px 25px;
        background: rgba(20, 20, 35, 0.98);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        color: #fff;
        font-size: 18px;
        outline: none;
        transition: all 0.3s;
    }

    .search-input:focus {
        border-color: #ec4899;
        box-shadow: 0 0 30px rgba(236, 72, 153, 0.2);
    }

    .search-input::placeholder {
        color: #666;
    }

    .search-btn {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #ec4899, #db2777);
        border: none;
        border-radius: 12px;
        color: #fff;
        font-size: 20px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .search-btn:hover {
        transform: translateY(-50%) scale(1.05);
    }

    /* Popular Keywords */
    .popular-keywords {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        margin-bottom: 40px;
    }

    .keyword-tag {
        padding: 8px 18px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        color: #888;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.3s;
    }

    .keyword-tag:hover {
        background: rgba(236, 72, 153, 0.15);
        border-color: rgba(236, 72, 153, 0.3);
        color: #ec4899;
    }

    /* Results Info */
    .results-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .results-count {
        color: #fff;
        font-size: 18px;
    }

    .results-count span {
        color: #ec4899;
        font-weight: 700;
    }

    /* Filters */
    .filters {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .filter-select {
        padding: 10px 20px;
        background: rgba(20, 20, 35, 0.98);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        color: #fff;
        font-size: 14px;
        cursor: pointer;
        outline: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2'%3E%3Cpolyline points='6,9 12,15 18,9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 16px;
        padding-right: 40px;
    }

    .filter-select:focus {
        border-color: #ec4899;
    }

    .filter-select option {
        background: #1a1a2e;
        color: #fff;
    }

    /* Results Grid */
    .results-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin-bottom: 50px;
    }

    @media (max-width: 1000px) {
        .results-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 600px) {
        .results-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Result Card */
    .result-card {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
        transition: all 0.3s;
    }

    .result-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        border-color: rgba(236, 72, 153, 0.3);
    }

    .result-card-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .result-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }

    .result-card:hover .result-card-image img {
        transform: scale(1.1);
    }

    .result-card-category {
        position: absolute;
        top: 15px;
        left: 15px;
        padding: 6px 14px;
        background: linear-gradient(135deg, #ec4899, #db2777);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        border-radius: 20px;
    }

    .result-card-content {
        padding: 25px;
    }

    .result-card-title {
        color: #fff;
        font-size: 18px;
        font-weight: 600;
        line-height: 1.4;
        margin-bottom: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .result-card-title a {
        color: inherit;
        text-decoration: none;
    }

    .result-card-title a:hover {
        color: #ec4899;
    }

    .result-card-excerpt {
        color: #888;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 15px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .result-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 15px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        color: #666;
        font-size: 13px;
    }

    .result-card-footer span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .result-card-footer i {
        color: #ec4899;
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
        background: rgba(236, 72, 153, 0.1);
        border-color: rgba(236, 72, 153, 0.3);
        color: #ec4899;
    }

    .pagination-btn.active {
        background: linear-gradient(135deg, #ec4899, #db2777);
        border-color: transparent;
        color: #fff;
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
        margin-bottom: 25px;
    }

    .empty-state .suggestions {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
    }

    /* No Keyword State */
    .no-keyword-state {
        text-align: center;
        padding: 60px 20px;
    }

    .no-keyword-state h2 {
        color: #fff;
        font-size: 28px;
        margin-bottom: 15px;
    }

    .no-keyword-state p {
        color: #888;
        font-size: 16px;
        margin-bottom: 30px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .search-header h1 {
            font-size: 28px;
        }

        .search-input {
            font-size: 16px;
            padding: 16px 55px 16px 20px;
        }

        .results-info {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="search-page">
    <div class="search-container">
        <!-- Header -->
        <div class="search-header">
            <div class="search-header-icon">
                <i class="fas fa-search"></i>
            </div>
            <h1>Tìm kiếm bài viết</h1>
        </div>

        <!-- Search Box -->
        <div class="search-box-wrapper">
            <form method="GET" class="search-box">
                <input type="text" name="keyword" class="search-input" placeholder="Nhập từ khóa tìm kiếm..." value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <!-- Popular Keywords -->
        <div class="popular-keywords">
            <span style="color: #888; margin-right: 10px;"><i class="fas fa-fire" style="color:#ec4899;"></i> Thẻ phổ biến:</span>
            <?php foreach ($popular_keywords as $kw): ?>
                <a href="?keyword=<?= urlencode($kw) ?>" class="keyword-tag">#<?= htmlspecialchars($kw) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($keyword)): ?>
            <!-- Results Info & Filters -->
            <div class="results-info">
                <div class="results-count">
                    Tìm thấy <span><?= number_format($total_results) ?></span> kết quả cho "<span><?= htmlspecialchars($keyword) ?></span>"
                    <div style="font-size:13px;color:#888;margin-top:5px;">
                        <i class="fas fa-info-circle"></i> Tìm kiếm theo: tiêu đề, nội dung, danh mục, thẻ game
                    </div>
                </div>
                <div class="filters">
                    <select class="filter-select" onchange="applyFilter('category', this.value)">
                        <option value="0">📁 Tất cả danh mục</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>" <?= $category_filter == $cat['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select class="filter-select" onchange="applyFilter('sort', this.value)">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>🕐 Mới nhất</option>
                        <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>📅 Cũ nhất</option>
                        <option value="views" <?= $sort === 'views' ? 'selected' : '' ?>>👁️ Nhiều lượt xem</option>
                    </select>
                </div>
            </div>

            <?php if (!empty($results)): ?>
                <!-- Results Grid -->
                <div class="results-grid">
                    <?php foreach ($results as $article): ?>
                        <?php 
                        $img = !empty($article['featured_image']) ? '../uploads/' . basename($article['featured_image']) : 'img/default.jpg';
                        $excerpt = mb_substr(strip_tags($article['excerpt'] ?? $article['content']), 0, 100) . '...';
                        ?>
                        <div class="result-card">
                            <div class="result-card-image">
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                                <span class="result-card-category"><?= htmlspecialchars($article['category_name'] ?? 'Tin tức') ?></span>
                            </div>
                            <div class="result-card-content">
                                <h3 class="result-card-title">
                                    <a href="article.php?id=<?= $article['article_id'] ?>"><?= htmlspecialchars($article['title']) ?></a>
                                </h3>
                                <p class="result-card-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                                <?php if (!empty($article['tags'])): ?>
                                <div style="margin-bottom:12px;">
                                    <?php foreach (explode(', ', $article['tags']) as $tag): ?>
                                        <span style="display:inline-block;padding:4px 10px;background:rgba(236,72,153,0.15);border-radius:12px;font-size:11px;color:#ec4899;margin-right:5px;margin-bottom:5px;">
                                            #<?= htmlspecialchars($tag) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                <div class="result-card-footer">
                                    <span><i class="fas fa-user"></i> <?= htmlspecialchars($article['author_name'] ?? 'Admin') ?></span>
                                    <span><i class="fas fa-eye"></i> <?= number_format($article['views']) ?></span>
                                    <span><i class="fas fa-calendar"></i> <?= date('d/m', strtotime($article['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-wrapper">
                        <a href="?keyword=<?= urlencode($keyword) ?>&category=<?= $category_filter ?>&sort=<?= $sort ?>&page=<?= max(1, $page - 1) ?>" class="pagination-btn <?= $page <= 1 ? 'disabled' : '' ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php for ($i = 1; $i <= min($total_pages, 5); $i++): ?>
                            <a href="?keyword=<?= urlencode($keyword) ?>&category=<?= $category_filter ?>&sort=<?= $sort ?>&page=<?= $i ?>" class="pagination-btn <?= $i == $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        <a href="?keyword=<?= urlencode($keyword) ?>&category=<?= $category_filter ?>&sort=<?= $sort ?>&page=<?= min($total_pages, $page + 1) ?>" class="pagination-btn <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- No Results -->
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3>Không tìm thấy kết quả</h3>
                    <p>Không có bài viết nào phù hợp với từ khóa "<?= htmlspecialchars($keyword) ?>"</p>
                    <div class="suggestions">
                        <span style="color: #888; margin-right: 10px;">Thử tìm:</span>
                        <?php foreach (array_slice($popular_keywords, 0, 4) as $kw): ?>
                            <a href="?keyword=<?= urlencode($kw) ?>" class="keyword-tag"><?= htmlspecialchars($kw) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- No Keyword -->
            <div class="no-keyword-state">
                <h2>Bạn muốn tìm gì?</h2>
                <p>Nhập từ khóa vào ô tìm kiếm hoặc chọn một trong các từ khóa phổ biến bên trên</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function applyFilter(type, value) {
        const url = new URL(window.location.href);
        url.searchParams.set(type, value);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }
</script>

<?php include 'footer.php'; ?>
