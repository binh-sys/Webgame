<?php
require_once('ketnoi.php');

// Lấy danh sách bài viết cùng tên danh mục và tác giả
$sql = "SELECT a.article_id, a.title, a.slug, a.status, a.views, a.created_at, 
        c.name AS category_name, au.name AS author_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN authors au ON a.author_id = au.author_id
        ORDER BY a.created_at DESC";
$query = mysqli_query($ketnoi, $sql);
?>

<style>
  /* ===================== GLOBAL ===================== */
  body {
    font-family: 'Segoe UI', sans-serif;
    background: #0a0a14;
    color: #fff;
  }

  h4 {
    color: #00eaff;
    font-weight: 700;
    text-shadow: 0 0 12px #00eaff, 0 0 25px rgba(0, 255, 255, 0.6);
  }

  /* ===================== CARD ===================== */
  .card {
    background: linear-gradient(145deg, rgba(10, 10, 20, 0.9), rgba(15, 15, 30, 0.95));
    border-radius: 16px;
    border: 1px solid rgba(0, 255, 255, 0.15);
    box-shadow: 0 0 25px rgba(0, 255, 255, 0.05);
  }

  .card-header {
    background: transparent;
    border-bottom: 1px solid rgba(0, 255, 255, 0.2);
    color: #00f6ff;
    font-weight: 600;
    font-size: 1rem;
    text-shadow: 0 0 8px #00f6ff;
    padding: 12px 16px;
  }

  /* ===================== BUTTON ADD ===================== */
  .btn-add {
    color: #00f6ff;
    border: 1px solid #00f6ff;
    border-radius: 25px;
    padding: 8px 20px;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.95rem;
    background: rgba(0, 255, 255, 0.1);
    box-shadow: 0 0 10px #00f6ff;
    transition: all 0.4s ease;
    animation: pulseNeon 2.5s infinite;
  }

  .btn-add:hover {
    background: #00f6ff;
    color: #000;
    box-shadow: 0 0 25px #00f6ff, 0 0 50px #00f6ff;
  }

  /* ===================== TABLE ===================== */
  .table {
    color: #fff;
    margin-bottom: 0;
  }

  .table th {
    color: #00f6ff;
    text-shadow: 0 0 8px #00f6ff;
    font-weight: 600;
    border-bottom: 1px solid rgba(0, 255, 255, 0.15);
    font-size: 0.95rem;
  }

  .table td {
    vertical-align: middle;
    font-size: 0.9rem;
  }

  .table tbody tr {
    transition: 0.3s;
  }

  .table tbody tr:hover {
    background: rgba(0, 255, 255, 0.08);
    box-shadow: 0 0 10px rgba(0, 255, 255, 0.2);
  }

  /* ===================== BADGE ===================== */
  .badge {
    font-weight: 700;
    border-radius: 15px;
    padding: 6px 10px;
    font-size: 0.8rem;
  }

  .badge.bg-success {
    background: linear-gradient(90deg, #00ff99, #00ffaa);
    color: #000;
    box-shadow: 0 0 15px rgba(0, 255, 180, 0.9);
  }

  .badge.bg-secondary {
    background: #666;
    color: #fff;
    box-shadow: 0 0 10px rgba(160, 160, 160, 0.5);
  }

  /* Nút hành động mới – Icon neon pro */
  .btn-action {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(6px);
    transition: 0.25s;
    cursor: pointer;
  }

  /* Icon style */
  .btn-action svg {
    width: 22px;
    height: 22px;
    filter: drop-shadow(0 0 5px rgba(255, 255, 255, 0.4));
    transition: 0.3s;
  }

  /* ================== DUYỆT ================== */
  .btn-approve {
    border-color: rgba(0, 255, 120, 0.4);
    box-shadow: 0 0 8px rgba(0, 255, 120, 0.2);
  }

  .btn-approve:hover {
    transform: scale(1.12);
    box-shadow: 0 0 18px rgba(0, 255, 120, 0.9);
  }

  .btn-approve:hover svg {
    filter: drop-shadow(0 0 8px #00ff88);
  }

  /* ================== SỬA ================== */
  .btn-edit {
    border-color: rgba(255, 210, 0, 0.4);
    box-shadow: 0 0 8px rgba(255, 210, 0, 0.2);
  }

  .btn-edit:hover {
    transform: scale(1.12);
    box-shadow: 0 0 18px rgba(255, 210, 0, 1);
  }

  .btn-edit:hover svg {
    filter: drop-shadow(0 0 10px #ffdd33);
  }

  /* ================== XÓA ================== */
  .btn-delete {
    border-color: rgba(255, 60, 60, 0.4);
    box-shadow: 0 0 8px rgba(255, 60, 60, 0.25);
  }

  .btn-delete:hover {
    transform: scale(1.12);
    box-shadow: 0 0 20px rgba(255, 60, 60, 1);
  }

  .btn-delete:hover svg {
    filter: drop-shadow(0 0 10px #ff4444);
  }
</style>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="fw-bold mb-0"><i class='bx bx-news'></i> Quản lý bài viết</h4>
      <a href="?page_layout=them_baiviet" class="btn-add">+ Thêm bài viết</a>
    </div>

    <!-- CARD TABLE -->
    <div class="card">
      <div class="card-header"><i class='bx bx-list-ul'></i> Danh sách bài viết</div>
      <div class="table-responsive px-2 py-2">
        <table class="table align-middle text-center">
          <thead>
            <tr>
              <th>STT</th>
              <th>Tiêu đề</th>
              <th>Danh mục</th>
              <th>Tác giả</th>
              <th>Trạng thái</th>
              <th>Lượt xem</th>
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
                <td class="text-start"><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo $row['category_name'] ?: '<em>Không có</em>'; ?></td>
                <td><?php echo $row['author_name'] ?: '<em>Ẩn danh</em>'; ?></td>
                <td>
                  <?php if ($row['status'] == 'published') { ?>
                    <span class="badge bg-success">ĐÃ XUẤT BẢN</span>
                  <?php } else { ?>
                    <span class="badge bg-secondary">NHÁP</span>
                  <?php } ?>
                </td>
                <td><?php echo $row['views']; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                <td>
                  <div class="d-flex justify-content-center gap-2">

                    <?php if ($row['status'] != 'published') { ?>
                      <a href="?page_layout=duyet_baiviet&id=<?php echo $row['article_id']; ?>"
                        onclick="return confirm('Bạn có chắc chắn muốn duyệt bài viết này không?');"
                        class="btn-action btn-approve" title="Duyệt bài">
                        <!-- ICON DUYỆT -->
                        <svg fill="#00ff88" viewBox="0 0 24 24">
                          <path d="M9 16.2l-3.5-3.5-1.4 1.5L9 19 20 8l-1.4-1.4z" />
                        </svg>
                      </a>
                    <?php } ?>

                    <a href="?page_layout=sua_baiviet&id=<?php echo $row['article_id']; ?>"
                      class="btn-action btn-edit" title="Sửa bài">
                      <!-- ICON SỬA -->
                      <svg fill="#ffd700" viewBox="0 0 24 24">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.42l-2.34-2.34a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.82z" />
                      </svg>
                    </a>

                    <a href="?page_layout=xoa_baiviet&id=<?php echo $row['article_id']; ?>"
                      onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');"
                      class="btn-action btn-delete" title="Xóa bài">
                      <!-- ICON XÓA -->
                      <svg fill="#ff4444" viewBox="0 0 24 24">
                        <path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12zm3.46-9.12l1.06-1.06L12 10.94l1.47-1.47 1.06 1.06L13.06 12l1.47 1.47-1.06 1.06L12 13.06l-1.47 1.47-1.06-1.06L10.94 12l-1.48-1.12zM15.5 4l-1-1h-5l-1 1H5v2h14V4z" />
                      </svg>
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