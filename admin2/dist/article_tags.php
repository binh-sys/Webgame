<?php 
require_once('ketnoi.php');

// Lấy danh sách liên kết bài viết - thẻ game
$sql = "
  SELECT 
    at.id AS article_tag_id,
    a.title AS article_title,
    t.name AS tag_name
  FROM article_tags at
  LEFT JOIN articles a ON at.article_id = a.article_id
  LEFT JOIN tags t ON at.tag_id = t.tag_id
  ORDER BY at.id DESC
";
$query = mysqli_query($ketnoi, $sql);
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <h4 class="fw-bold py-3 mb-4" style="color:#00ffff;text-shadow:0 0 10px #00ffff;">
      🔗 Quản lý liên kết bài viết & thẻ game
    </h4>

    <div class="card neon-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="card-title">Danh sách liên kết</span>
        <a href="?page_layout=themlienthethegame" class="neon-btn neon-add">
          <i class="bx bx-plus"></i> Thêm liên kết
        </a>
      </div>

      <div class="table-responsive text-nowrap">
        <table class="table table-dark align-middle neon-table">
          <thead>
            <tr>
              <th>STT</th>
              <th>Tên bài viết</th>
              <th>Thẻ game</th>
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
                <td><?php echo htmlspecialchars($row['tag_name']); ?></td>
                <td>
                  <div class="action-buttons">
                    <a href="?page_layout=sualienthethegame&id=<?php echo $row['article_tag_id']; ?>" 
                       class="neon-btn neon-edit">
                      <i class="bx bx-edit"></i> Sửa
                    </a>
                    <a href="?page_layout=xoalienthethegame&id=<?php echo $row['article_tag_id']; ?>" 
                       class="neon-btn neon-delete"
                       onclick="return confirm('Bạn có chắc chắn muốn xóa liên kết này không?');">
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
/* --- Neon Base --- */
.neon-card {
  background: rgba(10, 10, 25, 0.95);
  border: 1px solid #00ffff55;
  border-radius: 16px;
  box-shadow: 0 0 20px #00ffff33;
  overflow: hidden;
}

.card-title {
  color: #00ffff;
  font-weight: 600;
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
  background: rgba(0, 255, 255, 0.07);
  transition: 0.2s ease;
}

/* --- Neon Buttons --- */
.neon-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 14px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  transition: 0.25s;
  border: none;
}

.neon-btn i {
  font-size: 16px;
}

/* Thêm */
.neon-add {
  background: linear-gradient(90deg, #00ffff, #0099ff);
  color: #000;
  box-shadow: 0 0 12px #00ffff88;
}
.neon-add:hover {
  box-shadow: 0 0 20px #00ffffcc;
  transform: translateY(-2px);
}

/* Sửa */
.neon-edit {
  background: linear-gradient(90deg, #ffcc00, #ffdd33);
  color: #000;
  box-shadow: 0 0 10px #ffcc00aa;
}
.neon-edit:hover {
  transform: scale(1.05);
  box-shadow: 0 0 20px #ffcc00ff;
}

/* Xóa */
.neon-delete {
  background: linear-gradient(90deg, #ff0033, #cc0044);
  color: #fff;
  box-shadow: 0 0 10px #ff003388;
}
.neon-delete:hover {
  transform: scale(1.05);
  box-shadow: 0 0 18px #ff0033cc;
}

/* Bố cục nút */
.action-buttons {
  display: flex;
  gap: 8px;
  justify-content: center;
}
</style>
