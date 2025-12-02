<?php
require_once('ketnoi.php');

// Lấy dữ liệu từ bảng users
$sql = "SELECT * FROM users";
$query = mysqli_query($ketnoi, $sql);
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="fw-bold mb-0 text-neon"><i class="bx bx-group me-2"></i> Quản lý người dùng</h4>
      <a href="?page_layout=themus" class="btn btn-sky-neon">
        <i class="bx bx-plus me-1"></i> Thêm người dùng
      </a>
    </div>

    <!-- Card -->
    <div class="card bg-dark border-0 shadow-lg neon-card">
      <div class="card-header border-bottom border-secondary">
        <h5 class="text-light mb-0"><i class="bx bx-list-ul me-2"></i>Danh sách người dùng</h5>
      </div>

      <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0 neon-table">
          <thead class="text-uppercase text-neon-sm">
            <tr>
              <th>STT</th>
              <th>Tên đăng nhập</th>
              <th>Tên hiển thị</th>
              <th>Email</th>
              <th>Vai trò</th>
              <th class="text-center">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $i = 1;
            while ($row = mysqli_fetch_assoc($query)) { ?>
              <tr>
                <td><?php echo $i++; ?></td>
                <td class="fw-semibold text-white"><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['display_name']); ?></td>
                <td class="text-info"><?php echo htmlspecialchars($row['email']); ?></td>
                <td>
                  <?php 
                    switch ($row['role']) {
                      case 'admin':
                        echo '<span class="badge bg-danger text-uppercase neon-badge"><i class="bx bx-shield-quarter me-1"></i>Admin</span>';
                        break;
                      case 'editor':
                        echo '<span class="badge bg-warning text-dark text-uppercase neon-badge"><i class="bx bx-pencil me-1"></i>Biên tập</span>';
                        break;
                      default:
                        echo '<span class="badge bg-info text-dark text-uppercase neon-badge"><i class="bx bx-user me-1"></i>Người dùng</span>';
                    }
                  ?>
                </td>
                <td class="text-center">
                  <div class="d-flex justify-content-center gap-2">
                    <a href="?page_layout=suaus&id=<?php echo $row['user_id']; ?>" 
                       class="btn btn-warning-neon">
                      <i class="bx bx-edit-alt me-1"></i> Sửa
                    </a>
                    <button 
                       class="btn btn-danger-neon btn-delete"
                       data-id="<?php echo $row['user_id']; ?>">
                      <i class="bx bx-trash me-1"></i> Xóa
                    </button>
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const deleteButtons = document.querySelectorAll(".btn-delete");
  deleteButtons.forEach((btn) => {
    btn.addEventListener("click", function () {
      const id = this.getAttribute("data-id");
      Swal.fire({
        title: "Xác nhận xóa?",
        text: "Bạn có chắc chắn muốn xóa người dùng này không?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#e53935",
        cancelButtonColor: "#00e5ff",
        confirmButtonText: "Xóa",
        cancelButtonText: "Hủy",
        background: "#111",
        color: "#fff",
        backdrop: `rgba(0,0,0,0.7)`,
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "?page_layout=xoaus&id=" + id;
        }
      });
    });
  });
});
</script>
<style>
/* ===== NEON PHONG CÁCH CHÍNH ===== */
.text-neon {
  color: #00e5ff;
  text-shadow: 0 0 10px #00e5ff;
}

.text-neon-sm {
  color: #6ee2ff;
  letter-spacing: 0.5px;
}

/* ===== BUTTON THÊM ===== */
.btn-sky-neon {
  background: linear-gradient(90deg, #00bcd4, #1de9b6);
  color: #fff;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  box-shadow: 0 0 15px #00e5ff;
  transition: 0.3s;
}
.btn-sky-neon:hover {
  box-shadow: 0 0 25px #00e5ff, 0 0 10px #1de9b6;
  transform: translateY(-2px);
}

/* ===== KHUNG CARD ===== */
.neon-card {
  background: linear-gradient(145deg, #0b0c10, #111827);
  border-radius: 15px;
}

/* ===== NÚT SỬA / XÓA ===== */
.btn-warning-neon,
.btn-danger-neon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-weight: 600;
  border-radius: 25px;
  padding: 6px 14px;
  font-size: 14px;
  transition: all 0.3s ease;
  min-width: 80px;
  text-align: center;
}

/* Vàng */
.btn-warning-neon {
  background: #fbc02d;
  color: #000;
  box-shadow: 0 0 10px #fdd835;
  border: none;
}
.btn-warning-neon:hover {
  background: #ffeb3b;
  box-shadow: 0 0 20px #ffeb3b;
}

/* Đỏ */
.btn-danger-neon {
  background: #e53935;
  color: #fff;
  box-shadow: 0 0 10px #ef5350;
  border: none;
}
.btn-danger-neon:hover {
  background: #ff1744;
  box-shadow: 0 0 20px #ff1744;
}

/* ===== BẢNG ===== */
.table-dark {
  color: #e0e0e0;
  background-color: #121212;
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed; /* Giúp các cột thẳng hàng */
}

.neon-table th, .neon-table td {
  vertical-align: middle;
  border-color: rgba(255, 255, 255, 0.05);
  text-align: center;
  white-space: nowrap;
}

.neon-table th {
  color: #00e5ff;
  font-weight: 700;
  text-shadow: 0 0 5px #00e5ff;
  letter-spacing: 0.5px;
}

/* Căn đều cột */
.neon-table th:nth-child(1), .neon-table td:nth-child(1) { width: 5%; }
.neon-table th:nth-child(2), .neon-table td:nth-child(2) { width: 15%; }
.neon-table th:nth-child(3), .neon-table td:nth-child(3) { width: 20%; }
.neon-table th:nth-child(4), .neon-table td:nth-child(4) { width: 25%; }
.neon-table th:nth-child(5), .neon-table td:nth-child(5) { width: 15%; }
.neon-table th:nth-child(6), .neon-table td:nth-child(6) { width: 20%; }

.table-dark tbody tr:hover {
  background-color: rgba(0, 229, 255, 0.05);
  box-shadow: inset 0 0 10px #00e5ff;
  transition: 0.3s;
}

/* Badge */
.neon-badge {
  box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
  font-size: 0.8rem;
}

/* Giữ 2 nút ở giữa và thẳng hàng */
td .d-flex {
  justify-content: center !important;
  align-items: center;
  gap: 10px;
}
</style>