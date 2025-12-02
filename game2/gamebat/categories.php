<?php
include 'ketnoi.php';
include 'header.php';
?>

<!-- TIN MỚI NHẤT - PHIÊN BẢN FIX (Dán đè phần ticker cũ) -->
<div class="bat-ticker-wrapper" role="region" aria-label="Tin mới nhất">
  <div class="bat-ticker-inner container-fluid">
    <div class="bat-ln-title">Tin Mới Nhất</div>

    <div class="bat-news-ticker" aria-hidden="false">
      <div class="bat-news-ticker-track">
        <?php
        include 'ketnoi.php';
        $sql_latest = "
          SELECT a.article_id, a.title, c.name AS category_name
          FROM articles a
          LEFT JOIN categories c ON a.category_id = c.category_id
          WHERE a.status = 'published'
          ORDER BY a.created_at DESC
          LIMIT 12
        ";
        $res = $conn->query($sql_latest);
        if ($res && $res->num_rows > 0):
          while ($r = $res->fetch_assoc()):
            $cat = htmlspecialchars($r['category_name'] ?? 'Khác');
            $title = htmlspecialchars($r['title'] ?? '');
        ?>
          <span class="bat-nt-item">
            <span class="bat-badge-cat"><?php echo $cat; ?></span>
            <a class="bat-nt-link" href="article.php?id=<?php echo intval($r['article_id']); ?>">
              <?php echo $title; ?>
            </a>
          </span>
        <?php
          endwhile;
        else:
        ?>
          <span class="bat-nt-item">Chưa có bài viết mới.</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- CSS (thêm vào cuối CSS của bạn hoặc file riêng) -->
<style>
/* reset box-sizing cho khối này tránh unexpected sizing */
.bat-ticker-wrapper, .bat-ticker-wrapper * { box-sizing: border-box; }

/* Khung tổng: cố định chiều cao, không cho phồng */
.bat-ticker-wrapper {
  width: 100%;
  background: linear-gradient(90deg, #ff8f1a 0%, #ff6a00 100%);
  box-shadow: 0 2px 6px rgba(0,0,0,0.12);
  z-index: 60;             /* nổi trên hero nhưng thấp hơn navbar nếu cần chỉnh */
  position: relative;
}

/* inner container dùng flex, align giữa, không cho phần tiêu đề co giãn */
.bat-ticker-inner {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0;
  height: 46px;            /* cố định chiều cao */
  overflow: hidden;
}

/* Tiêu đề: KHÔNG được flex-grow, có min-width nhỏ */
.bat-ln-title {
  flex: 0 0 auto;          /* không giãn nở */
  min-width: 140px;
  padding: 0 18px;
  height: 46px;
  line-height: 46px;
  background: #ffb81c;
  color: #fff;
  font-weight: 700;
  font-size: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-right: 1px solid rgba(255,255,255,0.08);
  white-space: nowrap;
}

/* Vùng chạy tin: chiếm phần còn lại */
.bat-news-ticker {
  flex: 1 1 auto;
  height: 46px;
  overflow: hidden;
  position: relative;
}

/* Track chạy (kép nếu cần repeat) */
.bat-news-ticker-track {
  display: inline-block;
  white-space: nowrap;
  line-height: 46px;
  will-change: transform;
  padding-left: 100%;                /* bắt đầu ngoài màn hình phải */
  animation: batTickerMove 36s linear infinite;
}

/* Pause khi hover - tiện cho người đọc */
.bat-news-ticker-track:hover { animation-play-state: paused; }

/* Mỗi item */
.bat-nt-item {
  display: inline-block;
  margin-right: 48px;
  color: #fff;
  vertical-align: middle;
}

/* Badge danh mục */
.bat-badge-cat {
  display: inline-block;
  background: rgba(0,0,0,0.7);
  color: #fff;
  font-size: 11px;
  padding: 4px 8px;
  border-radius: 4px;
  text-transform: uppercase;
  margin-right: 8px;
}

/* Link tiêu đề */
.bat-nt-link {
  color: #fff;
  text-decoration: none;
  font-weight: 600;
}
.bat-nt-link:hover { text-decoration: underline; }

/* Animation: dịch từ phải sang trái */
/* Note: dùng transform translateX dùng % theo chính phần tử track */
@keyframes batTickerMove {
  0%   { transform: translateX(0); }
  100% { transform: translateX(-100%); }
}

/* Responsive: thu nhỏ padding/title trên nhỏ màn hình */
@media (max-width: 576px) {
  .bat-ln-title { min-width: 100px; font-size: 13px; padding: 0 12px; }
  .bat-nt-item { margin-right: 28px; font-size: 13px; }
  .bat-news-ticker-track { animation-duration: 28s; }
}
</style>


<!-- Page info -->
<section class="page-info-section set-bg" data-setbg="img/page-top-bg/1.jpg">
  <div class="pi-content">
    <div class="container">
      <div class="row">
        <div class="col-xl-5 col-lg-6 text-white">
          <h2>Danh Mục Bài Viết</h2>
          <p>Khám phá các bài viết theo từng thể loại game khác nhau.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Page section -->
<section class="page-section recent-game-page spad">
  <div class="container">
    <div class="row">

      <!-- Danh sách bài viết -->
      <div class="col-lg-8">
        <div class="row">
          <?php
          $category_id = isset($_GET['cat']) ? intval($_GET['cat']) : 0;

          // Phân trang
          $limit = 10;
          $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
          $offset = ($page - 1) * $limit;

// Tổng số bài viết đã xuất bản
$count_sql = "SELECT COUNT(*) AS total FROM articles WHERE status='published'";
if ($category_id > 0) {
    $count_sql .= " AND category_id = $category_id";
}
$count_result = $conn->query($count_sql);
$total_articles = $count_result ? ($count_result->fetch_assoc()['total'] ?? 0) : 0;
$total_pages = max(1, ceil($total_articles / $limit));

// Lấy danh sách bài viết đã xuất bản
$sql = "SELECT a.*, c.name AS category_name 
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.category_id
        WHERE a.status='published'";
if ($category_id > 0) {
    $sql .= " AND a.category_id = $category_id";
}
$sql .= " ORDER BY a.created_at DESC LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);


          if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              $image = !empty($row['featured_image']) ? 'img/' . $row['featured_image'] : 'img/default.jpg';
              $title = htmlspecialchars($row['title'] ?? '');
              $excerpt = mb_substr(strip_tags($row['excerpt'] ?? ''), 0, 100) . '...';
              $category = htmlspecialchars($row['category_name'] ?? 'Khác');
              $date = date('d/m/Y', strtotime($row['created_at'] ?? 'now'));

              echo '
              <div class="col-md-6">
                <div class="recent-game-item">
                  <div class="rgi-thumb set-bg" data-setbg="' . $image . '">
                    <div class="cata new">' . $category . '</div>
                  </div>
                  <div class="rgi-content">
                    <h5><a href="article.php?id=' . intval($row['article_id']) . '">' . $title . '</a></h5>
                    <p>' . $excerpt . '</p>
                    <a href="#" class="comment">Ngày đăng: ' . $date . '</a>
                    <div class="rgi-extra">
                      <div class="rgi-star"><img src="img/icons/star.png" alt=""></div>
                      <div class="rgi-heart"><img src="img/icons/heart.png" alt=""></div>
                    </div>
                  </div>
                </div>
              </div>';
            }
          } else {
            echo "<p>Hiện chưa có bài viết nào trong danh mục này.</p>";
          }
          ?>
        </div>

        <!-- Phân trang -->
        <div class="site-pagination">
          <?php
          if ($total_pages > 1) {
            for ($i = 1; $i <= $total_pages; $i++) {
              $active = ($i == $page) ? 'class="active"' : '';
              echo "<a $active href='?page=$i" . ($category_id > 0 ? "&cat=$category_id" : "") . "'>" . str_pad($i, 2, "0", STR_PAD_LEFT) . ".</a>";
            }
          }
          ?>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-lg-4 col-md-7 sidebar pt-5 pt-lg-0">
        <div class="widget-item">
          <form class="search-widget">
            <input type="text" placeholder="Tìm kiếm bài viết...">
            <button><i class="fa fa-search"></i></button>
          </form>
        </div>

        <!-- Latest Posts -->
        <div class="widget-item">
          <h4 class="widget-title">Bài Viết Mới Nhất</h4>
          <div class="latest-blog">
            <?php
            $latest = $conn->query("SELECT * FROM articles ORDER BY created_at DESC LIMIT 3");
            if ($latest && $latest->num_rows > 0) {
              while ($row = $latest->fetch_assoc()) {
                $latest_img = !empty($row['featured_image']) ? 'img/' . $row['featured_image'] : 'img/default.jpg';
                echo '
                <div class="lb-item">
                  <div class="lb-thumb set-bg" data-setbg="' . $latest_img . '"></div>
                  <div class="lb-content">
                    <div class="lb-date">' . date("d/m/Y", strtotime($row['created_at'] ?? 'now')) . '</div>
                    <p>' . htmlspecialchars($row['title'] ?? '') . '</p>
                    <a href="#" class="lb-author">Bởi Admin</a>
                  </div>
                </div>';
              }
            } else {
              echo "<p>Chưa có bài viết nào.</p>";
            }
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

<!-- Scripts -->
<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/jquery.marquee.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
