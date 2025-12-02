<?php
include 'ketnoi.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $display_name = trim($_POST['display_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    // 1. Kiểm tra tính hợp lệ cơ bản
    if ($username === '' || $display_name === '' || $email === '' || $password === '') {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } elseif ($password !== $confirm) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } else {
        // 2. Kiểm tra tài khoản/email đã tồn tại
        $check = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $check->bind_param('ss', $username, $email);
        $check->execute();
        $res = $check->get_result();
        
        if ($res->num_rows > 0) {
            $error = 'Tên đăng nhập hoặc email đã tồn tại.';
        } else {
            // 3. Đăng ký thành công
            $hash = password_hash($password, PASSWORD_BCRYPT);
            
            // Thêm trường 'avatar' với giá trị mặc định vào INSERT (nếu bạn dùng cột này)
            // Giả sử avatar mặc định là 'default.png'
            $default_avatar = 'default.png'; 
            
            $sql = $conn->prepare("INSERT INTO users (username, display_name, email, password, role, avatar, created_at) VALUES (?, ?, ?, ?, 'user', ?, NOW())");
            $sql->bind_param('sssss', $username, $display_name, $email, $hash, $default_avatar);
            
            if ($sql->execute()) {
                $success = 'Đăng ký thành công! <a href="login.php" class="text-warning">Đăng nhập ngay</a>';
            } else {
                $error = 'Lỗi khi tạo tài khoản: ' . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Tài Khoản</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <style>
        /* ===== STYLES BẮT CHƯỚC LOGIN.PHP ===== */
        body {
            background-color: #111; 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex-grow: 1;
            display: flex;
            align-items: center; 
            justify-content: center; 
            padding: 50px 15px;
        }
        
        /* Đổi tên class từ login-box thành register-box */
        .register-box { 
            max-width: 480px; /* Rộng hơn login một chút cho nhiều trường hơn */
            width: 100%;
            padding: 40px;
            background: rgba(10, 10, 10, 0.9); 
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5), 0 0 30px rgba(0, 204, 255, 0.1); /* Dùng màu Neon khác (xanh) */
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s;
        }

        .register-box:hover {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.7), 0 0 40px rgba(0, 204, 255, 0.2); 
        }

        .register-box h3 {
            color: #fff;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 25px;
            text-transform: uppercase;
        }

        /* ===== STYLES CHO INPUT FIELD ===== */
        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .form-control-custom {
            width: 100%;
            height: 50px;
            padding: 10px 15px 10px 45px;
            background-color: #222;
            border: 1px solid #444;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
            outline: none;
        }

        .form-control-custom:focus {
            background-color: #333;
            border-color: #00c3ff; /* Màu focus xanh */
            box-shadow: 0 0 5px rgba(0, 204, 255, 0.5);
        }

        .input-group-text-custom {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #00c3ff; /* Màu Icon xanh */
            font-size: 18px;
            pointer-events: none;
            z-index: 10;
        }

        /* ===== STYLES CHO BUTTON (NEON GLOW MÀU XANH) ===== */
        .btn-neon-blue {
            background: linear-gradient(135deg, #00c3ff, #00aaff);
            border: none;
            color: #111;
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 700;
            font-size: 16px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            box-shadow: 0 0 5px rgba(0, 204, 255, 0.5);
        }

        .btn-neon-blue:hover {
            background: linear-gradient(135deg, #33d7ff, #00bfff);
            transform: translateY(-2px);
            box-shadow: 0 0 15px #00c3ff; /* Hiệu ứng Neon mạnh hơn khi hover */
        }

        .alert-danger {
            background-color: #440000;
            color: #ff6666;
            border-color: #880000;
            margin-bottom: 25px;
        }
        .alert-success {
            background-color: #004400;
            color: #66ff66;
            border-color: #008800;
            margin-bottom: 25px;
        }
        .register-link a {
            color: #00c3ff; /* Màu link xanh */
            font-weight: 500;
            text-decoration: none;
            transition: color 0.3s;
        }

        .register-link a:hover {
            color: #33d7ff;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?> 

<div class="main-content">
    <div class="register-box text-center">
        <h3 class="mb-4">
            <i class="fas fa-user-plus me-2"></i> 
            ĐĂNG KÝ TÀI KHOẢN
        </h3>

        <?php
        if ($error) echo '<div class="alert alert-danger">'.$error.'</div>';
        if ($success) echo '<div class="alert alert-success">'.$success.'</div>';
        ?>

        <form method="POST">
            
            <div class="input-group">
                <span class="input-group-text-custom"><i class="fas fa-user"></i></span>
                <input type="text" name="username" class="form-control-custom" placeholder="Tên đăng nhập" required 
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>

            <div class="input-group">
                <span class="input-group-text-custom"><i class="fas fa-id-badge"></i></span>
                <input type="text" name="display_name" class="form-control-custom" placeholder="Tên hiển thị" required
                       value="<?= isset($_POST['display_name']) ? htmlspecialchars($_POST['display_name']) : '' ?>">
            </div>

            <div class="input-group">
                <span class="input-group-text-custom"><i class="fas fa-envelope"></i></span>
                <input type="email" name="email" class="form-control-custom" placeholder="Email" required
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>

            <div class="input-group">
                <span class="input-group-text-custom"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" class="form-control-custom" placeholder="Mật khẩu (ít nhất 6 ký tự)" required>
            </div>

            <div class="input-group">
                <span class="input-group-text-custom"><i class="fas fa-shield-alt"></i></span>
                <input type="password" name="confirm" class="form-control-custom" placeholder="Xác nhận mật khẩu" required>
            </div>

            <button type="submit" class="btn btn-neon-blue w-100 mt-2">Đăng ký</button>
        </form>

        <p class="mt-4 register-link">
            Đã có tài khoản? 
            <a href="login.php">Đăng nhập ngay</a>
        </p>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>