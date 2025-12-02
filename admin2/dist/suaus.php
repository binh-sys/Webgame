<?php
require_once('ketnoi.php');

// --- AJAX kiểm tra trùng username/email ---
if (isset($_POST['ajax_check'])) {
    $field = $_POST['field'] ?? '';
    $value = trim($_POST['value'] ?? '');
    $id = intval($_POST['id'] ?? 0);
    $response = ['exists' => false];

    if ($field && $value) {
        $sql = "SELECT user_id FROM users WHERE $field = '$value' AND user_id != $id LIMIT 1";
        $result = mysqli_query($ketnoi, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $response['exists'] = true;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// --- Lấy thông tin người dùng ---
$row = [
    'username' => '',
    'display_name' => '',
    'email' => '',
    'role' => '',
];

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM users WHERE user_id = $id";
    $result = mysqli_query($ketnoi, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    } else {
        echo '<script>alert("Không tìm thấy người dùng!"); window.location.href="index.php?page_layout=danhsachnguoidung";</script>';
        exit();
    }
} else {
    echo '<script>alert("Thiếu ID người dùng!"); window.location.href="index.php?page_layout=danhsachnguoidung";</script>';
    exit();
}

// --- Cập nhật thông tin ---
if (isset($_POST['update_user'])) {
    $username = mysqli_real_escape_string($ketnoi, $_POST['username']);
    $display_name = mysqli_real_escape_string($ketnoi, $_POST['display_name']);
    $email = mysqli_real_escape_string($ketnoi, $_POST['email']);
    $role = mysqli_real_escape_string($ketnoi, $_POST['role']);
    $new_password = mysqli_real_escape_string($ketnoi, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($ketnoi, $_POST['confirm_password']);

    if (!empty($new_password) && $new_password !== $confirm_password) {
        echo '<script>alert("❌ Mật khẩu xác nhận không khớp!");</script>';
    } else {
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_update = "UPDATE users SET username='$username', display_name='$display_name', email='$email', role='$role', password='$hashed_password' WHERE user_id = $id";
        } else {
            $sql_update = "UPDATE users SET username='$username', display_name='$display_name', email='$email', role='$role' WHERE user_id = $id";
        }

        if (mysqli_query($ketnoi, $sql_update)) {

    // --- CẬP NHẬT BẢNG AUTHORS ---
    if ($role === 'editor') {
        // Kiểm tra nếu chưa có tác giả thì thêm
        $check_author = mysqli_query($ketnoi, "SELECT * FROM authors WHERE user_id = $id");
        if (mysqli_num_rows($check_author) === 0) {
            mysqli_query($ketnoi, "INSERT INTO authors (user_id, name) VALUES ($id, '$display_name')");
        } else {
            // Nếu đã có, cập nhật tên
            mysqli_query($ketnoi, "UPDATE authors SET name='$display_name' WHERE user_id = $id");
        }
    } else {
        // Nếu không phải editor → xóa khỏi authors nếu có
        mysqli_query($ketnoi, "DELETE FROM authors WHERE user_id = $id");
    }

    echo '<script>alert("✅ Cập nhật thành công!"); window.location.href="index.php?page_layout=danhsachnguoidung";</script>';
    exit();
}

    }
}
?>

<!-- GIAO DIỆN FORM -->
<div class="container py-4">
  <div class="form-card mx-auto">
    <div class="form-header">
      <i class='bx bx-user-circle icon'></i>
      <h4>Chỉnh sửa người dùng</h4>
      <p>Cập nhật thông tin cá nhân, quyền và mật khẩu</p>
    </div>

    <form method="POST" onsubmit="return checkPasswordMatch()" class="form-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Tên đăng nhập</label>
          <div class="input-group-custom">
            <i class='bx bx-user'></i>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($row['username']); ?>" required>
          </div>
          <div id="usernameMsg" class="input-msg"></div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Tên hiển thị</label>
          <div class="input-group-custom">
            <i class='bx bx-id-card'></i>
            <input type="text" name="display_name" value="<?php echo htmlspecialchars($row['display_name']); ?>" required>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Email</label>
          <div class="input-group-custom">
            <i class='bx bx-envelope'></i>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
          </div>
          <div id="emailMsg" class="input-msg"></div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Vai trò</label>
          <div class="input-group-custom">
            <i class='bx bx-shield-quarter'></i>
            <select name="role" required>
              <option value="admin" <?php if($row['role']=='admin') echo 'selected'; ?>>Quản trị viên</option>
              <option value="editor" <?php if($row['role']=='editor') echo 'selected'; ?>>Biên tập viên</option>
              <option value="user" <?php if($row['role']=='user') echo 'selected'; ?>>Người dùng</option>
            </select>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Mật khẩu mới</label>
          <div class="input-group-custom">
            <i class='bx bx-lock'></i>
            <input type="password" name="password" id="password" placeholder="Để trống nếu không đổi">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Xác nhận mật khẩu</label>
          <div class="input-group-custom">
            <i class='bx bx-check-shield'></i>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Nhập lại mật khẩu mới">
          </div>
        </div>
      </div>

      <div class="form-footer">
        <a href="index.php?page_layout=danhsachnguoidung" class="btn-cancel">
          <i class='bx bx-arrow-back'></i> Quay lại
        </a>
        <button type="submit" name="update_user" id="submitBtn" class="btn-save">
          <i class='bx bx-save'></i> Cập nhật
        </button>
      </div>
    </form>
  </div>
</div>

<style>
.form-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.08);
  max-width: 900px;
  padding: 30px 40px;
}
.form-header {
  text-align: center;
  margin-bottom: 30px;
}
.form-header .icon {
  font-size: 60px;
  color: #007bff;
}
.form-header h4 {
  font-weight: 700;
  margin-top: 10px;
}
.form-header p {
  color: #6c757d;
  margin-top: 5px;
}
.input-group-custom {
  position: relative;
}
.input-group-custom i {
  position: absolute;
  top: 50%;
  left: 15px;
  transform: translateY(-50%);
  color: #007bff;
  font-size: 20px;
}
.input-group-custom input,
.input-group-custom select {
  width: 100%;
  border: 2px solid #e3e6ea;
  border-radius: 12px;
  padding: 10px 14px 10px 45px;
  transition: 0.3s;
  font-size: 1rem;
}
.input-group-custom input:focus,
.input-group-custom select:focus {
  border-color: #007bff;
  box-shadow: 0 0 10px rgba(0,123,255,0.15);
  outline: none;
}
.input-msg {
  color: #dc3545;
  font-size: 0.9rem;
  margin-top: 3px;
  font-weight: 500;
}
.form-footer {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  margin-top: 30px;
}
.btn-save {
  background: linear-gradient(90deg, #00b09b, #96c93d);
  border: none;
  color: #fff;
  font-weight: 600;
  padding: 10px 25px;
  border-radius: 10px;
  box-shadow: 0 0 10px #00b09b60;
  transition: 0.3s;
}
.btn-save:hover {
  box-shadow: 0 0 20px #00b09b;
  transform: translateY(-2px);
}
.btn-cancel {
  background: #adb5bd;
  color: #fff;
  padding: 10px 25px;
  border-radius: 10px;
  font-weight: 600;
  text-decoration: none;
  transition: 0.3s;
}
.btn-cancel:hover {
  background: #868e96;
  transform: translateY(-2px);
}
</style>

<script>
function checkPasswordMatch() {
  const pass = document.getElementById('password').value;
  const confirm = document.getElementById('confirm_password').value;
  if (pass !== confirm) {
    alert("❌ Mật khẩu xác nhận không khớp!");
    return false;
  }
  return true;
}

// AJAX kiểm tra trùng username/email
document.addEventListener("DOMContentLoaded", function() {
  const username = document.getElementById("username");
  const email = document.getElementById("email");
  const usernameMsg = document.getElementById("usernameMsg");
  const emailMsg = document.getElementById("emailMsg");
  const submitBtn = document.getElementById("submitBtn");

  function checkDuplicate(field, value) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
      if (xhr.status === 200) {
        const res = JSON.parse(xhr.responseText);
        if (res.exists) {
          if (field === "username") {
            usernameMsg.textContent = "❌ Tên đăng nhập đã tồn tại!";
          } else {
            emailMsg.textContent = "❌ Email đã được sử dụng!";
          }
          submitBtn.disabled = true;
        } else {
          if (field === "username") usernameMsg.textContent = "";
          if (field === "email") emailMsg.textContent = "";
          submitBtn.disabled = false;
        }
      }
    };
    xhr.send(`ajax_check=1&field=${field}&value=${encodeURIComponent(value)}&id=<?php echo $id; ?>`);
  }

  username.addEventListener("blur", () => {
    if (username.value.trim() !== "") checkDuplicate("username", username.value.trim());
  });
  email.addEventListener("blur", () => {
    if (email.value.trim() !== "") checkDuplicate("email", email.value.trim());
  });
});
</script>
