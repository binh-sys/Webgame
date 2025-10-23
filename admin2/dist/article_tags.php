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

<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
      <span class="text-muted fw-light"></span> 🔗 Quản lý liên kết bài viết & thẻ game
    </h4>

    <!-- Basic Bootstrap Table -->
    <div class="card">
      <h5 class="card-header">
        Danh sách liên kết
        <a href="?page_layout=themlienthethegame"><i class="bx bx-plus"></i></a>
      </h5>

      <div class="table-responsive text-nowrap">
        <table class="table align-middle">
          <thead class="table-light">
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
                  <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                      <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="?page_layout=sualienthethegame&id=<?php echo $row['article_tag_id']; ?>">
                        <i class="bx bx-edit-alt me-1"></i> Sửa
                      </a>
                      <a class="dropdown-item" href="?page_layout=xoalienthethegame&id=<?php echo $row['article_tag_id']; ?>">
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
