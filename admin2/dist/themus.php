<?php
require_once('ketnoi.php');

if (isset($_POST['add_user'])) {
    $username = mysqli_real_escape_string($ketnoi, $_POST['username']);
    $display_name = mysqli_real_escape_string($ketnoi, $_POST['display_name']);
    $email = mysqli_real_escape_string($ketnoi, $_POST['email']);
    $role = mysqli_real_escape_string($ketnoi, $_POST['role']);
    $password = mysqli_real_escape_string($ketnoi, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($ketnoi, $_POST['confirm_password']);

    // Kiểm tra mật khẩu nhập lại
    if ($password !== $confirm_password) {
        echo '<script>alert("❌ Mật khẩu xác nhận không khớp!");</script>';
    } else {
        // Kiểm tra xem username hoặc email đã tồn tại chưa
        $check_sql = "SELECT * FROM users WHERE username='$username' OR email='$email'";
        $check_result = mysqli_query($ketnoi, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            echo '<script>alert("⚠️ Tên đăng nhập hoặc email đã tồn tại!");</script>';
        } else {
            // Mã hóa mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $created_at = date('Y-m-d H:i:s');

            // Thêm user mới
            $sql_insert = "INSERT INTO users (username, display_name, email, password, role, created_at)
                           VALUES ('$username', '$display_name', '$email', '$hashed_password', '$role', '$created_at')";
            
            if (mysqli_query($ketnoi, $sql_insert)) {
                echo '<script>alert("✅ Thêm người dùng thành công!");
                      window.location.href="index.php?page_layout=danhsachnguoidung";</script>';
                exit();
            } else {
                echo '<script>alert("❌ Lỗi khi thêm người dùng!");</script>';
            }
        }
    }
}
?>

<?php
require_once('ketnoi.php');

// =================== XỬ LÝ THÊM NGƯỜI DÙNG ===================
if (isset($_POST['add_user'])) {
    $username = mysqli_real_escape_string($ketnoi, $_POST['username']);
    $display_name = mysqli_real_escape_string($ketnoi, $_POST['display_name']);
    $email = mysqli_real_escape_string($ketnoi, $_POST['email']);
    $role = mysqli_real_escape_string($ketnoi, $_POST['role']);
    $password = mysqli_real_escape_string($ketnoi, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($ketnoi, $_POST['confirm_password']);

    // Kiểm tra định dạng email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<script>alert("⚠️ Email không hợp lệ!");</script>';
        exit();
    }

    // Kiểm tra độ dài mật khẩu
    if (strlen($password) < 6) {
        echo '<script>alert("⚠️ Mật khẩu phải có ít nhất 6 ký tự!");</script>';
        exit();
    }

    // Kiểm tra mật khẩu nhập lại
    if ($password !== $confirm_password) {
        echo '<script>alert("❌ Mật khẩu xác nhận không khớp!");</script>';
    } else {
        // Kiểm tra username hoặc email đã tồn tại
        $check_sql = "SELECT * FROM users WHERE username='$username' OR email='$email'";
        $check_result = mysqli_query($ketnoi, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            echo '<script>alert("⚠️ Tên đăng nhập hoặc email đã tồn tại!");</script>';
        } else {
            // Mã hóa mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $created_at = date('Y-m-d H:i:s');

            // Thêm user mới
            $sql_insert = "INSERT INTO users (username, display_name, email, password, role, created_at)
                           VALUES ('$username', '$display_name', '$email', '$hashed_password', '$role', '$created_at')";
            
            if (mysqli_query($ketnoi, $sql_insert)) {

                // ⭐⭐⭐ TỰ ĐỘNG THÊM TÁC GIẢ NẾU LÀ "Biên tập" ⭐⭐⭐
                if ($role === 'Biên tập') {
                    $new_user_id = mysqli_insert_id($ketnoi);
                    $sql_author = "INSERT INTO authors (user_id, name) 
                                   VALUES ($new_user_id, '$display_name')";
                    mysqli_query($ketnoi, $sql_author);
                }

                // Popup thành công
                echo '
                <div id="success-popup" style="
                    position:fixed;top:0;left:0;width:100%;height:100%;
                    background:rgba(0,0,0,0.85);display:flex;
                    justify-content:center;align-items:center;z-index:9999;
                    color:#fff;font-family:Poppins,sans-serif;flex-direction:column;
                    animation:fadeIn 0.4s ease;">
                    <div style="
                        background:#0a0a1a;padding:30px 50px;border-radius:15px;
                        border:1px solid #00ffff88;text-align:center;
                        box-shadow:0 0 25px #00ffff55;">
                        <h2 style="color:#00ffff;">✅ Thêm người dùng thành công!</h2>
                        <p style="margin-top:10px;">Hệ thống sẽ tự động quay lại danh sách sau <b id=\"countdown\">3</b> giây...</p>
                    </div>
                </div>
                <style>
                @keyframes fadeIn { from {opacity:0;} to {opacity:1;} }
                @keyframes fadeOut { from {opacity:1;} to {opacity:0;} }
                </style>
                <script>
                let counter = 3;
                const countdown = document.getElementById("countdown");
                const popup = document.getElementById("success-popup");
                const timer = setInterval(() => {
                    counter--;
                    countdown.textContent = counter;
                    if(counter === 0){
                        clearInterval(timer);
                        popup.style.animation = "fadeOut 0.4s ease";
                        setTimeout(() => {
                            window.location.href="index.php?page_layout=danhsachnguoidung";
                        }, 400);
                    }
                },1000);
                </script>';
                exit();
            } else {
                echo '<script>alert("❌ Lỗi khi thêm người dùng!");</script>';
            }
        }
    }
}


// =================== XỬ LÝ AJAX KIỂM TRA TỒN TẠI ===================
if (isset($_POST['check_username']) || isset($_POST['check_email'])) {
    header('Content-Type: text/plain; charset=utf-8');
    if (isset($_POST['check_username'])) {
        $username = mysqli_real_escape_string($ketnoi, $_POST['check_username']);
        $query = mysqli_query($ketnoi, "SELECT * FROM users WHERE username='$username'");
        echo (mysqli_num_rows($query) > 0) ? 'exists' : 'ok';
        exit;
    }
    if (isset($_POST['check_email'])) {
        $email = mysqli_real_escape_string($ketnoi, $_POST['check_email']);
        $query = mysqli_query($ketnoi, "SELECT * FROM users WHERE email='$email'");
        echo (mysqli_num_rows($query) > 0) ? 'exists' : 'ok';
        exit;
    }
}
?>

<!-- =================== GIAO DIỆN FORM =================== -->
<div class="user-form-container">
  <h2 class="form-title">✨ Thêm người dùng mới ✨</h2>
  <form action="themus.php" method="POST" class="user-form">
    <div class="form-group">
      <label for="username">Tên đăng nhập</label>
      <input type="text" id="username" name="username" required placeholder="Nhập tên đăng nhập...">
      <small id="username-status" class="status-text"></small>
    </div>

    <div class="form-group">
      <label for="display_name">Tên hiển thị</label>
      <input type="text" id="display_name" name="display_name" required placeholder="Nhập tên hiển thị...">
    </div>

    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required placeholder="Nhập email...">
      <small id="email-status" class="status-text"></small>
    </div>

    <div class="form-group">
      <label for="password">Mật khẩu</label>
      <input type="password" id="password" name="password" required placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)...">
    </div>

    <div class="form-group">
      <label for="confirm_password">Xác nhận mật khẩu</label>
      <input type="password" id="confirm_password" name="confirm_password" required placeholder="Nhập lại mật khẩu...">
    </div>

    <div class="form-group">
      <label for="role">Vai trò</label>
      <select id="role" name="role" required>
        <option value="Người dùng">Người dùng</option>
        <option value="Biên tập">Biên tập</option>
        <option value="Admin">Admin</option>
      </select>
    </div>

    <div class="form-actions">
      <button type="submit" name="add_user" class="btn-submit">➕ Thêm người dùng</button>
      <a href="index.php?page_layout=danhsachnguoidung" class="btn-cancel">↩️ Quay lại</a>
    </div>
  </form>
</div>

<!-- =================== SCRIPT KIỂM TRA AJAX =================== -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  const usernameInput = document.getElementById('username');
  const emailInput = document.getElementById('email');
  const usernameStatus = document.getElementById('username-status');
  const emailStatus = document.getElementById('email-status');

  usernameInput.addEventListener('blur', () => {
    const val = usernameInput.value.trim();
    if (val) {
      fetch('themus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'check_username=' + encodeURIComponent(val)
      })
      .then(res => res.text())
      .then(data => {
        if (data === 'exists') {
          usernameStatus.textContent = '⚠️ Tên đăng nhập đã tồn tại';
          usernameStatus.style.color = '#ff6666';
        } else {
          usernameStatus.textContent = '✅ Tên đăng nhập khả dụng';
          usernameStatus.style.color = '#00ff99';
        }
      });
    } else {
      usernameStatus.textContent = '';
    }
  });

  emailInput.addEventListener('blur', () => {
    const val = emailInput.value.trim();
    if (val) {
      fetch('themus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'check_email=' + encodeURIComponent(val)
      })
      .then(res => res.text())
      .then(data => {
        if (data === 'exists') {
          emailStatus.textContent = '⚠️ Email đã được dùng';
          emailStatus.style.color = '#ff6666';
        } else {
          emailStatus.textContent = '✅ Email hợp lệ';
          emailStatus.style.color = '#00ff99';
        }
      });
    } else {
      emailStatus.textContent = '';
    }
  });
});
</script>

<!-- =================== CSS GIAO DIỆN =================== -->
<style>
.user-form-container {
  max-width: 550px;
  margin: 60px auto;
  background: rgba(10, 10, 25, 0.9);
  border: 1px solid #00ffff40;
  box-shadow: 0 0 25px #00ffff33;
  border-radius: 20px;
  padding: 30px 40px;
  color: #fff;
  font-family: 'Poppins', sans-serif;
}

.form-title {
  text-align: center;
  color: #00ffff;
  text-shadow: 0 0 8px #00ffff, 0 0 20px #0099ff;
  margin-bottom: 25px;
  font-size: 22px;
  letter-spacing: 1px;
}

.user-form .form-group {
  margin-bottom: 18px;
}

.user-form label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  color: #00ffff;
}

.user-form input,
.user-form select {
  width: 100%;
  padding: 10px 12px;
  background: #0b0b1e;
  border: 1px solid #00ffff55;
  border-radius: 8px;
  color: #fff;
  font-size: 15px;
  outline: none;
  transition: all 0.2s ease;
}

.user-form input:focus,
.user-form select:focus {
  border-color: #00ffff;
  box-shadow: 0 0 8px #00ffff;
}

.status-text {
  font-size: 13px;
  display: block;
  margin-top: 5px;
}

.form-actions {
  text-align: center;
  margin-top: 25px;
}

.btn-submit,
.btn-cancel {
  padding: 10px 20px;
  border: none;
  border-radius: 10px;
  font-size: 15px;
  cursor: pointer;
  transition: 0.3s;
  text-decoration: none;
}

.btn-submit {
  background: linear-gradient(90deg, #00ffff, #0077ff);
  color: #000;
  font-weight: bold;
  box-shadow: 0 0 10px #00ffff88;
}

.btn-submit:hover {
  box-shadow: 0 0 20px #00ffffcc;
  transform: translateY(-2px);
}

.btn-cancel {
  background: #222;
  color: #fff;
  margin-left: 10px;
  border: 1px solid #444;
}

.btn-cancel:hover {
  background: #111;
  border-color: #00ffff;
  color: #00ffff;
}
</style>
