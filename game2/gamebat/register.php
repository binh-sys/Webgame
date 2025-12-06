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

    if ($username === '' || $display_name === '' || $email === '' || $password === '') {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } elseif ($password !== $confirm) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } else {
        $check = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $check->bind_param('ss', $username, $email);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $error = 'Tên đăng nhập hoặc email đã tồn tại.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $default_avatar = 'default.png';

            $sql = $conn->prepare("INSERT INTO users (username, display_name, email, password, role, avatar, created_at) VALUES (?, ?, ?, ?, 'user', ?, NOW())");
            $sql->bind_param('sssss', $username, $display_name, $email, $hash, $default_avatar);

            if ($sql->execute()) {
                $success = 'Đăng ký thành công! <a href="login.php" class="success-link">Đăng nhập ngay</a>';
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
    <title>Đăng Ký - GameBat</title>
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
                radial-gradient(circle at 20% 80%, rgba(0, 195, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(0, 170, 255, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(0, 220, 255, 0.05) 0%, transparent 40%);
            z-index: 0;
            animation: bgPulse 8s ease-in-out infinite;
        }

        @keyframes bgPulse {
            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
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
            background: rgba(0, 195, 255, 0.6);
            border-radius: 50%;
            animation: float 15s infinite;
        }

        .particle:nth-child(1) {
            left: 10%;
            animation-delay: 0s;
            animation-duration: 12s;
        }

        .particle:nth-child(2) {
            left: 20%;
            animation-delay: 2s;
            animation-duration: 14s;
        }

        .particle:nth-child(3) {
            left: 30%;
            animation-delay: 4s;
            animation-duration: 11s;
        }

        .particle:nth-child(4) {
            left: 40%;
            animation-delay: 1s;
            animation-duration: 16s;
        }

        .particle:nth-child(5) {
            left: 50%;
            animation-delay: 3s;
            animation-duration: 13s;
        }

        .particle:nth-child(6) {
            left: 60%;
            animation-delay: 5s;
            animation-duration: 15s;
        }

        .particle:nth-child(7) {
            left: 70%;
            animation-delay: 2s;
            animation-duration: 12s;
        }

        .particle:nth-child(8) {
            left: 80%;
            animation-delay: 4s;
            animation-duration: 14s;
        }

        .particle:nth-child(9) {
            left: 90%;
            animation-delay: 1s;
            animation-duration: 11s;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(-100vh) scale(1);
                opacity: 0;
            }
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

        /* Register Container */
        .register-container {
            display: flex;
            max-width: 900px;
            width: 100%;
            background: rgba(15, 15, 25, 0.95);
            border-radius: 24px;
            overflow: hidden;
            box-shadow:
                0 25px 50px rgba(0, 0, 0, 0.5),
                0 0 100px rgba(0, 195, 255, 0.1),
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
        .register-brand {
            flex: 1;
            background: linear-gradient(135deg, #00aaff 0%, #00c3ff 50%, #00dcff 100%);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .register-brand::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 60%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
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
        .register-form-panel {
            flex: 1;
            padding: 50px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 30px;
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
            margin-bottom: 20px;
        }

        .input-group-custom label {
            display: block;
            color: #aaa;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 16px;
            transition: color 0.3s;
            z-index: 2;
        }

        .form-input {
            width: 100%;
            height: 52px;
            padding: 0 50px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input::placeholder {
            color: #555;
        }

        .form-input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #00c3ff;
            box-shadow: 0 0 20px rgba(0, 195, 255, 0.15);
        }

        .form-input:focus+i.input-icon,
        .input-wrapper:focus-within i.input-icon {
            color: #00c3ff;
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
            color: #00c3ff;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            height: 54px;
            background: linear-gradient(135deg, #00c3ff 0%, #00aaff 100%);
            border: none;
            border-radius: 12px;
            color: #111;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 195, 255, 0.4);
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
            margin: 25px 0;
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
            margin-bottom: 25px;
        }

        .social-btn {
            flex: 1;
            height: 48px;
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

        .social-btn.google:hover {
            border-color: #ea4335;
            color: #ea4335;
        }

        .social-btn.facebook:hover {
            border-color: #1877f2;
            color: #1877f2;
        }

        .social-btn.discord:hover {
            border-color: #5865f2;
            color: #5865f2;
        }

        /* Login Link */
        .login-link {
            text-align: center;
            color: #888;
            font-size: 15px;
        }

        .login-link a {
            color: #00c3ff;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .login-link a:hover {
            color: #33d7ff;
            text-decoration: underline;
        }

        /* Error & Success Alert */
        .alert-error {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%,
            100% {
                transform: translateX(0);
            }

            20%,
            60% {
                transform: translateX(-5px);
            }

            40%,
            80% {
                transform: translateX(5px);
            }
        }

        .alert-error i {
            color: #dc3545;
            font-size: 20px;
        }

        .alert-error span {
            color: #ff6b6b;
            font-size: 14px;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.15);
            border: 1px solid rgba(40, 167, 69, 0.3);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideUp 0.5s ease;
        }

        .alert-success i {
            color: #28a745;
            font-size: 20px;
        }

        .alert-success span {
            color: #66ff8c;
            font-size: 14px;
        }

        .success-link {
            color: #00c3ff !important;
            font-weight: 600;
        }

        /* Password Strength */
        .password-strength {
            margin-top: 8px;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
            border-radius: 2px;
        }

        .password-strength-bar.weak {
            width: 33%;
            background: #dc3545;
        }

        .password-strength-bar.medium {
            width: 66%;
            background: #ffc107;
        }

        .password-strength-bar.strong {
            width: 100%;
            background: #28a745;
        }

        .password-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .register-container {
                flex-direction: column;
                max-width: 450px;
            }

            .register-brand {
                padding: 40px 30px;
            }

            .brand-features {
                display: none;
            }

            .register-form-panel {
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
        <div class="register-container">
            <!-- Left Panel - Branding -->
            <div class="register-brand">
                <div class="brand-content">
                    <div class="brand-logo">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h1 class="brand-title">Tham Gia</h1>
                    <p class="brand-subtitle">Tạo tài khoản để trở thành thành viên của cộng đồng game thủ lớn nhất!</p>

                    <div class="brand-features">
                        <div class="brand-feature">
                            <i class="fas fa-check"></i>
                            <span>Bình luận & thảo luận</span>
                        </div>
                        <div class="brand-feature">
                            <i class="fas fa-check"></i>
                            <span>Lưu bài viết yêu thích</span>
                        </div>
                        <div class="brand-feature">
                            <i class="fas fa-check"></i>
                            <span>Nhận thông báo mới</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Form -->
            <div class="register-form-panel">
                <div class="form-header">
                    <h2>Tạo tài khoản mới</h2>
                    <p>Điền thông tin để đăng ký</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" id="registerForm">
                    <div class="input-group-custom">
                        <label>Tên đăng nhập</label>
                        <div class="input-wrapper">
                            <input type="text" name="username" class="form-input" placeholder="Nhập tên đăng nhập" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                            <i class="fas fa-user input-icon"></i>
                        </div>
                    </div>

                    <div class="input-group-custom">
                        <label>Tên hiển thị</label>
                        <div class="input-wrapper">
                            <input type="text" name="display_name" class="form-input" placeholder="Tên hiển thị công khai" required value="<?= isset($_POST['display_name']) ? htmlspecialchars($_POST['display_name']) : '' ?>">
                            <i class="fas fa-id-badge input-icon"></i>
                        </div>
                    </div>

                    <div class="input-group-custom">
                        <label>Email</label>
                        <div class="input-wrapper">
                            <input type="email" name="email" class="form-input" placeholder="example@email.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <div class="input-group-custom">
                        <label>Mật khẩu</label>
                        <div class="input-wrapper">
                            <input type="password" name="password" id="password" class="form-input" placeholder="Ít nhất 6 ký tự" required>
                            <i class="fas fa-lock input-icon"></i>
                            <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                                <i class="fas fa-eye" id="toggleIcon1"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                        <p class="password-hint" id="strengthText"></p>
                    </div>

                    <div class="input-group-custom">
                        <label>Xác nhận mật khẩu</label>
                        <div class="input-wrapper">
                            <input type="password" name="confirm" id="confirm" class="form-input" placeholder="Nhập lại mật khẩu" required>
                            <i class="fas fa-shield-alt input-icon"></i>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm', 'toggleIcon2')">
                                <i class="fas fa-eye" id="toggleIcon2"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-user-plus me-2"></i> Đăng ký
                    </button>
                </form>

                <div class="divider">
                    <span>hoặc đăng ký với</span>
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

                <p class="login-link">
                    Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
                </p>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);

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

        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            strengthBar.className = 'password-strength-bar';

            if (password.length === 0) {
                strengthText.textContent = '';
            } else if (strength <= 1) {
                strengthBar.classList.add('weak');
                strengthText.textContent = 'Mật khẩu yếu';
                strengthText.style.color = '#dc3545';
            } else if (strength <= 2) {
                strengthBar.classList.add('medium');
                strengthText.textContent = 'Mật khẩu trung bình';
                strengthText.style.color = '#ffc107';
            } else {
                strengthBar.classList.add('strong');
                strengthText.textContent = 'Mật khẩu mạnh';
                strengthText.style.color = '#28a745';
            }
        });

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
