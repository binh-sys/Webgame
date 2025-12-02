<?php
// 1. Khởi động Session nếu chưa được khởi động
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// 2. Kết nối CSDL (Đảm bảo file ketnoi.php tồn tại)
require_once 'ketnoi.php';

// 3. Logic tạo đường dẫn Avatar (Sử dụng Cache-Busting)
$header_avatar_url = 'img/default-avatar.png'; // Ảnh mặc định an toàn
$avatar_filename = $_SESSION['avatar'] ?? '';

if (!empty($avatar_filename) && is_string($avatar_filename)) {
    // Dùng urlencode() và time() làm Cache-Buster 
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
    <meta name="description" content="Mẫu Web Tin Tức Game chuyên nghiệp và hiện đại">
    <meta name="keywords" content="game, tin tức, esports, đánh giá, review, cộng đồng">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Tin Tức Game</title>

    <link href="img/bat.png" rel="shortcut icon" />

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="css/owl.carousel.css" />
    <link rel="stylesheet" href="css/animate.css" />

    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>" />
</head>

<body>

    <header class="header-section shadow-lg py-2" style="background:#111; position: sticky; top: 0; z-index: 9990;">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-nowrap px-4">

            <a href="index.php" class="site-logo d-flex align-items-center" style="text-decoration:none;">
                <img src="img/batvippromax.png" alt="Game BAT" style="height:95px;">
            </a>

            <nav class="main-menu d-none d-lg-block">
                <ul class="d-flex align-items-center gap-4 mb-0 list-unstyled">
                    <li><a href="index.php" class="nav-link-custom">Trang chủ</a></li>

                    <li class="menu-dropdown">
                        <a href="categories.php" class="nav-link-custom">Danh mục <i class="fas fa-chevron-down ms-1"></i></a>

                        <ul class="dropdown-box">
                            <?php if ($categories_menu && $categories_menu->num_rows > 0): ?>
                                <?php while ($cat = mysqli_fetch_assoc($categories_menu)): ?>
                                    <li>
                                        <a href="categories.php?cat=<?php echo intval($cat['category_id']); ?>">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li><a href="#">Chưa có danh mục</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <li><a href="review.php" class="nav-link-custom">Đánh giá</a></li>
                    <li><a href="community.php" class="nav-link-custom">Cộng đồng</a></li>
                    <li><a href="contact.php" class="nav-link-custom">Liên hệ</a></li>
                </ul>
            </nav>

            <div class="d-flex align-items-center gap-3">

                <div class="search-wrapper position-relative">
                    <form action="search.php" method="get" autocomplete="off">
                        <input type="text" name="keyword" class="search-input" placeholder="Tìm bài viết..." aria-label="Tìm kiếm bài viết">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>

                    <ul id="suggestions-list" class="suggestions-box"></ul>
                </div>

                <div class="user-panel d-flex align-items-center gap-2">

                    <?php
                    if ($is_logged_in):
                        $role = $user_role;
                    ?>

                        <span class="text-light small me-2 d-none d-sm-inline text-end">
                            Xin chào,<br>
                            <strong class="text-warning fw-bold"><?php echo htmlspecialchars($display_name); ?></strong>
                        </span>

                        <?php if ($role === 'editor'): ?>
                            <div class="author-icon-wrapper position-relative">

                                <div class="avatar-image-circle"
                                    style="background-image: url('<?php echo htmlspecialchars($header_avatar_url); ?>');"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-feather-alt author-badge"></i>
                                </div>

                                <ul class="author-menu shadow-lg" role="menu">
                                    <li role="presentation"><a href="profile.php" role="menuitem"><i class="fas fa-user-edit me-2"></i>Cập nhật Hồ sơ</a></li>
                                    <li class="dropdown-divider"></li>
                                    <li role="presentation"><a href="new-article.php" role="menuitem" class="text-warning fw-bold"><i class="fas fa-pen-nib me-2"></i>Viết bài</a></li>
                                    <li role="presentation"><a href="author-history.php" role="menuitem"><i class="fas fa-list-alt me-2"></i>Bài viết của tôi</a></li>
                                    <li class="dropdown-divider"></li>
                                    <li role="presentation"><a href="logout.php" role="menuitem" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                                </ul>

                            </div>

                        <?php elseif ($role === 'admin'): ?>
                            <div class="admin-icon-wrapper position-relative">

                                <div class="avatar-image-circle"
                                    style="background-image: url('<?php echo htmlspecialchars($header_avatar_url); ?>');"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-user-shield admin-badge"></i>
                                </div>

                                <ul class="admin-menu shadow-lg" role="menu">
                                    <li role="presentation"><a href="profile.php" role="menuitem"><i class="fas fa-user-edit me-2"></i>Cập nhật Hồ sơ</a></li>
                                    <li class="dropdown-divider"></li>
                                    <li role="presentation"><a href="http://localhost/Webgame/admin2/dist/index.php" role="menuitem" class="text-info fw-bold">
                                            <i class="fas fa-tools me-2"></i>Trang quản trị</a></li>
                                    <li role="presentation"><a href="manage-articles.php" role="menuitem">
                                            <i class="fas fa-newspaper me-2"></i>Quản lý bài viết</a></li>
                                    <li role="presentation"><a href="manage-users.php" role="menuitem">
                                            <i class="fas fa-users-cog me-2"></i>Quản lý người dùng</a></li>
                                    <li role="presentation"><a href="settings.php" role="menuitem">
                                            <i class="fas fa-cog me-2"></i>Cài đặt hệ thống</a></li>
                                    <li class="dropdown-divider"></li>
                                    <li role="presentation"><a href="logout.php" role="menuitem" class="text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                                </ul>

                            </div>

                        <?php else: // User thường 
                        ?>
                            <div class="user-icon-wrapper position-relative">

                                <div class="avatar-image-circle"
                                    style="background-image: url('<?php echo htmlspecialchars($header_avatar_url); ?>');"
                                    aria-haspopup="true" aria-expanded="false">
                                </div>

                                <ul class="user-menu shadow-lg" role="menu">
                                    <li role="presentation"><a href="profile.php" role="menuitem"><i class="fas fa-user-edit me-2"></i>Cập nhật Hồ sơ</a></li>
                                    <li role="presentation"><a href="favorites.php" role="menuitem"><i class="fas fa-heart me-2"></i>Bài viết yêu thích</a></li>
                                    <li class="dropdown-divider"></li>
                                    <li role="presentation"><a href="logout.php" role="menuitem" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                                </ul>
                            </div>

                        <?php endif; ?>

                    <?php else: ?>

                        <a href="login.php" class="btn-login-custom fw-semibold">
                            <i class="fa fa-user me-2"></i> Đăng nhập
                        </a>

                    <?php endif; ?>

                </div>
            </div>

        </div>

        <style>
            /* FIX QUAN TRỌNG cho dropdown */
            header,
            .header-section,
            .container-fluid {
                overflow: visible !important;
            }

            /* ===== STYLES CHUNG ===== */
            .nav-link-custom {
                color: #ddd;
                font-weight: 500;
                text-decoration: none;
                transition: 0.3s;
            }

            .nav-link-custom:hover {
                color: #ffb300;
                text-shadow: 0 0 6px #ffb30050;
            }

            /* ===== AVATAR VÀ BADGE (Cập nhật) ===== */
            .avatar-image-circle {
                width: 44px;
                height: 44px;
                border-radius: 50%;
                background-color: #333;
                background-size: cover;
                background-position: center;
                border: 3px solid #fff;
                cursor: pointer;
                transition: all 0.3s;
                position: relative;
                overflow: hidden;
            }

            .avatar-image-circle:hover {
                border-color: #ffc107;
                transform: scale(1.05);
            }

            .author-badge {
                position: absolute;
                bottom: -5px;
                right: -5px;
                font-size: 0.9rem;
                color: #ffb300;
                background: #111;
                border-radius: 50%;
                padding: 4px;
                border: 2px solid #fff;
                box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
            }

            .admin-badge {
                position: absolute;
                bottom: -5px;
                right: -5px;
                font-size: 0.9rem;
                color: #00eaff;
                background: #111;
                border-radius: 50%;
                padding: 4px;
                border: 2px solid #fff;
                box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
            }

            /* ===== MENU DROPWDOWN NGƯỜI DÙNG (Cập nhật) ===== */
            .user-icon-wrapper,
            .author-icon-wrapper,
            .admin-icon-wrapper {
                position: relative;
                z-index: 1000;
            }

            .user-menu,
            .author-menu,
            .admin-menu {
                list-style: none;
                position: absolute;
                top: 100%;
                right: 0;
                margin: 15px 0 0;
                padding: 8px 0;
                min-width: 250px;

                /* Glassmorphism & Shadow */
                background: rgba(10, 10, 10, 0.96);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.15);
                border-radius: 12px;
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.7);

                /* Animation */
                opacity: 0;
                transform: translateY(15px) scale(0.95);
                visibility: hidden;
                transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s;
                z-index: 9999;
            }

            .user-icon-wrapper:hover .user-menu,
            .author-icon-wrapper:hover .author-menu,
            .admin-icon-wrapper:hover .admin-menu {
                opacity: 1;
                transform: translateY(0) scale(1);
                visibility: visible;
            }

            .user-menu li a,
            .author-menu li a,
            .admin-menu li a {
                display: flex;
                align-items: center;
                padding: 10px 18px;
                color: #ddd;
                font-size: 15px;
                text-decoration: none;
                transition: background-color 0.2s, color 0.2s, padding 0.2s;
            }

            .user-menu li a i,
            .author-menu li a i,
            .admin-menu li a i {
                margin-right: 15px;
                font-size: 1.1rem;
                width: 20px;
            }

            .user-menu li a:hover,
            .author-menu li a:hover,
            .admin-menu li a:hover {
                background-color: rgba(255, 255, 255, 0.08);
                padding-left: 25px;
                color: #fff;
            }

            .admin-menu li a:hover {
                color: #00eaff;
                text-shadow: 0 0 8px #00eaff30;
            }

            .author-menu li a:hover {
                color: #ffb300;
                text-shadow: 0 0 8px #ffb30030;
            }

            .dropdown-divider {
                height: 1px;
                margin: 0.6rem 0;
                overflow: hidden;
                background-color: rgba(255, 255, 255, 0.1);
            }

            /* ================================================== */
            /* ===== Nút đăng nhập / đăng xuất ===== */
            .btn-login-custom {
                background: linear-gradient(135deg, #ffb300, #ff8800);
                border: none;
                color: #111;
                border-radius: 22px;
                padding: 8px 22px;
                font-weight: 600;
                font-size: 14px;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
            }

            .btn-login-custom:hover {
                background: linear-gradient(135deg, #ffc107, #ff9800);
                transform: translateY(-1px);
                box-shadow: 0 0 5px #ffb30070;
            }

            .user-panel {
                display: flex;
                align-items: center;
                gap: 12px;
                background: transparent !important;
            }

            .btn-login-custom:hover {
                background: linear-gradient(135deg, #ffc107, #ff9800);
                transform: translateY(-1px);
                box-shadow: 0 0 5px #ffb30070;
            }

            /* ================================================== */

            /* ===== SEARCH (Giữ nguyên logic ẩn/hiện) ===== */
            .search-wrapper {
                width: 260px;
            }

            .search-input {
                width: 0;
                opacity: 0;
                padding-left: 0;
                padding-right: 0;
                pointer-events: none;
                transition: width 0.4s ease, opacity 0.3s ease, padding 0.3s ease;
                height: 40px;
                border: none;
                border-radius: 20px;
                background: #222;
                color: #fff;
                font-size: 14px;
                outline: none;
            }

            .search-wrapper:hover .search-input {
                width: 100%;
                opacity: 1;
                padding: 0 45px 0 15px;
                pointer-events: auto;
            }

            .search-btn {
                position: absolute;
                top: 50%;
                right: 8px;
                transform: translateY(-50%);
                height: 32px;
                width: 32px;
                border-radius: 50%;
                border: none;
                background: #ffb400;
                color: #000;
                cursor: pointer;
            }

            /* Gợi ý kết quả */
            .suggestions-box {
                position: absolute;
                top: 110%;
                left: 0;
                width: 100%;
                background: #111;
                border: 1px solid #444;
                border-radius: 8px;
                list-style: none;
                padding: 0;
                margin: 6px 0 0 0;
                display: none;
                max-height: 260px;
                overflow-y: auto;
                z-index: 99999;
            }

            .suggestions-box li {
                padding: 10px 14px;
                border-bottom: 1px solid #222;
            }

            .suggestions-box li a {
                color: #fff;
                text-decoration: none;
                display: block;
            }

            .suggestions-box li:hover {
                background: #222;
            }

            .no-result {
                color: #ccc;
            }

            /* ===== MENU DROPDOWN DANH MỤC (Giữ nguyên style đẹp cũ) ========== */
            .menu-dropdown {
                position: relative;
            }

            .main-menu .menu-dropdown>a i {
                transition: 0.3s ease;
            }

            .dropdown-box {
                position: absolute;
                top: 45px;
                left: 0;
                min-width: 220px;
                background: rgba(20, 20, 20, 0.97);
                backdrop-filter: blur(4px);
                border-radius: 14px;
                padding: 10px 0;
                list-style: none;
                display: none;
                flex-direction: column;
                gap: 2px;
                animation: fadeIn 0.25s ease;
                box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.45);
                border: 1px solid rgba(255, 255, 255, 0.05);
                z-index: 5000;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .menu-dropdown:hover .dropdown-box {
                display: flex;
            }

            .dropdown-box li a {
                display: block;
                padding: 10px 18px;
                color: #fff;
                font-size: 15px;
                text-decoration: none;
                position: relative;
                transition: 0.2s ease;
                font-weight: 400;
            }

            .dropdown-box li:not(:last-child) a::after {
                content: "";
                position: absolute;
                bottom: 0;
                left: 18px;
                right: 18px;
                height: 1px;
                background: rgba(255, 255, 255, 0.05);
            }

            .dropdown-box li a:hover {
                background: rgba(255, 140, 0, 0.18);
                padding-left: 24px;
            }

            .menu-dropdown:hover>a i {
                transform: rotate(180deg);
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const input = document.querySelector('.search-input');
                const list = document.getElementById('suggestions-list');

                // Hàm debounce để giảm thiểu số lượng request gửi lên server
                const debounce = (func, delay) => {
                    let timeoutId;
                    return (...args) => {
                        clearTimeout(timeoutId);
                        timeoutId = setTimeout(() => func.apply(this, args), delay);
                    };
                };

                const fetchSuggestions = async () => {
                    const keyword = input.value.trim();

                    if (keyword.length < 2) {
                        list.style.display = 'none';
                        list.innerHTML = '';
                        return;
                    }

                    try {
                        // Gọi API gợi ý tìm kiếm
                        const res = await fetch(`search_suggest.php?keyword=${encodeURIComponent(keyword)}`);
                        if (!res.ok) throw new Error('Network response was not ok');

                        const data = await res.json();

                        if (data && data.length) {
                            list.innerHTML = data.map(i =>
                                `<li><a href="article.php?slug=${encodeURIComponent(i.slug)}">${i.title}</a></li>`
                            ).join('');
                            list.style.display = 'block';
                        } else {
                            list.innerHTML = '<li class="no-result">Không tìm thấy kết quả phù hợp</li>';
                            list.style.display = 'block';
                        }
                    } catch (error) {
                        console.error("Lỗi khi fetch gợi ý tìm kiếm:", error);
                        list.style.display = 'none';
                    }
                };

                // Gắn event listener với debounce 300ms
                input.addEventListener('input', debounce(fetchSuggestions, 300));

                // Ẩn gợi ý khi click ra ngoài
                document.addEventListener('click', e => {
                    if (!e.target.closest('.search-wrapper')) list.style.display = 'none';
                });
            });
        </script>

    </header>