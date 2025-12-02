<?php
require_once('ketnoi.php');

// Truy vấn danh sách yêu thích
$sql = "
  SELECT f.favorite_id, f.created_at, 
         u.display_name AS user_name, 
         a.title AS article_title
  FROM favorites f
  LEFT JOIN users u ON f.user_id = u.user_id
  LEFT JOIN articles a ON f.article_id = a.article_id
  ORDER BY f.created_at DESC
";
$query = mysqli_query($ketnoi, $sql);
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <!-- Tiêu đề + Nút thêm -->
    <div class="d-flex justify-content-between align-items-center mb-4" style="gap: 15px;">
      <h4 class="fw-bold mb-0 d-flex align-items-center" style="gap: 8px;">
        <i class='bx bx-heart text-danger'></i>
        <span>Quản lý yêu thích</span>
      </h4>

      <a href="?page_layout=themyeuthich" class="btn-add">
        + Thêm yêu thích
      </a>
    </div>

    <!-- Bảng danh sách -->
    <div class="card shadow-sm">
      <div class="card-header bg-light d-flex align-items-center justify-content-between">
        <h5 class="mb-0 d-flex align-items-center" style="gap: 6px;">
          <i class='bx bx-list-ul'></i> Danh sách bài viết được yêu thích
        </h5>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle text-nowrap favorites-table">
            <thead class="table-light text-center align-middle">
              <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Người dùng</th>
                <th style="width: 35%;">Bài viết</th>
                <th style="width: 20%;">Ngày thêm</th>
                <th style="width: 15%;">Hành động</th>
              </tr>
            </thead>

            <tbody>
              <?php 
              $i = 1;
              while ($row = mysqli_fetch_assoc($query)) { ?>
                <tr>
                  <td class="text-center fw-bold"><?php echo $i++; ?></td>
                  <td class="text-center"><?php echo htmlspecialchars($row['user_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['article_title']); ?></td>
                  <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                  <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                      <a href="?page_layout=suayeuthich&id=<?php echo $row['favorite_id']; ?>" class="btn-edit">✏️ Sửa</a>
                      <a href="?page_layout=xoayeuthich&id=<?php echo $row['favorite_id']; ?>" 
                         onclick="return confirm('Bạn có chắc muốn xóa yêu thích này không?');"
                         class="btn-delete">🗑️ Xóa</a>
                    </div>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* Bảng yêu thích căn đều, thẳng hàng */
.favorites-table {
  table-layout: fixed;
  width: 100%;
  border-collapse: collapse;
}
.favorites-table th, 
.favorites-table td {
  vertical-align: middle;
  padding: 10px 8px;
  word-wrap: break-word;
}
.favorites-table th {
  background: #0d1b2a;
  color: #ff5c93;
  text-shadow: 0 0 5px #ff5c93;
  border-bottom: 2px solid #ff5c9370;
}

/* Nút thêm yêu thích */
.btn-add {
  background: linear-gradient(90deg, #ff5c93, #ff0066);
  color: #fff;
  font-weight: 600;
  padding: 8px 18px;
  border-radius: 30px;
  text-decoration: none;
  box-shadow: 0 0 10px #ff5c9370;
  transition: 0.3s;
}
.btn-add:hover {
  box-shadow: 0 0 20px #ff5c93;
  transform: translateY(-2px);
  color: #fff;
}

/* Nút hành động */
.btn-edit, .btn-delete {
  display: inline-block;
  padding: 5px 12px;
  border-radius: 8px;
  font-weight: 500;
  color: #fff;
  transition: 0.3s;
  min-width: 60px;
  text-align: center;
}
.btn-edit {
  background: #ffc107;
  box-shadow: 0 0 8px #ffc10770;
}
.btn-edit:hover {
  background: #ffcf40;
  box-shadow: 0 0 15px #ffc107;
}
.btn-delete {
  background: #ff4d4d;
  box-shadow: 0 0 8px #ff4d4d70;
}
.btn-delete:hover {
  background: #ff1a1a;
  box-shadow: 0 0 15px #ff4d4d;
}

/* Căn chỉnh tiêu đề và nút */
.d-flex.justify-content-between.align-items-center {
  align-items: center !important;
}
</style>
