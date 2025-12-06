<?php
session_start();
include 'ketnoi.php';

// Kiểm tra cookie "remember me" khi vào trang
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    // Tìm user với token này
    $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token = ? AND remember_token IS NOT NULL");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Tự động đăng nhập
        $_SESSION['user_id']      = $user['user_id'];
        $_SESSION['display_name'] = $user['display_name'];
        $_SESSION['role']         = $user['role'];
        $_SESSION['avatar']       = $user['avatar'];
        $_SESSION['email']        = $user['email'];
        
        header("Location: index.php");
        exit();
    } else {
        // Token không hợp lệ, xóa cookie
        setcookie('remember_token', '', time() - 3600, '/');
    }
}

// Nếu đã đăng nhập → quay về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

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
                $_SESSION['user_id']      = $user['user_id'];
                $_SESSION['display_name'] = $user['display_name'];
                $_SESSION['role']         = $user['role'];
                $_SESSION['avatar']       = $user['avatar'];
                $_SESSION['email']        = $user['email'];

                // Xử lý "Ghi nhớ đăng nhập"
                if ($remember) {
                    // Tạo token ngẫu nhiên
                    $token = bin2hex(random_bytes(32));
                    
                    // Lưu token vào database
                    $update_stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE user_id = ?");
                    $update_stmt->bind_param('si', $token, $user['user_id']);
                    $update_stmt->execute();
                    
                    // Lưu cookie 30 ngày
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                }

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
    <title>Đăng Nhập - GameBat</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255, 179, 0, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 136, 0, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 193, 7, 0.05) 0%, transparent 40%);
            z-index: 0;
            animation: bgPulse 8s ease-in-out infinite;
        }

        @keyframes bgPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* Floating Particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 179, 0, 0.6);
            border-radius: 50%;
            animation: float 15s infinite;
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; animation-duration: 12s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 2s; animation-duration: 14s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 4s; animation-duration: 11s; }
        .particle:nth-child(4) { left: 40%; animation-delay: 1s; animation-duration: 16s; }
        .particle:nth-child(5) { left: 50%; animation-delay: 3s; animation-duration: 13s; }
        .particle:nth-child(6) { left: 60%; animation-delay: 5s; animation-duration: 15s; }
        .particle:nth-child(7) { left: 70%; animation-delay: 2s; animation-duration: 12s; }
        .particle:nth-child(8) { left: 80%; animation-delay: 4s; animation-duration: 14s; }
        .particle:nth-child(9) { left: 90%; animation-delay: 1s; animation-duration: 11s; }

        @keyframes float {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) scale(1); opacity: 0; }
        }

        .main-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
            z-index: 10;
        }

        /* Login Container */
        .login-container {
            display: flex;
            max-width: 900px;
            width: 100%;
            background: rgba(15, 15, 25, 0.95);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.5),
                0 0 100px rgba(255, 179, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.08);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Left Panel - Branding */
        .login-brand {
            flex: 1;
            background: linear-gradient(135deg, #ff8800 0%, #ffb300 50%, #ffc107 100%);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-brand::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .brand-content {
            position: relative;
            z-index: 2;
        }

        .brand-logo {
            width: 120px;
            height: 120px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .brand-logo i {
            font-size: 50px;
            color: #111;
        }

        .brand-title {
            font-size: 32px;
            font-weight: 800;
            color: #111;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .brand-subtitle {
            font-size: 16px;
            color: rgba(0, 0, 0, 0.7);
            line-height: 1.6;
            max-width: 280px;
        }

        .brand-features {
            margin-top: 40px;
            text-align: left;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            color: rgba(0, 0, 0, 0.8);
            font-size: 14px;
        }

        .brand-feature i {
            width: 24px;
            height: 24px;
            background: rgba(0, 0, 0, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        /* Right Panel - Form */
        .login-form-panel {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 40px;
        }

        .form-header h2 {
            color: #fff;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #888;
            font-size: 15px;
        }

        /* Input Groups */
        .input-group-custom {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group-custom label {
            display: block;
            color: #aaa;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 18px;
            transition: color 0.3s;
            z-index: 2;
        }

        .form-input {
            width: 100%;
            height: 56px;
            padding: 0 50px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            color: #fff;
            font-size: 16px;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input::placeholder {
            color: #555;
        }

        .form-input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #ffb300;
            box-shadow: 0 0 20px rgba(255, 179, 0, 0.15);
        }

        .form-input:focus + i,
        .input-wrapper:focus-within i {
            color: #ffb300;
        }

        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 5px;
            transition: color 0.3s;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
        }

        .password-toggle:hover {
            color: #ffb300;
        }

        /* Remember & Forgot */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            display: none;
        }

        .checkmark {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .checkmark i {
            font-size: 12px;
            color: #111;
            opacity: 0;
            transform: scale(0);
            transition: all 0.2s;
        }

        .remember-me input:checked + .checkmark {
            background: #ffb300;
            border-color: #ffb300;
        }

        .remember-me input:checked + .checkmark i {
            opacity: 1;
            transform: scale(1);
        }

        .remember-me span {
            color: #888;
            font-size: 14px;
        }

        .forgot-link {
            color: #ffb300;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .forgot-link:hover {
            color: #ffc107;
            text-decoration: underline;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            height: 56px;
            background: linear-gradient(135deg, #ffb300 0%, #ff8800 100%);
            border: none;
            border-radius: 14px;
            color: #111;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 179, 0, 0.4);
        }

        .btn-submit:hover::before {
            left: 100%;
        }

        .btn-submit:active {
            transform: translateY(-1px);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            gap: 15px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }

        .divider span {
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
        }

        /* Social Login */
        .social-login {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .social-btn {
            flex: 1;
            height: 50px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            background: transparent;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .social-btn:hover {
            border-color: rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.05);
            transform: translateY(-2px);
        }

        .social-btn.google:hover { border-color: #ea4335; color: #ea4335; }
        .social-btn.facebook:hover { border-color: #1877f2; color: #1877f2; }
        .social-btn.discord:hover { border-color: #5865f2; color: #5865f2; }

        /* Register Link */
        .register-link {
            text-align: center;
            color: #888;
            font-size: 15px;
        }

        .register-link a {
            color: #ffb300;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .register-link a:hover {
            color: #ffc107;
            text-decoration: underline;
        }

        /* Error Alert */
        .alert-error {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        .alert-error i {
            color: #dc3545;
            font-size: 20px;
        }

        .alert-error span {
            color: #ff6b6b;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 450px;
            }

            .login-brand {
                padding: 40px 30px;
            }

            .brand-features {
                display: none;
            }

            .login-form-panel {
                padding: 40px 30px;
            }

            .social-login {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<!-- Floating Particles -->
<div class="particles">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
</div>

<?php include 'header.php'; ?>

<div class="main-content">
    <div class="login-container">
        <!-- Left Panel - Branding -->
        <div class="login-brand">
            <div class="brand-content">
                <div class="brand-logo">
                    <i class="fas fa-gamepad"></i>
                </div>
                <h1 class="brand-title">GameBat</h1>
                <p class="brand-subtitle">Cộng đồng game thủ lớn nhất Việt Nam. Khám phá, chia sẻ và kết nối!</p>
                
                <div class="brand-features">
                    <div class="brand-feature">
                        <i class="fas fa-check"></i>
                        <span>Tin tức game mới nhất</span>
                    </div>
                    <div class="brand-feature">
                        <i class="fas fa-check"></i>
                        <span>Đánh giá chuyên sâu</span>
                    </div>
                    <div class="brand-feature">
                        <i class="fas fa-check"></i>
                        <span>Cộng đồng sôi động</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Form -->
        <div class="login-form-panel">
            <div class="form-header">
                <h2>Chào mừng trở lại!</h2>
                <p>Đăng nhập để tiếp tục trải nghiệm</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="input-group-custom">
                    <label>Tên đăng nhập</label>
                    <div class="input-wrapper">
                        <input type="text" name="username" class="form-input" placeholder="Nhập tên đăng nhập" required>
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <div class="input-group-custom">
                    <label>Mật khẩu</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" class="form-input" placeholder="Nhập mật khẩu" required>
                        <i class="fas fa-lock"></i>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span class="checkmark"><i class="fas fa-check"></i></span>
                        <span>Ghi nhớ đăng nhập</span>
                    </label>
                    <a href="#" class="forgot-link">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-sign-in-alt me-2"></i> Đăng nhập
                </button>
            </form>

            <div class="divider">
                <span>hoặc đăng nhập với</span>
            </div>

            <div class="social-login">
                <button type="button" class="social-btn google">
                    <i class="fab fa-google"></i>
                </button>
                <button type="button" class="social-btn facebook">
                    <i class="fab fa-facebook-f"></i>
                </button>
                <button type="button" class="social-btn discord">
                    <i class="fab fa-discord"></i>
                </button>
            </div>

            <p class="register-link">
                Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    // Add input animation
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
</script>

</body>
</html>
