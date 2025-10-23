<?php
require_once('ketnoi.php');

// Lấy danh sách tác giả
$sql = "SELECT * FROM authors ORDER BY author_id DESC";
$query = mysqli_query($ketnoi, $sql);
?>

<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
      <span class="text-muted fw-light"></span> ✍️ Quản lý tác giả
    </h4>

    <!-- Basic Bootstrap Table -->
    <div class="card">
      <h5 class="card-header">
        Danh sách tác giả
        <a href="?page_layout=themtacgia"><i class="bx bx-plus"></i></a>
      </h5>

      <div class="table-responsive text-nowrap">
        <table class="table align-middle">
          <thead class="table-light">
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
                    <img src="../uploads/authors/<?php echo $row['avatar']; ?>" 
                         alt="Avatar" class="rounded-circle" width="50" height="50">
                  <?php } else { ?>
                    <img src="../assets/img/avatars/default.png" 
                         alt="Avatar" class="rounded-circle" width="50" height="50">
                  <?php } ?>
                </td>
                <td><?php echo htmlspecialchars($row['bio']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                <td>
                  <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                      <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="?page_layout=suatacgia&id=<?php echo $row['author_id']; ?>">
                        <i class="bx bx-edit-alt me-1"></i> Sửa
                      </a>
                      <a class="dropdown-item" href="?page_layout=xoatacgia&id=<?php echo $row['author_id']; ?>">
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
