<?php
require_once('ketnoi.php');

$sql = "SELECT * FROM categories ORDER BY category_id DESC";
$query = mysqli_query($ketnoi, $sql);
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <!-- Tiêu đề + Nút thêm -->
    <div class="d-flex justify-content-between align-items-center mb-4" style="gap: 15px;">
      <h4 class="fw-bold mb-0 d-flex align-items-center" style="gap: 8px;">
        <i class='bx bx-folder text-primary'></i> 
        <span>Quản lý chuyên mục</span>
      </h4>

      <!-- Nút thêm chuyên mục -->
      <a href="?page_layout=themchuyenmuc" class="btn-add">+ Thêm chuyên mục</a>
    </div>

    <!-- Bảng danh sách -->
    <div class="card shadow-sm">
      <div class="card-header bg-light d-flex align-items-center justify-content-between">
        <h5 class="mb-0 d-flex align-items-center" style="gap: 6px;">
          <i class='bx bx-list-ul'></i> Danh sách chuyên mục
        </h5>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle text-nowrap category-table">
            <thead class="table-light text-center align-middle">
              <tr>
                <th style="width: 6%;">STT</th>
                <th style="width: 25%;">Tên chuyên mục</th>
                <th style="width: 18%;">Slug</th>
                <th style="width: 36%;">Mô tả</th>
                <th style="width: 15%;">Hành động</th>
              </tr>
            </thead>

            <tbody>
              <?php 
              $i = 1;
              while ($row = mysqli_fetch_assoc($query)) { ?>
                <tr>
                  <td class="text-center fw-bold"><?php echo $i++; ?></td>
                  <td class="text-center"><?php echo htmlspecialchars($row['name']); ?></td>
                  <td class="text-center"><?php echo htmlspecialchars($row['slug']); ?></td>
                  <td style="text-align: left;"><?php echo htmlspecialchars($row['description']); ?></td>
                  <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                      <a href="?page_layout=suachuyenmuc&id=<?php echo $row['category_id']; ?>" class="btn-edit">✏️ Sửa</a>
                      <a href="?page_layout=xoachuyenmuc&id=<?php echo $row['category_id']; ?>" 
                         onclick="return confirm('Bạn có chắc chắn muốn xóa chuyên mục này không?');"
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
/* Bảng chuyên mục căn đều và đẹp hơn */
.category-table {
  table-layout: fixed;
  width: 100%;
  border-collapse: collapse;
}
.category-table th, 
.category-table td {
  vertical-align: middle;
  padding: 10px 8px;
  word-wrap: break-word;
}
.category-table th {
  background: #0d1b2a;
  color: #00eaff;
  text-shadow: 0 0 5px #00eaff;
  border-bottom: 2px solid #00eaff70;
}

/* Nút thêm chuyên mục */
.btn-add {
  background: linear-gradient(90deg, #00e0ff, #0077ff);
  color: #fff;
  font-weight: 600;
  padding: 8px 18px;
  border-radius: 30px;
  text-decoration: none;
  box-shadow: 0 0 10px #00e0ff80;
  transition: 0.3s;
}
.btn-add:hover {
  box-shadow: 0 0 20px #00e0ff;
  transform: translateY(-2px);
  color: #fff;
}

/* Nút hành động Sửa/Xóa */
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
