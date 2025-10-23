<?php
require_once('ketnoi.php');

// Truy vấn danh sách yêu thích (liên kết với người dùng và bài viết)
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

<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
      <span class="text-muted fw-light"></span> ❤️ Quản lý yêu thích
    </h4>

    <!-- Basic Bootstrap Table -->
    <div class="card">
      <h5 class="card-header">
        Danh sách bài viết được yêu thích
      </h5>

      <div class="table-responsive text-nowrap">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th>STT</th>
              <th>Người dùng</th>
              <th>Bài viết</th>
              <th>Ngày thêm</th>
              <th>Hành động</th>
            </tr>
          </thead>

          <tbody>
            <?php 
            $i = 1;
            while ($row = mysqli_fetch_assoc($query)) { ?>
              <tr>
                <td><strong><?php echo $i++; ?></strong></td>
                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                <td><?php echo htmlspecialchars($row['article_title']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                <td>
                  <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                      <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="?page_layout=xoayeuthich&id=<?php echo $row['favorite_id']; ?>">
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
    <!--/ Basic Bootstrap Table -->
  </div>
  <!-- / Content -->
</div>
