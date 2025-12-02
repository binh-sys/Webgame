<?php
require_once('ketnoi.php');
$sql = "SELECT * FROM authors ORDER BY author_id DESC";
$query = mysqli_query($ketnoi, $sql);
?>

<style>
/* --- NỀN VÀ KHUNG CHUNG --- */
.card {
  background: linear-gradient(145deg, rgba(10,10,20,0.9), rgba(15,15,30,0.95));
  border-radius: 16px;
  border: 1px solid rgba(0,255,255,0.15);
  box-shadow: 0 0 25px rgba(0,255,255,0.05);
  color: #fff;
}

/* Tiêu đề chính */
h4 {
  color: #00eaff;
  font-weight: 700;
  text-shadow: 0 0 12px #00eaff, 0 0 25px rgba(0,255,255,0.6);
}

/* --- NÚT THÊM TÁC GIẢ --- */
.btn-add {
  color: #00f6ff;
  border: 1px solid #00f6ff;
  border-radius: 25px;
  padding: 8px 20px;
  font-weight: 600;
  text-decoration: none;
  font-size: 0.95rem;
  box-shadow: 0 0 10px #00f6ff;
  background: rgba(0,255,255,0.1);
  transition: all 0.4s ease;
  animation: pulseNeon 2.5s infinite;
}
.btn-add:hover {
  background: #00f6ff;
  color: #000;
  box-shadow: 0 0 25px #00f6ff, 0 0 50px #00f6ff;
}

@keyframes pulseNeon {
  0%,100% { box-shadow: 0 0 10px #00f6ff, 0 0 20px rgba(0,255,255,0.4); }
  50% { box-shadow: 0 0 25px #00f6ff, 0 0 50px rgba(0,255,255,0.8); }
}

/* --- BẢNG DANH SÁCH --- */
.table {
  color: #fff;
  margin-bottom: 0;
}
.table th {
  color: #00f6ff;
  text-shadow: 0 0 8px #00f6ff;
  font-weight: 600;
  border-bottom: 1px solid rgba(0,255,255,0.15);
  font-size: 0.95rem;
  text-align: center;
}
.table td {
  vertical-align: middle;
  font-size: 0.9rem;
  text-align: center;
}
.table tbody tr {
  transition: 0.3s;
}
.table tbody tr:hover {
  background: rgba(0,255,255,0.08);
  box-shadow: 0 0 10px rgba(0,255,255,0.2);
}

/* --- ẢNH ĐẠI DIỆN --- */
.table img {
  border-radius: 50%;
  border: 2px solid rgba(0,255,255,0.6);
  box-shadow: 0 0 12px rgba(0,255,255,0.3);
  transition: 0.3s;
}
.table img:hover {
  transform: scale(1.1);
  box-shadow: 0 0 25px rgba(0,255,255,0.8);
}

/* --- NÚT SỬA / XÓA --- */
.btn-edit, .btn-delete {
  border: none;
  border-radius: 18px;
  padding: 5px 14px;
  font-weight: 600;
  font-size: 0.85rem;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}
.btn-edit {
  background: linear-gradient(90deg, #ffcc00, #ffee58);
  color: #000;
  box-shadow: 0 0 10px rgba(255,220,0,0.7);
}
.btn-edit:hover {
  transform: scale(1.05);
  box-shadow: 0 0 25px rgba(255,220,0,1);
}
.btn-delete {
  background: linear-gradient(90deg, #ff3b3b, #ff7676);
  color: #fff;
  box-shadow: 0 0 10px rgba(255,60,60,0.7);
}
.btn-delete:hover {
  transform: scale(1.05);
  box-shadow: 0 0 25px rgba(255,80,80,1);
}

/* --- CARD HEADER --- */
.card-header {
  background: transparent;
  border-bottom: 1px solid rgba(0,255,255,0.2);
  color: #00f6ff;
  font-weight: 600;
  font-size: 1rem;
  text-shadow: 0 0 8px #00f6ff;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.card-header a {
  color: #00f6ff;
  font-size: 1.2rem;
  text-shadow: 0 0 8px #00f6ff;
  transition: 0.3s;
}
.card-header a:hover {
  color: #fff;
  text-shadow: 0 0 20px #00f6ff;
}
</style>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="fw-bold mb-0"><i class='bx bx-user-circle'></i> Quản lý tác giả</h4>
      <a href="?page_layout=themtacgia" class="btn-add">+ Thêm tác giả</a>
    </div>

    <div class="card">
      <div class="card-header">
        <span><i class='bx bx-list-ul'></i> Danh sách tác giả</span>
      </div>

      <div class="table-responsive px-2 py-2">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>STT</th>
              <th>Tên tác giả</th>
              <th>Email</th>
              <th>Ảnh đại diện</th>
              <th>Mô tả</th>
              <th>Ngày tạo</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $i = 1;
            while ($row = mysqli_fetch_assoc($query)) { ?>
              <tr>
                <td><strong><?php echo $i++; ?></strong></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td>
                  <?php if (!empty($row['avatar'])) { ?>
                    <img src="../uploads/authors/<?php echo $row['avatar']; ?>" width="50" height="50" alt="Avatar">
                  <?php } else { ?>
                    <img src="../assets/img/avatars/default.png" width="50" height="50" alt="Avatar">
                  <?php } ?>
                </td>
                <td><?php echo htmlspecialchars($row['bio']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                <td>
                  <div class="d-flex justify-content-center gap-2">
                    <a href="?page_layout=suatacgia&id=<?php echo $row['author_id']; ?>" class="btn-edit">✏️ Sửa</a>
                    <a href="?page_layout=xoatacgia&id=<?php echo $row['author_id']; ?>" 
                       onclick="return confirm('Bạn có chắc chắn muốn xóa tác giả này không?');" 
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
