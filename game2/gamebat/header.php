<?php
// 1. Khởi động Session nếu chưa được khởi động
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// 2. Kết nối CSDL
require_once 'ketnoi.php';

// 3. Logic tạo đường dẫn Avatar
$header_avatar_url = 'img/default-avatar.png';
$avatar_filename = $_SESSION['avatar'] ?? '';

if (!empty($avatar_filename) && is_string($avatar_filename)) {
    $header_avatar_url = 'img/' . urlencode($avatar_filename) . '?t=' . time();
}

// 4. Lấy danh mục Menu chính
$categories_query = "SELECT category_id, name, slug FROM categories ORDER BY name ASC";
$categories_menu = mysqli_query($conn, $categories_query);

// 5. Xác định Vai trò và trạng thái đăng nhập
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['role'] ?? 'guest';
$display_name = $_SESSION['display_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="GameBat - Tin tức game, đánh giá và cộng đồng game thủ">
    <meta name="keywords" content="game, tin tức, esports, đánh giá, review, cộng đồng">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameBat - Tin Tức Game</title>

    <link href="img/bat.png" rel="shortcut icon" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="css/owl.carousel.css" />
    <link rel="stylesheet" href="css/animate.css" />
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>" />
</head>

<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-container">
            <div class="top-bar-left">
                <span class="top-bar-item">
                    <i class="fas fa-fire"></i> Hot: E-Sports World Championship 2024
                </span>
                <span class="top-bar-divider"></span>
                <span class="top-bar-item">
                    <i class="far fa-calendar-alt"></i> <?= date('d/m/Y') ?>
                </span>
            </div>
            <div class="top-bar-right">
                <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                <a href="#" class="social-link"><i class="fab fa-discord"></i></a>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header" id="mainHeader">
        <div class="header-container">
            <!-- Logo -->
            <a href="index.php" class="header-logo">
                <img src="img/batvippromax.png" alt="GameBat">
                <div class="logo-text">
                    <span class="logo-name">Game<span class="highlight">Bat</span></span>
                    <span class="logo-tagline">Gaming News & Reviews</span>
                </div>
            </a>

            <!-- Navigation -->
            <nav class="header-nav" id="mainNav">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">
                            <i class="fas fa-home"></i>
                            <span>Trang chủ</span>
                        </a>
                    </li>
                    <li class="nav-item has-dropdown">
                        <a href="categories.php" class="nav-link">
                            <i class="fas fa-gamepad"></i>
                            <span>Danh mục</span>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <div class="mega-dropdown">
                            <div class="dropdown-inner">
                                <div class="dropdown-header">
                                    <i class="fas fa-th-large"></i>
                                    <span>Khám phá danh mục</span>
                                </div>
                                <ul class="dropdown-list">
                                    <?php if ($categories_menu && $categories_menu->num_rows > 0): ?>
                                        <?php while ($cat = mysqli_fetch_assoc($categories_menu)): ?>
                                            <li>
                                                <a href="categories.php?cat=<?= intval($cat['category_id']) ?>">
                                                    <i class="fas fa-angle-right"></i>
                                                    <?= htmlspecialchars($cat['name']) ?>
                                                </a>
                                            </li>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <li><a href="#"><i class="fas fa-angle-right"></i> Chưa có danh mục</a></li>
                                    <?php endif; ?>
                                </ul>
                                <a href="categories.php" class="dropdown-footer">
                                    Xem tất cả <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="review.php" class="nav-link">
                            <i class="fas fa-star"></i>
                            <span>Đánh giá</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="community.php" class="nav-link">
                            <i class="fas fa-users"></i>
                            <span>Cộng đồng</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="contact.php" class="nav-link">
                            <i class="fas fa-envelope"></i>
                            <span>Liên hệ</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Right Section -->
            <div class="header-right">
                <!-- Search -->
                <div class="search-box" id="searchBox">
                    <button class="search-toggle" id="searchToggle">
                        <i class="fas fa-search"></i>
                    </button>
                    <div class="search-form-wrapper">
                        <form action="search.php" method="get" class="search-form" autocomplete="off">
                            <input type="text" name="keyword" class="search-input" id="searchInput" 
                                   placeholder="Tìm kiếm bài viết...">
                            <button type="submit" class="search-submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                        <ul class="search-suggestions" id="searchSuggestions"></ul>
                    </div>
                </div>

                <!-- User Panel -->
                <div class="user-panel">
                    <?php if ($is_logged_in): ?>
                        <!-- Logged In User -->
                        <div class="user-dropdown">
                            <button class="user-trigger">
                                <div class="user-avatar" style="background-image: url('<?= htmlspecialchars($header_avatar_url) ?>')">
                                    <?php if ($user_role === 'admin'): ?>
                                        <span class="role-badge admin"><i class="fas fa-crown"></i></span>
                                    <?php elseif ($user_role === 'editor'): ?>
                                        <span class="role-badge editor"><i class="fas fa-pen"></i></span>
                                    <?php endif; ?>
                                </div>
                                <div class="user-info">
                                    <span class="user-name"><?= htmlspecialchars($display_name) ?></span>
                                    <span class="user-role"><?= ucfirst($user_role) ?></span>
                                </div>
                                <i class="fas fa-chevron-down"></i>
                            </button>

                            <div class="user-menu">
                                <div class="menu-header">
                                    <div class="menu-avatar" style="background-image: url('<?= htmlspecialchars($header_avatar_url) ?>')"></div>
                                    <div class="menu-user-info">
                                        <span class="menu-name"><?= htmlspecialchars($display_name) ?></span>
                                        <span class="menu-email"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></span>
                                    </div>
                                </div>
                                <div class="menu-body">
                                    <a href="profile.php" class="menu-item">
                                        <i class="fas fa-user-circle"></i>
                                        <span>Hồ sơ cá nhân</span>
                                    </a>
                                    
                                    <?php if ($user_role === 'editor' || $user_role === 'admin'): ?>
                                    <div class="menu-divider"></div>
                                    <div class="menu-label">Biên tập viên</div>
                                    <a href="new-article.php" class="menu-item highlight">
                                        <i class="fas fa-pen-fancy"></i>
                                        <span>Viết bài mới</span>
                                        <span class="item-badge">New</span>
                                    </a>
                                    <a href="author-history.php" class="menu-item">
                                        <i class="fas fa-newspaper"></i>
                                        <span>Bài viết của tôi</span>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($user_role === 'admin'): ?>
                                    <div class="menu-divider"></div>
                                    <div class="menu-label">Quản trị</div>
                                    <a href="http://localhost/Webgame/admin2/dist/index.php" class="menu-item admin-link">
                                        <i class="fas fa-cogs"></i>
                                        <span>Trang quản trị</span>
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <div class="menu-footer">
                                    <a href="logout.php" class="logout-btn">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Đăng xuất</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Guest -->
                        <a href="login.php" class="btn-login">
                            <i class="fas fa-user"></i>
                            <span>Đăng nhập</span>
                        </a>
                        <a href="register.php" class="btn-register">
                            <span>Đăng ký</span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Toggle -->
                <button class="mobile-toggle" id="mobileToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <style>
        /* ===== CSS VARIABLES ===== */
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --accent: #f59e0b;
            --accent-light: #fbbf24;
            --dark: #0a0a0f;
            --dark-light: #12121a;
            --dark-lighter: #1a1a25;
            --text: #ffffff;
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.08);
            --gradient-primary: linear-gradient(135deg, #3b82f6, #2563eb);
            --gradient-accent: linear-gradient(135deg, #f59e0b, #d97706);
            --shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--text);
        }

        /* ===== TOP BAR ===== */
        .top-bar {
            background: var(--dark);
            border-bottom: 1px solid var(--border);
            padding: 8px 0;
            font-size: 13px;
        }

        .top-bar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .top-bar-item {
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .top-bar-item i {
            color: var(--accent);
        }

        .top-bar-divider {
            width: 1px;
            height: 15px;
            background: var(--border);
        }

        .top-bar-right {
            display: flex;
            gap: 12px;
        }

        .social-link {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--dark-lighter);
            border-radius: 6px;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.3s;
        }

        .social-link:hover {
            background: var(--primary);
            color: #fff;
            transform: translateY(-2px);
        }

        /* ===== MAIN HEADER ===== */
        .main-header {
            background: rgba(10, 10, 15, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s;
        }

        .main-header.scrolled {
            background: rgba(10, 10, 15, 0.98);
            box-shadow: var(--shadow);
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 75px;
        }

        /* ===== LOGO ===== */
        .header-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .header-logo img {
            height: 50px;
            width: auto;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-name {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
        }

        .logo-name .highlight {
            color: var(--accent);
        }

        .logo-tagline {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ===== NAVIGATION ===== */
        .header-nav {
            display: flex;
            align-items: center;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 5px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 18px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .nav-link i:first-child {
            font-size: 15px;
        }

        .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .nav-item.active .nav-link {
            color: var(--accent);
            background: rgba(245, 158, 11, 0.1);
        }

        .dropdown-arrow {
            font-size: 10px;
            transition: transform 0.3s;
        }

        .nav-item.has-dropdown:hover .dropdown-arrow {
            transform: rotate(180deg);
        }


        /* ===== MEGA DROPDOWN ===== */
        .mega-dropdown {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(10px);
            min-width: 280px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            padding-top: 15px;
        }

        .nav-item.has-dropdown:hover .mega-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        .dropdown-inner {
            background: var(--dark-light);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .dropdown-header {
            padding: 15px 20px;
            background: rgba(59, 130, 246, 0.1);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary);
            font-weight: 600;
            font-size: 14px;
        }

        .dropdown-list {
            list-style: none;
            padding: 10px 0;
            margin: 0;
            max-height: 300px;
            overflow-y: auto;
        }

        .dropdown-list li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
        }

        .dropdown-list li a i {
            font-size: 10px;
            color: var(--primary);
            opacity: 0;
            transform: translateX(-5px);
            transition: all 0.2s;
        }

        .dropdown-list li a:hover {
            background: rgba(255, 255, 255, 0.03);
            color: #fff;
            padding-left: 25px;
        }

        .dropdown-list li a:hover i {
            opacity: 1;
            transform: translateX(0);
        }

        .dropdown-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            background: rgba(59, 130, 246, 0.05);
            border-top: 1px solid var(--border);
            color: var(--primary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .dropdown-footer:hover {
            background: rgba(59, 130, 246, 0.1);
        }

        /* ===== HEADER RIGHT ===== */
        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* ===== SEARCH BOX ===== */
        .search-box {
            position: relative;
        }

        .search-toggle {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--dark-lighter);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-toggle:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .search-form-wrapper {
            position: absolute;
            top: 100%;
            right: 0;
            width: 350px;
            padding-top: 15px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s;
        }

        .search-box.active .search-form-wrapper {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .search-form {
            display: flex;
            background: var(--dark-light);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            position: relative;
        }

        /* Ensure search-suggestions is outside form styling */
        .search-form-wrapper > .search-suggestions {
            position: relative;
            z-index: 10;
        }

        .search-input {
            flex: 1;
            padding: 14px 18px;
            background: transparent;
            border: none;
            color: #fff;
            font-size: 14px;
            outline: none;
        }

        .search-input::placeholder {
            color: var(--text-muted);
        }

        .search-form .search-submit {
            width: 50px;
            background: var(--gradient-primary);
            border: none;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-form .search-submit:hover {
            opacity: 0.9;
        }

        .search-suggestions {
            list-style: none;
            margin: 10px 0 0;
            padding: 0;
            background: var(--dark-light);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            display: none;
            position: relative;
            z-index: 100;
            max-height: 300px;
            overflow-y: auto;
        }

        .search-suggestions.show {
            display: block;
        }

        .search-suggestions li {
            border-bottom: 1px solid var(--border);
        }

        .search-suggestions li:last-child {
            border-bottom: none;
        }

        .search-suggestions li a {
            display: block;
            padding: 12px 18px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
            position: relative;
        }

        .search-suggestions li a:hover {
            background: rgba(255, 255, 255, 0.03);
            color: #fff;
        }

        .search-suggestions li {
            position: relative;
        }

        .search-suggestions li::after,
        .search-suggestions li::before,
        .search-suggestions li a::after,
        .search-suggestions li a::before {
            display: none !important;
            content: none !important;
        }

        .search-suggestions li > button,
        .search-suggestions li > .search-btn,
        .search-suggestions li > [class*="btn"],
        .search-suggestions li a > i.fa-search,
        .search-suggestions li a > [class*="fa-search"] {
            display: none !important;
        }

        /* Hide any floating search button inside suggestions */
        .search-suggestions .search-submit,
        .search-suggestions button,
        .search-suggestions input[type="submit"],
        .search-suggestions .btn,
        .search-suggestions [class*="btn"] {
            display: none !important;
        }

        /* Ensure suggestions list items don't have extra elements */
        .search-suggestions li a {
            display: flex !important;
            flex-direction: column !important;
            padding: 14px 18px !important;
            position: relative !important;
        }

        .search-suggestions li a div {
            width: 100% !important;
        }

        /* Hide any icon that might appear from external CSS */
        .search-suggestions li a > i,
        .search-suggestions li a > svg,
        .search-suggestions li a > img,
        .search-suggestions li a > .icon,
        .search-suggestions li a > [class*="icon"],
        .search-suggestions li a > [class*="fa-"],
        .search-suggestions li a > [class*="btn"] {
            display: none !important;
        }

        /* Override any absolute positioned elements inside suggestions */
        .search-suggestions * {
            position: relative !important;
        }

        .search-suggestions li a::after,
        .search-suggestions li a::before {
            content: none !important;
            display: none !important;
        }

        .search-suggestions .no-result {
            padding: 15px 18px;
            color: var(--text-muted);
            text-align: center;
        }

        /* ===== USER PANEL ===== */
        .user-panel {
            display: flex;
            align-items: center;
            gap: 10px;
            background: none !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            border-radius: 0 !important;
        }

        .btn-login {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 25px;
            color: #fff !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-login i,
        .btn-login span {
            color: #fff !important;
        }

        .btn-login:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff !important;
            border-color: #fff;
            transform: translateY(-2px);
        }

        .btn-register {
            display: flex;
            align-items: center;
            padding: 10px 24px;
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            border-radius: 25px;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
            background: linear-gradient(135deg, #34d399, #10b981);
        }

        /* ===== USER DROPDOWN ===== */
        .user-dropdown {
            position: relative;
        }

        .user-trigger {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 12px 6px 6px;
            background: var(--dark-lighter);
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .user-trigger:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-size: cover;
            background-position: center;
            background-color: var(--dark);
            position: relative;
        }

        .role-badge {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 8px;
            border: 2px solid var(--dark-lighter);
        }

        .role-badge.admin {
            background: var(--gradient-accent);
            color: #000;
        }

        .role-badge.editor {
            background: var(--gradient-primary);
            color: #fff;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            text-align: left;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            line-height: 1.2;
        }

        .user-role {
            font-size: 11px;
            color: var(--text-muted);
        }

        .user-trigger > i {
            font-size: 10px;
            color: var(--text-muted);
            transition: transform 0.3s;
        }

        .user-dropdown:hover .user-trigger > i {
            transform: rotate(180deg);
        }


        /* ===== USER MENU ===== */
        .user-menu {
            position: absolute;
            top: 100%;
            right: 0;
            width: 280px;
            padding-top: 15px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s;
        }

        .user-dropdown:hover .user-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .menu-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px;
            background: linear-gradient(135deg, #1e293b, #1a1a25);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px 16px 0 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .menu-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-size: cover;
            background-position: center;
            background-color: #333;
            border: 3px solid #3b82f6;
        }

        .menu-user-info {
            flex: 1;
            overflow: hidden;
        }

        .menu-name {
            display: block;
            font-size: 15px;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .menu-email {
            display: block;
            font-size: 12px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .menu-body {
            background: #1a1a25;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-top: none;
            border-bottom: none;
            padding: 10px 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #ccc !important;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
        }

        .menu-item i {
            width: 18px;
            text-align: center;
            color: #3b82f6;
        }

        .menu-item:hover {
            background: rgba(59, 130, 246, 0.15);
            color: #fff !important;
        }

        .menu-item.highlight {
            color: #fbbf24 !important;
        }

        .menu-item.highlight i {
            color: #fbbf24;
        }

        .menu-item.highlight:hover {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24 !important;
        }

        .menu-item.admin-link {
            color: #60a5fa !important;
        }

        .menu-item.admin-link i {
            color: #60a5fa;
        }

        .menu-item.admin-link:hover {
            background: rgba(96, 165, 250, 0.15);
        }

        .item-badge {
            margin-left: auto;
            padding: 3px 10px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
            color: #fff;
        }

        .menu-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 8px 0;
        }

        .menu-label {
            padding: 10px 20px 5px;
            font-size: 11px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .menu-footer {
            background: #1a1a25;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0 0 16px 16px;
            padding: 12px;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-radius: 10px;
            color: #f87171 !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .logout-btn i {
            color: #f87171;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        /* ===== MOBILE TOGGLE ===== */
        .mobile-toggle {
            display: none;
            flex-direction: column;
            justify-content: center;
            gap: 5px;
            width: 42px;
            height: 42px;
            background: var(--dark-lighter);
            border: 1px solid var(--border);
            border-radius: 10px;
            cursor: pointer;
            padding: 12px;
        }

        .mobile-toggle span {
            display: block;
            height: 2px;
            background: #fff;
            border-radius: 2px;
            transition: all 0.3s;
        }

        .mobile-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .mobile-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .mobile-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }

        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 999;
        }

        .mobile-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1200px) {
            .nav-link span {
                display: none;
            }
            
            .nav-link {
                padding: 12px 14px;
            }
            
            .nav-link i:first-child {
                font-size: 18px;
            }
        }

        @media (max-width: 992px) {
            .top-bar {
                display: none;
            }
            
            .header-nav {
                position: fixed;
                top: 0;
                right: -300px;
                width: 300px;
                height: 100vh;
                background: var(--dark-light);
                flex-direction: column;
                padding: 80px 20px 20px;
                transition: all 0.3s;
                z-index: 1001;
                overflow-y: auto;
            }
            
            .header-nav.active {
                right: 0;
            }
            
            .nav-menu {
                flex-direction: column;
                width: 100%;
                gap: 5px;
            }
            
            .nav-item {
                width: 100%;
            }
            
            .nav-link {
                width: 100%;
                padding: 15px;
                justify-content: flex-start;
            }
            
            .nav-link span {
                display: inline;
            }
            
            .mega-dropdown {
                position: static;
                transform: none;
                width: 100%;
                padding: 0;
                max-height: 0;
                overflow: hidden;
                opacity: 1;
                visibility: visible;
            }
            
            .nav-item.has-dropdown.open .mega-dropdown {
                max-height: 500px;
                padding-top: 10px;
            }
            
            .dropdown-inner {
                border-radius: 12px;
            }
            
            .mobile-toggle {
                display: flex;
            }
            
            .user-info {
                display: none;
            }
            
            .user-trigger > i:last-child {
                display: none;
            }
            
            .user-trigger {
                padding: 5px;
                border-radius: 50%;
            }
            
            .search-form-wrapper {
                position: fixed;
                top: 75px;
                left: 0;
                right: 0;
                width: 100%;
                padding: 15px;
            }
        }

        @media (max-width: 576px) {
            .header-container {
                height: 65px;
            }
            
            .header-logo img {
                height: 40px;
            }
            
            .logo-text {
                display: none;
            }
            
            .btn-register span {
                display: none;
            }
            
            .btn-register {
                width: 42px;
                height: 42px;
                padding: 0;
                justify-content: center;
                border-radius: 10px;
            }
            
            .btn-register::before {
                content: '\f234';
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
            }
        }
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Header scroll effect
            const header = document.getElementById('mainHeader');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });

            // Search toggle
            const searchBox = document.getElementById('searchBox');
            const searchToggle = document.getElementById('searchToggle');
            const searchInput = document.getElementById('searchInput');
            const searchSuggestions = document.getElementById('searchSuggestions');

            searchToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                searchBox.classList.toggle('active');
                if (searchBox.classList.contains('active')) {
                    searchInput.focus();
                }
            });

            document.addEventListener('click', function(e) {
                if (!searchBox.contains(e.target)) {
                    searchBox.classList.remove('active');
                    searchSuggestions.classList.remove('show');
                }
            });

            // Search suggestions
            let debounceTimer;
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const keyword = this.value.trim();

                if (keyword.length < 1) {
                    searchSuggestions.classList.remove('show');
                    searchSuggestions.innerHTML = '';
                    return;
                }

                debounceTimer = setTimeout(async function() {
                    try {
                        const res = await fetch(`search_suggest.php?keyword=${encodeURIComponent(keyword)}`);
                        const data = await res.json();

                        if (data && data.length) {
                            searchSuggestions.innerHTML = data.map(item =>
                                `<li>
                                    <a href="article.php?id=${item.id}">
                                        <div style="font-weight:600;color:#fff;margin-bottom:3px;">${item.title}</div>
                                        <div style="font-size:12px;color:#888;">
                                            <span style="color:#ec4899;">${item.category}</span> • ${item.author}
                                        </div>
                                    </a>
                                </li>`
                            ).join('');
                        } else {
                            searchSuggestions.innerHTML = '<li class="no-result">Không tìm thấy kết quả</li>';
                        }
                        searchSuggestions.classList.add('show');
                    } catch (error) {
                        console.error('Search error:', error);
                    }
                }, 300);
            });

            // Mobile menu toggle
            const mobileToggle = document.getElementById('mobileToggle');
            const mainNav = document.getElementById('mainNav');
            const mobileOverlay = document.getElementById('mobileOverlay');

            mobileToggle.addEventListener('click', function() {
                this.classList.toggle('active');
                mainNav.classList.toggle('active');
                mobileOverlay.classList.toggle('active');
                document.body.style.overflow = mainNav.classList.contains('active') ? 'hidden' : '';
            });

            mobileOverlay.addEventListener('click', function() {
                mobileToggle.classList.remove('active');
                mainNav.classList.remove('active');
                this.classList.remove('active');
                document.body.style.overflow = '';
            });

            // Mobile dropdown toggle
            const dropdownItems = document.querySelectorAll('.nav-item.has-dropdown');
            dropdownItems.forEach(item => {
                const link = item.querySelector('.nav-link');
                link.addEventListener('click', function(e) {
                    if (window.innerWidth <= 992) {
                        e.preventDefault();
                        item.classList.toggle('open');
                    }
                });
            });

            // Close mobile menu on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) {
                    mobileToggle.classList.remove('active');
                    mainNav.classList.remove('active');
                    mobileOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
    </script>
