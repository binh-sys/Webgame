<?php
require_once('ketnoi.php');

// Lấy bình luận kèm bài viết & người dùng
$sql = "
  SELECT 
      c.comment_id, 
      c.content, 
      c.created_at, 
      a.title AS article_title, 
      u.display_name AS user_name
  FROM comments c
  LEFT JOIN articles a ON c.article_id = a.article_id
  LEFT JOIN users u ON c.user_id = u.user_id
  ORDER BY c.created_at DESC
";
$query = mysqli_query($ketnoi, $sql);
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4" style="color:#00ffff;text-shadow:0 0 10px #00ffff;">💬 Quản lý bình luận</h4>

    <div class="card neon-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="card-title">Danh sách bình luận</span>
        <a href="?page_layout=thembinhluan" class="neon-btn neon-add">
          <i class="bx bx-plus"></i> Thêm bình luận
        </a>
      </div>

      <div class="table-responsive text-nowrap">
        <table class="table table-dark align-middle neon-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Bài viết</th>
              <th>Người bình luận</th>
              <th>Nội dung</th>
              <th>Ngày bình luận</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $i = 1;
            while ($row = mysqli_fetch_assoc($query)) { ?>
              <tr>
                <td><strong><?php echo $i++; ?></strong></td>
                <td><?php echo htmlspecialchars($row['article_title']); ?></td>
                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($row['content'])); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                <td>
                  <div class="action-buttons">
                    <a href="?page_layout=suabinhluan&id=<?php echo $row['comment_id']; ?>" class="neon-btn neon-edit">
                      <i class="bx bx-edit"></i> Sửa
                    </a>
                    <a href="?page_layout=xoabinhluan&id=<?php echo $row['comment_id']; ?>" class="neon-btn neon-delete" onclick="return confirm('Xóa bình luận này?')">
                      <i class="bx bx-trash"></i> Xóa
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

<style>
/* --- Neon style --- */
.neon-card {
  background: rgba(10, 10, 25, 0.95);
  border: 1px solid #00ffff55;
  border-radius: 16px;
  box-shadow: 0 0 20px #00ffff33;
  overflow: hidden;
}

.card-title {
  color: #00ffff;
  font-weight: bold;
  font-size: 18px;
  text-shadow: 0 0 8px #00ffff;
}

.neon-table {
  color: #fff;
  background-color: transparent;
}

.neon-table thead tr {
  background: linear-gradient(90deg, #001a1a, #002b33);
  color: #00ffff;
  text-shadow: 0 0 5px #00ffff;
}

.neon-table tbody tr:hover {
  background: rgba(0, 255, 255, 0.05);
  transition: 0.2s;
}

/* --- Buttons --- */
.neon-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 14px;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  transition: 0.25s;
  box-shadow: 0 0 6px #00ffff44;
}

.neon-btn i {
  font-size: 16px;
}

/* Add Button */
.neon-add {
  background: linear-gradient(90deg, #00ffff, #0099ff);
  color: #000;
  box-shadow: 0 0 10px #00ffff88;
}
.neon-add:hover {
  box-shadow: 0 0 20px #00ffffcc;
  transform: translateY(-2px);
}

/* Edit Button */
.neon-edit {
  background: linear-gradient(90deg, #ffd900ff, #ffcc00ff);
  color: #000;
}
.neon-edit:hover {
  box-shadow: 0 0 10px #ffd500ff;
  transform: scale(1.05);
}

/* Delete Button */
.neon-delete {
  background: linear-gradient(90deg, #ff0033, #cc0044);
  color: #fff;
}
.neon-delete:hover {
  box-shadow: 0 0 10px #ff0033aa;
  transform: scale(1.05);
}

.action-buttons {
  display: flex;
  gap: 8px;
}
</style>
