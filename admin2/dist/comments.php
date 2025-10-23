<?php
require_once('ketnoi.php');

// Truy vấn tất cả bình luận, kèm bài viết và người dùng
$sql = "
  SELECT c.comment_id, c.content, c.created_at, 
         a.title AS article_title, 
         u.display_name AS user_name
  FROM comments c
  LEFT JOIN articles a ON c.article_id = a.article_id
  LEFT JOIN users u ON c.user_id = u.user_id
  ORDER BY c.created_at DESC
";
$query = mysqli_query($ketnoi, $sql);
?>

<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
      <span class="text-muted fw-light"></span> 💬 Quản lý bình luận
    </h4>

    <!-- Basic Bootstrap Table -->
    <div class="card">
      <h5 class="card-header">
        Danh sách bình luận
      </h5>

      <div class="table-responsive text-nowrap">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th>STT</th>
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
                  <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                      <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="?page_layout=xoabinhluan&id=<?php echo $row['comment_id']; ?>">
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
