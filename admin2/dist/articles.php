<?php
require_once('ketnoi.php');

$sql = "SELECT a.article_id, a.title, a.slug, a.status, a.views, a.created_at, 
        c.name AS category_name, au.name AS author_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN authors au ON a.author_id = au.author_id
        ORDER BY a.created_at DESC";
$query = mysqli_query($ketnoi, $sql);
?>

<div class="content-wrapper">
  <!-- Nội dung chính -->
  <div class="container-xxl flex-grow-1 container-p-y">

    <!-- Tiêu đề trang -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="fw-bold mb-0">
        <i class='bx bx-news text-primary'></i> Quản lý bài viết
      </h4>
      <a href="?page_layout=them_baiviet" class="btn btn-primary btn-sm">
        <i class='bx bx-plus'></i> Thêm bài viết
      </a>
    </div>

    <!-- Bảng danh sách -->
    <div class="card shadow-sm">
      <div class="card-header bg-light">
        <h5 class="mb-0">Danh sách bài viết</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle text-nowrap">
            <thead class="table-light">
              <tr class="text-center">
                <th width="5%">STT</th>
                <th width="25%">Tiêu đề</th>
                <th width="15%">Danh mục</th>
                <th width="15%">Tác giả</th>
                <th width="10%">Trạng thái</th>
                <th width="10%">Lượt xem</th>
                <th width="15%">Ngày tạo</th>
                <th width="5%">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $i = 1;
              while ($row = mysqli_fetch_assoc($query)) {
              ?>
                <tr>
                  <td class="text-center fw-bold"><?php echo $i++; ?></td>
                  <td><?php echo htmlspecialchars($row['title']); ?></td>
                  <td class="text-center"><?php echo $row['category_name'] ?: '<em>Không có</em>'; ?></td>
                  <td class="text-center"><?php echo $row['author_name'] ?: '<em>Ẩn danh</em>'; ?></td>
                  <td class="text-center">
                    <?php if ($row['status'] == 'published') { ?>
                      <span class="badge bg-success">Đã xuất bản</span>
                    <?php } else { ?>
                      <span class="badge bg-secondary">Nháp</span>
                    <?php } ?>
                  </td>
                  <td class="text-center"><?php echo $row['views']; ?></td>
                  <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                  <td class="text-center">
                    <div class="dropdown">
                      <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                      </button>
                      <div class="dropdown-menu">
                        <a class="dropdown-item" href="?page_layout=sua_baiviet&id=<?php echo $row['article_id']; ?>">
                          <i class="bx bx-edit-alt me-1"></i> Sửa
                        </a>
                        <a class="dropdown-item" href="?page_layout=xoa_baiviet&id=<?php echo $row['article_id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?')">
                          <i class="bx bx-trash me-1"></i> Xóa
                        </a>
                      </div>
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
