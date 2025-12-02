<?php
require_once 'ketnoi.php'; // file kết nối đã có trong project

// Lấy danh mục có slug hoặc name liên quan đến 'review' hoặc 'đánh giá'
$sql_category = "SELECT category_id FROM categories 
                 WHERE slug = 'review' OR name LIKE '%đánh giá%' LIMIT 1";
$category_result = $conn->query($sql_category);
$category_id = 0;

if ($category_result && $category_result->num_rows > 0) {
  $cat = $category_result->fetch_assoc();
  $category_id = $cat['category_id'];
}

// Nếu tìm thấy danh mục, lấy bài viết
$result = null;
if ($category_id > 0) {
  $sql_articles = "
        SELECT a.article_id, a.title, a.excerpt, a.featured_image, a.views, a.created_at, c.name AS category_name
        FROM articles a
        JOIN categories c ON a.category_id = c.category_id
        WHERE a.category_id = $category_id AND a.status = 'published'
        ORDER BY a.created_at DESC
        LIMIT 4
    ";
  $result = $conn->query($sql_articles);
} else {
  $result = false;
}
// === BÀI VIẾT MỚI NHẤT ===
$sql_latest = "
  SELECT a.article_id, a.title, a.excerpt, a.featured_image, a.created_at, u.display_name AS author_name
  FROM articles a
  LEFT JOIN users u ON a.author_id = u.user_id
  WHERE a.status = 'published'
  ORDER BY a.created_at DESC
  LIMIT 3
";
$latest_articles = $conn->query($sql_latest);

// === BÌNH LUẬN NỔI BẬT ===
$sql_comments = "
  SELECT c.content, c.created_at, u.display_name AS user_name, a.title AS article_title, u.username
  FROM comments c
  JOIN users u ON c.user_id = u.user_id
  JOIN articles a ON c.article_id = a.article_id
  ORDER BY c.created_at DESC
  LIMIT 4
";
$latest_comments = $conn->query($sql_comments);
?>
<?php include 'header.php'; ?>




<!-- Hero section -->
<section class="hero-section">
  <div class="hero-slider owl-carousel">
    <div class="hs-item set-bg" data-setbg="img/bgr1.jpg">
      <div class="hs-text">
        <div class="container">
          <h2>Những <span>Trò Chơi</span> Hay Nhất Hiện Nay</h2>
          <p>Khám phá thế giới game đầy hấp dẫn, với trải nghiệm mượt mà và đồ họa đỉnh cao.<br>
            Cập nhật tin tức, sự kiện và giải đấu mới nhất dành cho game thủ.</p>
          <a href="#" class="site-btn">Xem thêm</a>
        </div>
      </div>
    </div>
    <div class="hs-item set-bg" data-setbg="img/bgr3.jpg">
      <div class="hs-text">
        <div class="container">
          <h2>Top <span>Game</span> Đáng Chơi Nhất</h2>
          <p>Khám phá những tựa game hot nhất, từ hành động, phiêu lưu đến chiến thuật.<br>
            Cập nhật bài viết và review mới mỗi ngày.</p>
          <a href="#" class="site-btn">Xem thêm</a>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Hero section end -->


<!-- Tin mới -->
<div class="latest-news-section">
  <div class="ln-title">Tin mới nhất</div>
  <div class="news-ticker">
    <div class="news-ticker-contant">
      <?php
      // Lấy bài viết mới trong 7 ngày gần đây
      $sql_news = "
        SELECT a.title, a.slug, a.created_at, c.name AS category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.category_id
        WHERE a.status = 'published'
        ORDER BY a.created_at DESC
        LIMIT 10
      ";
      $news = $conn->query($sql_news);

      if ($news && $news->num_rows > 0) {
        $today = new DateTime();

        while ($n = $news->fetch_assoc()) {
          $created = new DateTime($n['created_at']);
          $diff_days = $today->diff($created)->days;

          // Gắn nhãn "mới" nếu trong 7 ngày, ngược lại dùng tên danh mục
          if ($diff_days <= 7) {
            $label = '<span class="new">mới</span>';
          } else {
            $label_name = $n['category_name'] ? strtolower($n['category_name']) : 'tin';
            $label = '<span class="' . htmlspecialchars($label_name) . '">' . htmlspecialchars($n['category_name'] ?? 'tin') . '</span>';
          }

          echo '
          <div class="nt-item">
            ' . $label . '
            <a href="article.php?slug=' . urlencode($n['slug']) . '">' . htmlspecialchars($n['title']) . '</a>
          </div>';
        }
      } else {
        echo '<div class="nt-item"><span>Thông báo</span>Chưa có bài viết nào.</div>';
      }
      ?>
    </div>
  </div>
</div>
<!-- Tin mới end -->


<!-- Feature section -->
<section class="feature-section spad">
  <div class="container">
    <div class="row g-4">
      <?php
      $sql_feature = "
        SELECT a.article_id, a.title, a.slug, a.featured_image, a.created_at, a.views,
               c.name AS category_name,
               (SELECT COUNT(*) FROM comments cm WHERE cm.article_id = a.article_id) AS comment_count
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.category_id
        WHERE a.status = 'published'
        ORDER BY a.created_at DESC
        LIMIT 4
      ";

      $features = $conn->query($sql_feature);

      if ($features && $features->num_rows > 0) {
        $today = new DateTime();

        while ($f = $features->fetch_assoc()) {

          // Tính số ngày
          $created = new DateTime($f['created_at']);
          $diff_days = $today->diff($created)->days;

          // Nhãn
          if ($diff_days <= 7) {
            $label = '<span class="cata new">Mới</span>';
          } else {
            $label_class = strtolower($f['category_name'] ?? 'tin');
            $label = '<span class="cata ' . htmlspecialchars($label_class) . '">' . htmlspecialchars($f['category_name'] ?? 'Tin') . '</span>';
          }

          // -----------------------------
          //  FIX ĐƯỜNG DẪN ẢNH (CHUẨN)
          // -----------------------------
          $img_file = trim($f['featured_image']);

          // nếu trong DB còn /uploads/... thì chỉ lấy tên file cuối
          $img_file = basename($img_file);

          // ghép vào đúng thư mục chứa ảnh
          $img_path = '/Webgame/game2/gamebat/img/' . $img_file;

          echo '
          <div class="col-lg-3 col-md-6">
            <div class="feature-item position-relative overflow-hidden rounded shadow-sm">
              <img src="' . $img_path . '" alt="' . htmlspecialchars($f['title']) . '" class="img-fluid feature-img">

              ' . $label . '

              <div class="fi-content text-white p-3 d-flex flex-column justify-content-end h-100">
                <h5 class="mb-2">
                  <a href="article.php?slug=' . urlencode($f['slug']) . '" class="text-white text-decoration-none">
                    ' . htmlspecialchars($f['title']) . '
                  </a>
                </h5>

                <div class="fi-meta">
                  <span><i class="fa fa-eye"></i> ' . number_format((int)$f['views']) . '</span>
                  <span><i class="fa fa-comments"></i> ' . (int)$f['comment_count'] . '</span>
                </div>
              </div>
            </div>
          </div>';
        }
      } else {
        echo '<p class="text-center w-100">Chưa có bài viết nào để hiển thị.</p>';
      }
      ?>
    </div>
  </div>
</section>



<style>
.feature-section .feature-item {
    position: relative;
    height: 300px;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}

.feature-section .feature-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 35px rgba(0,0,0,0.3);
}

.feature-item .feature-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.feature-item:hover .feature-img {
    transform: scale(1.05);
}

.feature-item .cata {
    position: absolute;
    top: 15px;
    left: 15px;
    background: #f39c12;
    padding: 5px 10px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    border-radius: 3px;
    z-index: 2;
}

.feature-item .cata.new {
    background: #e74c3c;
}

.feature-item .fi-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
    padding: 20px;
    z-index: 2;
}

.feature-item .fi-content h5 {
    font-size: 16px;
    line-height: 1.3;
}

.feature-item .fi-content h5 a:hover {
    color: #f39c12;
}

.feature-item .fi-meta {
    margin-top: 8px;
    font-size: 13px;
    color: #ddd;
}

.feature-item .fi-meta span {
    margin-right: 15px;
}

.feature-item .fi-meta i {
    margin-right: 5px;
    color: #f39c12;
}
</style>
<!-- Feature section end -->

<style>
.feature-section .feature-item {
    position: relative;
    height: 320px;
    cursor: pointer;
    overflow: hidden;
    border-radius: 8px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-section .feature-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 35px rgba(0,0,0,0.3);
}

.feature-item .feature-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.feature-item:hover .feature-img {
    transform: scale(1.05);
}

.feature-item .cata {
    position: absolute;
    top: 15px;
    left: 15px;
    background: #f39c12;
    padding: 5px 10px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    border-radius: 3px;
    z-index: 2;
}

.feature-item .cata.new {
    background: #e74c3c;
}

.feature-item .fi-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 100%;
    background: linear-gradient(to top, rgba(0,0,0,0.7), transparent 50%);
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 20px;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 2;
}

.feature-item:hover .fi-overlay {
    opacity: 1;
}

.feature-item .fi-overlay h5 a {
    color: #fff;
    font-size: 16px;
    text-decoration: none;
}

.feature-item .fi-overlay h5 a:hover {
    color: #f39c12;
}

.feature-item .fi-overlay p {
    font-size: 13px;
    margin-bottom: 8px;
}

.feature-item .fi-overlay .fi-meta {
    font-size: 12px;
    color: #ddd;
    margin-bottom: 8px;
}

.feature-item .fi-overlay .fi-meta span {
    margin-right: 15px;
}

.feature-item .fi-overlay .fi-meta i {
    color: #f39c12;
    margin-right: 5px;
}

.feature-item .fi-overlay .btn {
    font-size: 12px;
    padding: 5px 10px;
}
</style>
<!-- Feature section end -->


<section class="recent-game-section spad set-bg" data-setbg="img/recent-game-bg.png">
  <div class="container">
    <div class="section-title">
      <div class="cata new">mới</div>
      <h2>Top Game Gần Đây</h2>
    </div>
    <div class="row">
      <?php
      $sql_recent = "
        SELECT a.article_id, a.title, a.slug, a.featured_image, a.excerpt, a.created_at, a.views,
               c.name AS category_name,
               (SELECT COUNT(*) FROM comments cm WHERE cm.article_id = a.article_id) AS comment_count
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.category_id
        WHERE a.status = 'published' AND a.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY a.created_at DESC
        LIMIT 3
      ";
      $recent = $conn->query($sql_recent);

      if ($recent && $recent->num_rows > 0) {
        while ($row = $recent->fetch_assoc()) {

          // -----------------------------
          // Logic ảnh chuẩn như Feature section
          // -----------------------------
          $img_file = trim($row['featured_image']);
          $img_file = basename($img_file); // chỉ lấy tên file cuối
          $img_path = '/Webgame/game2/gamebat/img/' . $img_file;

          if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $img_path) || empty($img_file)) {
              $img_path = '/Webgame/game2/gamebat/img/default-article.png';
          }

          echo '
          <div class="col-lg-4 col-md-6">
            <div class="recent-game-item">
              <a href="article.php?slug=' . urlencode($row['slug']) . '">
                <div class="rgi-thumb set-bg" data-setbg="' . $img_path . '">
                  <div class="cata new">mới</div>
                </div>
              </a>
              <div class="rgi-content">
                <h5><a href="article.php?slug=' . urlencode($row['slug']) . '">' . htmlspecialchars($row['title']) . '</a></h5>
                <div class="rgi-meta">
                  <span><i class="fa fa-eye"></i> ' . number_format((int)$row['views']) . ' lượt xem</span>
                  <span><i class="fa fa-comments"></i> ' . (int)$row['comment_count'] . ' bình luận</span>
                </div>
              </div>
            </div>
          </div>';
        }
      } else {
        echo "<p class='text-center w-100'>Chưa có bài viết nào trong 7 ngày gần đây.</p>";
      }
      ?>
    </div>
  </div>
</section>

<script>
document.querySelectorAll('.set-bg').forEach(el => {
    const bg = el.getAttribute('data-setbg');
    if(bg) el.style.backgroundImage = 'url(' + bg + ')';
});
</script>

<style>
  .rgi-content {
    background: rgba(0, 0, 0, 0.6);
    padding: 15px;
    color: #fff;
    text-align: center;
  }

  .rgi-content h5 {
    font-size: 18px;
    margin-bottom: 10px;
  }

  .rgi-meta {
    font-size: 14px;
    color: #ddd;
  }

  .rgi-meta span {
    margin-right: 12px;
  }

  .rgi-meta i {
    margin-right: 5px;
    color: #f39c12;
  }
</style>


<!-- Giải đấu -->
<section class="tournaments-section spad">
  <div class="container">
    <div class="tournament-title">Giải Đấu</div>
    <div class="row">
      <?php
      $sql_tournaments = "
        SELECT a.article_id, a.title, a.slug, a.featured_image, a.created_at,
               a.views, c.name AS category_name, au.name AS author_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN authors au ON a.author_id = au.author_id
        WHERE c.name = 'Esports' AND a.status = 'published'
        ORDER BY a.created_at DESC
        LIMIT 2
      ";
      $tournaments = $conn->query($sql_tournaments);

      if ($tournaments && $tournaments->num_rows > 0) {
          $today = new DateTime();

          while ($t = $tournaments->fetch_assoc()) {
              $created = new DateTime($t['created_at']);
              $diff_days = $today->diff($created)->days;

              // Nhãn "Mới" hoặc "Esports"
              $label = ($diff_days <= 7)
                ? '<div class="ti-notic">Mới</div>'
                : '<div class="ti-notic">Esports</div>';

              // -----------------------------
              // Logic ảnh giống Feature section
              // -----------------------------
              $img_file = trim($t['featured_image']);
              $img_file = basename($img_file); // chỉ lấy tên file cuối
              $img_path = '/Webgame/game2/gamebat/img/' . $img_file;

              // Nếu không có ảnh, dùng ảnh mặc định
              if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $img_path) || empty($img_file)) {
                  $img_path = '/Webgame/game2/gamebat/img/default-article.png';
              }

              echo '
              <div class="col-md-6">
                <div class="tournament-item mb-4 mb-lg-0">
                  ' . $label . '
                  <div class="ti-content">
                    <a href="article.php?slug=' . urlencode($t['slug']) . '">
                      <div class="ti-thumb set-bg" data-setbg="' . $img_path . '"></div>
                    </a>
                    <div class="ti-text">
                      <h4><a href="article.php?slug=' . urlencode($t['slug']) . '">' . htmlspecialchars($t['title']) . '</a></h4>
                      <ul>
                        <li><span>Ngày đăng:</span> ' . date('d/m/Y', strtotime($t['created_at'])) . '</li>
                        <li><span>Tác giả:</span> ' . htmlspecialchars($t['author_name'] ?? 'Không rõ') . '</li>
                        <li><span>Lượt xem:</span> ' . (int)$t['views'] . '</li>
                        <li><span>Chuyên mục:</span> ' . htmlspecialchars($t['category_name']) . '</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>';
          }
      } else {
          echo "<p class='text-center w-100'>Hiện chưa có bài viết Esports nào được đăng.</p>";
      }
      ?>
    </div>
  </div>
</section>

<script>
document.querySelectorAll('.set-bg').forEach(el => {
    const bg = el.getAttribute('data-setbg');
    if(bg) el.style.backgroundImage = 'url(' + bg + ')';
});
</script>


<!-- Review -->
<section class="review-section spad set-bg" data-setbg="img/review-bg.png">
  <div class="container">
    <div class="section-title">
      <div class="cata new">mới</div>
      <h2>Đánh Giá Gần Đây</h2>
    </div>
    <div class="row">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>

          <?php
          // -----------------------------
          // Logic ảnh giống Feature section
          // -----------------------------
          $img_file = trim($row['featured_image']);
          $img_file = basename($img_file); // chỉ lấy tên file cuối
          $img_path = '/Webgame/game2/gamebat/img/' . $img_file;

          if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $img_path) || empty($img_file)) {
              $img_path = '/Webgame/game2/gamebat/img/default-article.png';
          }
          ?>

          <div class="col-lg-3 col-md-6">
            <div class="review-item">
              <div class="review-cover set-bg" data-setbg="<?php echo $img_path; ?>">
                <div class="score yellow">
                  <?php echo number_format(rand(85, 99)/10, 1); ?>
                </div>
              </div>
              <div class="review-text">
                <h5><?php echo htmlspecialchars($row['title']); ?></h5>
                <p><?php echo htmlspecialchars($row['excerpt']); ?></p>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12 text-center">
          <p>Chưa có bài đánh giá nào.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
document.querySelectorAll('.set-bg').forEach(el => {
    const bg = el.getAttribute('data-setbg');
    if(bg) el.style.backgroundImage = 'url(' + bg + ')';
});
</script>



<?php include 'footer.php'; ?>

</body>

</html>