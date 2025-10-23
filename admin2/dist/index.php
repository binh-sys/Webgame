<?php 
require_once('ketnoi.php');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Bảng Quản Trị Tin Tức Game</title>

    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="assets/vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="assets/vendors/chartist/chartist.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/logogame.png" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

  </head>
  <body>
    <div class="container-scroller">
      <div class="row p-0 m-0 proBanner" id="proBanner">
        <div class="col-md-12 p-0 m-0">
          <div class="card-body card-body-padding d-flex align-items-center justify-content-between">
            <div class="ps-lg-1">

            </div>
            <div class="d-flex align-items-center justify-content-between">
              <a href="https://www.bootstrapdash.com/product/stellar-admin-template/"><i class="icon-home me-3 text-white"></i></a>
              <button id="bannerClose" class="btn border-0 p-0">
                <i class="icon-close text-white me-0"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- partial:partials/_navbar.html -->
      <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
          <a class="navbar-brand brand-logo" href="index.php">
         <img src="assets/images/logogame.png" alt="logo" class="logo-dark" style="width:220px; height:auto;" />

          </a>
          <a class="navbar-brand brand-logo-mini" href="index.php"><img src="assets/images/logogame.svg" alt="logo" /></a>
          <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="icon-menu"></span>
          </button>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-center">
          <h5 class="mb-0 font-weight-medium d-none d-lg-flex">Chào mừng đến với bảng điều khiển!</h5>
          <ul class="navbar-nav navbar-nav-right">
            <form class="search-form d-none d-md-block" action="#">
              <i class="icon-magnifier"></i>
              <input type="search" class="form-control" placeholder="Search Here" title="Search here">
            </form>
            <li class="nav-item"><a href="#" class="nav-link"><i class="icon-basket-loaded"></i></a></li>
            <li class="nav-item"><a href="#" class="nav-link"><i class="icon-chart"></i></a></li>
            <li class="nav-item dropdown">
              <a class="nav-link count-indicator message-dropdown" id="messageDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="icon-speech"></i>
                <span class="count">7</span>
              </a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="messageDropdown">
                <a class="dropdown-item py-3">
                  <p class="mb-0 font-weight-medium float-start me-2">You have 7 unread mails </p>
                  <span class="badge badge-pill badge-primary float-end">View all</span>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="assets/images/faces/face10.jpg" alt="image" class="img-sm profile-pic">
                  </div>
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis font-weight-medium text-dark">Marian Garner </p>
                    <p class="font-weight-light small-text"> The meeting is cancelled </p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="assets/images/faces/face12.jpg" alt="image" class="img-sm profile-pic">
                  </div>
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis font-weight-medium text-dark">David Grey </p>
                    <p class="font-weight-light small-text"> The meeting is cancelled </p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="assets/images/faces/face1.jpg" alt="image" class="img-sm profile-pic">
                  </div>
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis font-weight-medium text-dark">Travis Jenkins </p>
                    <p class="font-weight-light small-text"> The meeting is cancelled </p>
                  </div>
                </a>
              </div>
            </li>
            <li class="nav-item dropdown language-dropdown d-none d-sm-flex align-items-center">
              <a class="nav-link d-flex align-items-center dropdown-toggle" id="LanguageDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="d-inline-flex">
                  <i class="flag-icon flag-icon-us"></i>
                </div>
                <span class="profile-text font-weight-normal">English</span>
              </a>
              <div class="dropdown-menu dropdown-menu-left navbar-dropdown py-2" aria-labelledby="LanguageDropdown">
                <a class="dropdown-item">
                  <i class="flag-icon flag-icon-us"></i> English </a>
                <a class="dropdown-item">
                  <i class="flag-icon flag-icon-fr"></i> French </a>
                <a class="dropdown-item">
                  <i class="flag-icon flag-icon-ae"></i> Arabic </a>
                <a class="dropdown-item">
                  <i class="flag-icon flag-icon-ru"></i> Russian </a>
              </div>
            </li>
            <li class="nav-item dropdown d-none d-xl-inline-flex user-dropdown">
              <a class="nav-link dropdown-toggle" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="img-xs rounded-circle ms-2" src="assets/images/faces/face.png" alt="Profile image"> <span class="font-weight-normal"> Mr.Binh </span></a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                <div class="dropdown-header text-center">
                  <img class="img-md rounded-circle" src="assets/images/faces/face8.jpg" alt="Profile image">
                  <p class="mb-1 mt-3">Mr.Binh</p>
                  <p class="font-weight-light text-muted mb-0">mrbinh@gmail.com</p>
                </div>
                <a class="dropdown-item"><i class="dropdown-item-icon icon-user text-primary"></i> Hồ sơ cá nhân <span class="badge badge-pill badge-danger">1</span></a>
                <a class="dropdown-item"><i class="dropdown-item-icon icon-speech text-primary"></i> Tin nhắn</a>
                <a class="dropdown-item"><i class="dropdown-item-icon icon-energy text-primary"></i> Hoạt động</a>
                <a class="dropdown-item"><i class="dropdown-item-icon icon-question text-primary"></i> FAQ</a>
                <a class="dropdown-item"><i class="dropdown-item-icon icon-power text-primary"></i>Đăng xuất</a>
              </div>
            </li>
          </ul>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="icon-menu"></span>
          </button>
        </div>
      </nav>
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
          <ul class="nav">
            <li class="nav-item navbar-brand-mini-wrapper">
              <a class="nav-link navbar-brand brand-logo-mini" href="index.php"><img src="assets/images/logogame.png" alt="logo" /></a>
            </li>
            <li class="nav-item nav-profile">
              <a href="#" class="nav-link">
                <div class="profile-image">
                  <img class="img-xs rounded-circle" src="assets/images/faces/face.png" alt="profile image">
                  <div class="dot-indicator bg-success"></div>
                </div>
                <div class="text-wrapper">
                  <p class="profile-name">Mr.Binh</p>
                  <p class="designation">Quản Trị Viên</p>
                </div>
                <div class="icon-container">
                  <i class="icon-bubbles"></i>
                  <div class="dot-indicator bg-danger"></div>
                </div>
              </a>
            </li>
            <li class="nav-item nav-category">
              <span class="nav-link">Trang Quản Trị</span>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="index.php">
                <span class="menu-title">Trang tổng quan</span>
                <i class="icon-screen-desktop menu-icon"></i>
              </a>
            </li>
            <li class="nav-item nav-category"><span class="nav-link">Quản lý</span></li>
    
           <!-- Forms -->

<li class="menu-item">
 <a href="?page_layout=danhsachbaiviet" class="menu-link">
    <i class="bx bx-news"></i>
    <div>Quản lý bài viết</div>
  </a>
</li>

            <li class="menu-item">
  <a href="?page_layout=danhsachchuyenmuc" class="menu-link">
    <i class="menu-icon tf-icons bx bx-book-content"></i>
    <div>Chuyên mục</div>
  </a>
</li>

           <li class="menu-item">
  <a href="?page_layout=danhsachthegame" class="menu-link">
    <i class="menu-icon tf-icons bx bx-purchase-tag-alt"></i>
    <div>Thẻ game</div>
  </a>
</li>

            
           <li class="menu-item">
  <a href="?page_layout=danhsachbinhluan" class="menu-link">
    <i class="menu-icon tf-icons bx bx-purchase-tag"></i>
    <div>Bình Luận</div>
  </a>
</li>
           <li class="menu-item">
  <a href="?page_layout=danhsachyeuthich" class="menu-link">
    <i class="menu-icon tf-icons bx bx-purchase-tag"></i>
    <div>Yêu Thích</div>
  </a>
</li>

            <li class="menu-item">
  <a href="?page_layout=danhsachlienthethegame" class="menu-link">
    <i class="menu-icon tf-icons bx bx-purchase-tag"></i>
    <div>Liên Kết Thẻ Game</div>
  </a>
</li>

<li class="nav-item nav-category"><span class="nav-link">Quản lý Người Dùng</span></li>
            <li class="menu-item">
  <a href="?page_layout=danhsachnguoidung" class="menu-link">
    <i class="menu-icon tf-icons bx bx-user"></i>
    <div>Người Dùng</div>
  </a>
</li>
          <li class="menu-item">
  <a href="?page_layout=danhsachtacgia" class="menu-link">
    <i class="menu-icon tf-icons bx bx-user"></i>
    <div>Tác Giả</div>
  </a>
</li>

            <li class="nav-item nav-category"><span class="nav-link">Help</span></li>
          
                
              </a>
            </li>
          </ul>
        </nav>

       <?php
if (isset($_GET["page_layout"])) {
  switch ($_GET["page_layout"]) {
    case "danhsachbaiviet":
  require_once 'articles.php';
  break;
    case "danhsachchuyenmuc":
  require_once 'categories.php';
  break;
    case "danhsachthegame":
  require_once 'tags.php';
  break;
   case "danhsachbinhluan":
  require_once 'comments.php';
  break;
   case "danhsachyeuthich":
  require_once 'favorites.php';
  break;
   case "danhsachnguoidung":
  require_once 'users.php';
  break;
    case "danhsachtacgia":
  require_once 'authors.php';
  break;
     case "danhsachlienthethegame":
  require_once 'article_tags.php';
  break;
  }
} else {
  require_once 'content.php';
}
?>
<style>
/* ======= TỔNG THỂ GIAO DIỆN ======= */
body {
  background-color: #f4f7fa;
  font-family: "Poppins", sans-serif;
}

/* ======= SIDEBAR ======= */
.sidebar {
  background: #1b1e29;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.15);
}

.nav-item.nav-category span {
  color: #9aa0a6;
  font-size: 13px;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  margin-top: 10px;
  display: block;
}

.menu-item {
  margin-bottom: 3px;
}

.menu-link {
  color: #cbd5e1 !important;
  font-weight: 500;
  text-decoration: none;
  border-radius: 6px;
  padding: 8px 15px;
  display: flex;
  align-items: center;
  transition: all 0.2s ease;
}

.menu-link i {
  font-size: 18px;
  margin-right: 10px;
  color: #cbd5e1;
}

.menu-link:hover {
  background-color: #2c3341;
  color: #fff !important;
}

.menu-link:hover i {
  color: #4fc3f7 !important;
}

/* Menu đang active */
.menu-item.active > .menu-link,
.menu-link.active {
  background-color: #2d8cff !important;
  color: #fff !important;
  box-shadow: 0 2px 8px rgba(45, 140, 255, 0.4);
}

.menu-item.active > .menu-link i {
  color: #fff !important;
}

/* ======= NAVBAR ======= */
.navbar {
  background-color: #fff !important;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  height: 70px;
}

.navbar .navbar-brand img {
  transition: 0.3s;
}

.navbar .navbar-brand img:hover {
  transform: scale(1.05);
}

/* Navbar text */
.navbar-menu-wrapper h5 {
  font-weight: 600;
  color: #333;
  letter-spacing: 0.3px;
}

/* ======= MAIN CONTENT ======= */
.page-body-wrapper {
  background: #f4f6fb;
  min-height: 100vh;
  padding-top: 80px;
}

.main-panel {
  background-color: #fff;
  border-radius: 10px;
  padding: 25px;
  margin: 20px;
  box-shadow: 0 3px 15px rgba(0, 0, 0, 0.05);
}

/* ======= BẢNG DANH SÁCH ======= */
.table {
  border-collapse: separate;
  border-spacing: 0 8px;
}

.table thead th {
  background-color: #edf2f7;
  border: none;
  font-weight: 600;
  color: #444;
  text-align: center;
}

.table tbody tr {
  background: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.table tbody tr:hover {
  background-color: #f9fbff;
}

.table td, .table th {
  vertical-align: middle;
  text-align: center;
}

/* ======= FOOTER ======= */
.footer {
  background: #fff;
  border-top: 1px solid #e9ecef;
  padding: 15px;
  font-size: 14px;
  color: #6c757d;
}

/* ======= SCROLLBAR ======= */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-thumb {
  background-color: #4fc3f7;
  border-radius: 5px;
}

::-webkit-scrollbar-track {
  background-color: #1b1e29;
}
</style>


          <!-- partial:partials/_footer.html -->
          <footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
              <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2024 Stellar. All rights reserved. <a href="#"> Terms of use</a><a href="#">Privacy Policy</a></span>
              <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with <i class="icon-heart text-danger"></i></span>
            </div>
          </footer>
          <!-- partial -->
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="assets/vendors/chart.js/chart.umd.js"></script>
    <script src="assets/vendors/jvectormap/jquery-jvectormap.min.js"></script>
    <script src="assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
    <script src="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="assets/vendors/moment/moment.min.js"></script>
    <script src="assets/vendors/daterangepicker/daterangepicker.js"></script>
    <script src="assets/vendors/chartist/chartist.min.js"></script>
    <script src="assets/vendors/progressbar.js/progressbar.min.js"></script>
    <script src="assets/js/jquery.cookie.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/hoverable-collapse.js"></script>
    <script src="assets/js/misc.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/todolist.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="assets/js/dashboard.js"></script>
    <!-- End custom js for this page -->
  </body>
</html>