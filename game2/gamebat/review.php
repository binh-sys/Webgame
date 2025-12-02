<?php
require_once 'ketnoi.php';

// -- ensure $ketnoi exists (patch)
if (!isset($ketnoi) || $ketnoi === null) {
    if (isset($conn) && $conn) $ketnoi = $conn;
    elseif (isset($mysqli) && $mysqli) $ketnoi = $mysqli;
    elseif (isset($link) && $link) $ketnoi = $link;
    else {
        $ketnoi = new mysqli("localhost", "root", "", "webtintuc");
        if ($ketnoi->connect_error) die("DB ERROR: ".$ketnoi->connect_error);
    }
}

/* --- Lấy category review --- */
$category_id = 0;
$q = "SELECT category_id FROM categories WHERE slug='review' OR name LIKE '%review%' LIMIT 1";
$r = $ketnoi->query($q);
if ($r && $r->num_rows > 0) {
    $category_id = intval($r->fetch_assoc()['category_id']);
}
$r?->free();

/* --- LẤY DANH SÁCH REVIEW ĐẦY ĐỦ (dùng cho phần lớn) --- */
$review_list = [];
if ($category_id > 0) {
    $sql = "SELECT article_id, title, slug, excerpt, featured_image, created_at
            FROM articles
            WHERE category_id = $category_id AND status='published'
            ORDER BY created_at DESC
            LIMIT 12";
    $rs = $ketnoi->query($sql);
    if ($rs) {
        while ($row = $rs->fetch_assoc()) {
            $review_list[] = $row;
        }
        $rs->free();
    }
}

/* --- LẤY 4 BÀI GẦN ĐÂY NHẤT CHO RECENT REVIEWS (review-dark) --- */
$recent_reviews = [];
if ($category_id > 0) {
    $sql2 = "SELECT article_id, title, slug, excerpt, featured_image
             FROM articles
             WHERE category_id = $category_id AND status='published'
             ORDER BY created_at DESC
             LIMIT 4";
    $rs2 = $ketnoi->query($sql2);
    if ($rs2) {
        while ($row = $rs2->fetch_assoc()) {
            $recent_reviews[] = $row;
        }
        $rs2->free();
    }
}

$default_img = "img/review/default.jpg";
?>

<?php include 'header.php'; ?>
<!-- Latest news section -->
<div class="latest-news-section">
    <div class="ln-title">Review Mới Nhất</div>
    <div class="news-ticker">
        <div class="news-ticker-contant">
            <?php if (!empty($latest_news)): ?>
                <?php foreach ($latest_news as $news): ?>
                    <div class="nt-item">
                        <span class="new">new</span>
                        <a href="article.php?slug=<?= htmlspecialchars($news['slug']) ?>">
                            <?= htmlspecialchars($news['title']) ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="nt-item"><span class="new">new</span>Chưa có bài viết review.</div>
            <?php endif; ?>
        </div>
    </div>
</div>


	<!-- Page info section -->
	<section class="page-info-section set-bg" data-setbg="img/page-top-bg/3.jpg">
		<div class="pi-content">
			<div class="container">
				<div class="row">
					<div class="col-xl-5 col-lg-6 text-white">
						<h2>Game reviews</h2>
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec malesuada lorem maximus mauris scelerisque, at rutrum nulla dictum.</p>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- Page info section -->


	<!-- Page section -->
<section class="page-section review-page spad">
    <div class="container">
        <div class="row">

            <?php if (!empty($review_list)): ?>
                <?php foreach ($review_list as $item): ?>
                    <?php
                        $img = (!empty($item['featured_image'])) ? $item['featured_image'] : $default_img;
                    ?>
                    <div class="col-md-6">
                        <div class="review-item">

                            <div class="review-cover set-bg" data-setbg="<?= $img ?>">
                                <div class="score yellow">9.3</div>
                            </div>

                            <div class="review-text">
                                <h4>
                                    <a href="article.php?slug=<?= $item['slug'] ?>">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </a>
                                </h4>

                                <div class="rating">
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star is-fade"></i>
                                </div>

                                <p>
                                    <?= htmlspecialchars($item['excerpt'] ?? "Không có mô tả.") ?>
                                </p>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <p class="text-white text-center">Chưa có bài review nào.</p>
            <?php endif; ?>

        </div>

        <div class="text-center pt-4">
            <button class="site-btn btn-sm">Load More</button>
        </div>

    </div>
</section>
<!-- Page section end -->



	<!-- Review section -->
<section class="review-section review-dark spad set-bg" data-setbg="img/review-bg-2.jpg">
    <div class="container">
        <div class="section-title text-white">
            <div class="cata new">new</div>
            <h2>Recent Reviews</h2>
        </div>
        <div class="row text-white">

            <?php if (!empty($recent_reviews)): ?>
                <?php foreach ($recent_reviews as $item): ?>
                    <?php
                        $img = (!empty($item['featured_image'])) ? $item['featured_image'] : $default_img;
                    ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="review-item">

                            <div class="review-cover set-bg" data-setbg="<?= $img ?>">
                                <div class="score yellow">9.3</div>
                            </div>

                            <div class="review-text">
                                <h5>
                                    <a style="color:white;" href="article.php?slug=<?= $item['slug'] ?>">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </a>
                                </h5>
                                <p><?= htmlspecialchars($item['excerpt'] ?? "Không có mô tả.") ?></p>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <p class="text-white">Không có bài viết gần đây.</p>
            <?php endif; ?>

        </div>
    </div>
</section>
<!-- Review section end -->



<?php include 'footer.php'; ?>
</body>

</html>