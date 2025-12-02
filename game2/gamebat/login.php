<?php
session_start();
include 'ketnoi.php'; // chỉ kết nối CSDL, không include header

// Nếu đã đăng nhập → quay về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } else {

        $sql = "SELECT * FROM users WHERE username = ?"; 
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                // Lưu SESSION
                $_SESSION['user_id']      = $user['user_id'];
                $_SESSION['display_name'] = $user['display_name'];
                $_SESSION['role']         = $user['role'];
                
                // LƯU TÊN FILE AVATAR VÀO SESSION
                // Đảm bảo tên cột là 'avatar'
                $_SESSION['avatar']       = $user['avatar']; 

                // Sau khi đăng nhập → quay về trang chủ
                header("Location: index.php");
                exit();
            } else {
                $error = 'Sai mật khẩu.';
            }
        } else {
            $error = 'Tài khoản không tồn tại.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <style>
        /* ===== SETUP CHO PAGE LOGIN ===== */
        body {
            /* Giả sử nền tối của website là #111 */
            background-color: #111; 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Đẩy nội dung chính (login form) xuống dưới header */
        .main-content {
            flex-grow: 1;
            display: flex;
            align-items: center; /* Căn giữa theo chiều dọc */
            justify-content: center; /* Căn giữa theo chiều ngang */
            padding: 50px 15px;
        }
        
        /* ===== THIẾT KẾ FORM CHÍNH (NEON DARK) ===== */
        .login-box {
            max-width: 420px;
            width: 100%;
            padding: 40px;
            background: rgba(10, 10, 10, 0.9); /* Nền tối nhẹ */
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5), 0 0 30px rgba(255, 179, 0, 0.1); /* Shadow và Neon Glow nhẹ */
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s;
        }

        .login-box:hover {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.7), 0 0 40px rgba(255, 179, 0, 0.2); 
        }

        .login-box h3 {
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
            padding: 10px 15px 10px 45px; /* Thêm padding trái cho icon */
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
            border-color: #ffb300;
            box-shadow: 0 0 5px rgba(255, 179, 0, 0.5);
        }

        .input-group-text-custom {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #ffb300; /* Màu Icon */
            font-size: 18px;
            pointer-events: none;
            z-index: 10;
        }

        /* ===== STYLES CHO BUTTON (NEON GLOW) ===== */
        .btn-neon {
            background: linear-gradient(135deg, #ffb300, #ff8800);
            border: none;
            color: #111;
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 700;
            font-size: 16px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            box-shadow: 0 0 5px rgba(255, 179, 0, 0.5);
        }

        .btn-neon:hover {
            background: linear-gradient(135deg, #ffc107, #ff9800);
            transform: translateY(-2px);
            box-shadow: 0 0 15px #ffb300; /* Hiệu ứng Neon mạnh hơn khi hover */
        }

        .alert-danger {
            background-color: #440000;
            color: #ff6666;
            border-color: #880000;
            margin-bottom: 25px;
        }

        .register-link a {
            color: #ffb300;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.3s;
        }

        .register-link a:hover {
            color: #ffc107;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?> 

<div class="main-content">
    <div class="login-box text-center">
        <h3 class="mb-4">
            <i class="fas fa-sign-in-alt me-2"></i> 
            ĐĂNG NHẬP
        </h3>

        <?php if ($error) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>

        <form method="POST">
            
            <div class="input-group">
                <span class="input-group-text-custom"><i class="fas fa-user"></i></span>
                <input type="text" name="username" class="form-control-custom" placeholder="Tên đăng nhập" required>
            </div>

            <div class="input-group">
                <span class="input-group-text-custom"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" class="form-control-custom" placeholder="Mật khẩu" required>
            </div>

            <button type="submit" class="btn btn-neon w-100 mt-2">Đăng nhập</button>
        </form>

        <p class="mt-4 register-link">
            Chưa có tài khoản? 
            <a href="register.php">Đăng ký ngay</a>
        </p>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>