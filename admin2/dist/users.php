<?php
require_once('ketnoi.php'); // Đảm bảo đã kết nối CSDL

// Lấy dữ liệu từ bảng users
$sql = "SELECT * FROM users";
$query = mysqli_query($ketnoi, $sql);
?>

<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light"></span> Quản lý người dùng</h4>

    <!-- Bảng danh sách người dùng -->
    <div class="card">
      <h5 class="card-header">
        Danh sách người dùng
        <a href="?page_layout=themuser"><i class="bx bx-plus"></i></a>
      </h5>

      <div class="table-responsive text-nowrap">
        <table class="table">
          <thead>
            <tr>
              <th>STT</th>
              <th>Tên đăng nhập</th>
              <th>Tên hiển thị</th>
              <th>Email</th>
              <th>Vai trò</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php 
            $i = 1;
            while ($row = mysqli_fetch_assoc($query)) { ?>
              <tr>
                <td><strong><?php echo $i++; ?></strong></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['display_name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td>
                  <?php 
                    if ($row['role'] == 'admin') {
                      echo '<span class="badge bg-danger">Admin</span>';
                    } elseif ($row['role'] == 'editor') {
                      echo '<span class="badge bg-warning text-dark">Biên tập</span>';
                    } else {
                      echo '<span class="badge bg-info text-dark">Người dùng</span>';
                    }
                  ?>
                </td>

                <td>
                  <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                      <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="?page_layout=suauser&id=<?php echo $row['user_id']; ?>">
                        <i class="bx bx-edit-alt me-1"></i> Sửa
                      </a>
                      <a class="dropdown-item" href="?page_layout=xoauser&id=<?php echo $row['user_id']; ?>" 
                         onclick="return confirm('Bạn có chắc muốn xóa người dùng này không?');">
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
    <!-- /Bảng danh sách người dùng -->
  </div>
  <!-- / Content -->
</div>
<!-- / Content wrapper -->
