<?php
session_start();
require_once('ketnoi.php');

// Lấy ID bài viết từ URL
$article_id = intval($_GET['id'] ?? 0);
if($article_id <= 0){
    echo '<script>alert("Bài viết không hợp lệ!"); window.location.href="index.php";</script>';
    exit();
}

// Lấy thông tin bài viết, chỉ bài được admin duyệt
$sql = "SELECT a.*, c.name as category_name, u.display_name as author_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN users u ON a.author_id = u.user_id
        WHERE a.article_id = $article_id AND a.status='approved'";
$result = mysqli_query($conn, $sql);

if(!$result || mysqli_num_rows($result) == 0){
    echo '<script>alert("Bài viết không tồn tại hoặc chưa được duyệt!"); window.location.href="index.php";</script>';
    exit();
}

$article = mysqli_fetch_assoc($result);
?>

<?php include 'header.php'; ?>

<div class="container py-5">
    <div class="card p-4">
        <h1 class="mb-3"><?= htmlspecialchars($article['title']) ?></h1>

        <div class="mb-2 text-muted small">
            <span>Thể loại: <?= htmlspecialchars($article['category_name']) ?></span> |
            <span>Tác giả: <?= htmlspecialchars($article['author_name']) ?></span> |
            <span>Ngày đăng: <?= date('d/m/Y H:i', strtotime($article['created_at'])) ?></span>
        </div>

        <?php if(!empty($article['featured_image'])): ?>
    <div class="mb-4 text-center">
        <img src="../<?= $article['featured_image'] ?>" alt="Ảnh bài viết" class="img-fluid rounded">
    </div>
<?php endif; ?>


        <div class="article-content">
            <?= $article['content'] ?>
        </div>

        <a href="index.php" class="btn btn-secondary mt-4">← Quay lại danh sách</a>
    </div>
</div>

<style>
.card { background:#fff; border-radius:15px; box-shadow:0 5px 20px rgba(0,0,0,0.1); }
.article-content img { max-width:100%; height:auto; margin:10px 0; border-radius:10px; }
.article-content p { line-height:1.7; margin-bottom:15px; }
</style>

<?php include 'footer.php'; ?>
