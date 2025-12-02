<?php
require_once('ketnoi.php');

// Lấy dữ liệu từ bảng tags
$sql = "SELECT * FROM tags";
$query = mysqli_query($ketnoi, $sql);
?>

<style>
/* 🌌 Giao diện tổng thể */
.card {
  background: rgba(10, 10, 20, 0.9);
  border-radius: 15px;
  box-shadow: 0 0 20px rgba(0, 255, 255, 0.1);
  color: #fff;
  border: 1px solid rgba(0, 255, 255, 0.1);
  padding-bottom: 10px;
}

/* 💠 Header bảng */
.card-header {
  background: transparent;
  border-bottom: 1px solid rgba(0, 255, 255, 0.25);
  color: #00eaff;
  font-weight: bold;
  font-size: 1.05rem;
  text-shadow: 0 0 8px #00eaff;
  padding: 12px 20px;
}

/* ✨ Nút thêm thẻ */
.btn-add {
  color: #00f6ff;
  border: 1px solid #00f6ff;
  border-radius: 25px;
  padding: 6px 18px;
  font-weight: 600;
  text-decoration: none;
  font-size: 0.95rem;
  box-shadow: 0 0 8px #00f6ff;
  transition: all 0.3s ease;
}
.btn-add:hover {
  background: #00f6ff;
  color: #000;
  box-shadow: 0 0 20px #00f6ff, 0 0 40px #00f6ff;
}

/* 🟡 Nút sửa */
.btn-edit {
  background: #ffcc00;
  border: none;
  border-radius: 20px;
  padding: 4px 12px;
  color: #000;
  font-weight: 600;
  font-size: 0.9rem;
  box-shadow: 0 0 10px rgba(255, 204, 0, 0.7);
  transition: 0.3s ease;
}
.btn-edit:hover {
  background: #ffe45c;
  transform: scale(1.05);
  box-shadow: 0 0 20px rgba(255, 230, 90, 1);
}

/* 🔴 Nút xóa */
.btn-delete {
  background: #ff4444;
  border: none;
  border-radius: 20px;
  padding: 4px 12px;
  color: #fff;
  font-weight: 600;
  font-size: 0.9rem;
  box-shadow: 0 0 10px rgba(255, 0, 0, 0.6);
  transition: 0.3s ease;
}
.btn-delete:hover {
  background: #ff6666;
  transform: scale(1.05);
  box-shadow: 0 0 20px rgba(255, 80, 80, 1);
}

/* 📋 Bảng dữ liệu */
.table {
  color: #fff;
  margin-bottom: 0;
}
.table th {
  color: #00f6ff;
  text-shadow: 0 0 6px #00f6ff;
  font-weight: 600;
  font-size: 0.95rem;
  border-bottom: 1px solid rgba(0, 255, 255, 0.2);
}
.table td {
  vertical-align: middle;
  padding: 10px;
  font-size: 0.92rem;
}
.table tbody tr:hover {
  background: rgba(0, 255, 255, 0.08);
  transition: 0.3s;
}
</style>

<!-- Content wrapper -->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <!-- Tiêu đề -->
    <h4 class="fw-bold py-3 mb-4" style="color:#00eaff; text-shadow:0 0 8px #00eaff;">
      🎮 Quản lý thẻ game
    </h4>

    <!-- Card danh sách -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>📘 Danh sách thẻ game</span>
        <a href="?page_layout=themthegame" class="btn-add">+ Thêm thẻ game</a>
      </div>

      <div class="table-responsive text-nowrap px-2">
        <table class="table align-middle text-center">
          <thead>
            <tr>
              <th style="width:5%">STT</th>
              <th style="width:20%">Tên thẻ</th>
              <th style="width:20%">Slug</th>
              <th style="width:35%">Mô tả</th>
              <th style="width:20%">Hành động</th>
            </tr>
          </thead>

          <tbody>
            <?php 
            $i = 1;
            while ($row = mysqli_fetch_assoc($query)) { ?>
              <tr>
                <td><strong><?php echo $i++; ?></strong></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['slug']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td>
                  <div class="d-flex justify-content-center gap-2">
                    <a href="?page_layout=suathegame&id=<?php echo $row['tag_id']; ?>" class="btn-edit">
                      ✏️ Sửa
                    </a>
                    <a href="?page_layout=xoathegame&id=<?php echo $row['tag_id']; ?>" class="btn-delete"
                      onclick="return confirm('Bạn có chắc muốn xóa thẻ này không?');">
                      🗑️ Xóa
                    </a>
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
