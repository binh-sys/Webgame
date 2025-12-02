<?php require_once 'ketnoi.php'; ?>

<?php
/* ================================
   LATEST NEWS
================================ */
$latest_news = [];

$sql_latest = "
    SELECT a.article_id, a.title, a.slug, c.name AS category_name
    FROM articles a
    LEFT JOIN categories c ON a.category_id = c.category_id
    WHERE a.status = 'published'
    ORDER BY a.created_at DESC
    LIMIT 5
";

if ($res = $conn->query($sql_latest)) {
    while ($row = $res->fetch_assoc()) $latest_news[] = $row;
    $res->free();
}


/* ================================
   COMMUNITY MEMBERS
================================ */
$members = [];

$sql_members = "
    SELECT user_id, display_name, username, created_at
    FROM users
    ORDER BY created_at DESC
";

if ($res = $conn->query($sql_members)) {
    while ($row = $res->fetch_assoc()) $members[] = $row;
    $res->free();
}


/* ================================
   COMMUNITY POSTS (comments)
================================ */
$community_posts = [];

$sql_posts = "
    SELECT cm.comment_id, cm.content, cm.created_at,
           u.display_name
    FROM comments cm
    LEFT JOIN users u ON cm.user_id = u.user_id
    ORDER BY cm.created_at DESC
    LIMIT 10
";

if ($res = $conn->query($sql_posts)) {
    while ($row = $res->fetch_assoc()) $community_posts[] = $row;
    $res->free();
}
?>


<?php include 'header.php'; ?>

<!-- Latest news -->
<div class="latest-news-section">
    <div class="ln-title">Latest News</div>
    <div class="news-ticker">
        <div class="news-ticker-contant">

            <?php if (!empty($latest_news)): ?>
                <?php foreach ($latest_news as $n): ?>
                    <div class="nt-item">
                        <span class="<?= strtolower($n['category_name']); ?>">
                            <?= htmlspecialchars($n['category_name']); ?>
                        </span>
                        <a href="article.php?slug=<?= $n['slug']; ?>">
                            <?= htmlspecialchars($n['title']); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="nt-item"><span class="new">new</span>Chưa có bài viết.</div>
            <?php endif; ?>

        </div>
    </div>
</div>
<!-- Latest news end -->


<!-- Page info -->
<section class="page-info-section set-bg" data-setbg="img/page-top-bg/4.jpg">
    <div class="pi-content">
        <div class="container">
            <div class="row">
                <div class="col-xl-5 col-lg-6 text-white">
                    <h2>Our Community</h2>
                    <p>Cộng đồng game thủ - cập nhật liên tục.</p>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- COMMUNITY PAGE -->
<section class="page-section community-page set-bg" data-setbg="img/community-bg.jpg">
    <div class="community-warp spad">
        <div class="container">

            <!-- Members Count -->
            <div class="row">
                <div class="col-md-6">
                    <h3 class="community-top-title">All Members (<?= count($members); ?>)</h3>
                </div>
                <div class="col-md-6 text-lg-right">
                    <form class="community-filter">
                        <label>Show</label>
                        <select>
                            <option>Everything</option>
                        </select>
                    </form>
                </div>
            </div>


            <!-- COMMUNITY POSTS -->
            <ul class="community-post-list">

                <?php foreach ($community_posts as $p): ?>

                    <?php 
                        // Avatar default — vì DB của bạn không có cột avatar
                        $avatar = "img/authors/default.jpg"; 
                    ?>

                    <li>
                        <div class="community-post">

                            <!-- Avatar -->
                            <div class="author-avator set-bg"
                                 data-setbg="<?= $avatar; ?>">
                            </div>

                            <div class="post-content">
                                <h5>
                                    <?= htmlspecialchars($p['display_name']); ?>
                                    <span>posted an update</span>
                                </h5>

                                <div class="post-date">
                                    <?= date('F d, Y', strtotime($p['created_at'])); ?>
                                </div>

                                <p><?= nl2br(htmlspecialchars($p['content'])); ?></p>
                            </div>

                        </div>
                    </li>

                <?php endforeach; ?>

            </ul>


            <!-- PAGINATION -->
            <div class="site-pagination sp-style-2">
                <span class="active">01.</span>
                <a href="#">02.</a>
                <a href="#">03.</a>
            </div>

        </div>
    </div>
</section>


<?php include 'footer.php'; ?>
</body>
</html>
