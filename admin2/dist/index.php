<?php
require_once('ketnoi.php');
ob_start();
session_start();
?>
<!doctype html>
<html lang="vi">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>GameNova Pro — Bảng Quản Trị</title>

  <!-- Icons -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="assets/images/logogame.png" />

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">

  <style>
    /* ==========================
     GameNova Pro — Neon Cyan & Deep Magenta (Arcade)
     ========================== */

    :root {
      --bg-dark: #05030a;
      --panel: rgba(6, 8, 14, 0.82);
      --cyan: #00f6ff;
      --magenta: #ff1bbf;
      --accent-gradient: linear-gradient(90deg, var(--cyan), var(--magenta));
      --glass: rgba(255, 255, 255, 0.03);
    }

    * {
      box-sizing: border-box
    }

    html,
    body {
      height: 100%;
      margin: 0;
      font-family: "Poppins", sans-serif;
      background:
        radial-gradient(circle at 10% 10%, #081026 0%, #030114 50%, #000007 100%);
      color: #dff7ff;
      -webkit-font-smoothing: antialiased
    }

    /* NAVBAR */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 72px;
      padding: 10px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: linear-gradient(180deg, rgba(2, 6, 12, 0.7), rgba(2, 6, 12, 0.45));
      border-bottom: 1px solid rgba(255, 255, 255, 0.03);
      z-index: 1200;
      backdrop-filter: blur(6px);
      box-shadow: 0 8px 40px rgba(0, 0, 0, 0.6), 0 0 30px rgba(0, 0, 0, 0.35) inset;
    }

    .brand-left {
      display: flex;
      align-items: center;
      gap: 14px
    }

    .brand-left img {
      height: 46px;
      transition: transform .18s
    }

    .brand-left img:hover {
      transform: scale(1.05)
    }

    .center-title {
      color: var(--cyan);
      font-weight: 700;
      letter-spacing: 0.8px;
      font-family: "Orbitron", sans-serif
    }

    .nav-right {
      display: flex;
      align-items: center;
      gap: 14px
    }

    .avatar {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      border: 2px solid rgba(255, 255, 255, 0.06);
      object-fit: cover;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.6)
    }

    /* SIDEBAR */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: 260px;
      height: 100vh;
      padding-top: 86px;
      background:
        linear-gradient(180deg, #061225, #040417);
      border-right: 1px solid rgba(255, 255, 255, 0.02);
      box-shadow: inset -8px 0 36px rgba(0, 0, 0, 0.7);
      z-index: 1100;
      overflow: auto;
    }

    .sidebar.minimized {
      width: 86px
    }

    .nav-profile {
      text-align: center;
      padding: 18px 12px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.02)
    }

    .avatar-lg {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      border: 3px solid rgba(255, 255, 255, 0.04);
      box-shadow: 0 8px 26px rgba(255, 27, 191, 0.04)
    }

    .profile-name {
      color: #fff;
      font-weight: 700;
      margin-top: 8px;
      font-size: 15px
    }

    .designation {
      color: rgba(255, 255, 255, 0.55);
      font-size: 12px;
      margin-top: 4px
    }

    .nav-category {
      padding: 12px 18px
    }

    .nav-category span {
      color: var(--cyan);
      font-size: 12px;
      text-transform: uppercase;
      opacity: 0.95;
      font-weight: 700
    }

    .menu-item {
      margin: 8px 10px
    }

    .menu-link {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 12px;
      border-radius: 12px;
      color: #bfefff;
      text-decoration: none;
      transition: all .18s
    }

    .menu-link i {
      font-size: 20px;
      color: var(--cyan);
      transition: all .18s
    }

    .menu-link div {
      font-weight: 600
    }

    .menu-link:hover {
      background: linear-gradient(90deg, rgba(0, 246, 255, 0.06), rgba(255, 27, 191, 0.04));
      box-shadow: 0 10px 24px rgba(0, 0, 0, 0.6);
      border-left: 4px solid var(--cyan);
      transform: translateX(2px)
    }

    .menu-item.active .menu-link {
      background: linear-gradient(90deg, rgba(255, 27, 191, 0.08), rgba(0, 246, 255, 0.06));
      box-shadow: 0 12px 38px rgba(255, 27, 191, 0.05);
      border-left: 4px solid var(--magenta);
      color: #fff
    }

    .menu-item.active .menu-link i {
      color: var(--magenta);
      text-shadow: 0 0 10px var(--magenta)
    }

    .sidebar.minimized .menu-link div,
    .sidebar.minimized .nav-category span,
    .sidebar.minimized .profile-name,
    .sidebar.minimized .designation {
      display: none
    }

    .sidebar.minimized .menu-link i {
      margin: 0 auto;
      width: 100%;
      text-align: center
    }

    /* PAGE BODY WRAPPER */
    .page-body-wrapper {
      margin-left: 260px;
      padding-top: 86px;
      transition: margin-left .28s
    }

    .sidebar.minimized+.page-body-wrapper {
      margin-left: 86px
    }

    /* MAIN PANEL */
    .main-panel {
      margin: 22px 24px;
      padding: 22px;
      min-height: calc(100vh - 140px);
      background:
        linear-gradient(180deg, rgba(8, 10, 14, 0.6), rgba(2, 4, 8, 0.6));
      border-radius: 14px;
      border: 1px solid rgba(255, 255, 255, 0.02);
      box-shadow: 0 14px 48px rgba(0, 0, 0, 0.6);
    }

    /* Layout helpers */
    .row {
      display: flex;
      flex-wrap: wrap;
      margin: 0 -12px
    }

    .col-md-3,
    .col-md-6,
    .col-md-12,
    .col-lg-3 {
      padding: 0 12px
    }

    .col-md-3 {
      width: 25%
    }

    .col-md-6 {
      width: 50%
    }

    .col-md-12,
    .col-lg-12 {
      width: 100%
    }

    @media (max-width: 992px) {
      .col-md-3 {
        width: 50%
      }

      .page-body-wrapper {
        margin-left: 86px
      }
    }

    .card {
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.01), rgba(0, 0, 0, 0.02));
      border-radius: 12px;
      padding: 14px;
      border: 1px solid rgba(255, 255, 255, 0.02)
    }

    .neon-card {
      background: linear-gradient(180deg, rgba(8, 10, 14, 0.75), rgba(6, 8, 12, 0.75));
      border-radius: 12px;
      padding: 14px;
      border: 1px solid rgba(255, 255, 255, 0.02);
      box-shadow: 0 6px 22px rgba(0, 0, 0, 0.6)
    }

    h4,
    h5 {
      margin: 0 0 8px 0;
      color: #cfefff
    }

    /* STAT SQUARES */
    .neon-card-square {
      position: relative;
      background: radial-gradient(circle at 8% 8%, rgba(0, 246, 255, 0.03), rgba(6, 8, 12, 0.9));
      border-radius: 14px;
      padding: 18px;
      min-height: 150px;
      overflow: hidden;
      transition: transform .32s, box-shadow .32s;
      border: 2px solid transparent;
    }

    .neon-card-square .icon {
      font-size: 1.6rem;
      display: inline-block;
      margin-bottom: 6px
    }

    .neon-card-square h5 {
      font-size: 0.95rem;
      font-weight: 800;
      letter-spacing: 0.6px
    }

    .neon-card-square h2 {
      font-size: 2.4rem;
      margin: 8px 0 6px;
      color: #fff;
      font-family: "Orbitron", sans-serif
    }

    .neon-card-square p {
      color: rgba(255, 255, 255, 0.6);
      margin: 0
    }

    .neon-card-square:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 20px 48px rgba(255, 27, 191, 0.07), inset 0 0 40px rgba(0, 246, 255, 0.03)
    }

    .neon-card-square .neon-border::before {
      filter: drop-shadow(0 0 10px var(--neon-color));
    }

    .neon-border {
      position: absolute;
      inset: 0;
      border-radius: 14px;
      pointer-events: none
    }

    .neon-border::before {
      content: "";
      position: absolute;
      inset: -2px;
      border-radius: 14px;
      border: 2px solid var(--neon-color);
      animation: neon-scan 4s linear infinite;
      opacity: 0.92
    }

    @keyframes neon-scan {
      0% {
        clip-path: polygon(0 0, 0 0, 0 0, 0 0);
        opacity: 0
      }

      10% {
        clip-path: polygon(0 0, 100% 0, 0 0, 0 0);
        opacity: 1
      }

      40% {
        clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
        opacity: 1
      }

      70% {
        clip-path: polygon(0 100%, 100% 100%, 100% 0, 0 0);
        opacity: 0.9
      }

      100% {
        clip-path: polygon(0 0, 0 0, 0 0, 0 0);
        opacity: 0
      }
    }

    /* BUTTONS */
    .neon-btn {
      display: inline-block;
      padding: 10px 14px;
      border-radius: 12px;
      font-weight: 700;
      color: #002;
      text-decoration: none;
      margin: 6px;
      border: 1px solid rgba(0, 0, 0, 0.08);
      transition: all .18s;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6)
    }

    .neon-btn.blue {
      background: linear-gradient(90deg, var(--cyan), #4db8ff);
      box-shadow: 0 14px 36px rgba(0, 246, 255, 0.08);
      color: #001
    }

    .neon-btn.purple {
      background: linear-gradient(90deg, #b36bff, var(--magenta));
      color: #fff;
      box-shadow: 0 14px 36px rgba(255, 27, 191, 0.08)
    }

    .neon-btn.green {
      background: linear-gradient(90deg, #00ffa3, #66ffc3);
      box-shadow: 0 14px 36px rgba(0, 255, 163, 0.06);
      color: #001
    }

    .neon-btn.pink {
      background: linear-gradient(90deg, #ff8fd1, var(--magenta));
      box-shadow: 0 14px 36px rgba(255, 27, 191, 0.08);
      color: #001
    }

    .neon-btn:hover {
      transform: translateY(-6px);
      filter: saturate(1.08);
      box-shadow: 0 22px 50px rgba(255, 27, 191, 0.12)
    }

    /* CHARTS */
    .card canvas {
      width: 100% !important;
      height: 320px !important;
      display: block
    }

    @media (max-width:1200px) {
      .card canvas {
        height: 300px !important
      }
    }

    @media (max-width:768px) {
      .card canvas {
        height: 260px !important
      }
    }

    /* TABLE */
    .table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 8px
    }

    .table thead th {
      background: rgba(255, 255, 255, 0.02);
      color: var(--cyan);
      font-weight: 700;
      padding: 10px;
      border: none
    }

    .table tbody tr {
      background: rgba(255, 255, 255, 0.02);
      transition: transform .12s
    }

    .table tbody tr:hover {
      transform: translateY(-6px);
      background: rgba(255, 255, 255, 0.02)
    }

    /* ALERT */
    .alert {
      border-radius: 10px;
      padding: 12px 14px;
      text-align: center;
      background: linear-gradient(90deg, rgba(0, 246, 255, 0.06), rgba(255, 27, 191, 0.04));
      border: 1px solid rgba(0, 246, 255, 0.12);
      color: #eaffff
    }

    /* FOOTER */
    .footer {
      position: fixed;
      bottom: 0;
      left: 260px;
      width: calc(100% - 260px);
      padding: 12px 16px;
      background: linear-gradient(90deg, rgba(3, 6, 10, 0.8), rgba(6, 8, 12, 0.8));
      color: #9fefff;
      border-top: 1px solid rgba(255, 255, 255, 0.02)
    }

    .sidebar.minimized+.page-body-wrapper .footer {
      left: 86px;
      width: calc(100% - 86px)
    }

    /* small screens */
    @media (max-width:992px) {
      .sidebar {
        left: 0
      }

      .page-body-wrapper {
        margin-left: 86px;
        padding-top: 86px
      }

      .footer {
        left: 86px;
        width: calc(100% - 86px)
      }

      .center-title {
        display: none
      }
    }
  </style>
</head>

<body>
  <div class="container-scroller">

    <!-- NAVBAR -->
    <nav class="navbar">
      <div class="brand-left">
        <a href="index.php"><img src="assets/images/logogame.png" alt="logo"></a>
        <div class="center-title">GameNova Pro — Bảng điều khiển</div>
      </div>

      <div class="nav-right">
        <button id="toggleSidebar" class="btn" title="Thu/Phóng sidebar" style="background:transparent;border:none;color:#9fefff;font-size:18px">☰</button>
        <img class="avatar" src="assets/images/faces/face.png" alt="avatar">
      </div>
    </nav>

    <!-- SIDEBAR -->
    <nav class="sidebar" id="sidebar">
      <div class="nav-profile">
        <img class="avatar-lg" src="assets/images/faces/face.png" alt="profile">
        <div class="profile-name">Mr.Binh</div>
        <div class="designation">Quản Trị Viên</div>
      </div>

      <div class="nav-category"><span>Trang Quản Trị</span></div>
      <div class="menu-item <?php if (!isset($_GET['page_layout'])) echo 'active'; ?>">
        <a class="menu-link" href="index.php"><i class='bx bx-home'></i>
          <div>Trang Tổng Quan</div>
        </a>
      </div>

      <div class="nav-category"><span>Quản Lý</span></div>
      <div class="menu-item <?php if (isset($_GET['page_layout']) && $_GET['page_layout'] == 'danhsachbaiviet') echo 'active'; ?>">
        <a class="menu-link" href="?page_layout=danhsachbaiviet"><i class='bx bx-news'></i>
          <div>Bài Viết</div>
        </a>
      </div>
      <div class="menu-item <?php if (isset($_GET['page_layout']) && $_GET['page_layout'] == 'danhsachchuyenmuc') echo 'active'; ?>">
        <a class="menu-link" href="?page_layout=danhsachchuyenmuc"><i class='bx bx-category-alt'></i>
          <div>Chuyên Mục</div>
        </a>
      </div>
      <div class="menu-item <?php if (isset($_GET['page_layout']) && $_GET['page_layout'] == 'danhsachthegame') echo 'active'; ?>">
        <a class="menu-link" href="?page_layout=danhsachthegame"><i class='bx bx-joystick'></i>
          <div>Thẻ Game</div>
        </a>
      </div>
      <div class="menu-item <?php if (isset($_GET['page_layout']) && $_GET['page_layout'] == 'danhsachbinhluan') echo 'active'; ?>">
        <a class="menu-link" href="?page_layout=danhsachbinhluan"><i class='bx bx-message-dots'></i>
          <div>Bình Luận</div>
        </a>
      </div>
      <div class="menu-item <?php if (isset($_GET['page_layout']) && $_GET['page_layout'] == 'danhsachyeuthich') echo 'active'; ?>">
        <a class="menu-link" href="?page_layout=danhsachyeuthich"><i class='bx bx-heart'></i>
          <div>Yêu Thích</div>
        </a>
      </div>
      <div class="menu-item <?php if (isset($_GET['page_layout']) && $_GET['page_layout'] == 'danhsachlienthethegame') echo 'active'; ?>">
        <a class="menu-link" href="?page_layout=danhsachlienthethegame"><i class='bx bx-link-alt'></i>
          <div>Liên Kết Thẻ</div>
        </a>
      </div>

      <div class="nav-category"><span>Quản lý Người Dùng</span></div>
      <div class="menu-item <?php if (isset($_GET['page_layout']) && $_GET['page_layout'] == 'danhsachnguoidung') echo 'active'; ?>">
        <a class="menu-link" href="?page_layout=danhsachnguoidung"><i class='bx bx-user-circle'></i>
          <div>Người Dùng</div>
        </a>
      </div>
      <div class="menu-item <?php if (isset($_GET['page_layout']) && $_GET['page_layout'] == 'danhsachtacgia') echo 'active'; ?>">
        <a class="menu-link" href="?page_layout=danhsachtacgia"><i class='bx bx-id-card'></i>
          <div>Tác Giả</div>
        </a>
      </div>
    </nav>

    <!-- PAGE BODY WRAPPER -->
    <div class="page-body-wrapper">
      <!-- notifications -->
      <div style="position:fixed; top:86px; left:50%; transform:translateX(-50%); z-index:1300; width:48%;">
        <?php if (isset($_SESSION['message'])): ?>
          <div class="alert"><?= $_SESSION['message'];
                              unset($_SESSION['message']); ?></div>
        <?php elseif (isset($_SESSION['error'])): ?>
          <div class="alert" style="background:linear-gradient(90deg, rgba(255,27,191,0.06), rgba(0,246,255,0.03));"><?= $_SESSION['error'];
                                                                                                                      unset($_SESSION['error']); ?></div>
        <?php endif; ?>
      </div>

      <!-- MAIN CONTENT (if page_layout -> include files; else show dashboard content) -->
      <div class="main-panel">
        <?php
        if (isset($_GET["page_layout"])) {
          switch ($_GET["page_layout"]) {
            case "danhsachbaiviet":
              require_once 'articles.php';
              break;
            case "them_baiviet":
              require_once 'them_baiviet.php';
              break;
            case "sua_baiviet":
              require_once 'sua_baiviet.php';
              break;
            case "xoa_baiviet":
              require_once 'xoa_baiviet.php';
              break;
            case "duyet_baiviet":
              require_once 'duyet_baiviet.php';
              break;

            case "danhsachchuyenmuc":
              require_once 'categories.php';
              break;
            case "themchuyenmuc":
              require_once 'themchuyenmuc.php';
              break;
            case "suachuyenmuc":
              require_once 'suachuyenmuc.php';
              break;
            case "xoachuyenmuc":
              require_once 'xoachuyenmuc.php';
              break;

            case "danhsachthegame":
              require_once 'tags.php';
              break;
            case "themthegame":
              require_once 'themthegame.php';
              break;
            case "suathegame":
              require_once 'suathegame.php';
              break;
            case "xoathegame":
              require_once 'xoathegame.php';
              break;

            case "danhsachbinhluan":
              require_once 'comments.php';
              break;
            case "thembinhluan":
              require_once 'thembinhluan.php';
              break;
            case "suabinhluan":
              require_once 'suabinhluan.php';
              break;
            case "xoabinhluan":
              require_once 'xoabinhluan.php';
              break;

            case "danhsachyeuthich":
              require_once 'favorites.php';
              break;
            case "themyeuthich":
              require_once 'themyeuthich.php';
              break;
            case "suayeuthich":
              require_once 'suayeuthich.php';
              break;
            case "xoayeuthich":
              require_once 'xoayeuthich.php';
              break;

            case "danhsachlienthethegame":
              require_once 'article_tags.php';
              break;
            case "themlienthethegame":
              require_once 'themlienthethegame.php';
              break;
            case "sualienthethegame":
              require_once 'sualienthethegame.php';
              break;
            case "xoalienthethegame":
              require_once 'xoalienthethegame.php';
              break;

            case "danhsachnguoidung":
              require_once 'users.php';
              break;
            case "themus":
              require_once 'themus.php';
              break;
            case "suaus":
              require_once 'suaus.php';
              break;
            case "xoaus":
              require_once 'xoaus.php';
              break;

            case "danhsachtacgia":
              require_once 'authors.php';
              break;
            case "themtacgia":
              require_once 'themtacgia.php';
              break;
            case "suatacgia":
              require_once 'suatacgia.php';
              break;
            case "xoatacgia":
              require_once 'xoatacgia.php';
              break;

            default:
              require_once 'content.php';
              break;
          }
        } else {
          /* -------- DASHBOARD CONTENT INLINE (so index.php is standalone) -------- */

          // Ensure DB connection present (already required above)
          // Statistics
          $total_articles  = (int)($ketnoi->query("SELECT COUNT(*) AS total FROM articles")->fetch_assoc()['total'] ?? 0);
          $total_users     = (int)($ketnoi->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0);
          $total_comments  = (int)($ketnoi->query("SELECT COUNT(*) AS total FROM comments")->fetch_assoc()['total'] ?? 0);
          $total_favorites = (int)($ketnoi->query("SELECT COUNT(*) AS total FROM favorites")->fetch_assoc()['total'] ?? 0);

          // Charts data
          $categories = $counts = [];
          $q = $ketnoi->query("SELECT c.name AS category, COUNT(a.article_id) AS cnt FROM categories c LEFT JOIN articles a ON c.category_id=a.category_id GROUP BY c.category_id");
          while ($r = $q->fetch_assoc()) {
            $categories[] = $r['category'];
            $counts[] = (int)$r['cnt'];
          }

          $roles = $role_counts = [];
          $qr = $ketnoi->query("SELECT role, COUNT(user_id) AS total FROM users GROUP BY role");
          while ($r = $qr->fetch_assoc()) {
            $roles[] = ucfirst($r['role']);
            $role_counts[] = (int)$r['total'];
          }

        ?>

          <!-- DASHBOARD: STAT SQUARES (horizontal) -->
          <div id="section-summary" class="row mb-4 justify-content-center text-center">
            <?php
            $cards = [
              ['icon' => '📰', 'title' => 'BÀI VIẾT', 'count' => $total_articles, 'color' => 'var(--cyan)', 'desc' => 'Tổng số bài viết'],
              ['icon' => '👤', 'title' => 'NGƯỜI DÙNG', 'count' => $total_users, 'color' => '#b36bff', 'desc' => 'Tổng tài khoản'],
              ['icon' => '💬', 'title' => 'BÌNH LUẬN', 'count' => $total_comments, 'color' => '#00ffa3', 'desc' => 'Tổng bình luận'],
              ['icon' => '❤️', 'title' => 'YÊU THÍCH', 'count' => $total_favorites, 'color' => 'var(--magenta)', 'desc' => 'Tổng lượt yêu thích']
            ];
            foreach ($cards as $index => $c): ?>
              <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                <div class="card neon-card-square animate-card" style="--delay:<?= $index * 0.08 ?>s; --neon-color: <?= $c['color'] ?>;">
                  <div class="icon" style="color:<?= $c['color'] ?>;"><?= $c['icon'] ?></div>
                  <h5 style="color:<?= $c['color'] ?>;"><?= $c['title'] ?></h5>
                  <h2><?= $c['count'] ?></h2>
                  <p class="text-muted small"><?= $c['desc'] ?></p>
                  <span class="neon-border"></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- QUICK ACTIONS -->
          <div class="row mb-4">
            <div class="col-md-12">
              <div class="card neon-card p-3">
                <div style="display:flex;align-items:center;justify-content:space-between">
                  <h5 class="text-glow">⚡ Quick Actions</h5>
                  <small class="text-muted">Thao tác nhanh</small>
                </div>
                <div style="margin-top:12px;display:flex;flex-wrap:wrap;justify-content:center">
                  <a class="neon-btn blue" href="#section-users">👤 Người dùng</a>
                  <a class="neon-btn purple" href="#section-articles">📰 Bài viết</a>
                  <a class="neon-btn green" href="#section-comments">💬 Bình luận</a>
                  <a class="neon-btn pink" href="#section-favorites">❤️ Yêu thích</a>
                </div>
              </div>
            </div>
          </div>

          <!-- CHARTS -->
          <div class="row">
            <div class="col-md-6">
              <div class="card neon-card">
                <div class="card-body">
                  <h4 class="text-center text-glow">📊 Bài viết theo danh mục</h4>
                  <div style="position:relative">
                    <canvas id="chartCategories"></canvas>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card neon-card">
                <div class="card-body">
                  <h4 class="text-center text-glow">🧑‍💻 Vai trò người dùng</h4>
                  <div style="position:relative">
                    <canvas id="chartRoles"></canvas>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- RECENT LISTS -->
          <?php
          $sections = [
            'users' => ['title' => 'Người dùng gần đây', 'query' => "SELECT * FROM users ORDER BY created_at DESC LIMIT 5", 'cols' => ['ID', 'Tên đăng nhập', 'Email', 'Quyền', 'Ngày tạo']],
            'articles' => ['title' => 'Bài viết gần đây', 'query' => "SELECT title, created_at FROM articles ORDER BY created_at DESC LIMIT 5"],
            'comments' => ['title' => 'Bình luận mới nhất', 'query' => "SELECT c.content,u.username,c.created_at FROM comments c JOIN users u ON c.user_id=u.user_id ORDER BY c.created_at DESC LIMIT 5"],
            'favorites' => ['title' => 'Yêu thích gần đây', 'query' => "SELECT f.created_at,u.username,a.title FROM favorites f JOIN users u ON f.user_id=u.user_id JOIN articles a ON f.article_id=a.article_id ORDER BY f.created_at DESC LIMIT 5"]
          ];
          foreach ($sections as $id => $sec):
            $res = $ketnoi->query($sec['query']);
          ?>
            <div id="section-<?= $id ?>" class="row mt-4">
              <div class="col-md-12">
                <div class="card neon-card p-3">
                  <h4 class="text-glow"><?= $sec['title'] ?></h4>
                  <?php if ($id === 'users'): ?>
                    <div class="table-responsive">
                      <table class="table">
                        <thead>
                          <tr><?php foreach ($sec['cols'] as $c) echo "<th>$c</th>"; ?></tr>
                        </thead>
                        <tbody>
                          <?php while ($u = $res->fetch_assoc()): ?>
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
                  <?php else: ?>
                    <ul style="list-style:none;padding-left:0;color:#cfefff">
                      <?php while ($r = $res->fetch_assoc()): ?>
                        <?php if ($id === 'articles'): ?>
                          <li>📰 <b><?= htmlspecialchars($r['title']) ?></b> <small>(<?= $r['created_at'] ?>)</small></li>
                        <?php elseif ($id === 'comments'): ?>
                          <li>💬 <b><?= htmlspecialchars($r['username']) ?></b>: <?= htmlspecialchars($r['content']) ?> <small>(<?= $r['created_at'] ?>)</small></li>
                        <?php else: ?>
                          <li>❤️ <b><?= htmlspecialchars($r['username']) ?></b> yêu thích <i><?= htmlspecialchars($r['title']) ?></i> <small>(<?= $r['created_at'] ?>)</small></li>
                        <?php endif; ?>
                      <?php endwhile; ?>
                    </ul>
                  <?php endif; ?>
                </div>
              </div>
            </div>
        <?php
          endforeach;
        } // end else (dashboard)
        ?>
      </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer">© <?= date('Y') ?> GameNova Pro — Designed with Neon Power Mode</footer>
  </div>

  <!-- SCRIPTS -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('toggleSidebar');
    toggle && toggle.addEventListener('click', () => {
      sidebar.classList.toggle('minimized');
      document.querySelector('.page-body-wrapper').classList.toggle('minimized');
    });

    // Auto-hide alerts
    setTimeout(() => document.querySelectorAll('.alert').forEach(a => a && (a.style.display = 'none')), 3500);

    // Smooth scroll for quick action buttons
    document.querySelectorAll('.neon-btn').forEach(btn => {
      btn.addEventListener('click', e => {
        const href = btn.getAttribute('href');
        if (!href || !href.startsWith('#')) return;
        e.preventDefault();
        const t = document.querySelector(href);
        if (t) window.scrollTo({
          top: t.offsetTop - 80,
          behavior: 'smooth'
        });
      });
    });

    // If dashboard present, init charts + micro-interactions + particle glow + boot-up
    (function() {
      if (!document.getElementById('chartCategories')) return;

      // Data from PHP
      const categories = <?= json_encode($categories ?? []) ?>;
      const counts = <?= json_encode($counts ?? []) ?>;
      const roles = <?= json_encode($roles ?? []) ?>;
      const roleCounts = <?= json_encode($role_counts ?? []) ?>;

      // helper gradient
      function neonGradient(ctx, color) {
        const g = ctx.createLinearGradient(0, 0, 0, 400);
        g.addColorStop(0, color + 'AA');
        g.addColorStop(1, color + '22');
        return g;
      }

      // Categories bar chart
      const ctxCat = document.getElementById('chartCategories').getContext('2d');
      const chartCategories = new Chart(ctxCat, {
        type: 'bar',
        data: {
          labels: categories,
          datasets: [{
            label: 'Số bài viết',
            data: counts,
            backgroundColor: function(ctx) {
              const palette = ['#00f6ff', '#b36bff', '#00ffa3', '#ff7ad1'];
              const color = palette[ctx.dataIndex % palette.length];
              return neonGradient(ctx.chart.ctx, color);
            },
            borderColor: '#071216',
            borderWidth: 1.2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: {
              ticks: {
                color: '#dff7ff'
              },
              grid: {
                color: 'rgba(255,255,255,0.02)'
              }
            },
            y: {
              ticks: {
                color: '#dff7ff'
              },
              beginAtZero: true,
              grid: {
                color: 'rgba(255,255,255,0.02)'
              }
            }
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: '#07131a',
              titleColor: '#bffcff',
              bodyColor: '#dff7ff',
              borderColor: 'rgba(0,246,255,0.2)',
              borderWidth: 1
            }
          },
          interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: true
          },
          animation: {
            duration: 900,
            easing: 'easeOutQuart'
          },
          onHover: (evt, elements) => {
            evt.native.target.style.cursor = elements.length ? 'pointer' : 'default';
            if (elements.length) {
              const el = elements[0];
              chartCategories.setActiveElements([{
                datasetIndex: el.datasetIndex,
                index: el.index
              }]);
              chartCategories.update();
            } else {
              chartCategories.setActiveElements([]);
              chartCategories.update();
            }
          },
          transitions: {
            active: {
              animation: {
                duration: 300,
                easing: 'easeOutBounce'
              }
            }
          }
        }
      });

      // Roles doughnut
      const ctxRole = document.getElementById('chartRoles').getContext('2d');
      const chartRoles = new Chart(ctxRole, {
        type: 'doughnut',
        data: {
          labels: roles,
          datasets: [{
            data: roleCounts,
            backgroundColor: ['#b36bff', '#00ffa3', '#ff7ad1', '#00f6ff'],
            borderColor: '#071216',
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '60%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                color: '#dff7ff'
              }
            },
            tooltip: {
              backgroundColor: '#07131a',
              titleColor: '#bffcff',
              bodyColor: '#dff7ff'
            }
          },
          animation: {
            animateRotate: true,
            duration: 1000,
            easing: 'easeOutElastic'
          },
          onHover: (evt, elements) => {
            evt.native.target.style.cursor = elements.length ? 'pointer' : 'default';
            if (elements.length) {
              const el = elements[0];
              const segment = chartRoles.getDatasetMeta(0).data[el.index];
              segment.options.borderColor = '#00f6ff';
              segment.options.borderWidth = 3;
              chartRoles.update();
            } else {
              chartRoles.data.datasets[0].borderColor = '#071216';
              chartRoles.data.datasets[0].borderWidth = 2;
              chartRoles.update();
            }
          }
        }
      });

      // Particle glow layer for each chart
      function createParticleCanvas(wrapper, color) {
        const canvas = document.createElement('canvas');
        canvas.className = 'chart-glow';
        canvas.style.position = 'absolute';
        canvas.style.left = '0';
        canvas.style.top = '0';
        canvas.style.width = '100%';
        canvas.style.height = '100%';
        canvas.width = wrapper.offsetWidth || 400;
        canvas.height = wrapper.offsetHeight || 240;
        wrapper.insertBefore(canvas, wrapper.firstChild);
        const ctx = canvas.getContext('2d');
        const particles = [];
        const count = 22;
        for (let i = 0; i < count; i++) {
          particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            s: Math.random() * 2 + 0.6,
            vx: (Math.random() - 0.5) * 0.4,
            vy: (Math.random() - 0.5) * 0.4,
            o: Math.random() * 0.5 + 0.12
          });
        }

        function draw() {
          ctx.clearRect(0, 0, canvas.width, canvas.height);
          particles.forEach(p => {
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.s, 0, Math.PI * 2);
            ctx.globalCompositeOperation = 'lighter';
            ctx.fillStyle = color.replace('rgb', 'rgba').replace(')', `,` + p.o + ')');
            ctx.fill();
            ctx.globalCompositeOperation = 'source-over';
            p.x += p.vx;
            p.y += p.vy;
            if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
            if (p.y < 0 || p.y > canvas.height) p.vy *= -1;
          });
          requestAnimationFrame(draw);
        }
        draw();
        // responsive resize
        window.addEventListener('resize', () => {
          canvas.width = wrapper.offsetWidth || 400;
          canvas.height = wrapper.offsetHeight || 240;
        });
      }

      // attach to chart wrappers
      document.querySelectorAll('.card canvas').forEach(cv => {
        const wrap = cv.parentElement;
        try {
          createParticleCanvas(wrap, 'rgb(255,27,191)');
        } catch (e) {}
      });

      // Boot-up animation plugin (visual shadow)
      Chart.register({
        id: 'gameNovaBoot',
        beforeDraw(chart) {
          const ctx = chart.ctx;
          ctx.save();
          ctx.shadowColor = 'rgba(255,27,191,0.18)';
          ctx.shadowBlur = 14;
          ctx.restore();
        }
      });

      // run boot animation
      [chartCategories, chartRoles].forEach(ch => {
        let start = null;

        function anim(t) {
          if (!start) start = t;
          const p = t - start;
          ch.currentStep = p;
          ch.update();
          if (p < 1000) requestAnimationFrame(anim);
        }
        requestAnimationFrame(anim);
      });

    })();
  </script>
</body>

</html>
<?php ob_end_flush(); ?>