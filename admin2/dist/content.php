<?php
require_once(__DIR__ . '/ketnoi.php');

// === Thống kê dữ liệu ===
$total_articles = $ketnoi->query("SELECT COUNT(*) AS total FROM articles")->fetch_assoc()['total'];
$total_users = $ketnoi->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$total_comments = $ketnoi->query("SELECT COUNT(*) AS total FROM comments")->fetch_assoc()['total'];
$total_favorites = $ketnoi->query("SELECT COUNT(*) AS total FROM favorites")->fetch_assoc()['total'];

// === Biểu đồ bài viết theo danh mục ===
$chart_categories = $ketnoi->query("
    SELECT c.name AS category, COUNT(a.article_id) AS count 
    FROM categories c 
    LEFT JOIN articles a ON c.category_id = a.category_id 
    GROUP BY c.category_id
");
$categories = [];
$counts = [];
while ($row = $chart_categories->fetch_assoc()) {
    $categories[] = $row['category'];
    $counts[] = $row['count'];
}

// === Biểu đồ vai trò người dùng ===
$chart_roles = $ketnoi->query("
    SELECT role, COUNT(user_id) AS total FROM users GROUP BY role
");
$roles = [];
$role_counts = [];
while ($row = $chart_roles->fetch_assoc()) {
    $roles[] = ucfirst($row['role']);
    $role_counts[] = $row['total'];
}
?>

<!-- partial -->
<div class="main-panel">
  <div class="content-wrapper">

    <!-- PHẦN 1: THỐNG KÊ NHANH -->
    <div id="section-summary" class="row mb-4">
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card text-center p-3">
          <h4 class="text-info mb-1">BÀI VIẾT</h4>
          <h2><?= $total_articles ?></h2>
          <p class="text-muted">Tổng số bài viết</p>
        </div>
      </div>
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card text-center p-3">
          <h4 class="text-success mb-1">NGƯỜI DÙNG</h4>
          <h2><?= $total_users ?></h2>
          <p class="text-muted">Tổng tài khoản</p>
        </div>
      </div>
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card text-center p-3">
          <h4 class="text-warning mb-1">BÌNH LUẬN</h4>
          <h2><?= $total_comments ?></h2>
          <p class="text-muted">Tổng bình luận</p>
        </div>
      </div>
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card text-center p-3">
          <h4 class="text-danger mb-1">YÊU THÍCH</h4>
          <h2><?= $total_favorites ?></h2>
          <p class="text-muted">Tổng lượt yêu thích</p>
        </div>
      </div>
    </div>

    <!-- PHẦN 2: QUICK ACTIONS -->
    <div id="section-actions" class="row quick-action-toolbar">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-header d-block d-md-flex">
            <h5 class="mb-0">Quick Actions</h5>
            <p class="ms-auto mb-0">Thao tác nhanh quản lý dữ liệu</p>
          </div>
          <div class="d-md-flex row m-0 quick-action-btns" role="group" aria-label="Quick action buttons">
            <div class="col-sm-6 col-md-3 p-3 text-center btn-wrapper">
              <a href="#section-users" class="btn px-0 text-info scroll-link"><i class="icon-user me-2"></i> Quản lý người dùng</a>
            </div>
            <div class="col-sm-6 col-md-3 p-3 text-center btn-wrapper">
              <a href="#section-articles" class="btn px-0 text-success scroll-link"><i class="icon-docs me-2"></i> Quản lý bài viết</a>
            </div>
            <div class="col-sm-6 col-md-3 p-3 text-center btn-wrapper">
              <a href="#section-comments" class="btn px-0 text-warning scroll-link"><i class="icon-speech me-2"></i> Quản lý bình luận</a>
            </div>
            <div class="col-sm-6 col-md-3 p-3 text-center btn-wrapper">
              <a href="#section-favorites" class="btn px-0 text-danger scroll-link"><i class="icon-heart me-2"></i> Quản lý yêu thích</a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- PHẦN 3: BIỂU ĐỒ -->
    <div id="section-charts" class="row mt-4">
      <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title text-center">Thống kê bài viết theo danh mục</h4>
            <canvas id="chartCategories"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title text-center">Tỉ lệ vai trò người dùng</h4>
            <canvas id="chartRoles"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- PHẦN 4: DANH SÁCH LIÊN KẾT -->
    <div id="section-users" class="row mt-4">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Người dùng gần đây</h4>
            <?php
              $users = $ketnoi->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
            ?>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr><th>ID</th><th>Tên đăng nhập</th><th>Email</th><th>Quyền</th><th>Ngày tạo</th></tr>
                </thead>
                <tbody>
                  <?php while($u = $users->fetch_assoc()): ?>
                    <tr>
                      <td><?= $u['user_id'] ?></td>
                      <td><?= htmlspecialchars($u['username']) ?></td>
                      <td><?= htmlspecialchars($u['email']) ?></td>
                      <td><?= ucfirst($u['role']) ?></td>
                      <td><?= $u['created_at'] ?></td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- PHẦN 5: BÀI VIẾT -->
    <div id="section-articles" class="row mt-4">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Bài viết gần đây</h4>
            <?php
              $articles = $ketnoi->query("SELECT title, created_at FROM articles ORDER BY created_at DESC LIMIT 5");
            ?>
            <ul>
              <?php while($a = $articles->fetch_assoc()): ?>
                <li><b><?= htmlspecialchars($a['title']) ?></b> <small>(<?= $a['created_at'] ?>)</small></li>
              <?php endwhile; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- PHẦN 6: BÌNH LUẬN -->
    <div id="section-comments" class="row mt-4">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Bình luận mới nhất</h4>
            <?php
              $comments = $ketnoi->query("
                SELECT c.content, u.username, c.created_at
                FROM comments c
                JOIN users u ON c.user_id = u.user_id
                ORDER BY c.created_at DESC LIMIT 5
              ");
            ?>
            <ul>
              <?php while($c = $comments->fetch_assoc()): ?>
                <li><b><?= htmlspecialchars($c['username']) ?></b>: <?= htmlspecialchars($c['content']) ?> <small>(<?= $c['created_at'] ?>)</small></li>
              <?php endwhile; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- PHẦN 7: YÊU THÍCH -->
    <div id="section-favorites" class="row mt-4 mb-5">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Danh sách yêu thích gần đây</h4>
            <?php
              $favorites = $ketnoi->query("
                SELECT f.created_at, u.username, a.title
                FROM favorites f
                JOIN users u ON f.user_id = u.user_id
                JOIN articles a ON f.article_id = a.article_id
                ORDER BY f.created_at DESC LIMIT 5
              ");
            ?>
            <ul>
              <?php while($f = $favorites->fetch_assoc()): ?>
                <li><b><?= htmlspecialchars($f['username']) ?></b> yêu thích bài <i><?= htmlspecialchars($f['title']) ?></i> <small>(<?= $f['created_at'] ?>)</small></li>
              <?php endwhile; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- SCRIPT: ChartJS + Scroll smooth -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Smooth scroll
  document.querySelectorAll('.scroll-link').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      const target = document.querySelector(link.getAttribute('href'));
      if (target) {
        window.scrollTo({
          top: target.offsetTop - 80,
          behavior: 'smooth'
        });
      }
    });
  });

  // ChartJS: Bài viết theo danh mục
  const ctx1 = document.getElementById('chartCategories');
  new Chart(ctx1, {
    type: 'bar',
    data: {
      labels: <?= json_encode($categories) ?>,
      datasets: [{
        label: 'Số lượng bài viết',
        data: <?= json_encode($counts) ?>,
        borderWidth: 1
      }]
    },
    options: { scales: { y: { beginAtZero: true } } }
  });

  // ChartJS: Vai trò người dùng
  const ctx2 = document.getElementById('chartRoles');
  new Chart(ctx2, {
    type: 'doughnut',
    data: {
      labels: <?= json_encode($roles) ?>,
      datasets: [{
        data: <?= json_encode($role_counts) ?>,
        borderWidth: 1
      }]
    }
  });
</script>
