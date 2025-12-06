<?php
// Bật hiển thị lỗi PHP (Chỉ dùng khi DEV)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'ketnoi.php';

// ==========================================================
// 🔒 BẢO MẬT: XỬ LÝ CSRF TOKEN
// ==========================================================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = (string)$_SESSION['csrf_token'];

function check_csrf()
{
    global $csrf_token;
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        header('Content-Type: application/json', true, 403);
        echo json_encode(['success' => false, 'message' => 'Lỗi bảo mật: Token CSRF không hợp lệ.']);
        die();
    }
}

// ==========================================================
// 🔹 HÀM XỬ LÝ LƯỢT THÍCH/KHÔNG THÍCH (AJAX)
// ==========================================================
function get_reaction_counts($conn, $comment_id)
{
    $stmt = $conn->prepare("
        SELECT 
            (SELECT COUNT(*) FROM comment_likes WHERE comment_id = ?) as total_likes,
            (SELECT COUNT(*) FROM comment_dislikes WHERE comment_id = ?) as total_dislikes
    ");
    $stmt->bind_param("ii", $comment_id, $comment_id);
    $stmt->execute();
    $counts = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $counts;
}

// XỬ LÝ LIKE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_like') {
    header('Content-Type: application/json');
    check_csrf();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập.']);
        die();
    }
    $comment_id = intval($_POST['comment_id']);
    $user_id = $_SESSION['user_id'];
    $is_liked = false;
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("SELECT like_id FROM comment_likes WHERE comment_id = ? AND user_id = ? LIMIT 1");
        $stmt->bind_param("ii", $comment_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            $stmt = $conn->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $comment_id, $user_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $comment_id, $user_id);
            $stmt->execute();
            $stmt->close();
            $is_liked = true;
            $stmt_del = $conn->prepare("DELETE FROM comment_dislikes WHERE comment_id = ? AND user_id = ?");
            $stmt_del->bind_param("ii", $comment_id, $user_id);
            $stmt_del->execute();
            $stmt_del->close();
        }
        $conn->commit();
        $counts = get_reaction_counts($conn, $comment_id);
        echo json_encode(['success' => true, 'is_liked' => $is_liked, 'total_likes' => $counts['total_likes'], 'total_dislikes' => $counts['total_dislikes']]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống.']);
    }
    die();
}

// XỬ LÝ DISLIKE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_dislike') {
    header('Content-Type: application/json');
    check_csrf();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập.']);
        die();
    }
    $comment_id = intval($_POST['comment_id']);
    $user_id = $_SESSION['user_id'];
    $is_disliked = false;
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("SELECT dislike_id FROM comment_dislikes WHERE comment_id = ? AND user_id = ? LIMIT 1");
        $stmt->bind_param("ii", $comment_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            $stmt = $conn->prepare("DELETE FROM comment_dislikes WHERE comment_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $comment_id, $user_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO comment_dislikes (comment_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $comment_id, $user_id);
            $stmt->execute();
            $stmt->close();
            $is_disliked = true;
            $stmt_del = $conn->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
            $stmt_del->bind_param("ii", $comment_id, $user_id);
            $stmt_del->execute();
            $stmt_del->close();
        }
        $conn->commit();
        $counts = get_reaction_counts($conn, $comment_id);
        echo json_encode(['success' => true, 'is_disliked' => $is_disliked, 'total_likes' => $counts['total_likes'], 'total_dislikes' => $counts['total_dislikes']]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống.']);
    }
    die();
}

// ==========================================================
// 🔹 LẤY THÔNG TIN BÀI VIẾT
// ==========================================================
$article = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $conn->prepare("SELECT a.*, c.name AS category_name, u.display_name AS author_name, u.avatar AS author_avatar
        FROM articles a LEFT JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN users u ON a.author_id = u.user_id
        WHERE a.article_id = ? AND a.status = 'published' LIMIT 1");
    $stmt->bind_param("i", $_GET['id']);
} elseif (isset($_GET['slug'])) {
    $stmt = $conn->prepare("SELECT a.*, c.name AS category_name, u.display_name AS author_name, u.avatar AS author_avatar
        FROM articles a LEFT JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN users u ON a.author_id = u.user_id
        WHERE a.slug = ? AND a.status = 'published' LIMIT 1");
    $stmt->bind_param("s", $_GET['slug']);
} else {
    header("HTTP/1.0 404 Not Found");
    die("Bài viết không tồn tại.");
}
$stmt->execute();
$result = $stmt->get_result();
if (!$result || $result->num_rows == 0) {
    header("HTTP/1.0 404 Not Found");
    die("Không tìm thấy bài viết.");
}
$article = $result->fetch_assoc();
$id = $article['article_id'];
$stmt->close();

// Tăng lượt xem
$update = $conn->prepare("UPDATE articles SET views = views + 1 WHERE article_id = ?");
$update->bind_param("i", $id);
$update->execute();
$update->close();

$image = $article['featured_image'] ? '../uploads/' . basename($article['featured_image']) : 'img/default.jpg';
$author_avatar = $article['author_avatar'] ? 'img/' . $article['author_avatar'] : 'img/default-avatar.png';

// ==========================================================
// 🔹 DANH SÁCH TỪ NHẠY CẢM
// ==========================================================
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

// ==========================================================
// 🔹 GỬI BÌNH LUẬN (AJAX)
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    header('Content-Type: application/json');
    check_csrf();
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để bình luận.']);
        die();
    }
    
    $article_id = intval($_POST['article_id'] ?? 0);
    $content = trim($_POST['comment'] ?? '');
    $parent_id_input = intval($_POST['parent_id'] ?? 0);
    $parent_id = ($parent_id_input > 0) ? $parent_id_input : NULL;
    
    if ($article_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Bài viết không hợp lệ.']);
        die();
    }
    
    if ($content === '' || strlen($content) > 500) {
        echo json_encode(['success' => false, 'message' => 'Nội dung bình luận không hợp lệ (1-500 ký tự).']);
        die();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Kiểm tra từ nhạy cảm - nếu có thì đặt status = pending
    $has_sensitive = contains_sensitive_words($content, $sensitive_words);
    $status = $has_sensitive ? 'pending' : 'approved';
    
    try {
        if ($parent_id !== NULL) {
            $stmt = $conn->prepare("INSERT INTO comments (article_id, user_id, content, parent_id, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("iisis", $article_id, $user_id, $content, $parent_id, $status);
        } else {
            $stmt = $conn->prepare("INSERT INTO comments (article_id, user_id, content, status, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiss", $article_id, $user_id, $content, $status);
        }
        $stmt->execute();
        $new_comment_id = $conn->insert_id;
        $stmt->close();
        
        // Lấy thông tin user để trả về
        $stmt = $conn->prepare("SELECT display_name, avatar FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_info = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        // Thông báo khác nhau tùy theo status
        $message = $has_sensitive 
            ? 'Bình luận của bạn đang chờ kiểm duyệt do chứa nội dung nhạy cảm.' 
            : 'Bình luận đã được gửi!';
        
        echo json_encode([
            'success' => true,
            'pending' => $has_sensitive,
            'message' => $message,
            'comment' => [
                'comment_id' => $new_comment_id,
                'content' => $content,
                'parent_id' => $parent_id,
                'status' => $status,
                'created_at' => date('d/m/Y H:i'),
                'display_name' => $user_info['display_name'] ?? 'Ẩn danh',
                'avatar' => $user_info['avatar'] ?? 'default.png'
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống. Vui lòng thử lại.']);
    }
    die();
}

// ==========================================================
// 🔹 LẤY BÌNH LUẬN (chỉ lấy bình luận đã duyệt)
// ==========================================================
$current_user_id = $_SESSION['user_id'] ?? 0;
$stmt = $conn->prepare("SELECT c.*, u.display_name, u.avatar,
    (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.comment_id) as total_likes,
    (SELECT COUNT(*) FROM comment_dislikes cd WHERE cd.comment_id = c.comment_id) as total_dislikes,
    (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.comment_id AND cl.user_id = ?) as user_liked,
    (SELECT COUNT(*) FROM comment_dislikes cd WHERE cd.comment_id = c.comment_id AND cd.user_id = ?) as user_disliked
    FROM comments c JOIN users u ON c.user_id = u.user_id 
    WHERE c.article_id = ? AND (c.status = 'approved' OR c.status IS NULL)
    ORDER BY c.created_at ASC");
$stmt->bind_param("iii", $current_user_id, $current_user_id, $id);
$stmt->execute();
$allComments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function build_comments_tree($comments, $parent_id = NULL)
{
    $tree = [];
    foreach ($comments as $comment) {
        if ($comment['parent_id'] === $parent_id) {
            $comment['replies'] = build_comments_tree($comments, $comment['comment_id']);
            $tree[] = $comment;
        }
    }
    return $tree;
}
$comments_tree = build_comments_tree($allComments);

// Tính thời gian đọc
$word_count = str_word_count(strip_tags($article['content']));
$read_time = max(1, ceil($word_count / 200));

// Lấy bài viết liên quan
$cat_id = intval($article['category_id']);
$stmt = $conn->prepare("SELECT article_id, title, featured_image, slug, created_at, views FROM articles 
    WHERE category_id = ? AND article_id != ? AND status = 'published' ORDER BY created_at DESC LIMIT 4");
$stmt->bind_param("ii", $cat_id, $id);
$stmt->execute();
$related_articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Lấy bài viết phổ biến
$stmt = $conn->prepare("SELECT article_id, title, featured_image, views FROM articles 
    WHERE status = 'published' AND article_id != ? ORDER BY views DESC LIMIT 5");
$stmt->bind_param("i", $id);
$stmt->execute();
$popular_articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<?php include 'header.php'; ?>

<style>
    /* ===== READING PROGRESS BAR ===== */
    .reading-progress {
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 4px;
        background: linear-gradient(90deg, #ff6b35, #f7931e, #ffcc00);
        z-index: 9999;
        transition: width 0.1s ease;
        box-shadow: 0 0 10px rgba(255, 107, 53, 0.5);
    }

    /* ===== ARTICLE PAGE STYLES ===== */
    .article-page {
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
        min-height: 100vh;
        padding-bottom: 80px;
    }

    /* Hero Section with Parallax */
    .article-hero {
        position: relative;
        height: 550px;
        overflow: hidden;
    }

    .article-hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 120%;
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        filter: blur(2px);
        transform: scale(1.1);
        transition: transform 0.5s ease;
    }

    .article-hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, 
            rgba(0, 0, 0, 0.2) 0%, 
            rgba(10, 10, 30, 0.7) 50%,
            rgba(10, 10, 30, 0.98) 100%);
    }

    .article-hero-content {
        position: relative;
        z-index: 10;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding-bottom: 80px;
        animation: fadeInUp 0.8s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Breadcrumb */
    .article-breadcrumb {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .article-breadcrumb a {
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        transition: color 0.3s;
    }

    .article-breadcrumb a:hover {
        color: #ff6b35;
    }

    .article-breadcrumb span {
        color: rgba(255, 255, 255, 0.4);
    }

    .article-breadcrumb .current {
        color: #ff6b35;
    }

    .article-category-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 24px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        border-radius: 30px;
        margin-bottom: 25px;
        box-shadow: 0 5px 20px rgba(255, 107, 53, 0.4);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 5px 20px rgba(255, 107, 53, 0.4); }
        50% { box-shadow: 0 5px 30px rgba(255, 107, 53, 0.6); }
    }

    .article-title {
        color: #fff;
        font-size: 48px;
        font-weight: 800;
        line-height: 1.15;
        margin-bottom: 30px;
        text-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
        max-width: 900px;
    }

    .article-excerpt {
        color: rgba(255, 255, 255, 0.8);
        font-size: 18px;
        line-height: 1.6;
        margin-bottom: 30px;
        max-width: 700px;
    }

    .article-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 30px;
    }

    .article-author {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .author-avatar {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #ff6b35;
        box-shadow: 0 5px 20px rgba(255, 107, 53, 0.3);
        transition: transform 0.3s;
    }

    .author-avatar:hover {
        transform: scale(1.1);
    }

    .author-info {
        display: flex;
        flex-direction: column;
    }

    .author-name {
        color: #fff;
        font-weight: 700;
        font-size: 16px;
    }

    .author-role {
        color: #ff6b35;
        font-size: 13px;
        font-weight: 500;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        backdrop-filter: blur(10px);
    }

    .meta-item i {
        color: #ff6b35;
        font-size: 16px;
    }

    /* Main Content */
    .article-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .article-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 50px;
        margin-top: -100px;
        position: relative;
        z-index: 20;
    }

    @media (max-width: 1100px) {
        .article-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Article Content Card */
    .article-content-card {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 28px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.5);
        transition: transform 0.3s;
    }

    .article-featured-image-wrapper {
        position: relative;
        overflow: hidden;
    }

    .article-featured-image {
        width: 100%;
        height: 450px;
        object-fit: cover;
        transition: transform 0.5s;
    }

    .article-featured-image-wrapper:hover .article-featured-image {
        transform: scale(1.02);
    }

    .image-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 100px;
        background: linear-gradient(to top, rgba(20, 20, 35, 1), transparent);
    }

    /* Quick Actions Bar */
    .quick-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 40px;
        background: rgba(255, 255, 255, 0.03);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .action-group {
        display: flex;
        gap: 15px;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 25px;
        color: #ccc;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .action-btn:hover {
        background: rgba(255, 107, 53, 0.2);
        border-color: rgba(255, 107, 53, 0.3);
        color: #ff6b35;
    }

    .action-btn.active {
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        border-color: transparent;
        color: #fff;
    }

    .action-btn i {
        font-size: 16px;
    }

    .article-body {
        padding: 50px;
    }

    /* Table of Contents */
    .toc-wrapper {
        background: rgba(255, 107, 53, 0.08);
        border: 1px solid rgba(255, 107, 53, 0.2);
        border-radius: 16px;
        padding: 25px 30px;
        margin-bottom: 40px;
    }

    .toc-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        cursor: pointer;
    }

    .toc-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #ff6b35;
        font-size: 16px;
        font-weight: 600;
    }

    .toc-toggle {
        color: #ff6b35;
        transition: transform 0.3s;
    }

    .toc-toggle.collapsed {
        transform: rotate(-90deg);
    }

    .toc-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .toc-list li {
        padding: 10px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .toc-list li:last-child {
        border-bottom: none;
    }

    .toc-list a {
        color: #ccc;
        text-decoration: none;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;
    }

    .toc-list a:hover {
        color: #ff6b35;
        padding-left: 10px;
    }

    .toc-list a::before {
        content: '';
        width: 6px;
        height: 6px;
        background: #ff6b35;
        border-radius: 50%;
        opacity: 0.5;
    }

    .toc-list a:hover::before {
        opacity: 1;
    }

    .article-content {
        color: #d0d0d0;
        font-size: 18px;
        line-height: 1.9;
    }

    .article-content p {
        margin-bottom: 24px;
    }

    .article-content h2 {
        color: #fff;
        font-size: 28px;
        font-weight: 700;
        margin: 50px 0 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
        position: relative;
    }

    .article-content h2::before {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 80px;
        height: 2px;
        background: #ff6b35;
    }

    .article-content h3 {
        color: #fff;
        font-size: 22px;
        font-weight: 600;
        margin: 40px 0 20px;
    }

    .article-content h4 {
        color: #eee;
        font-size: 18px;
        font-weight: 600;
        margin: 30px 0 15px;
    }

    .article-content img {
        max-width: 100%;
        border-radius: 16px;
        margin: 30px 0;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }

    .article-content blockquote {
        border-left: 4px solid #ff6b35;
        padding: 25px 30px;
        margin: 35px 0;
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.1), rgba(247, 147, 30, 0.05));
        border-radius: 0 16px 16px 0;
        font-style: italic;
        color: #ddd;
        font-size: 17px;
        position: relative;
    }

    .article-content blockquote::before {
        content: '"';
        position: absolute;
        top: 10px;
        left: 15px;
        font-size: 60px;
        color: rgba(255, 107, 53, 0.3);
        font-family: Georgia, serif;
        line-height: 1;
    }

    .article-content a {
        color: #ff6b35;
        text-decoration: none;
        border-bottom: 1px dashed rgba(255, 107, 53, 0.5);
        transition: all 0.3s;
    }

    .article-content a:hover {
        border-bottom-style: solid;
    }

    .article-content ul, .article-content ol {
        margin: 25px 0;
        padding-left: 30px;
    }

    .article-content li {
        margin-bottom: 12px;
        position: relative;
    }

    .article-content ul li::marker {
        color: #ff6b35;
    }

    .article-content code {
        background: rgba(255, 255, 255, 0.1);
        padding: 3px 8px;
        border-radius: 5px;
        font-family: 'Fira Code', monospace;
        font-size: 15px;
        color: #ff6b35;
    }

    .article-content pre {
        background: rgba(0, 0, 0, 0.4);
        padding: 25px;
        border-radius: 12px;
        overflow-x: auto;
        margin: 30px 0;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .article-content pre code {
        background: none;
        padding: 0;
        color: #ccc;
    }

    /* Tags */
    .article-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 40px;
        padding-top: 40px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .tags-label {
        color: #888;
        font-size: 14px;
        font-weight: 500;
        margin-right: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tag-item {
        padding: 10px 20px;
        background: rgba(255, 107, 53, 0.12);
        color: #ff6b35;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
        text-decoration: none;
        border: 1px solid rgba(255, 107, 53, 0.2);
    }

    .tag-item:hover {
        background: rgba(255, 107, 53, 0.25);
        transform: translateY(-2px);
    }

    /* Share Section */
    .share-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 40px;
        padding: 30px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .share-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .share-label {
        color: #888;
        font-size: 15px;
        font-weight: 500;
    }

    .share-buttons {
        display: flex;
        gap: 12px;
    }

    .share-btn {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 18px;
        transition: all 0.3s;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .share-btn.facebook { background: linear-gradient(135deg, #1877f2, #0d65d9); }
    .share-btn.twitter { background: linear-gradient(135deg, #1da1f2, #0c85d0); }
    .share-btn.linkedin { background: linear-gradient(135deg, #0077b5, #005885); }
    .share-btn.telegram { background: linear-gradient(135deg, #0088cc, #006699); }
    .share-btn.copy { 
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .share-btn:hover {
        transform: translateY(-5px) scale(1.1);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }

    .share-right {
        display: flex;
        gap: 15px;
    }

    .utility-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 25px;
        color: #ccc;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .utility-btn:hover {
        background: rgba(255, 107, 53, 0.2);
        border-color: rgba(255, 107, 53, 0.3);
        color: #ff6b35;
    }

    /* Author Box */
    .author-box {
        display: flex;
        gap: 25px;
        margin-top: 40px;
        padding: 35px;
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.08), rgba(247, 147, 30, 0.05));
        border-radius: 20px;
        border: 1px solid rgba(255, 107, 53, 0.2);
    }

    .author-box-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #ff6b35;
        flex-shrink: 0;
    }

    .author-box-info {
        flex: 1;
    }

    .author-box-label {
        color: #ff6b35;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }

    .author-box-name {
        color: #fff;
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .author-box-bio {
        color: #aaa;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    .author-box-social {
        display: flex;
        gap: 12px;
    }

    .author-social-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ccc;
        font-size: 14px;
        transition: all 0.3s;
        text-decoration: none;
    }

    .author-social-btn:hover {
        background: #ff6b35;
        color: #fff;
        transform: translateY(-3px);
    }
</style>

<style>
    /* Sidebar */
    .article-sidebar {
        display: flex;
        flex-direction: column;
        gap: 30px;
        position: sticky;
        top: 100px;
        height: fit-content;
    }

    .sidebar-card {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .sidebar-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
    }

    .sidebar-header {
        padding: 22px 25px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255, 255, 255, 0.02);
    }

    .sidebar-header i {
        color: #ff6b35;
        font-size: 20px;
    }

    .sidebar-header h3 {
        color: #fff;
        font-size: 17px;
        font-weight: 600;
        margin: 0;
    }

    .sidebar-body {
        padding: 25px;
    }

    /* Author Card */
    .author-card-content {
        text-align: center;
        padding: 35px 25px;
    }

    .author-card-avatar {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #ff6b35;
        margin-bottom: 20px;
        box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
        transition: transform 0.3s;
    }

    .author-card-avatar:hover {
        transform: scale(1.05);
    }

    .author-card-name {
        color: #fff;
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .author-card-role {
        color: #ff6b35;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 15px;
    }

    .author-card-bio {
        color: #888;
        font-size: 14px;
        line-height: 1.7;
        margin-bottom: 20px;
    }

    .author-follow-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 30px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        border: none;
        border-radius: 25px;
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .author-follow-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(255, 107, 53, 0.4);
    }

    /* Stats Card */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .stat-box {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02));
        padding: 22px 15px;
        border-radius: 16px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s;
    }

    .stat-box:hover {
        background: rgba(255, 107, 53, 0.1);
        border-color: rgba(255, 107, 53, 0.2);
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        margin: 0 auto 12px;
        background: rgba(255, 107, 53, 0.15);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ff6b35;
        font-size: 18px;
    }

    .stat-number {
        color: #fff;
        font-size: 26px;
        font-weight: 700;
        display: block;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #888;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Related Articles */
    .related-item {
        display: flex;
        gap: 15px;
        padding: 18px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s;
    }

    .related-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .related-item:first-child {
        padding-top: 0;
    }

    .related-item:hover {
        padding-left: 10px;
    }

    .related-thumb {
        width: 85px;
        height: 65px;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
        transition: transform 0.3s;
    }

    .related-item:hover .related-thumb {
        transform: scale(1.05);
    }

    .related-info {
        flex: 1;
    }

    .related-title {
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.4;
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .related-title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.3s;
    }

    .related-title a:hover {
        color: #ff6b35;
    }

    .related-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #666;
        font-size: 12px;
    }

    .related-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .related-meta i {
        color: #ff6b35;
        font-size: 11px;
    }

    /* Popular Articles */
    .popular-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .popular-item:last-child {
        border-bottom: none;
    }

    .popular-rank {
        width: 35px;
        height: 35px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .popular-rank.top-3 {
        background: linear-gradient(135deg, #ffd700, #ffaa00);
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
        transition: color 0.3s;
    }

    .popular-title a:hover {
        color: #ff6b35;
    }

    .popular-views {
        color: #666;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .popular-views i {
        color: #ff6b35;
    }

    /* Newsletter Card */
    .newsletter-card {
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.15), rgba(247, 147, 30, 0.1));
        border: 1px solid rgba(255, 107, 53, 0.3);
    }

    .newsletter-content {
        text-align: center;
        padding: 30px 25px;
    }

    .newsletter-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #fff;
    }

    .newsletter-title {
        color: #fff;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .newsletter-desc {
        color: #aaa;
        font-size: 14px;
        margin-bottom: 20px;
    }

    .newsletter-form {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .newsletter-input {
        padding: 14px 18px;
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        color: #fff;
        font-size: 14px;
        outline: none;
        transition: all 0.3s;
    }

    .newsletter-input:focus {
        border-color: #ff6b35;
    }

    .newsletter-btn {
        padding: 14px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        border: none;
        border-radius: 12px;
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .newsletter-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(255, 107, 53, 0.4);
    }

    /* Comments Section */
    .comments-section {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 28px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        padding: 50px;
        margin-top: 50px;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.5);
    }

    .comments-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 40px;
        padding-bottom: 25px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .comments-title {
        color: #fff;
        font-size: 24px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .comments-title i {
        color: #ff6b35;
        font-size: 28px;
    }

    .comments-count {
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: #fff;
        padding: 6px 16px;
        border-radius: 25px;
        font-size: 15px;
        font-weight: 600;
    }

    /* Comment Box */
    .comment-box {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 25px;
        transition: all 0.3s;
    }

    .comment-box:hover {
        border-color: rgba(255, 107, 53, 0.3);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .comment-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 18px;
    }

    .comment-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #ff6b35;
        transition: transform 0.3s;
    }

    .comment-avatar:hover {
        transform: scale(1.1);
    }

    .comment-user-info {
        flex: 1;
    }

    .comment-username {
        color: #fff;
        font-weight: 600;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .comment-badge {
        padding: 3px 10px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        border-radius: 10px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .comment-date {
        color: #666;
        font-size: 13px;
        margin-top: 3px;
    }

    .comment-content {
        color: #ccc;
        font-size: 15px;
        line-height: 1.8;
        margin-bottom: 18px;
    }

    .comment-actions {
        display: flex;
        gap: 25px;
    }

    .comment-action {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #666;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        background: none;
        border: none;
        padding: 8px 15px;
        border-radius: 20px;
    }

    .comment-action:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #ff6b35;
    }

    .comment-action.liked {
        color: #ff6b35;
        background: rgba(255, 107, 53, 0.15);
    }

    .comment-action.disliked {
        color: #dc3545;
        background: rgba(220, 53, 69, 0.15);
    }

    .comment-action i {
        font-size: 16px;
    }

    /* Reply Box */
    .comment-replies {
        margin-left: 70px;
        padding-left: 25px;
        border-left: 3px solid rgba(255, 107, 53, 0.3);
        margin-top: 20px;
    }

    .reply-box {
        background: rgba(255, 255, 255, 0.02);
    }

    /* Comment Form */
    .comment-form-card {
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.08), rgba(247, 147, 30, 0.05));
        border: 1px solid rgba(255, 107, 53, 0.2);
        border-radius: 20px;
        padding: 30px;
        margin-top: 40px;
    }

    .comment-form-title {
        color: #fff;
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .comment-form-title i {
        color: #ff6b35;
    }

    .comment-textarea {
        width: 100%;
        min-height: 140px;
        padding: 20px;
        background: rgba(0, 0, 0, 0.3);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        color: #fff;
        font-size: 15px;
        resize: vertical;
        outline: none;
        transition: all 0.3s;
        font-family: inherit;
    }

    .comment-textarea:focus {
        border-color: #ff6b35;
        box-shadow: 0 0 30px rgba(255, 107, 53, 0.15);
    }

    .comment-textarea::placeholder {
        color: #555;
    }

    .comment-form-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
    }

    .char-counter {
        color: #666;
        font-size: 13px;
    }

    .comment-submit {
        padding: 15px 35px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        border: none;
        border-radius: 25px;
        color: #fff;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .comment-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(255, 107, 53, 0.4);
    }

    .login-prompt {
        text-align: center;
        padding: 40px;
        color: #888;
    }

    .login-prompt p {
        margin-bottom: 20px;
        font-size: 16px;
    }

    .login-prompt a {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 30px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: #fff;
        font-weight: 600;
        text-decoration: none;
        border-radius: 25px;
        transition: all 0.3s;
    }

    .login-prompt a:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(255, 107, 53, 0.4);
    }

    /* Reply Form */
    .reply-form {
        margin-top: 20px;
        padding: 20px;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 16px;
        display: none;
    }

    .reply-form textarea {
        width: 100%;
        min-height: 100px;
        padding: 15px;
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        color: #fff;
        font-size: 14px;
        resize: none;
        outline: none;
        margin-bottom: 15px;
        font-family: inherit;
    }

    .reply-form textarea:focus {
        border-color: #ff6b35;
    }

    .reply-form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .reply-form button {
        padding: 12px 25px;
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        border: none;
        border-radius: 20px;
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .reply-form button:hover {
        transform: translateY(-2px);
    }

    .reply-form .cancel-btn {
        background: rgba(255, 255, 255, 0.1);
    }

    /* Floating Share */
    .floating-share {
        position: fixed;
        left: 30px;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        flex-direction: column;
        gap: 12px;
        z-index: 100;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s;
    }

    .floating-share.visible {
        opacity: 1;
        visibility: visible;
    }

    .floating-share-btn {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 18px;
        transition: all 0.3s;
        text-decoration: none;
        border: none;
        cursor: pointer;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
    }

    .floating-share-btn.facebook { background: #1877f2; }
    .floating-share-btn.twitter { background: #1da1f2; }
    .floating-share-btn.linkedin { background: #0077b5; }
    .floating-share-btn.bookmark { 
        background: rgba(30, 30, 50, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .floating-share-btn:hover {
        transform: scale(1.15);
    }

    .floating-share-btn.bookmark.active {
        background: #ff6b35;
        border-color: #ff6b35;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .floating-share {
            display: none;
        }
    }

    @media (max-width: 768px) {
        .article-hero {
            height: 450px;
        }

        .article-title {
            font-size: 28px;
        }

        .article-layout {
            margin-top: -50px;
        }

        .article-body {
            padding: 30px;
        }

        .article-content {
            font-size: 16px;
        }

        .comments-section {
            padding: 30px;
        }

        .comment-replies {
            margin-left: 30px;
            padding-left: 15px;
        }

        .quick-actions {
            flex-direction: column;
            gap: 15px;
        }

        .share-section {
            flex-direction: column;
            text-align: center;
        }

        .share-left, .share-right {
            justify-content: center;
        }
    }

    /* Print Styles */
    @media print {
        .article-hero, .article-sidebar, .comments-section, 
        .floating-share, .quick-actions, .share-section,
        .author-box, header, footer {
            display: none !important;
        }

        .article-page {
            background: #fff !important;
        }

        .article-content-card {
            box-shadow: none !important;
            border: none !important;
        }

        .article-content {
            color: #000 !important;
        }

        .article-content h2, .article-content h3 {
            color: #000 !important;
        }
    }
</style>

<!-- Reading Progress Bar -->
<div class="reading-progress" id="readingProgress"></div>

<!-- Floating Share Buttons -->
<div class="floating-share" id="floatingShare">
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($_SERVER['REQUEST_URI']) ?>" target="_blank" class="floating-share-btn facebook" title="Chia sẻ Facebook">
        <i class="fab fa-facebook-f"></i>
    </a>
    <a href="https://twitter.com/intent/tweet?url=<?= urlencode($_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" class="floating-share-btn twitter" title="Chia sẻ Twitter">
        <i class="fab fa-twitter"></i>
    </a>
    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" target="_blank" class="floating-share-btn linkedin" title="Chia sẻ LinkedIn">
        <i class="fab fa-linkedin-in"></i>
    </a>
    <button class="floating-share-btn bookmark" id="bookmarkBtn" title="Lưu bài viết">
        <i class="fas fa-bookmark"></i>
    </button>
</div>

<div class="article-page">
    <!-- Hero Section -->
    <div class="article-hero">
        <div class="article-hero-bg" style="background-image: url('<?= htmlspecialchars($image) ?>');" id="heroBg"></div>
        <div class="article-hero-overlay"></div>
        <div class="article-container">
            <div class="article-hero-content">
                <!-- Breadcrumb -->
                <nav class="article-breadcrumb">
                    <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
                    <span>/</span>
                    <a href="categories.php?id=<?= $article['category_id'] ?>"><?= htmlspecialchars($article['category_name'] ?? 'Danh mục') ?></a>
                    <span>/</span>
                    <span class="current"><?= mb_substr(htmlspecialchars($article['title']), 0, 40) ?>...</span>
                </nav>

                <span class="article-category-badge">
                    <i class="fas fa-gamepad"></i>
                    <?= htmlspecialchars($article['category_name'] ?? 'Chưa phân loại') ?>
                </span>
                <h1 class="article-title"><?= htmlspecialchars($article['title']) ?></h1>
                
                <?php if (!empty($article['excerpt'])): ?>
                <p class="article-excerpt"><?= htmlspecialchars($article['excerpt']) ?></p>
                <?php endif; ?>

                <div class="article-meta">
                    <div class="article-author">
                        <img src="<?= htmlspecialchars($author_avatar) ?>" alt="Author" class="author-avatar">
                        <div class="author-info">
                            <span class="author-name"><?= htmlspecialchars($article['author_name'] ?? 'Admin') ?></span>
                            <span class="author-role">Biên tập viên</span>
                        </div>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span><?= date('d/m/Y', strtotime($article['created_at'])) ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-eye"></i>
                        <span><?= number_format($article['views']) ?> lượt xem</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-clock"></i>
                        <span><?= $read_time ?> phút đọc</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-comments"></i>
                        <span><?= count($allComments) ?> bình luận</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="article-container">
        <div class="article-layout">
            <!-- Article Content -->
            <div class="article-main">
                <div class="article-content-card">
                    <div class="article-featured-image-wrapper">
                        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="article-featured-image">
                        <div class="image-overlay"></div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <div class="action-group">
                            <button class="action-btn" id="likeArticleBtn">
                                <i class="fas fa-heart"></i>
                                <span>Thích</span>
                            </button>
                            <button class="action-btn" id="saveArticleBtn">
                                <i class="fas fa-bookmark"></i>
                                <span>Lưu</span>
                            </button>
                            <button class="action-btn" onclick="window.print()">
                                <i class="fas fa-print"></i>
                                <span>In</span>
                            </button>
                        </div>
                        <div class="action-group">
                            <button class="action-btn" id="fontSizeBtn">
                                <i class="fas fa-text-height"></i>
                                <span>Cỡ chữ</span>
                            </button>
                            <button class="action-btn" id="readModeBtn">
                                <i class="fas fa-book-reader"></i>
                                <span>Đọc</span>
                            </button>
                        </div>
                    </div>

                    <div class="article-body">
                        <!-- Table of Contents -->
                        <div class="toc-wrapper" id="tocWrapper">
                            <div class="toc-header" onclick="toggleTOC()">
                                <span class="toc-title">
                                    <i class="fas fa-list-ul"></i>
                                    Mục lục bài viết
                                </span>
                                <i class="fas fa-chevron-down toc-toggle" id="tocToggle"></i>
                            </div>
                            <ul class="toc-list" id="tocList">
                                <!-- Generated by JavaScript -->
                            </ul>
                        </div>

                        <div class="article-content" id="articleContent">
                            <?= $article['content'] ?>
                        </div>

                        <!-- Tags -->
                        <div class="article-tags">
                            <span class="tags-label"><i class="fas fa-tags"></i> Tags:</span>
                            <a href="search.php?q=<?= urlencode($article['category_name'] ?? 'Game') ?>" class="tag-item">#<?= htmlspecialchars($article['category_name'] ?? 'Game') ?></a>
                            <a href="search.php?q=TinTuc" class="tag-item">#TinTức</a>
                            <a href="search.php?q=GameBat" class="tag-item">#GameBat</a>
                            <a href="search.php?q=Review" class="tag-item">#Review</a>
                        </div>

                        <!-- Share Section -->
                        <div class="share-section">
                            <div class="share-left">
                                <span class="share-label">Chia sẻ bài viết:</span>
                                <div class="share-buttons">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($_SERVER['REQUEST_URI']) ?>" target="_blank" class="share-btn facebook" title="Facebook">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode($_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" class="share-btn twitter" title="Twitter">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" target="_blank" class="share-btn linkedin" title="LinkedIn">
                                        <i class="fab fa-linkedin-in"></i>
                                    </a>
                                    <a href="https://t.me/share/url?url=<?= urlencode($_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" class="share-btn telegram" title="Telegram">
                                        <i class="fab fa-telegram-plane"></i>
                                    </a>
                                    <button class="share-btn copy" onclick="copyLink()" title="Sao chép link">
                                        <i class="fas fa-link"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="share-right">
                                <button class="utility-btn" onclick="window.print()">
                                    <i class="fas fa-print"></i> In bài viết
                                </button>
                                <button class="utility-btn" id="reportBtn">
                                    <i class="fas fa-flag"></i> Báo cáo
                                </button>
                            </div>
                        </div>

                        <!-- Author Box -->
                        <div class="author-box">
                            <img src="<?= htmlspecialchars($author_avatar) ?>" alt="Author" class="author-box-avatar">
                            <div class="author-box-info">
                                <span class="author-box-label">Tác giả</span>
                                <h4 class="author-box-name"><?= htmlspecialchars($article['author_name'] ?? 'Admin') ?></h4>
                                <p class="author-box-bio">Đam mê game và công nghệ. Luôn cập nhật những tin tức mới nhất, đánh giá chân thực nhất cho cộng đồng game thủ Việt Nam.</p>
                                <div class="author-box-social">
                                    <a href="#" class="author-social-btn"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#" class="author-social-btn"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="author-social-btn"><i class="fab fa-instagram"></i></a>
                                    <a href="#" class="author-social-btn"><i class="fab fa-youtube"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="comments-section" id="comments">
                    <div class="comments-header">
                        <h3 class="comments-title">
                            <i class="fas fa-comments"></i>
                            Bình luận
                        </h3>
                        <span class="comments-count"><?= count($allComments) ?></span>
                    </div>

                    <!-- Comment List -->
                    <div class="comment-list">
                        <?php if (!empty($comments_tree)): ?>
                            <?php foreach ($comments_tree as $comment): ?>
                                <?php display_comment($comment, isset($_SESSION['user_id']), $id); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 50px; color: #666;">
                                <i class="fas fa-comment-slash" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                                <p style="font-size: 16px;">Chưa có bình luận nào. Hãy là người đầu tiên!</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Comment Form -->
                    <div class="comment-form-card">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <h4 class="comment-form-title">
                                <i class="fas fa-pen"></i>
                                Viết bình luận của bạn
                            </h4>
                            <form method="POST" id="commentForm">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="parent_id" value="">
                                <textarea name="comment" class="comment-textarea" id="commentTextarea" placeholder="Chia sẻ suy nghĩ của bạn về bài viết này..." required maxlength="500"></textarea>
                                <div class="comment-form-footer">
                                    <span class="char-counter"><span id="charCount">0</span>/500 ký tự</span>
                                    <button type="submit" class="comment-submit">
                                        <i class="fas fa-paper-plane"></i>
                                        Gửi bình luận
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="login-prompt">
                                <i class="fas fa-user-lock" style="font-size: 48px; color: #ff6b35; margin-bottom: 20px;"></i>
                                <p>Bạn cần đăng nhập để bình luận</p>
                                <a href="login.php">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Đăng nhập ngay
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="article-sidebar">
                <!-- Author Card -->
                <div class="sidebar-card">
                    <div class="author-card-content">
                        <img src="<?= htmlspecialchars($author_avatar) ?>" alt="Author" class="author-card-avatar">
                        <h4 class="author-card-name"><?= htmlspecialchars($article['author_name'] ?? 'Admin') ?></h4>
                        <p class="author-card-role">Biên tập viên</p>
                        <p class="author-card-bio">Đam mê game và công nghệ. Luôn cập nhật những tin tức mới nhất cho cộng đồng.</p>
                        <button class="author-follow-btn">
                            <i class="fas fa-user-plus"></i>
                            Theo dõi
                        </button>
                    </div>
                </div>

                <!-- Stats Card -->
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <i class="fas fa-chart-bar"></i>
                        <h3>Thống kê bài viết</h3>
                    </div>
                    <div class="sidebar-body">
                        <div class="stats-grid">
                            <div class="stat-box">
                                <div class="stat-icon"><i class="fas fa-eye"></i></div>
                                <span class="stat-number"><?= number_format($article['views']) ?></span>
                                <span class="stat-label">Lượt xem</span>
                            </div>
                            <div class="stat-box">
                                <div class="stat-icon"><i class="fas fa-comments"></i></div>
                                <span class="stat-number"><?= count($allComments) ?></span>
                                <span class="stat-label">Bình luận</span>
                            </div>
                            <div class="stat-box">
                                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                                <span class="stat-number"><?= $read_time ?></span>
                                <span class="stat-label">Phút đọc</span>
                            </div>
                            <div class="stat-box">
                                <div class="stat-icon"><i class="fas fa-font"></i></div>
                                <span class="stat-number"><?= number_format($word_count) ?></span>
                                <span class="stat-label">Từ</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Articles -->
                <?php if (!empty($related_articles)): ?>
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <i class="fas fa-newspaper"></i>
                        <h3>Bài viết liên quan</h3>
                    </div>
                    <div class="sidebar-body">
                        <?php foreach ($related_articles as $r): ?>
                            <?php $r_img = !empty($r['featured_image']) ? '../uploads/' . basename($r['featured_image']) : 'img/default.jpg'; ?>
                            <div class="related-item">
                                <img src="<?= htmlspecialchars($r_img) ?>" alt="" class="related-thumb">
                                <div class="related-info">
                                    <h5 class="related-title">
                                        <a href="article.php?id=<?= $r['article_id'] ?>"><?= htmlspecialchars($r['title']) ?></a>
                                    </h5>
                                    <div class="related-meta">
                                        <span><i class="fas fa-eye"></i> <?= number_format($r['views']) ?></span>
                                        <span><i class="fas fa-calendar"></i> <?= date('d/m', strtotime($r['created_at'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Popular Articles -->
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <i class="fas fa-fire"></i>
                        <h3>Bài viết phổ biến</h3>
                    </div>
                    <div class="sidebar-body">
                        <?php $rank = 1; foreach ($popular_articles as $p): ?>
                            <?php $p_img = !empty($p['featured_image']) ? '../uploads/' . basename($p['featured_image']) : 'img/default.jpg'; ?>
                            <div class="popular-item">
                                <span class="popular-rank <?= $rank <= 3 ? 'top-3' : '' ?>"><?= $rank ?></span>
                                <div class="popular-info">
                                    <h5 class="popular-title">
                                        <a href="article.php?id=<?= $p['article_id'] ?>"><?= htmlspecialchars($p['title']) ?></a>
                                    </h5>
                                    <span class="popular-views">
                                        <i class="fas fa-eye"></i> <?= number_format($p['views']) ?> lượt xem
                                    </span>
                                </div>
                            </div>
                        <?php $rank++; endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
// Hàm hiển thị comment
function display_comment($comment, $is_logged_in, $article_id)
{
    global $csrf_token;
    $is_reply = $comment['parent_id'] !== NULL;
    $display_name = (string)($comment['display_name'] ?? 'Ẩn danh');
    $comment_content = (string)($comment['content'] ?? '');
    $avatar_file = $comment['avatar'] ?? 'default.png';
    $avatar_path = 'img/' . htmlspecialchars((string)$avatar_file);
?>
    <div class="comment-box <?= $is_reply ? 'reply-box' : '' ?>" data-comment-id="<?= $comment['comment_id'] ?>" id="comment-<?= $comment['comment_id'] ?>">
        <div class="comment-header">
            <img src="<?= $avatar_path ?>" alt="Avatar" class="comment-avatar">
            <div class="comment-user-info">
                <span class="comment-username">
                    <?= htmlspecialchars($display_name) ?>
                    <?php if (!$is_reply): ?>
                    <span class="comment-badge">Thành viên</span>
                    <?php endif; ?>
                </span>
                <span class="comment-date">
                    <i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>
                </span>
            </div>
        </div>
        <div class="comment-content"><?= nl2br(htmlspecialchars($comment_content)) ?></div>
        <div class="comment-actions">
            <button class="comment-action like-action <?= $comment['user_liked'] > 0 ? 'liked' : '' ?>" data-id="<?= $comment['comment_id'] ?>">
                <i class="fas fa-thumbs-up"></i>
                <span class="like-count"><?= intval($comment['total_likes']) ?></span>
            </button>
            <button class="comment-action dislike-action <?= $comment['user_disliked'] > 0 ? 'disliked' : '' ?>" data-id="<?= $comment['comment_id'] ?>">
                <i class="fas fa-thumbs-down"></i>
                <span class="dislike-count"><?= intval($comment['total_dislikes']) ?></span>
            </button>
            <?php if ($is_logged_in): ?>
                <button class="comment-action reply-toggle" data-id="<?= $comment['comment_id'] ?>">
                    <i class="fas fa-reply"></i>
                    <span>Trả lời</span>
                </button>
            <?php endif; ?>
        </div>

        <?php if ($is_logged_in): ?>
            <div class="reply-form" id="reply-form-<?= $comment['comment_id'] ?>">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="parent_id" value="<?= $comment['comment_id'] ?>">
                    <textarea name="comment" placeholder="Trả lời <?= htmlspecialchars($display_name) ?>..." required maxlength="500"></textarea>
                    <div class="reply-form-actions">
                        <button type="button" class="cancel-btn" onclick="hideReplyForm(<?= $comment['comment_id'] ?>)">Hủy</button>
                        <button type="submit">
                            <i class="fas fa-paper-plane"></i> Gửi
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($comment['replies'])): ?>
        <div class="comment-replies">
            <?php foreach ($comment['replies'] as $reply) {
                display_comment($reply, $is_logged_in, $article_id);
            } ?>
        </div>
    <?php endif;
}
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // ===== READING PROGRESS BAR =====
    const progressBar = document.getElementById('readingProgress');
    const articleContent = document.getElementById('articleContent');
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = (scrollTop / docHeight) * 100;
        progressBar.style.width = progress + '%';
        
        // Show/hide floating share
        const floatingShare = document.getElementById('floatingShare');
        if (scrollTop > 500) {
            floatingShare.classList.add('visible');
        } else {
            floatingShare.classList.remove('visible');
        }
    });

    // ===== PARALLAX EFFECT =====
    const heroBg = document.getElementById('heroBg');
    window.addEventListener('scroll', function() {
        const scrolled = window.scrollY;
        if (scrolled < 600) {
            heroBg.style.transform = 'scale(1.1) translateY(' + (scrolled * 0.3) + 'px)';
        }
    });

    // ===== TABLE OF CONTENTS =====
    function generateTOC() {
        const content = document.getElementById('articleContent');
        const tocList = document.getElementById('tocList');
        const headings = content.querySelectorAll('h2, h3');
        
        if (headings.length === 0) {
            document.getElementById('tocWrapper').style.display = 'none';
            return;
        }
        
        headings.forEach((heading, index) => {
            const id = 'heading-' + index;
            heading.id = id;
            
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = '#' + id;
            a.textContent = heading.textContent;
            a.style.paddingLeft = heading.tagName === 'H3' ? '20px' : '0';
            
            a.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            
            li.appendChild(a);
            tocList.appendChild(li);
        });
    }
    generateTOC();

    // ===== TOGGLE TOC =====
    window.toggleTOC = function() {
        const tocList = document.getElementById('tocList');
        const tocToggle = document.getElementById('tocToggle');
        tocList.classList.toggle('collapsed');
        tocToggle.classList.toggle('collapsed');
        tocList.style.display = tocList.style.display === 'none' ? 'block' : 'none';
    };

    // ===== CHARACTER COUNTER =====
    const textarea = document.getElementById('commentTextarea');
    const charCount = document.getElementById('charCount');
    if (textarea && charCount) {
        textarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
            if (this.value.length > 450) {
                charCount.style.color = '#ff6b35';
            } else {
                charCount.style.color = '#666';
            }
        });
    }

    // ===== GỬI BÌNH LUẬN AJAX =====
    $('#commentForm').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $textarea = $form.find('textarea[name="comment"]');
        var $submitBtn = $form.find('button[type="submit"]');
        var content = $textarea.val().trim();
        
        if (content === '') {
            showToast('Vui lòng nhập nội dung bình luận!', 'warning');
            return;
        }
        
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang gửi...');
        
        $.ajax({
            type: 'POST',
            url: 'article.php?id=<?= $id ?>',
            data: {
                action: 'add_comment',
                article_id: <?= $id ?>,
                comment: content,
                parent_id: $form.find('input[name="parent_id"]').val() || 0,
                csrf_token: '<?= $csrf_token ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Reset form
                    $textarea.val('');
                    $('#charCount').text('0');
                    
                    // Nếu bình luận cần chờ duyệt (có từ nhạy cảm)
                    if (response.pending) {
                        showToast(response.message, 'warning');
                    } else {
                        // Thêm bình luận mới vào danh sách
                        var newCommentHtml = createCommentHtml(response.comment);
                        
                        // Xóa thông báo "Chưa có bình luận" nếu có
                        $('.comment-list > div[style*="text-align: center"]').remove();
                        
                        $('.comment-list').prepend(newCommentHtml);
                        
                        // Cập nhật số lượng bình luận
                        var $countEl = $('.comments-count');
                        var currentCount = parseInt($countEl.text() || 0) + 1;
                        $countEl.text(currentCount);
                        
                        showToast(response.message, 'success');
                        
                        // Scroll đến bình luận mới
                        $('html, body').animate({
                            scrollTop: $('#comment-' + response.comment.comment_id).offset().top - 100
                        }, 500);
                    }
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Gửi bình luận');
            }
        });
    });

    // ===== GỬI TRẢ LỜI AJAX =====
    $(document).on('submit', '.reply-form form', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $textarea = $form.find('textarea[name="comment"]');
        var $submitBtn = $form.find('button[type="submit"]');
        var content = $textarea.val().trim();
        var parentId = $form.find('input[name="parent_id"]').val();
        
        if (content === '') {
            showToast('Vui lòng nhập nội dung trả lời!', 'warning');
            return;
        }
        
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            type: 'POST',
            url: 'article.php?id=<?= $id ?>',
            data: {
                action: 'add_comment',
                article_id: <?= $id ?>,
                comment: content,
                parent_id: parentId,
                csrf_token: '<?= $csrf_token ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Thêm reply vào dưới comment cha
                    var $parentComment = $('#comment-' + parentId);
                    var $repliesContainer = $parentComment.next('.comment-replies');
                    
                    if ($repliesContainer.length === 0) {
                        $repliesContainer = $('<div class="comment-replies"></div>');
                        $parentComment.after($repliesContainer);
                    }
                    
                    var newReplyHtml = createCommentHtml(response.comment, true);
                    $repliesContainer.append(newReplyHtml);
                    
                    // Reset và ẩn form
                    $textarea.val('');
                    $form.closest('.reply-form').slideUp(200);
                    
                    showToast(response.message, 'success');
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Gửi');
            }
        });
    });

    // Hàm tạo HTML cho bình luận mới
    function createCommentHtml(comment, isReply) {
        var avatarPath = 'img/' + (comment.avatar || 'default.png');
        var boxClass = isReply ? 'comment-box reply-box' : 'comment-box';
        
        return `
            <div class="${boxClass}" data-comment-id="${comment.comment_id}" id="comment-${comment.comment_id}">
                <div class="comment-header">
                    <img src="${avatarPath}" alt="Avatar" class="comment-avatar">
                    <div class="comment-user-info">
                        <span class="comment-username">
                            ${escapeHtml(comment.display_name)}
                            ${!isReply ? '<span class="comment-badge">Thành viên</span>' : ''}
                        </span>
                        <span class="comment-date">
                            <i class="fas fa-clock"></i> ${comment.created_at}
                        </span>
                    </div>
                </div>
                <div class="comment-content">${escapeHtml(comment.content).replace(/\n/g, '<br>')}</div>
                <div class="comment-actions">
                    <button class="comment-action like-action" data-id="${comment.comment_id}">
                        <i class="fas fa-thumbs-up"></i>
                        <span class="like-count">0</span>
                    </button>
                    <button class="comment-action dislike-action" data-id="${comment.comment_id}">
                        <i class="fas fa-thumbs-down"></i>
                        <span class="dislike-count">0</span>
                    </button>
                    <button class="comment-action reply-toggle" data-id="${comment.comment_id}">
                        <i class="fas fa-reply"></i>
                        <span>Trả lời</span>
                    </button>
                </div>
                <div class="reply-form" id="reply-form-${comment.comment_id}" style="display: none;">
                    <form>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="parent_id" value="${comment.comment_id}">
                        <textarea name="comment" placeholder="Trả lời ${escapeHtml(comment.display_name)}..." required maxlength="500"></textarea>
                        <div class="reply-form-actions">
                            <button type="button" class="cancel-btn" onclick="hideReplyForm(${comment.comment_id})">Hủy</button>
                            <button type="submit">
                                <i class="fas fa-paper-plane"></i> Gửi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    }

    // Hàm escape HTML để tránh XSS
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ===== REPLY FORM =====
    $(document).on('click', '.reply-toggle', function() {
        var commentId = $(this).data('id');
        var $replyForm = $('#reply-form-' + commentId);
        $('.reply-form').not($replyForm).slideUp(200);
        $replyForm.slideToggle(200);
    });

    window.hideReplyForm = function(commentId) {
        $('#reply-form-' + commentId).slideUp(200);
    };

    // ===== LIKE/DISLIKE HANDLERS =====
    $(document).on('click', '.like-action', function() {
        handleReaction($(this), 'toggle_like');
    });

    $(document).on('click', '.dislike-action', function() {
        handleReaction($(this), 'toggle_dislike');
    });

    function handleReaction($this, action) {
        var commentId = $this.data('id');

        if (!<?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>) {
            showToast('Bạn cần đăng nhập để thực hiện!', 'warning');
            setTimeout(() => window.location = 'login.php', 1500);
            return;
        }

        $.ajax({
            type: 'POST',
            url: 'article.php?id=<?= $id ?>',
            data: {
                action: action,
                comment_id: commentId,
                csrf_token: '<?= $csrf_token ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var $parent = $this.closest('.comment-actions');
                    var $likeBtn = $parent.find('.like-action');
                    var $dislikeBtn = $parent.find('.dislike-action');

                    $likeBtn.find('.like-count').text(response.total_likes);
                    $dislikeBtn.find('.dislike-count').text(response.total_dislikes);

                    if (action === 'toggle_like') {
                        $dislikeBtn.removeClass('disliked');
                        if (response.is_liked) {
                            $likeBtn.addClass('liked');
                            showToast('Đã thích bình luận!', 'success');
                        } else {
                            $likeBtn.removeClass('liked');
                        }
                    } else if (action === 'toggle_dislike') {
                        $likeBtn.removeClass('liked');
                        if (response.is_disliked) {
                            $dislikeBtn.addClass('disliked');
                        } else {
                            $dislikeBtn.removeClass('disliked');
                        }
                    }
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
            }
        });
    }

    // ===== COPY LINK =====
    window.copyLink = function() {
        navigator.clipboard.writeText(window.location.href).then(function() {
            showToast('Đã sao chép link bài viết!', 'success');
        });
    };

    // ===== BOOKMARK =====
    const bookmarkBtn = document.getElementById('bookmarkBtn');
    const saveArticleBtn = document.getElementById('saveArticleBtn');
    const articleId = <?= $id ?>;
    
    // Check if already bookmarked
    let bookmarks = JSON.parse(localStorage.getItem('bookmarks') || '[]');
    if (bookmarks.includes(articleId)) {
        bookmarkBtn?.classList.add('active');
        saveArticleBtn?.classList.add('active');
    }
    
    function toggleBookmark() {
        bookmarks = JSON.parse(localStorage.getItem('bookmarks') || '[]');
        const index = bookmarks.indexOf(articleId);
        
        if (index > -1) {
            bookmarks.splice(index, 1);
            bookmarkBtn?.classList.remove('active');
            saveArticleBtn?.classList.remove('active');
            showToast('Đã bỏ lưu bài viết!', 'info');
        } else {
            bookmarks.push(articleId);
            bookmarkBtn?.classList.add('active');
            saveArticleBtn?.classList.add('active');
            showToast('Đã lưu bài viết!', 'success');
        }
        
        localStorage.setItem('bookmarks', JSON.stringify(bookmarks));
    }
    
    bookmarkBtn?.addEventListener('click', toggleBookmark);
    saveArticleBtn?.addEventListener('click', toggleBookmark);

    // ===== FONT SIZE =====
    let currentFontSize = 18;
    const fontSizeBtn = document.getElementById('fontSizeBtn');
    
    fontSizeBtn?.addEventListener('click', function() {
        currentFontSize = currentFontSize >= 22 ? 16 : currentFontSize + 2;
        document.getElementById('articleContent').style.fontSize = currentFontSize + 'px';
        showToast('Cỡ chữ: ' + currentFontSize + 'px', 'info');
    });

    // ===== TOAST NOTIFICATION =====
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = 'toast-notification toast-' + type;
        toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : type === 'warning' ? 'exclamation-circle' : 'info-circle') + '"></i> ' + message;
        
        toast.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 15px 25px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
            color: #fff;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            z-index: 10000;
            animation: slideIn 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Add animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    // ===== SMOOTH SCROLL TO COMMENTS =====
    if (window.location.hash === '#comments') {
        setTimeout(() => {
            document.getElementById('comments').scrollIntoView({ behavior: 'smooth' });
        }, 500);
    }

    // ===== LIKE ARTICLE =====
    const likeArticleBtn = document.getElementById('likeArticleBtn');
    likeArticleBtn?.addEventListener('click', function() {
        this.classList.toggle('active');
        if (this.classList.contains('active')) {
            showToast('Đã thích bài viết!', 'success');
        }
    });

    // ===== REPORT BUTTON =====
    document.getElementById('reportBtn')?.addEventListener('click', function() {
        showToast('Cảm ơn bạn đã báo cáo. Chúng tôi sẽ xem xét!', 'info');
    });
});
</script>

<?php include 'footer.php'; ?>
